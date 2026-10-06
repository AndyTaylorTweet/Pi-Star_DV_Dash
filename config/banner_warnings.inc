<?php
/**
 * Single source of truth for site-wide warning banners and the
 * default-password forced-redirect.
 *
 * Two public entry points:
 *
 *   pistar_warnings_enforce_redirect()
 *       Call ONCE near the top of every authenticated page, BEFORE
 *       any output. Issues a 302 + exit if the operator is on the
 *       default password and connecting from a remote subnet. Wired
 *       to /admin/* URLs only — the public root `/` is unauthenticated
 *       and we deliberately do NOT advertise default-password state
 *       to anonymous visitors.
 *
 *   pistar_warnings_render()
 *       Call from the body to emit any/all of the seven warning
 *       banners that currently apply. Renders nothing on the public
 *       root (warnings target the operator, not the public).
 *
 * Banners managed here (consolidated from index.php and configure.php):
 *   1. Pi-Star outdated (<4.1 on RPi)                    yellow
 *   2. Upgrade available 4.1.0..<4.1.13                  yellow
 *   3. Upgrade available 4.2.0..<4.2.6                   yellow
 *   4. Upgrade available 4.3.0..<4.3.9                   yellow
 *   5. DMR public mode without ACL (loop risk)           yellow
 *   6. BM API v1 key still in use                        red
 *   7. Default basic-auth password ('raspberry')         red
 *
 * Layer 2 (forced redirect) precedence — most → least specific:
 *   - Setup-not-done flow in index.php takes priority. Redirect is
 *     suppressed when neither /etc/dstar-radio.{mmdvmhost,dstarrepeater}
 *     marker exists, so the existing "No Mode Defined" 10-second
 *     redirect to configure.php remains the dominant path.
 *   - Default password + remote client → 302 to /admin/change_password_required.php.
 *   - Default password + local client → red banner only (no redirect).
 *   - Custom password → no warning, no redirect.
 *
 * Helpers (private; underscore prefix):
 *   _pistar_banner_emit()             — render one banner row
 *   _pistar_get_basic_auth()          — extract user/password from request
 *   _pistar_default_password_in_use() — true iff pi-star user using 'raspberry'
 *   _pistar_local_subnets()           — Pi's interface CIDRs (IPv4 + IPv6)
 *   _pistar_ip_in_subnet()            — bitmask compare, family-aware
 *   _pistar_remote_is_local()         — REMOTE_ADDR ∈ any local subnet
 */

/**
 * Render a single banner row.
 *
 * Matches the inline-styled <div><table> shape used throughout the
 * dashboard (no CSS class — same convention as the existing banners
 * in index.php / configure.php this file replaces).
 *
 * @param string $html  Banner body. INTERPOLATED RAW — callers must
 *                      ensure any dynamic content is already escaped
 *                      (none of the current banners include dynamic
 *                      content, so this is a non-issue today; flagged
 *                      for any future caller).
 * @param string $level 'warn' (yellow) or 'danger' (red).
 *
 * @return void
 */
function _pistar_banner_emit($html, $level = 'warn')
{
    $style = ($level === 'danger')
        ? 'background-color: #ff9090; color: #f01010;'
        : 'background-color: #ffff90; color: #906000;';
    echo '<div>' . "\n";
    echo '  <table align="center" width="760px" style="margin: 0px 0px 10px 0px; width: 100%;">' . "\n";
    echo '    <tr>' . "\n";
    echo '    <td align="center" valign="top" style="' . $style . '">' . $html . '</td>' . "\n";
    echo '    </tr>' . "\n";
    echo '  </table>' . "\n";
    echo '</div>' . "\n";
}

/**
 * Extract the HTTP Basic auth user + password from the current request.
 *
 * On Pi-Star's stock nginx + PHP-FPM config, $_SERVER['PHP_AUTH_PW']
 * is populated automatically (PHP synthesises it from the forwarded
 * Authorization header). The HTTP_AUTHORIZATION fallback handles
 * unusual setups where only the raw header survives.
 *
 * Note: the REMOTE_USER fallback only sets $user — that var contains
 * the authenticated username but never the password. Callers that need
 * the password (default-password check, etc.) treat empty $pw as
 * "unable to verify" and fail closed.
 *
 * @return array{0: string, 1: string} [$user, $password]; either or
 *                                     both may be '' when basic auth
 *                                     hasn't happened (e.g. on the
 *                                     unauthenticated public root).
 */
