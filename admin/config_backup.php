<?php
/**
 * Configuration backup & restore.
 *
 * Two POST actions:
 *   - download: zips the operator's config files into
 *     /tmp/config_backup.zip and sends it as a download. Files
 *     are listed once in {@see backup_files()} and shared with
 *     the restore handler so the two paths cannot drift.
 *   - restore: accepts a ZIP upload, validates it, extracts a
 *     known-safe subset into /tmp/config_restore/, stops every
 *     DV service, atomically installs each file at its mapped
 *     target, then restarts services.
 *
 * Security model (post-#16 remediation):
 *   - Upload is finfo_file()-typechecked, size-capped, then
 *     moved to a fixed temp filename — the operator-supplied
 *     name never reaches a path concat.
 *   - ZIP entries are filtered against backup_files() before
 *     extraction. Anything not on the allowlist (including any
 *     entry whose name contains `/`, `\`, or starts with `.`)
 *     is silently skipped — no zip-slip vector remains.
 *   - The blind `sudo mv -v -f /tmp/config_restore/* /etc/`
 *     pattern is replaced by per-file `sudo install -m … -o
 *     root -g root` driven by the same allowlist. No glob ever
 *     reaches the shell after extraction.
 *   - The two post-restore "re-apply from restored config"
 *     paths (timezone via shell-interpolated config.php grep,
 *     and remotePassword via shell-interpolated sed-i) are
 *     replaced by data-side propagation: timezone validated
 *     against DateTimeZone::listIdentifiers() and passed via
 *     escapeshellarg(), remotePassword routed through
 *     config_writer's privileged-flat helper (no shell sees
 *     the value).
 *
 * Backup contents (and therefore the restore allowlist) are
 * defined ONCE in {@see backup_files()}. Files NOT in that list
 * are deliberately NOT backed up — notably /etc/hostapd/hostapd.conf
 * and /root/.Remote Control, which carry secrets that should
 * never leave the device in a portable form.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/security_headers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/banner_warnings.inc');
setSecurityHeaders();

require_once($_SERVER['DOCUMENT_ROOT'].'/config/csrf.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/config/config_writer.php');

/**
 * Canonical map of files in a Pi-Star backup ZIP.
 *
 * Single source of truth: the backup loop iterates this map to
 * decide what to package, and the restore loop iterates this map
 * to decide what to install (and where). ZIP entries with names
 * outside the keys here are silently ignored on restore.
 *
 *   key   = basename inside the ZIP (the backup is a zip -j
 *           "junk paths" archive — every entry sits at the
 *           archive root with no directory component).
 *   value = absolute target path on the device.
 *
 * Files NOT in this map are deliberately excluded from backup:
 *   - /etc/hostapd/hostapd.conf      (AP wpa_passphrase secret)
 *   - /root/.Remote Control          (linker password secret)
 *   - /etc/sudoers* / /etc/passwd    (system auth)
 *
 * @return array<string, array{0:string, 1:int, 2:string, 3:string}>
 *         basename => [path, mode, owner, group]
 */
