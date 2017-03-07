<?php
// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/update.php" || $_SERVER["PHP_SELF"] == "/admin/update.php?ajax") {
  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();

  if (isset($_GET['ajax'])) {
    $handle = fopen('/var/log/pi-star/pi-star_update.log', 'r');
    if (isset($_SESSION['offset'])) {
      fseek($handle, $_SESSION['offset']);
      // echo nl2br($data);
      while (($buffer = fgets($handle, 4096)) !== false) {
        echo nl2br($buffer);
        }
      if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
        }
      }
   fseek($handle, 0, SEEK_END); 
   $_SESSION['offset'] = ftell($handle);
   exit();
   }
  else {
    system('sudo nohup rm -rf /var/log/pi-star/pi-star_update.log &');
    system('sudo nohup /usr/local/sbin/pistar-update &');
    }
 unset($_SESSION['offset']);
  
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
        });
      });
    });
    </script>
  </head>
  <body>
  <div class="container">
  <div id="header">
  <h1>Pi-Star Digital Voice Update</h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" alt="Dashboard" style="color: #ffffff;">Dashboard</a> |
    <a href="/admin/" alt="Administration" style="color: #ffffff;">Admin</a> |
    <a href="/admin/configure.php" alt="Configuration" style="color: #ffffff;">Config</a>
  </p>
  </div>
  <div id="contentwide">
  <table width="100%">
  <tr><th>Update Running</th></tr>
  <tr><td align="left"><div id="tail">Starting update...<br></div></td></tr>
  </table>
  </div>
  <div id="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.mw0mwz.co.uk/pi-star/" target="_blank">here</a>.<br />
  <br />
  </div>
  </div>
  </body>
  </html>

<?php
}
?>