function _pistar_get_basic_auth()
{
    $user = isset($_SERVER['PHP_AUTH_USER']) ? (string)$_SERVER['PHP_AUTH_USER'] : '';
    $pw   = isset($_SERVER['PHP_AUTH_PW'])   ? (string)$_SERVER['PHP_AUTH_PW']   : '';
    if ($user !== '' && $pw !== '') {
        return array($user, $pw);
    }
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])
            && stripos($_SERVER['HTTP_AUTHORIZATION'], 'Basic ') === 0) {
        $decoded = base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6), true);
        if ($decoded !== false && strpos($decoded, ':') !== false) {
            list($user, $pw) = explode(':', $decoded, 2);
            return array($user, $pw);
        }
    }
    if ($user === '' && !empty($_SERVER['REMOTE_USER'])) {
        $user = (string)$_SERVER['REMOTE_USER'];
    }
    return array($user, $pw);
}

/**
 * True iff the operator is the canonical 'pi-star' account AND
 * still using the factory default password 'raspberry'.
 *
 * Account scope is deliberately narrow: the requirement is to
 * protect against drive-by auth using the well-known stock
 * credentials. Operators who created their own usernames are
 * out of scope (they made an active choice).
 *
 * Constant-time compare via hash_equals — defence in depth; the
 * default password is publicly documented so timing isn't really
 * an oracle, but it's the right idiom for password compares.
 *
 * @return bool
 */
function _pistar_default_password_in_use()
{
    list($user, $pw) = _pistar_get_basic_auth();
    if ($user !== 'pi-star' || $pw === '') {
        return false;
    }
    return hash_equals('raspberry', $pw);
}

/**
 * The Pi's local interface subnets (IPv4 + IPv6).
 *
 * Read once per request from `ip -j addr show` (iproute2 JSON output;
 * always present on Pi-Star). Cached in a static for repeated calls.
 *
 * @return array<int, array{ip:string, prefix:int}>
 */
function _pistar_local_subnets()
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $cached = array();

    $out = array();
    $rc  = 1;
    exec('ip -j addr show 2>/dev/null', $out, $rc);
    if ($rc !== 0 || empty($out)) {
        return $cached;
    }
    $ifaces = json_decode(implode('', $out), true);
    if (!is_array($ifaces)) {
        return $cached;
    }
    foreach ($ifaces as $iface) {
        if (empty($iface['addr_info']) || !is_array($iface['addr_info'])) {
            continue;
        }
        foreach ($iface['addr_info'] as $a) {
            if (empty($a['local']) || !isset($a['prefixlen'])) {
                continue;
            }
            $cached[] = array(
                'ip'     => (string)$a['local'],
                'prefix' => (int)$a['prefixlen'],
            );
        }
    }
    return $cached;
}

/**
 * Family-aware bitmask subnet membership test.
 *
 * Handles IPv4, IPv6, and the IPv4-mapped IPv6 form (`::ffff:a.b.c.d`)
 * that nginx may surface in REMOTE_ADDR when the listener is dual-stack.
 *
 * @param string $remote The address to test.
 * @param string $netIp  The subnet's network/anchor address.
 * @param int    $prefix Prefix length in bits (0..32 for IPv4, 0..128 for IPv6).
 *
 * @return bool
 */
function _pistar_ip_in_subnet($remote, $netIp, $prefix)
{
    $remoteBin = @inet_pton($remote);
    $netBin    = @inet_pton($netIp);
    if ($remoteBin === false || $netBin === false) {
        return false;
    }
    // Handle v4-mapped v6 (::ffff:1.2.3.4) when comparing against a
    // bare v4 subnet — strip the 12-byte v4-mapped prefix. Mapping is
    // one-directional by design: nginx surfaces REMOTE_ADDR in mapped
    // form on dual-stack listeners while `ip -j addr show` reports the
    // interface as bare v4, so the asymmetric strip is what the real
    // topology produces. The reverse case (bare v4 client, mapped
    // subnet) cannot occur in practice and falls through to "not a
    // match" below.
    if (strlen($remoteBin) === 16 && strlen($netBin) === 4
            && substr($remoteBin, 0, 12) === "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff") {
        $remoteBin = substr($remoteBin, 12);
    }
    if (strlen($remoteBin) !== strlen($netBin)) {
        // Different families and no compatible mapping — not in subnet.
        return false;
    }
    $bits = strlen($remoteBin) * 8;
    if ($prefix < 0 || $prefix > $bits) {
        return false;
    }
    $bytesFull = intdiv($prefix, 8);
    $bitsExtra = $prefix % 8;
    if ($bytesFull > 0
            && substr($remoteBin, 0, $bytesFull) !== substr($netBin, 0, $bytesFull)) {
        return false;
    }
    if ($bitsExtra > 0) {
        $mask = chr((0xFF << (8 - $bitsExtra)) & 0xFF);
        if ((substr($remoteBin, $bytesFull, 1) & $mask)
                !== (substr($netBin, $bytesFull, 1) & $mask)) {
            return false;
        }
    }
    return true;
}

