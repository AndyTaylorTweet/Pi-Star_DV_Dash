<?php
// Load the language support
require_once('../config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('../config/version.php');
if (!isset($_GET["log"])) {
  $log = "MMDVMHost";
} else {
  $log = $_GET["log"];
}

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/logs/log.php") {

  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();

  if (!isset($_GET['ajax'])) {
    unset($_SESSION['offset']);
    //$_SESSION['offset'] = 0;
  }

  switch ($log) {
    case "MMDVMHost":
      $logfile = "/var/log/pi-star/MMDVM-".gmdate('Y-m-d').".log";
      break;
    case "DStarRepeater":
      $logfile = "/var/log/pi-star/DStarRepeater-".gmdate('Y-m-d').".log";
      break;
    case "DMRGateway":
      $logfile = "/var/log/pi-star/DMRGateway-".gmdate('Y-m-d').".log";
      break;
    case "YSFGateway":
      $logfile = "/var/log/pi-star/YSFGateway-".gmdate('Y-m-d').".log";
      break;
    case "ircDDBGateway":
      $logfile = "/var/log/pi-star/ircDDBGateway-".gmdate('Y-m-d').".log";
      break;
    case "P25Gateway":
      $logfile = "/var/log/pi-star/P25Gateway-".gmdate('Y-m-d').".log";
      break;
    case "NXDNGateway":
      $logfile = "/var/log/pi-star/NXDNGateway-".gmdate('Y-m-d').".log";
      break;
    case "DAPNETGateway":
      $logfile = "/var/log/pi-star/DAPNETGateway-".gmdate('Y-m-d').".log";
      break;
  }

  if (isset($_GET['ajax'])) {
    //session_start();
    
    if (empty($logfile) || !file_exists($logfile)) {
      exit();
    }
    
    $handle = fopen($logfile, 'rb');
    if (isset($_SESSION['offset'])) {
      fseek($handle, 0, SEEK_END);
      if ($_SESSION['offset'] > ftell($handle)) //log rotated/truncated
        $_SESSION['offset'] = 0; //continue at beginning of the new log
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
    <meta http-equiv="pragma" content="no-cache" />
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['live_logs']." ".$log?></title>
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php" />
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
    <script type="text/javascript" src="http://creativecouple.github.com/jquery-timing/jquery-timing.min.js"></script>
    <script type="text/javascript">
    $(function() {
      $.repeat(1000, function() {
        $.get('/admin/logs/log.php?log=<?php echo "$log";?>&ajax', function(data) {
          if (data.length < 1) return;
          var objDiv = document.getElementById("tail");
          var isScrolledToBottom = objDiv.scrollHeight - objDiv.clientHeight <= objDiv.scrollTop + 1;
          $('#tail').append(data);
          if (isScrolledToBottom)
            objDiv.scrollTop = objDiv.scrollHeight;
        });
      });
    });
    </script>
  </head>
  <body>
  <div class="container">
  <?php include './header-menu.inc'; ?>
  <div class="contentwide">
  <table width="100%">
  <tr><th><?php echo $lang['live_logs']." ".$log;?></th></tr>
  <tr><td align="left"><div id="tail">Starting <?php echo $log;?> logging, please wait...<br />
  <?php
    if (!file_exists($logfile)) {
      print "File $logfile not found!";
    }
  ?>
  </div></td></tr>
  <?php
    if (file_exists($logfile)) {
  ?>
  <tr><th>Download the log: <a href="/admin/logs/download_log.php?log=<?php echo $log;?>" style="color: #ffffff;">here</a></th></tr>
  <?php
    }
  ?>
  </table>
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
