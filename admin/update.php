<?php
// Load the language support
require_once('config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('config/version.php');

// Force the Locale to the stock locale just while we run the update
setlocale(LC_ALL, "LC_CTYPE=en_GB.UTF-8;LC_NUMERIC=C;LC_TIME=C;LC_COLLATE=C;LC_MONETARY=C;LC_MESSAGES=C;LC_PAPER=C;LC_NAME=C;LC_ADDRESS=C;LC_TELEPHONE=C;LC_MEASUREMENT=C;LC_IDENTIFICATION=C");

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/update.php") {

  if (!isset($_GET['ajax'])) {
    if (!file_exists('/var/log/pi-star')) {
      system('sudo mkdir -p /var/log/pi-star/');
      system('sudo chmod 775 /var/log/pi-star/');
      system('sudo chown root:mmdvm /var/log/pi-star/');
    }
    system('sudo touch /var/log/pi-star/pi-star_update.log > /dev/null 2>&1 &');
    system('sudo echo "" > /var/log/pi-star/pi-star_update.log > /dev/null 2>&1 &');
    system('sudo /usr/local/sbin/pistar-update > /dev/null 2>&1 &');
  }

  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();

  if (!isset($_GET['ajax'])) {
    //unset($_SESSION['update_offset']);
    if (file_exists('/var/log/pi-star/pi-star_update.log')) {
      $_SESSION['update_offset'] = filesize('/var/log/pi-star/pi-star_update.log');
    } else {
      $_SESSION['update_offset'] = 0;
    }
  }
  
  if (isset($_GET['ajax'])) {
    //session_start();
    if (!file_exists('/var/log/pi-star/pi-star_update.log')) {
      exit();
    }
    
    $handle = fopen('/var/log/pi-star/pi-star_update.log', 'rb');
    if (isset($_SESSION['update_offset'])) {
      fseek($handle, 0, SEEK_END);
      if ($_SESSION['update_offset'] > ftell($handle)) //log rotated/truncated
        $_SESSION['update_offset'] = 0; //continue at beginning of the new log
      $data = stream_get_contents($handle, -1, $_SESSION['update_offset']);
      $_SESSION['update_offset'] += strlen($data);
      echo nl2br($data);
      }
    else {
      fseek($handle, 0, SEEK_END);
      $_SESSION['update_offset'] = ftell($handle);
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
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['update'];?></title>
    <link rel="stylesheet" type="text/css" href="css/pistar-css.php" />
    <script type="text/javascript" src="/jquery.min.js"></script>
    <script type="text/javascript" src="/jquery-timing.min.js"></script>
    <script type="text/javascript">
    $(function() {
      $.repeat(1000, function() {
        $.get('/admin/update.php?ajax', function(data) {
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
  <div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['update'];?></h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
    <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
    <a href="/admin/power.php" style="color: #ffffff;"><?php echo $lang['power'];?></a> |
    <a href="/admin/config_backup.php" style="color: #ffffff;"><?php echo $lang['backup_restore'];?></a> |
    <a href="/admin/configure.php" style="color: #ffffff;"><?php echo $lang['configuration'];?></a>
  </p>
  </div>
  <div class="contentwide">
  <table width="100%">
  <tr><th>Update Running</th></tr>
  <tr><td align="left"><div id="tail">Starting update, please wait...<br /></div></td></tr>
  </table>
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
?>