/**
 * Is REMOTE_ADDR in the same L3 subnet as ANY of the Pi's interfaces?
 *
 * Loopback (127.0.0.0/8 on v4, ::1/128 on v6) is treated as local —
 * those addresses appear when an admin page is hit via the device
 * itself or via a CLI test harness on the Pi.
 *
 * @return bool
 */
function _pistar_remote_is_local()
{
    $remote = isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : '';
    if ($remote === '') {
        return false;
    }
    foreach (_pistar_local_subnets() as $sub) {
        if (_pistar_ip_in_subnet($remote, $sub['ip'], $sub['prefix'])) {
            return true;
        }
    }
    return false;
}

/**
 * Layer 2: redirect to the forced-change page when default password +
 * remote client. Must be called BEFORE any output so header() works.
 *
 * Skips:
 *   - Public (non-/admin/) URLs — anonymous visitors don't get told
 *     the device is on default credentials.
 *   - The /admin/change_password_required.php page itself — would
 *     loop.
 *   - Pages reached during the setup-not-done flow (no mode marker
 *     file yet) — let the existing "No Mode Defined" handler in
 *     index.php drive the operator through configure.php first.
 *
 * @return void
 */
function pistar_warnings_enforce_redirect()
{
    $self = isset($_SERVER['PHP_SELF']) ? (string)$_SERVER['PHP_SELF'] : '';
    if (strpos($self, '/admin/') !== 0) {
        return;
    }
    if ($self === '/admin/change_password_required.php') {
        return;
    }
    // Long-running operational tools — let the operator finish what
    // they're doing. The Layer-1 banner still appears at the top of
    // these pages so the warning isn't suppressed; only the redirect
    // is. update.php and expert/upgrade.php both poll a `?ajax`
    // log-tail every 1 s; redirecting on those polls causes jQuery
    // to follow the 302 and append the full change-password HTML
    // into the page's #tail div, "flooding" the operator with copies
    // of the change-password form.
    if ($self === '/admin/update.php'
            || $self === '/admin/expert/upgrade.php') {
        return;
    }
    // Defence in depth: any GET that includes ?ajax skips the redirect.
    // Catches AJAX log-tail / status-poll endpoints elsewhere in the
    // dashboard (live_modem_log.php, expert/jitter_test.php, etc.) so
    // a future poll-style page can't accidentally regress the same way.
    // The initial GET that loaded the parent page already carried the
    // banner — defaulting AJAX to "no redirect" is safe.
    if (isset($_GET['ajax'])) {
        return;
    }
    if (!file_exists('/etc/dstar-radio.mmdvmhost')
            && !file_exists('/etc/dstar-radio.dstarrepeater')) {
        return;
    }
    if (!_pistar_default_password_in_use()) {
        return;
    }
    if (_pistar_remote_is_local()) {
        return;
    }
    header('Location: /admin/change_password_required.php', true, 302);
    exit;
}

/**
 * Render every applicable warning banner. Admin URLs only.
 *
 * Adding a new banner here? The body string is interpolated into the
 * <td> raw — pre-escape any dynamic content with htmlspecialchars()
 * before passing it to _pistar_banner_emit(). Today's seven banners
 * are all string literals so the issue does not arise.
 *
 * @return void
 */
