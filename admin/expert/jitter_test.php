<?php
// Load the language support
require_once('../config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('../config/version.php');

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/expert/jitter_test.php") {

  if (isset($_GET['group'])) {
    if ($_GET['group'] == "brandmeister") { $target = "BM"; }
    if ($_GET['group'] == "dmrplus")      { $target = "DMR+"; }
    if ($_GET['group'] == "hblink")       { $target = "HB"; }
  } else { $target = "DMR+"; }

  if (!isset($_GET['ajax'])) {
    system('sudo touch /var/log/pi-star/pi-star_icmptest.log > /dev/null 2>&1 &');
    system('sudo echo "" > /var/log/pi-star/pi-star_icmptest.log > /dev/null 2>&1 &');
    system('sudo /usr/local/sbin/pistar-jittertest '.$target.' > /dev/null 2>&1 &');
  }

  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();

  if (!isset($_GET['ajax'])) {
    //unset($_SESSION['update_offset']);
    if (file_exists('/var/log/pi-star/pi-star_icmptest.log')) {
      $_SESSION['update_offset'] = filesize('/var/log/pi-star/pi-star_icmptest.log');
    } else {
      $_SESSION['update_offset'] = 0;
    }
  }

  if (isset($_GET['ajax'])) {
    //session_start();
    if (!file_exists('/var/log/pi-star/pi-star_icmptest.log')) {
      exit();
    }

    $handle = fopen('/var/log/pi-star/pi-star_icmptest.log', 'rb');
    if (isset($_SESSION['update_offset'])) {
      fseek($handle, 0, SEEK_END);
      if ($_SESSION['update_offset'] > ftell($handle)) //log rotated/truncated
        $_SESSION['update_offset'] = 0; //continue at beginning of the new log
      $data = stream_get_contents($handle, -1, $_SESSION['update_offset']);
      $_SESSION['update_offset'] += strlen($data);
      echo "<pre>$data</pre>";
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
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['update'];?></title>
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php" />
    <script type="text/javascript" src="/jquery.min.js"></script>
    <script type="text/javascript" src="/jquery-timing.min.js"></script>
    <script type="text/javascript">
    $(function() {
      $.repeat(1000, function() {
        $.get('/admin/expert/jitter_test.php?ajax', function(data) {
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
  <tr><th>Test Running</th></tr>
  <tr><td align="left"><div id="tail">Starting test, please wait...<br /></div></td></tr>
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
