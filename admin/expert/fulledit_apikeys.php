<?php
/**
 * Combined editor for every dashboard-managed API credential file.
 *
 * One page, one section per credential, replacing the separate "BM API" and
 * "DAPNET API" entries that were crowding the Expert menu. The individual
 * editors (fulledit_bmapikey.php, fulledit_dapnetapi.php,
 * fulledit_tgifapikey.php) are deliberately kept: configure.php links
 * straight to them from the relevant protocol sections.
 *
 * Each section posts its own form and is dispatched by a hidden apikeyTarget
 * marker, so one save never touches another credential's file. All three
 * files are installed mode 600 www-data:www-data — the dashboard reads them
 * back with parse_ini_file() and no sudo (see mmdvmhost/bm_manager.php), so
 * root:root ownership would silently break those reads.
 *
 * TGIF is only offered from 4.3.9: installing /etc/tgifapi.key needs the
 * matching entry in /etc/sudoers.d/pistar-dashboard, which cannot be relied
 * on below that release.
 */
require_once($_SERVER['DOCUMENT_ROOT'].'/config/security_headers.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/config/csrf.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/config/banner_warnings.inc');
setSecurityHeaders();

// CSRF protection — see config/csrf.php for the full rationale. Must run
// BEFORE any output: bootstraps the session on GET so Set-Cookie ships, and
// rejects forged POSTs with a 403 before any state change. It also strips
// csrf_token from $_POST, which the INI writer below relies on.
csrf_verify();

// Layer 2 of the default-password protection — see config/banner_warnings.inc.
// MUST run BEFORE any output so header('Location: ...') works.
pistar_warnings_enforce_redirect();

// Load the language support
require_once('../config/language.php');
//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
//Load the Version Info
require_once('../config/version.php');

$apikeyRelease = isset($configPistarRelease['Pi-Star']['Version'])
    ? (string)$configPistarRelease['Pi-Star']['Version']
    : '';
$apikeyTgifOk = ($apikeyRelease !== '' && version_compare($apikeyRelease, '4.3.9', '>='));

/**
 * The credential files this page owns.
 *
 * seed      — written when the file does not exist yet, so the operator gets
 *             an editable skeleton rather than an empty section.
 * available — false hides the section entirely and makes a POST for it a
 *             no-op, so a hand-crafted POST cannot reach a write path the
 *             sudoers allowlist would refuse anyway.
 */
$apikeyTargets = array(
    'bm' => array(
        'path'      => '/etc/bmapi.key',
        'title'     => 'BrandMeister API Key',
        'seed'      => "[key]\napikey=None\n",
        'editor'    => 'fulledit_bmapikey.php',
        'available' => true,
    ),
    'dapnet' => array(
        'path'      => '/etc/dapnetapi.key',
        'title'     => 'DAPNET API Credentials',
        'seed'      => "[DAPNETAPI]\nUSER=\nPASS=\nTRXAREA=\n",
        'editor'    => 'fulledit_dapnetapi.php',
        'available' => true,
    ),
    'tgif' => array(
        'path'      => '/etc/tgifapi.key',
        'title'     => 'TGIF Static Talkgroups API Key',
        'seed'      => "[key]\napikey=None\n",
        'editor'    => 'fulledit_tgifapikey.php',
        'available' => $apikeyTgifOk,
    ),
);

/**
 * Build an ini body from the posted sections and install it over $path.
 *
 * Mirrors the writer in fulledit_bmapikey.php: each remaining top-level POST
 * key is an [INI section] whose value array supplies the key=value lines.
 * CR/LF are stripped from values so a newline cannot split into a fresh ini
 * line and inject extra keys.
 *
 * @param array  $data Posted sections, control fields already removed.
 * @param string $path Absolute destination path.
 * @return bool True when the staged file was installed.
 */
function apikey_install_ini($data, $path)
{
    $content = "";
    foreach ($data as $section => $values) {
        if (!is_array($values)) {
            // Not a section/value pair — ignore rather than emit a bare
            // [section] header for a stray scalar.
            continue;
        }
        // UnBreak special cases
        $section = str_replace("_", " ", $section);
        $content .= "[".$section."]\n";
        foreach ($values as $key => $value) {
            $value = str_replace(array("\r", "\n"), "", (string)$value);
            if ($value == '') {
                $content .= $key."=none\n";
            } else {
                $content .= $key."=".$value."\n";
            }
        }
        $content .= "\n";
    }

    if ($content === "") {
        return false;
    }

    // Staged write under an unguessable name — see edit_ircddbgateway.php for
    // the full TOCTOU rationale. tempnam() creates it mode 600 owned by
    // www-data, so PHP writes through without sudo.
    $filepath = tempnam('/tmp', 'pistar-edit-');
    if ($filepath === false) {
        return false;
    }
    if (file_put_contents($filepath, $content) === false) {
        @unlink($filepath);
        return false;
    }

    // Atomic install: mode + owner in one syscall sequence, between the
    // read-only remounts the rest of the dashboard uses. The install exit
    // status is checked so a sudo refusal (a missing allowlist entry, say) is
    // reported to the operator rather than being announced as a save that
    // silently did nothing. The read-only remount runs either way.
    $installRc = 0;
    $installOut = array();
    exec('sudo mount -o remount,rw /');
    exec('sudo install -m 600 -o www-data -g www-data '
         . escapeshellarg($filepath) . ' ' . escapeshellarg($path)
         . ' 2>&1', $installOut, $installRc);
    exec('sudo mount -o remount,ro /');

    @unlink($filepath);
    return ($installRc === 0);
}

