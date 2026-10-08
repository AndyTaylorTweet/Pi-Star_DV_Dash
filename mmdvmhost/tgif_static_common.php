<?php
/**
 * Shared helpers for the TGIF Static Talkgroups feature.
 *
 * Included by both halves of the pair, which mirrors the BrandMeister
 * split: tgif_static_links.php is the AJAX-loaded status partial (it makes
 * the read-only API call), and tgif_static_manager.php is the inline
 * add/remove form (it calls the API only on an operator POST).
 *
 * The bearer credential lives in /etc/tgifapi.key, mode 600
 * www-data:www-data, in the same [key]/apikey ini shape as /etc/bmapi.key.
 * It is set in Expert > API Keys and is never emitted into HTML.
 */

/**
 * Work out the TGIF DMR ID for this hotspot, or "" if TGIF is not in use.
 *
 * Mirrors bm_manager.php's approach of deriving the ID from the live
 * configuration rather than storing a copy: when MMDVMHost points at
 * 127.0.0.1 the master is DMRGateway, so each of its five network slots is
 * checked for an enabled tgif.network entry; otherwise MMDVMHost talks to
 * TGIF directly and its own DMR / General Id applies.
 *
 * @param array $mmdvmconfigs Slurped /etc/mmdvmhost, as getMMDVMConfig().
 * @return string Six-to-nine digit DMR ID, or "" when TGIF is not active.
 */
function tgif_static_detect_dmr_id($mmdvmconfigs)
{
    $dmrID = "";
    if (getConfigItem("DMR", "Enable", $mmdvmconfigs) != 1) {
        return $dmrID;
    }

    $dmrGatewayConfigFile = '/etc/dmrgateway';
    $configdmrgateway = array();
    if (is_readable($dmrGatewayConfigFile)) {
        $parsed = parse_ini_file($dmrGatewayConfigFile, true);
        if (is_array($parsed)) {
            $configdmrgateway = $parsed;
        }
    }

    $dmrMasterHost = getConfigItem("DMR Network", "Address", $mmdvmconfigs);
    if ($dmrMasterHost == '127.0.0.1') {
        for ($network = 1; $network <= 5; $network++) {
            $section = 'DMR Network '.$network;
            if (!isset($configdmrgateway[$section])) {
                continue;
            }
            $cfg = $configdmrgateway[$section];
            if ((isset($cfg['Address']) ? $cfg['Address'] : '') == 'tgif.network'
                && !empty($cfg['Enabled'])
                && isset($cfg['Id'])) {
                $dmrID = preg_replace('/[^0-9]/', '', (string)$cfg['Id']);
            }
        }
    } elseif ($dmrMasterHost == 'tgif.network') {
        $candidate = getConfigItem("DMR", "Id", $mmdvmconfigs);
        if (!$candidate) {
            $candidate = getConfigItem("General", "Id", $mmdvmconfigs);
        }
        $dmrID = preg_replace('/[^0-9]/', '', (string)$candidate);
    }

    if (!preg_match('/^[0-9]{6,9}$/', $dmrID)) {
        return "";
    }
    return $dmrID;
}

/**
 * Read the API token from /etc/tgifapi.key.
 *
 * parse_ini_file() handles the [key]/apikey shape and any quoting, exactly
 * as bm_manager.php reads /etc/bmapi.key — no hand-rolled line parser. A
 * cleared file (apikey=None) or any malformed value reads as "".
 *
 * @param string $path Absolute path to the credential file.
 * @return string The token, or "" when absent, cleared or malformed.
 */
function tgif_static_read_token($path)
{
    if (!is_readable($path)) {
        return "";
    }
    $parsed = @parse_ini_file($path, true);
    if (!is_array($parsed) || !isset($parsed['key']['apikey'])) {
        return "";
    }
    $value = trim((string)$parsed['key']['apikey']);
    if (preg_match('/^TGIFSTG1\.[A-Fa-f0-9]{16}\.[A-Fa-f0-9]{16}\.[A-Za-z0-9_-]{40,64}$/', $value)) {
        return $value;
    }
    return "";
}