function backup_files()
{
    // Each entry is [path, mode, owner, group]. The mode/owner/group
    // tuple is what the restore loop passes to `sudo install -m -o
    // -g`, and MUST match the corresponding /etc/sudoers.d/pistar-
    // dashboard PISTAR_DASH_INSTALL line for that destination — or
    // sudo will reject the install and the file will silently fail
    // to restore. Pre-2026-05-01 the restore loop used a hardcoded
    // `install -m 644 -o root -g root` for every entry, which
    // worked under the old NOPASSWD: ALL sudoers but breaks under
    // the path-scoped allowlist for: the .key files (require 600
    // www-data:www-data so the dashboard's per-page sudo-less
    // parse_ini_file() reads still work — see fulledit_bmapikey.php
    // for the full rationale), wpa_supplicant.conf (600 root:root),
    // and the three system files (dhcpcd.conf, hosts, hostname)
    // which have no install entry in the deployed sudoers at all
    // and rely on a re-spun image to gain them.
    return array(
        // Network / WiFi
        'dhcpcd.conf'                 => array('/etc/dhcpcd.conf',                            644, 'root',     'root'),
        'wpa_supplicant.conf'         => array('/etc/wpa_supplicant/wpa_supplicant.conf',     600, 'root',     'root'),
        // Gateway daemon configs (flat key=value INI-ish)
        'ircddbgateway'               => array('/etc/ircddbgateway',                          644, 'root',     'root'),
        'mmdvmhost'                   => array('/etc/mmdvmhost',                              644, 'root',     'root'),
        'dstarrepeater'               => array('/etc/dstarrepeater',                          644, 'root',     'root'),
        'dapnetgateway'               => array('/etc/dapnetgateway',                          644, 'root',     'root'),
        'p25gateway'                  => array('/etc/p25gateway',                             644, 'root',     'root'),
        'm17gateway'                  => array('/etc/m17gateway',                             644, 'root',     'root'),
        'ysfgateway'                  => array('/etc/ysfgateway',                             644, 'root',     'root'),
        'ysf2dmr'                     => array('/etc/ysf2dmr',                                644, 'root',     'root'),
        'dgidgateway'                 => array('/etc/dgidgateway',                            644, 'root',     'root'),
        'nxdngateway'                 => array('/etc/nxdngateway',                            644, 'root',     'root'),
        'dmrgateway'                  => array('/etc/dmrgateway',                             644, 'root',     'root'),
        'mobilegps'                   => array('/etc/mobilegps',                              644, 'root',     'root'),
        'starnetserver'               => array('/etc/starnetserver',                          644, 'root',     'root'),
        'timeserver'                  => array('/etc/timeserver',                             644, 'root',     'root'),
        // Mode markers (operator has at most one of these)
        'dstar-radio.mmdvmhost'       => array('/etc/dstar-radio.mmdvmhost',                  644, 'root',     'root'),
        'dstar-radio.dstarrepeater'   => array('/etc/dstar-radio.dstarrepeater',              644, 'root',     'root'),
        // Pi-Star service / dashboard config
        'pistar-remote'               => array('/etc/pistar-remote',                          644, 'root',     'root'),
        'hosts'                       => array('/etc/hosts',                                  644, 'root',     'root'),
        'hostname'                    => array('/etc/hostname',                               644, 'root',     'root'),
        // .key files: 600 www-data so unprivileged dashboard reads
        // (banner_warnings.inc, bm_links.php, bm_manager.php — all
        // parse_ini_file() without sudo) still work post-restore.
        'bmapi.key'                   => array('/etc/bmapi.key',                              600, 'www-data', 'www-data'),
        'dapnetapi.key'               => array('/etc/dapnetapi.key',                          600, 'www-data', 'www-data'),
        'tgifapi.key'                 => array('/etc/tgifapi.key',                            600, 'www-data', 'www-data'),
        'pistar-css.ini'              => array('/etc/pistar-css.ini',                         644, 'root',     'root'),
        'RSSI.dat'                    => array('/usr/local/etc/RSSI.dat',                     644, 'root',     'root'),
        'ircddblocal.php'             => array('/var/www/dashboard/config/ircddblocal.php',   644, 'root',     'root'),
        'config.php'                  => array('/var/www/dashboard/config/config.php',        644, 'root',     'root'),
    );
}

// CSRF protection — see config/csrf.php for the full rationale.
// Must run BEFORE any output: bootstraps the session on GET (so
// Set-Cookie ships) and rejects forged POSTs cleanly with 403
// before the download / restore handlers run.
//
// CSRF protection here makes C1's zip-slip / restore-pipeline RCE
// harder to exploit (attacker can no longer trigger restore via a
// cross-site click); the underlying bugs in the restore handler
// remain — they are tracked separately as the C1/C2 work-on-hold.
csrf_verify();

// Layer 2 of the default-password protection — see config/banner_warnings.inc.
// MUST run BEFORE any output so header('Location: ...') works.
pistar_warnings_enforce_redirect();

