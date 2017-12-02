<?php
// Load the language support
require_once('../config/language.php');
//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
//Load the Version Info
require_once('../config/version.php');
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
    <link rel="stylesheet" type="text/css" href="../css/ircddb.css?version=1.3" />
  </head>
  <body>
  <div class="container">
  <div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star Digital Voice - Expert Editor</h1>
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
    <tr><th>Expert Editors</th></tr>
    <tr><td align="center">
      <h2>**WARNING**</h2>
      Pi-Star Expert editors have been created to make editing some of the extra settings in the<br />
      config files more simple, allowing you to update some areas of the config files without the<br />
      need to login to your Pi over SSH.<br />
      <br />
      Please keep in mind when making your edits here, that these config files can be updated by<br />
      the dashboard, and that your edits can be over-written. It is assumed that you already know<br />
      what you are doing editing the files by hand, and that you understand what parts of the files<br />
      are maintained by the dashboard.<br />
      <br />
      With that warning in mind, you are free to make any changes you like, for help come to the Facebook<br />
      group (link at the bottom of the page) and ask for help if / when you need it.<br />
      73 and enjoy your Pi-Star experiance.<br />
      Pi-Star UK Team.<br />
      <br />
    </td></tr>
  </table>
  </div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
ircDDBGateway Dashboard by Hans-J. Barthen (DL5DI),<br />
MMDVMDash developed by Kim Huebel (DG9VH), <br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistar/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_new">here</a>.<br />
</div>

</div>
</body>
</html>
