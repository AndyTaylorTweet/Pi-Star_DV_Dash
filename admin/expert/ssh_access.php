<?php
// Load the language support
require_once('../config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('../config/version.php');

if (file_exists('/etc/default/shellinabox')) {
  $getPortCommand = "grep -m 1 'SHELLINABOX_PORT=' /etc/default/shellinabox | awk -F '=' '/SHELLINABOX_PORT=/ {print $2}'";
  $shellPort = exec($getPortCommand);
}

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/expert/ssh_access.php") {
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
    <meta name="Description" content="Pi-Star Update" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - SSH";?></title>
    <link rel="stylesheet" type="text/css" href="../css/ircddb.css?version=1.3" />
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
    <script type="text/javascript" src="http://creativecouple.github.com/jquery-timing/jquery-timing.min.js"></script>
  </head>
  <body>
  <div class="container">
  <div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star Digital Voice - SSH Access</h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
    <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
    <a href="/admin/update.php" style="color: #ffffff;"><?php echo $lang['update'];?></a> |
    <a href="/admin/config_backup.php" style="color: #ffffff;"><?php echo $lang['backup_restore'];?></a> |
    <a href="/admin/configure.php" style="color: #ffffff;"><?php echo $lang['configuration'];?></a>
  </p>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    Edit:
    <a href="edit_dstarrepeater.php" style="color: #ffffff;">DStarRepeater</a> |
    <a href="edit_mmdvmhost.php" style="color: #ffffff;">MMDVMHost</a> |
    <a href="edit_dmrgateway.php" style="color: #ffffff;">DMRGateway</a> |
    <a href="edit_ysfgateway.php" style="color: #ffffff;">YSFGateway</a> |
    <a href="edit_p25gateway.php" style="color: #ffffff;">P25Gateway</a> |
    <a href="edit_ircddbgateway.php" style="color: #ffffff;">ircDDBGateway</a> |
    <a href="edit_timeserver.php" style="color: #ffffff;">TimeServer</a> |
    <a href="edit_pistar-remote.php" style="color: #ffffff;">PiStar-Remote</a> |
    <a href="ssh_access.php" style="color: #ffffff;">SSH Access</a>
  </p>
  </div>
  <div class="contentwide">
  <table width="100%">
  <tr><th>SSH - Pi-Star</th></tr>
  <tr><td align="left"><div id="tail">
    <?php if (isset($shellPort)) {
      echo "<iframe src=\"http://".$_SERVER['HTTP_HOST'].":".$shellPort."\" style=\"border:0px #ffffff none; background:#ffffff; color:#00ff00;\" name=\"Pi-Star_SSH\" scrolling=\"no\" frameborder=\"0\" marginheight=\"0px\" marginwidth=\"0px\" height=\"100%\" width=\"100%\"></iframe>";
    }
    else {
      echo "SSH Feature not yet installed";
    } ?>
  </div></td></tr>
  </table>
  <?php if (isset($shellPort)) { echo "<a href=\"http://".$_SERVER['HTTP_HOST'].":".$shellPort."\">Click here for fullscreen SSH client</a><br />\n"; } ?>
  </div>
  <div class="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistar/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  <br />
  </div>
  </div>
  </body>
  </html>

<?php
}
?>