// Load the language support
require_once('config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('config/version.php');
// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/config_backup.php") {
  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  // session_start() is no longer called here — csrf_verify() at
  // the top of the file already started the session via
  // csrf_session_start(). A second session_start() would emit a
  // "session is already active" NOTICE on PHP 8.x.
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
  <head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Author" content="Andrew Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Power" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['backup_restore'];?></title>
    <link rel="stylesheet" type="text/css" href="css/pistar-css.php" />
  </head>
  <body>
  <?php pistar_warnings_render(); ?>
  <div class="container">
  <div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / <?php echo $lang['dashboard'].": ".$version; ?></div>
  <h1>Pi-Star <?php echo $lang['digital_voice']." - ".$lang['backup_restore'];?></h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
    <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
    <a href="/admin/power.php" style="color: #ffffff;"><?php echo $lang['power'];?></a> |
    <a href="/admin/update.php" style="color: #ffffff;"><?php echo $lang['update'];?></a> |
    <a href="/admin/configure.php" style="color: #ffffff;"><?php echo $lang['configuration'];?></a>
  </p>
  </div>
  <div class="contentwide">
<?php if (!empty($_POST)) {
  echo '<table width="100%">'."\n";

        if ( $_POST["action"] === "download" ) {
          echo "<tr><th colspan=\"2\">".$lang['backup_restore']."</th></tr>\n";

          $output = "Finding config files to be backed up\n";
          $backupDir = "/tmp/config_backup";
          $backupZip = "/tmp/config_backup.zip";
      $hostNameInfo = exec('cat /etc/hostname');

          $output .= shell_exec("sudo rm -rf " . escapeshellarg($backupZip) . " 2>&1");
          $output .= shell_exec("sudo rm -rf " . escapeshellarg($backupDir) . " 2>&1");
          $output .= shell_exec("sudo mkdir " . escapeshellarg($backupDir) . " 2>&1");

          // Iterate the canonical backup map so the backup and the
          // restore allowlist cannot drift from each other. dhcpcd.conf
          // is special-cased (only included when the operator has a
          // static IP configured) — everything else is unconditional;
          // missing source files are silently skipped (the cp returns
          // an error to stderr that nobody reads, same as the legacy
          // behaviour).
          foreach (backup_files() as $basename => $meta) {
              // $meta is [path, mode, owner, group]. The backup half
              // only needs the path — mode/owner/group are consumed
              // on the restore-half install command.
              $srcpath = $meta[0];
              if ($basename === 'dhcpcd.conf') {
                  // Only back up dhcpcd.conf if the operator has set a
                  // static IP — otherwise restoring would clobber
                  // working DHCP config on the target device.
                  if (!shell_exec('cat /etc/dhcpcd.conf | grep "static ip_address" | grep -v "#"')) {
                      continue;
                  }
              }
              if (file_exists($srcpath)) {
                  $output .= shell_exec(
                      "sudo cp " . escapeshellarg($srcpath) . " "
                      . escapeshellarg($backupDir . '/' . $basename) . " 2>&1"
                  );
              }
          }

          $output .= "Compressing backup files\n";
          $output .= shell_exec("sudo zip -j " . escapeshellarg($backupZip) . " " . escapeshellarg($backupDir) . "/* 2>&1");
          $output .= "Starting download\n";

          echo "<tr><td align=\"left\"><pre>"
             . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . "</pre></td></tr>\n";

          if (file_exists($backupZip)) {
            $utc_time = gmdate('Y-m-d H:i:s');
            $utc_tz =  new DateTimeZone('UTC');
            $local_tz = new DateTimeZone(date_default_timezone_get ());
            $dt = new DateTime($utc_time, $utc_tz);
            $dt->setTimeZone($local_tz);
            $local_time = $dt->format('Y-M-d');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
        if ($hostNameInfo != "pi-star") {
            header('Content-Disposition: attachment; filename="'.basename("Pi-Star_Config_".$hostNameInfo."_".$local_time.".zip").'"');
        }
        else {
            header('Content-Disposition: attachment; filename="'.basename("Pi-Star_Config_$local_time.zip").'"');
        }
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backupZip));
            ob_clean();
            flush();
            readfile($backupZip);
            exit;
          }

        };
        if ( $_POST["action"] === "restore" ) {
          echo "<tr><th colspan=\"2\">Config Restore</th></tr>\n";
          $output = "Uploading your Config data\n";

          // Wrap the whole restore in a do/while(false) so an early
          // bail-out (upload validation failure, ZIP parse error,
          // unsafe entry name, etc.) can break cleanly out of the
          // sequence without an `if/else` pyramid. PHP 7.0+ idiom.
          do {

          // Hardened restore pipeline. See the file-level docblock for
          // the security model. Constants here:
          //   - $target_dir is hardcoded; the operator-supplied filename
          //     never touches a path concat.
          //   - $upload_path is also hardcoded; we move the uploaded
          //     temp file to a known name before any further processing.
          //   - $max_zip_bytes caps the upload; a real Pi-Star backup
          //     is ~25 KB, nginx already caps the body at 512 KB, so
          //     256 KB is generous.
          $target_dir    = '/tmp/config_restore';
          $upload_path   = '/tmp/config_restore_upload.zip';
          $max_zip_bytes = 256 * 1024;
          $allowlist     = backup_files();

          shell_exec("sudo rm -rf " . escapeshellarg($target_dir) . " 2>&1");
          shell_exec("rm -f "       . escapeshellarg($upload_path) . " 2>&1");
          shell_exec("mkdir -p "    . escapeshellarg($target_dir) . " 2>&1");

          // ----- Upload validation -------------------------------------
          $upload_ok = false;
          $err_msg   = '';
          if (!isset($_FILES['fileToUpload']) ||
              $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
              $err_msg = 'No file uploaded, or upload failed.';
          } elseif ($_FILES['fileToUpload']['size'] <= 0 ||
                    $_FILES['fileToUpload']['size'] > $max_zip_bytes) {
              $err_msg = 'Upload size out of range (expected up to '
                       . round($max_zip_bytes / 1024) . ' KB).';
          } else {
              $tmp_name = $_FILES['fileToUpload']['tmp_name'];
              // finfo_file() reads the magic bytes — much harder for an
              // attacker to fake than the client-supplied $_FILES['type'].
              $finfo = finfo_open(FILEINFO_MIME_TYPE);
              $magic = $finfo ? finfo_file($finfo, $tmp_name) : '';
              if ($finfo) finfo_close($finfo);
              if ($magic !== 'application/zip') {
                  $err_msg = 'Uploaded file is not a ZIP archive (detected '
                           . htmlspecialchars($magic, ENT_QUOTES, 'UTF-8') . ').';
              } elseif (!move_uploaded_file($tmp_name, $upload_path)) {
                  $err_msg = 'Could not stage upload to ' . $upload_path . '.';
              } else {
                  $upload_ok = true;
              }
          }

          if (!$upload_ok) {
              $output .= $err_msg . "\n";
              echo "<tr><td align=\"left\"><pre>"
                 . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . "</pre></td></tr>\n";
              // Don't emit </table> here — the outer code does it once
              // after the if-action blocks finish. The "Go Back" button
              // is rendered AFTER the table closes (see end of file).
              break;
          }

          // ----- ZIP entry validation ---------------------------------
          // Open the archive and decide which entries to extract BEFORE
          // any extraction happens. An entry is admitted iff:
          //   - its name appears verbatim as a key in $allowlist
          //     (basename → target map), AND
          //   - its name contains no `/`, no `\`, and does not start
          //     with `.` (defence in depth — the backup writer uses
          //     `zip -j` so directory components are never legitimate).
          // Entries outside the allowlist are silently skipped — that's
          // the documented behaviour for forward-compat with future
          // Pi-Star versions adding new files.
          $zip = new ZipArchive();
          if ($zip->open($upload_path) !== true) {
              $output .= "Could not open the uploaded ZIP.\n";
              @unlink($upload_path);
              echo "<tr><td align=\"left\"><pre>"
                 . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . "</pre></td></tr>\n";
              break;
          }

          $admit = array();
          for ($i = 0; $i < $zip->numFiles; $i++) {
              $name = $zip->getNameIndex($i);
              if ($name === false || $name === '') {
                  continue;
              }
              if (strpos($name, '/') !== false ||
                  strpos($name, '\\') !== false ||
                  strpos($name, "\0") !== false ||
                  $name[0] === '.') {
                  // Path-traversal / hidden / NUL-injection attempt.
                  // Reject the whole archive — this is well outside any
                  // Pi-Star-generated backup shape. break 2 exits the
                  // for() AND the surrounding do/while(false) early-
                  // exit block in one hop.
                  $zip->close();
                  @unlink($upload_path);
                  $output .= "Refusing ZIP — entry '"
                           . htmlspecialchars(substr($name, 0, 64), ENT_QUOTES, 'UTF-8')
                           . "' has an unsafe name.\n";
                  error_log("config_backup: rejected ZIP with unsafe entry '$name'");
                  echo "<tr><td align=\"left\"><pre>"
                     . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . "</pre></td></tr>\n";
                  break 2;
              }
              if (isset($allowlist[$name])) {
                  $admit[$name] = $i;
              }
              // else: silently ignored — unknown filename.
          }

          // Extract only the admitted entries into $target_dir. We use
          // ZipArchive::extractTo() with an explicit entry list so
          // ANYTHING outside that list is left in the archive and
          // never written to disk.
          $entries_to_extract = array_keys($admit);
          if (empty($entries_to_extract)) {
              $zip->close();
              @unlink($upload_path);
              $output .= "ZIP contained no recognised Pi-Star config files.\n";
              echo "<tr><td align=\"left\"><pre>"
                 . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . "</pre></td></tr>\n";
              break;
          }
          if (!$zip->extractTo($target_dir, $entries_to_extract)) {
              $zip->close();
              @unlink($upload_path);
              $output .= "Failed to extract ZIP entries.\n";
              echo "<tr><td align=\"left\"><pre>"
                 . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . "</pre></td></tr>\n";
              break;
          }
          $zip->close();
          @unlink($upload_path);
          $output .= "Your .zip file was uploaded and unpacked ("
                   . count($entries_to_extract) . " files).\n";

          // ----- Service stop ----------------------------------------
          $output .= "Stopping Services.\n";
          $stop_services = array(
              'cron.service', 'dstarrepeater.service', 'mmdvmhost.service',
              'ircddbgateway.service', 'timeserver.service',
              'pistar-watchdog.service', 'pistar-remote.service',
              'ysfgateway.service', 'ysf2dmr.service', 'p25gateway.service',
              'nxdngateway.service', 'm17gateway.service',
              'dapnetgateway.service', 'mobilegps.service',
          );
          foreach ($stop_services as $svc) {
              shell_exec('sudo systemctl stop ' . escapeshellarg($svc) . ' 2>&1');
          }

          shell_exec('sudo mount -o remount,rw / 2>&1');

          // ----- Per-file install ------------------------------------
          // Replace the previous blind `sudo mv -v -f /tmp/.../* /etc/`
          // with a per-file `sudo install -m 644 -o root -g root` driven
          // by the canonical map. install is atomic on same-fs (rename)
          // and falls back to safe copy across filesystems. The mode and
          // owner are forced regardless of how the file arrived in the
          // archive.
          $output .= "Writing new Config\n";

          // Tear down stale dstar-radio.* markers first — only one of
          // the two can be in use, and the restored archive may carry
          // a different mode than the device currently has. Use
          // `rm -rf` (not `rm -f`) so the call matches the
          // PISTAR_DASH_MARKERS sudoers entry — different argv tokens
          // yield different sudoers matches, and only `-rf` is
          // allowlisted.
          shell_exec('sudo rm -rf /etc/dstar-radio.* 2>&1');

          $installed = 0;
          foreach ($admit as $basename => $_idx) {
              $src   = $target_dir . '/' . $basename;
              $meta  = $allowlist[$basename];
              $target = $meta[0];
              $mode  = (string)$meta[1];
              $owner = $meta[2];
              $group = $meta[3];
              if (!file_exists($src)) {
                  // Should be impossible after a successful extractTo,
                  // but guard anyway — extraction can fail per-entry on
                  // unusual archives without throwing.
                  continue;
              }
              // Per-file install args from backup_files() metadata —
              // mode/owner/group must match the deployed sudoers
              // PISTAR_DASH_INSTALL line for $target or sudo will
              // reject. The hardcoded `install -m 644 -o root -g root`
              // here used to silently fail on .key files (require
              // 600 www-data:www-data) and wpa_supplicant.conf
              // (600 root:root) under the path-scoped sudoers.
              $cmd = 'sudo install -m ' . $mode
                   . ' -o ' . escapeshellarg($owner)
                   . ' -g ' . escapeshellarg($group) . ' '
                   . escapeshellarg($src) . ' '
                   . escapeshellarg($target);
              $rc = 0; $cmd_out = array();
              exec($cmd . ' 2>&1', $cmd_out, $rc);
              if ($rc === 0) {
                  $installed++;
              } else {
                  $output .= "  install failed for $basename: "
                           . implode(' / ', $cmd_out) . "\n";
              }
          }
          $output .= "Installed $installed file(s).\n";

          // ----- Post-restore re-applies (data-side, not shell) -----

          // Timezone: the restored config.php contains the operator's
          // timezone in a date_default_timezone_set('…') call. We want
          // the OS clock to match the dashboard's view. PRE-fix this
          // path was a shell pipeline interpolating the grepped value;
          // post-fix we extract via PHP-side parsing and validate
          // strictly against PHP's own list of valid timezone IDs
          // before passing through escapeshellarg().
          $cfg_path = '/var/www/dashboard/config/config.php';
          if (is_readable($cfg_path)) {
              $cfg = file_get_contents($cfg_path);
              if (preg_match("/date_default_timezone_set\\(\\s*['\"]([^'\"]+)['\"]\\s*\\)/",
                             $cfg, $m)) {
                  $tz = $m[1];
                  if (in_array($tz, DateTimeZone::listIdentifiers(), true)) {
                      shell_exec('sudo timedatectl set-timezone '
                               . escapeshellarg($tz) . ' 2>&1');
                  } else {
                      error_log("config_backup: skipping unknown timezone '$tz' from restored config.php");
                  }
              }
          }

          // ircDDBGateway Remote Control password: the restored
          // /etc/ircddbgateway carries `remotePassword=...`; the
          // /root/.Remote Control sibling file (mode 600 root:root,
          // not in the backup set) needs the same value or the
          // remotecontrold tool can't reach the daemon.
          //
          // Pre-fix this was a shell-interpolated `sudo sed -i ...`.
          // Post-fix: read via PHP-side fopen/fgets (the file is mode
          // 644 root:root after we just installed it, so www-data can
          // read it back), validate, then route through the helper's
          // privileged-flat editor — same primitive the C6.7 fix uses
          // and also what the configure.php confPassword handler now
          // uses. No shell ever sees the value.
          $rp_target = '/etc/ircddbgateway';
          if (is_readable($rp_target)) {
              $rp = '';
              foreach (file($rp_target, FILE_IGNORE_NEW_LINES) as $line) {
                  if (strpos($line, 'remotePassword=') === 0) {
                      $rp = substr($line, strlen('remotePassword='));
                      break;
                  }
              }
              // ircDDBGateway accepts arbitrary printable bytes for the
              // password; the helper's NUL/CR/LF guard is the floor.
              if ($rp !== '' && !preg_match('/[\x00\r\n]/', $rp)) {
                  config_writer_stage_privileged_flat(
                      '/root/.Remote Control', 'password', $rp
                  );
                  config_writer_commit(false);
              }
          }

          shell_exec('sudo mount -o remount,ro / 2>&1');

          // ----- Service start ---------------------------------------
          $output .= "Starting Services.\n";
          $start_services = array(
              'dstarrepeater.service', 'mmdvmhost.service',
              'ircddbgateway.service', 'timeserver.service',
              'pistar-watchdog.service', 'pistar-remote.service',
          );
          foreach ($start_services as $svc) {
              shell_exec('sudo systemctl start ' . escapeshellarg($svc) . ' 2>&1');
          }
          if (substr(exec('grep "pistar-upnp.service" /etc/crontab | cut -c 1'), 0, 1) !== '#') {
              shell_exec('sudo systemctl start pistar-upnp.service 2>&1');
          }
          $start_services_after_upnp = array(
              'ysfgateway.service', 'ysf2dmr.service', 'p25gateway.service',
              'nxdngateway.service', 'm17gateway.service',
              'dapnetgateway.service', 'mobilegps.service', 'cron.service',
          );
          foreach ($start_services_after_upnp as $svc) {
              shell_exec('sudo systemctl start ' . escapeshellarg($svc) . ' 2>&1');
          }

          // Cleanup: remove the staged extraction dir so subsequent
          // restores don't see leftovers.
          shell_exec("sudo rm -rf " . escapeshellarg($target_dir) . " 2>&1");

          $output .= "Configuration Restore Complete.\n";
          echo "<tr><td align=\"left\"><pre>"
             . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . "</pre></td></tr>\n";

          } while (false);  // end do/while(false) early-exit block
        };

  echo "</table>\n";
  } else { ?>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
  <?php csrf_field(); ?>
  <table width="100%">
  <tr>
    <th colspan="2"><?php echo $lang['backup_restore'];?></th>
  </tr>
  <tr>
    <td align="center" valign="top" width="50%">Download Configuration<br />
    <button style="border: none; background: none;" name="action" value="download"><img src="/images/download.png" border="0" alt="Download Config" /></button>
    </td>
    <td align="center" valign="top">Restore Configuration<br />
    <button style="border: none; background: none;" name="action" value="restore"><img src="/images/restore.png" border="0" alt="Restore Config" /></button><br />
        <input type="file" name="fileToUpload" id="fileToUpload" />
    </td>
  </tr>
  <tr>
  <td colspan="2" align="justify">
      <br />
      <b>WARNING:</b><br />
      Editing the files outside of Pi-Star *could* have un-desireable side effects.<br />
      <br />
      This backup and restore tool, will backup your config files to a Zip file, and allow you to restore them later<br />
      either to this Pi-Star or another one.<br />
      <ul>
          <li>System Passwords / Dashboard passwords are NOT backed up / restored.</li>
          <li>Wireless Configuration IS backed up and restored</li>
      </ul>
  </td>
  </tr>
  </table>
  </form>
<?php } ?>
  </div>
  <div class="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  <br />
  </div>
  </div>
  </body>
  </html>
<?php
}