/**
 * Decide whether the static TG feature should appear at all, and gather the
 * two values both halves need.
 *
 * Single source of truth so the status partial and the manager form can
 * never disagree about whether to render. Three conditions:
 *  - the release is 4.3.9 or later. The token can only be set in
 *    Expert > API Keys, which needs the /etc/tgifapi.key entry in
 *    /etc/sudoers.d/pistar-dashboard, and that is only guaranteed from
 *    4.3.9. version_compare() is used so 4.3.10 sorts above 4.3.9.
 *  - TGIF is a live DMR network on this hotspot.
 *  - a valid token is stored.
 *
 * @param array $mmdvmconfigs Slurped /etc/mmdvmhost.
 * @return array|false array('dmrID' => string, 'token' => string), or false
 *                     when the feature should not be shown.
 */
function tgif_static_context($mmdvmconfigs)
{
    $release = '';
    $configPistarRelease = @parse_ini_file('/etc/pistar-release', true);
    if (is_array($configPistarRelease) && isset($configPistarRelease['Pi-Star']['Version'])) {
        $release = (string)$configPistarRelease['Pi-Star']['Version'];
    }
    if ($release === '' || version_compare($release, '4.3.9', '<')) {
        return false;
    }

    $dmrID = tgif_static_detect_dmr_id($mmdvmconfigs);
    if ($dmrID === '') {
        return false;
    }

    $token = tgif_static_read_token('/etc/tgifapi.key');
    if ($token === '') {
        return false;
    }

    return array('dmrID' => $dmrID, 'token' => $token);
}

/**
 * Render the "Active TGIF Connections" table.
 *
 * Shared so index.php can paint the table immediately with placeholder TG
 * cells and tgif_static_links.php can repaint the identical markup with real
 * values. Keeping one copy of the markup is what stops the AJAX fill causing
 * a layout jump: only the two TG cells change, the table shape does not.
 *
 * $staticList / $dynamicList are emitted unescaped because they carry
 * wordwrap-injected <br /> tags by design; every talkgroup and slot inside
 * them is cast to (int) by the caller, the same reasoning as bm_links.php.
 * Callers must not pass unsanitised API text.
 *
 * @param string $dmrID       Hotspot DMR ID / ESSID.
 * @param string $staticList  Pre-formatted static TG cell contents.
 * @param string $dynamicList Pre-formatted dynamic TG cell contents.
 * @return void
 */
function tgif_render_connections_table($dmrID, $staticList, $dynamicList)
{
    echo '<b>Active TGIF Connections</b>
    <table>
      <tr>
        <th><a class=tooltip href="#">TGIF Master<span><b>Connected Master</b></span></a></th>
        <th><a class=tooltip href="#">Repeater ID<span><b>The ID for this Repeater/Hotspot</b></span></a></th>
        <th><a class=tooltip href="#">Static TGs<span><b>Statically linked talkgroups</b></span></a></th>
        <th><a class=tooltip href="#">Dynamic TGs<span><b>Dynamically linked talkgroups</b></span></a></th>
      </tr>'."\n";
    echo '    <tr>'."\n";
    echo '      <td>tgif.network</td>';
    echo '<td>'.htmlspecialchars((string)$dmrID, ENT_QUOTES, 'UTF-8').'</td>';
    echo '<td>'.$staticList.'</td>';
    echo '<td>'.$dynamicList.'</td>';
    echo '</tr>'."\n";
    echo '  </table>'."\n";
    echo '  <br />'."\n";
}

/**
 * Call the TGIF talkgroup API.
 *
 * One request helper for both resources — 'static-talkgroups' and
 * 'dynamic-talkgroups' differ only in the path, so they share the auth,
 * TLS and timeout handling rather than each carrying a copy of it.
 *
 * The 5 second timeout bounds both the connect and read phases for the http
 * stream wrapper, so a slow or blackholed endpoint cannot stall the caller
 * indefinitely.
 *
 * @param string     $token    Bearer credential.
 * @param string     $dmrID    Hotspot DMR ID, used as the resource path.
 * @param string     $resource 'static-talkgroups' or 'dynamic-talkgroups'.
 * @param string     $method   GET, POST or DELETE.
 * @param array|null $body     Optional JSON request body.
 * @return array array('status' => int HTTP status (0 on no response),
 *               'json' => array decoded response, empty on failure).
 */