function pistar_warnings_render()
{
    $self = isset($_SERVER['PHP_SELF']) ? (string)$_SERVER['PHP_SELF'] : '';
    if (strpos($self, '/admin/') !== 0) {
        return;
    }

    // Default-password banner FIRST — highest severity, most actionable.
    if (_pistar_default_password_in_use()) {
        _pistar_banner_emit(
            '<b>Security Alert:</b> Your dashboard is using the default password. '
            . 'Please <a href="/admin/configure.php" style="color: #f01010;"><b>change it now</b></a> '
            . 'from the Configuration page.',
            'danger'
        );
    }

    // Pi-Star release-derived banners. parse_ini_file() once per request.
    $release  = @parse_ini_file('/etc/pistar-release', true);
    $version  = is_array($release) && isset($release['Pi-Star']['Version'])
                ? (string)$release['Pi-Star']['Version']
                : '';
    $hardware = is_array($release) && isset($release['Pi-Star']['Hardware'])
                ? (string)$release['Pi-Star']['Hardware']
                : '';

    if ($version !== '' && $hardware === 'RPi'
            && version_compare($version, '4.1', '<')) {
        _pistar_banner_emit(
            'Alert: You are running an outdated version of Pi-Star, please upgrade.<br />'
            . 'New versions are available from here: '
            . '<a href="http://www.pistar.uk/downloads/" alt="Pi-Star Downloads">http://www.pistar.uk/downloads/</a>.',
            'warn'
        );
    }
    if ($version !== ''
            && version_compare($version, '4.1.0', '>=')
            && version_compare($version, '4.1.13', '<')) {
        _pistar_banner_emit(
            'Alert: An upgrade to Pi-Star has been released, click here to upgrade now: '
            . '<a href="/admin/expert/upgrade.php" alt="Upgrade Pi-Star">Upgrade Pi-Star</a>.',
            'warn'
        );
    }
    if ($version !== ''
            && version_compare($version, '4.2.0', '>=')
            && version_compare($version, '4.2.6', '<')) {
        _pistar_banner_emit(
            'Alert: An upgrade to Pi-Star has been released, click here to upgrade now: '
            . '<a href="/admin/expert/upgrade.php" alt="Upgrade Pi-Star">Upgrade Pi-Star</a>.',
            'warn'
        );
    }
    // Ceiling is the 4.3.x ladder's terminal version in pistar-upgrade, the
    // same convention the 4.1.x and 4.2.x banners above follow. Bump this
    // whenever a new arm goes live, or devices on the previous release are
    // never told an upgrade exists.
    if ($version !== ''
            && version_compare($version, '4.3.0', '>=')
            && version_compare($version, '4.3.9', '<')) {
        _pistar_banner_emit(
            'Alert: An upgrade to Pi-Star has been released, click here to upgrade now: '
            . '<a href="/admin/expert/upgrade.php" alt="Upgrade Pi-Star">Upgrade Pi-Star</a>.',
            'warn'
        );
    }

    // DMR public mode without ACL (loop risk). Was configure.php-only;
    // now appears across the admin surface so the operator sees it
    // regardless of which page they land on first.
    if (file_exists('/etc/dstar-radio.mmdvmhost')) {
        $mmdvm = @parse_ini_file('/etc/mmdvmhost', true);
        if (is_array($mmdvm)
                && isset($mmdvm['DMR']['Enable']) && $mmdvm['DMR']['Enable'] == 1
                && isset($mmdvm['DMR']['SelfOnly']) && $mmdvm['DMR']['SelfOnly'] == 0
                && isset($mmdvm['General']['Id'])
                && strlen((string)$mmdvm['General']['Id']) >= 7
                && !isset($mmdvm['DMR']['WhiteList'])) {
            _pistar_banner_emit(
                'Alert: You are running a hotspot in public mode without an access list for DMR, '
                . 'this setup *could* participate in network loops!',
                'warn'
            );
        }
    }

    // BM API v1 key (length-based heuristic, kept identical to the
    // configure.php trigger this consolidates).
    $bmKey = '/etc/bmapi.key';
    if (file_exists($bmKey)) {
        $bm = @parse_ini_file($bmKey, true);
        if (is_array($bm) && !empty($bm['key']['apikey'])
                && strlen((string)$bm['key']['apikey']) <= 200) {
            _pistar_banner_emit(
                'Alert: You have a BM API v1 Key, click here for the announcement: '
                . '<a href="https://news.brandmeister.network/introducing-user-api-keys/" alt="BM API Keys">BM API Keys - Announcement</a>.',
                'danger'
            );
        }
    }
}
