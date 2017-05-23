<?php
//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
//Load the Version Info
require_once('config/version.php');

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/update.php") {

  if (!isset($_GET['ajax'])) {
    system('sudo touch /var/log/pi-star/pi-star_update.log > /dev/null 2>&1 &');
    system('sudo echo "" > /var/log/pi-star/pi-star_update.log > /dev/null 2>&1 &');
    system('sudo /usr/local/sbin/pistar-update > /dev/null 2>&1 &');
    }

  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();

  if (isset($_GET['ajax'])) {
    //session_start();
    $handle = fopen('/var/log/pi-star/pi-star_update.log', 'rb');
    if (isset($_SESSION['offset'])) {
      $data = stream_get_contents($handle, -1, $_SESSION['offset']);
      $_SESSION['offset'] += strlen($data);
      echo nl2br($data);
      }
    else {
      fseek($handle, 0, SEEK_END);
      $_SESSION['offset'] = ftell($handle);
      } 
  exit();
  }
  
?>
  <!doctype html>
  <html xmlns="http://www.w3.org/1999/xhtml"xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Author" content="Andrew Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Update" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Hotspot Update Dashboard</title>
    <LINK REL="stylesheet" type="text/css" href="css/ircddb.css" />
    <script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
    <script src="http://creativecouple.github.com/jquery-timing/jquery-timing.min.js"></script>
    <script>
    $(function() {
      $.repeat(1000, function() {
        $.get('/admin/update.php?ajax', function(data) {
          $('#tail').append(data);
          var objDiv = document.getElementById("tail");
          objDiv.scrollTop = objDiv.scrollHeight;
        });
      });
    });
    </script>
  </head>
  <body>
  <div class="container">
  <div id="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star Digital Voice - Software Updater</h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" alt="Dashboard" style="color: #ffffff;">Dashboard</a> |
    <a href="/admin/" alt="Administration" style="color: #ffffff;">Admin</a> |
    <a href="/admin/power.php" alt="Power Control" style="color: #ffffff;">Power</a> |
    <a href="/admin/configure.php" alt="Configuration" style="color: #ffffff;">Config</a>
  </p>
  </div>
  <div id="contentwide">
  <table width="100%">
  <tr><th>Update Running</th></tr>
  <tr><td align="left"><div id="tail">Starting update, please wait...<br></div></td></tr>
  </table>
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