function tgif_api_request($token, $dmrID, $resource, $method, $body = null)
{
    $url = 'https://api.tgif.network/v1/' . $resource . '/' . rawurlencode($dmrID);
    $headers = array(
        'Accept: application/json',
        'Authorization: Bearer ' . $token,
        'User-Agent: Pi-Star TGIF TG Manager/' . $dmrID,
    );

    $http = array(
        'method' => $method,
        'timeout' => 5,
        'ignore_errors' => true,
        'follow_location' => 0,
        'max_redirects' => 0,
    );

    if ($body !== null) {
        $json = json_encode($body);
        if (!is_string($json)) {
            return array('status' => 0, 'json' => array('error' => 'json_encode_failed'));
        }
        $headers[] = 'Content-Type: application/json';
        $http['content'] = $json;
    }

    $http['header'] = implode("\r\n", $headers) . "\r\n";

    $context = stream_context_create(array(
        'http' => $http,
        'ssl' => array(
            'verify_peer' => true,
            'verify_peer_name' => true,
            'allow_self_signed' => false,
            'cafile' => '/etc/ssl/certs/ca-certificates.crt',
            'SNI_enabled' => true,
        ),
    ));

    $result = @file_get_contents($url, false, $context);
    $status = 0;
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $responseHeader) {
            if (preg_match('#^HTTP/\S+\s+([0-9]{3})\b#', $responseHeader, $matches)) {
                $status = (int)$matches[1];
                break;
            }
        }
    }

    $decoded = is_string($result) ? json_decode($result, true) : null;
    return array(
        'status' => $status,
        'json' => is_array($decoded) ? $decoded : array(),
    );
}

/**
 * Turn an API response into a sentence an operator can act on.
 *
 * @param array $response As returned by tgif_api_request().
 * @return string Human-readable error text.
 */
function tgif_static_error_text($response)
{
    $status = isset($response['status']) ? (int)$response['status'] : 0;
    $error = isset($response['json']['error']) ? (string)$response['json']['error'] : '';

    $friendly = array(
        'invalid_token' => 'The Static TG API token is invalid or has been revoked.',
        'token_device_mismatch' => 'This token was created for a different hotspot ID / ESSID.',
        'device_not_connected' => 'The hotspot is not currently connected to TGIF.',
        'device_not_owned' => 'TGIF does not see this hotspot as belonging to the token account.',
        'secure_hotspot_required' => 'TGIF Hotspot Security is required for Static Talkgroups.',
        'static_tg_not_available' => 'Static Talkgroups are not available for this account.',
        'static_tg_disabled' => 'Static Talkgroups are currently disabled on TGIF.',
        // Observed as HTTP 403 on /v1/dynamic-talkgroups with a token that
        // still reads /v1/static-talkgroups with a 200. The cause is the
        // token's age, not the account: one issued before TGIF added dynamic
        // support does not carry the dynamic permission, and re-creating it
        // on tgif.network clears the error. Confirmed on hardware.
        'dynamic_tg_permission_required' => 'This TGIF API token cannot manage Dynamic Talkgroups. Re-create the token on tgif.network, then save the new one in Expert > API Keys.',
        'unsupported_slot' => 'That timeslot is not supported by this hotspot session.',
        'invalid_or_reserved_talkgroup' => 'That talkgroup is invalid or reserved for another TGIF function.',
        'limit_exceeded' => 'The Static Talkgroup limit for this account has been reached.',
        'membership_conflict' => 'The Static Talkgroup state changed at the same time. Refresh and try again.',
    );

    if (isset($friendly[$error])) {
        return $friendly[$error];
    }
    if ($status === 429) {
        return 'TGIF rate limited the request. Wait before trying again.';
    }
    if ($status >= 500) {
        return 'TGIF could not complete the request. Try again later.';
    }
    if ($status === 0) {
        // Resource-neutral: this helper is shared by the static and dynamic
        // paths, and callers prefix their own "Static:" / "Dynamic:" label.
        return 'No response from the TGIF API.';
    }
    return 'TGIF API returned HTTP ' . $status . ($error !== '' ? ' (' . $error . ')' : '') . '.';
}
