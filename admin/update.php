<?php
// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/update.php") {

  // Sanity Check Passed.
  require_once('config/ircddblocal.php');
  $MYCALL=strtoupper($callsign);
  //Load the pistar-release file
  $pistarReleaseConfig = '/etc/pistar-release';
  $configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
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
    <title><?php echo "$MYCALL" ?> Hotspot Update Dashboard</title>
    <LINK REL="stylesheet" type="text/css" href="css/ircddb.css" />
    <script type="text/javascript" src="/jquery.min.js"></script>
  </head>
  <body>
  <div class="container">
  <div id="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">V<?php echo $configPistarRelease['Pi-Star']['Version']?></div>
  <h1>Pi-Star Digital Voice Update</h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" alt="Dashboard" style="color: #ffffff;">Dashboard</a> |
    <a href="/admin/" alt="Administration" style="color: #ffffff;">Admin</a> |
    <a href="/admin/configure.php" alt="Configuration" style="color: #ffffff;">Config</a>
  </p>
  </div>
  <div id="contentwide">
  <table width="100%">
    <tr>

  <?php
    
  $cmd = 'ping -c4 www.yahoo.com';
  
  function setupStreaming() {
    // Turn off output buffering
    ini_set('output_buffering', 'off');
    // Turn off PHP output compression
    ini_set('zlib.output_compression', false);
  }
  
  function runStreamingCommand($cmd){
    echo <th>running $cmd</th></tr>\n<tr><td>\n";
    system($cmd);
  }

  setupStreaming();
  runStreamingCommand($cmd);

  ?>
      </td></tr>
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
