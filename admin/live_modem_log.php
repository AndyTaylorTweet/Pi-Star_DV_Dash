<?php
//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
//Load the Version Info
require_once('config/version.php');

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/live_modem_log.php") {

  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  $lifetime=30;
  session_set_cookie_params($lifetime);
  session_start();

  if (isset($_GET['ajax'])) {
    //session_start();
    if (file_exists('/etc/dstar-radio.mmdvmhost')) {
      $logfile = "/var/log/pi-star/MMDVM-".gmdate('Y-m-d').".log";
    }
    elseif (file_exists('/etc/dstar-radio.dstarrepeater')) {
      $logfile = "/var/log/pi-star/DStarRepeater-".gmdate('Y-m-d').".log";
    }
    
    $handle = fopen($logfile, 'rb');
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
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - Digital Voice Dashboard - Live Log</title>
    <link rel="stylesheet" type="text/css" href="css/ircddb.css?version=1.2" />
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
    <script type="text/javascript" src="http://creativecouple.github.com/jquery-timing/jquery-timing.min.js"></script>
    <script type="text/javascript">
    $(function() {
      $.repeat(1000, function() {
        $.get('/admin/live_modem_log.php?ajax', function(data) {
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
  <div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star Digital Voice - Live Modem Log</h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" style="color: #ffffff;">Dashboard</a> |
    <a href="/admin/" style="color: #ffffff;">Admin</a> |
    <a href="/admin/power.php" style="color: #ffffff;">Power</a> |
    <a href="/admin/config_backup.php" style="color: #ffffff;">Backup/Restore</a> |
    <a href="/admin/configure.php" style="color: #ffffff;">Config</a>
  </p>
  </div>
  <div class="contentwide">
  <table width="100%">
  <tr><th>Live Modem Log</th></tr>
  <tr><td align="left"><div id="tail">Starting logging, please wait...<br /></div></td></tr>
  </table>
  </div>
  <div class="footer">
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
