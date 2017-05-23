<?php
//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
//Load the Version Info
require_once('config/version.php');
// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/config_backup.php") {
  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();
?>
  <!doctype html>
  <html xmlns="http://www.w3.org/1999/xhtml"xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Author" content="Andrew Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Power" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star Config Backup</title>
    <LINK REL="stylesheet" type="text/css" href="css/ircddb.css" />
  </head>
  <body>
  <div class="container">
  <div id="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star Digital Voice - Config Backup / Restore</h1>
  <div style="padding-left: 5px; text-align: left; color: #ffffff;"><a href="/" alt="Dashboard" style="color: #ffffff;">Dashboard</a></div><div style="padding-right: 5px; text-align: right; color: #ffffff;"><a href="/" alt="Dashboard" style="color: #ffffff;">Dashboard</a> | <a href="/admin/" alt="Administration" style="color: #ffffff;">Admin</a> | <a href="/admin/update.php" alt="System Update" style="color: #ffffff;">Update</a> | <a href="/admin/configure.php" alt="Configuration" style="color: #ffffff;">Config</a></div>
  </div>
  <div id="contentwide">
<?php if (!empty($_POST)) { ?>
  <table width="100%">
  <?php
        if ( escapeshellcmd($_POST["action"]) == "backup" ) {
                echo "<tr><th colspan=\"2\">Config Backup</th></tr>\n";
                };
        if ( escapeshellcmd($_POST["action"]) == "restore" ) {
                echo "<tr><th colspan=\"2\">Config Restore</th></tr>\n";
                };
  ?>
  </table>
<?php } else { ?>
  <form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
  <table width="100%">
  <tr>
    <th colspan="2">Backup / Restore</th>
  </tr>
  <tr>
    <td align="center">Download Configuration<br /><input type="image" src="/images/download.png" name="action" value="download" /></td>
    <td align="center">Restore Configuration<br /><input type="image"  src="/images/restore.png" name="action" value="restore" /></td>
  </tr>
  </table>
  </form>
<?php } ?>
  </div>
  <div id="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistar/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.mw0mwz.co.uk/pi-star/" target="_blank">here</a>.<br />
  <br />
  </div>
  </div>
  </body>
  </html>
<?php
}
?>