// Handle a save. Only a known, available target is dispatched.
$apikeySaved = '';
$apikeyFailed = '';
if (!empty($_POST) && isset($_POST['apikeyTarget'])) {
    $target = (string)$_POST['apikeyTarget'];
    if (isset($apikeyTargets[$target]) && $apikeyTargets[$target]['available']) {
        $data = $_POST;
        unset($data['apikeyTarget'], $data['apikeySave']);
        if (apikey_install_ini($data, $apikeyTargets[$target]['path'])) {
            $apikeySaved = $apikeyTargets[$target]['title'];
        } else {
            $apikeyFailed = $apikeyTargets[$target]['title'];
        }
    }
    unset($_POST);
}
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
    <meta name="Description" content="Pi-Star Expert Editor" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - Digital Voice Dashboard - Expert Editor</title>
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php" />
  </head>
  <body>
  <?php pistar_warnings_render(); ?>
  <div class="container">
  <?php include './header-menu.inc'; ?>
  <div class="contentwide">

<?php
if ($apikeySaved !== '') {
    echo "<table>\n";
    echo '<tr><td role="status"><b>'
         . htmlspecialchars($apikeySaved, ENT_QUOTES, 'UTF-8')
         . ' saved.</b></td></tr>'."\n";
    echo "</table>\n<br />\n";
}
if ($apikeyFailed !== '') {
    // Most likely cause is sudo refusing the install because the allowlist
    // entry for this path is not present on this release.
    echo "<table>\n";
    echo '<tr><td role="alert"><b>'
         . htmlspecialchars($apikeyFailed, ENT_QUOTES, 'UTF-8')
         . ' could not be saved.</b></td></tr>'."\n";
    echo "</table>\n<br />\n";
}

foreach ($apikeyTargets as $targetKey => $target) {
    if (!$target['available']) {
        continue;
    }

    // These files are mode 600 www-data:www-data, so the dashboard reads them
    // directly — same as mmdvmhost/bm_manager.php does. No sudo cp staging is
    // needed just to render the current values.
    $parsed = array();
    if (file_exists($target['path'])) {
        $parsed = @parse_ini_file($target['path'], true);
    }
    if (!is_array($parsed) || $parsed === array()) {
        // Fall back to the skeleton so the operator has fields to fill in.
        $seedFile = tempnam('/tmp', 'pistar-seed-');
        if ($seedFile !== false) {
            file_put_contents($seedFile, $target['seed']);
            $parsed = @parse_ini_file($seedFile, true);
            @unlink($seedFile);
        }
        if (!is_array($parsed)) {
            $parsed = array();
        }
    }

    echo '<form action="" method="post">'."\n";
    echo csrf_field_html()."\n";
    echo '<input type="hidden" name="apikeyTarget" value="'
         . htmlspecialchars($targetKey, ENT_QUOTES, 'UTF-8') . '" />'."\n";
    echo "<table>\n";
    echo '<tr><th colspan="2">' . htmlspecialchars($target['title'], ENT_QUOTES, 'UTF-8')
         . ' &nbsp; <small><a href="' . htmlspecialchars($target['editor'], ENT_QUOTES, 'UTF-8')
         . '" style="color: #ffffff;">full editor</a></small></th></tr>'."\n";

    foreach ($parsed as $section => $values) {
        if (!is_array($values)) {
            continue;
        }
        $sectionHtml = htmlspecialchars((string)$section, ENT_QUOTES, 'UTF-8');
        // Keep the section name so the writer can rebuild the ini on submit.
        echo '<input type="hidden" value="'.$sectionHtml.'" name="'.$sectionHtml.'" />'."\n";
        foreach ($values as $key => $value) {
            $keyHtml   = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
            $valueHtml = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            // Single-line inputs rather than the 13-row textareas the
            // individual editors use — these values are all one line, and
            // compactness is the point of this page.
            echo '<tr><td align="right" width="30%">'.$keyHtml.'</td>'
                 . '<td align="left"><input type="text" size="72" '
                 . 'name="'.$sectionHtml.'['.$keyHtml.']" value="'.$valueHtml.'" /></td></tr>'."\n";
        }
    }

    echo "</table>\n";
    echo '<input type="submit" value="'.$lang['apply'].'" name="apikeySave" />'."\n";
    echo "</form>\n<br />\n";
}
?>
</div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_new">here</a>.<br />
</div>

</div>
</body>
</html>
