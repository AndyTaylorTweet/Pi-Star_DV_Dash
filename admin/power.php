<?php
// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/power.php") {
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
    <title>Pi-Star Power Control</title>
    <LINK REL="stylesheet" type="text/css" href="css/ircddb.css" />
  </head>
  <body>
  <div class="container">
  <div id="header">
  <h1>Pi-Star Digital Voice - Power Control</h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" alt="Dashboard" style="color: #ffffff;">Dashboard</a> |
    <a href="/admin/" alt="Administration" style="color: #ffffff;">Admin</a> |
    <a href="/admin/power.php" alt="Power Control" style="color: #ffffff;">Power</a> |
    <a href="/admin/configure.php" alt="Configuration" style="color: #ffffff;">Config</a>
  </p>
  </div>
  <div id="contentwide">
<?php if (!empty($_POST)) { ?>
  <table width="100%">
  <tr><th colspan="2">Power Control</th></tr>
  <?php
        if ( escapeshellcmd($_POST["action"]) == "reboot" ) {
                echo '<tr><td colspan="2" style="background: #000000; color: #00ff00;"><br /><br />Reboot command has been sent to your Pi,
                        <br />please wait 30 secs for it to reboot.<br />
                        <br />You will be re-directed back to the
                        <br />dashboard automatically in 20 seconds.<br /><br /><br />
                        <script language="JavaScript" type="text/javascript">
                                setTimeout("location.href = \'/index.php\'",30000);
                        </script>
                        </td></tr>';
                system('sudo mount -o remount,ro / > /dev/null &');
                exec('sleep 5 && sudo shutdown -r now > /dev/null &');
                };
        if ( escapeshellcmd($_POST["action"]) == "shutdown" ) {
                echo '<tr><td colspan="2" style="background: #000000; color: #00ff00;"><br /><br />Shutdown command has been sent to your Pi,
                        <br /> please wait 30 secs for it to fully shutdown<br />before removing the power.<br /><br /><br /></td></tr>';
                system('sudo mount -o remount,ro / > /dev/null &');
                exec('sleep 5 && sudo shutdown -h now > /dev/null &');
                };
  ?>
  </table>
<?php } else { ?>
  <form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
  <table width="100%">
  <tr>
    <th colspan="2">Power Control</th>
  </tr>
  <tr>
    <td align="center">Reboot<br /><input type="image" src="/images/reboot.png" name="action" value="reboot" /></td>
    <td align="center">Shutdown<br /><input type="image"  src="/images/shutdown.png" name="action" value="shutdown" /></td>
  </tr>
  </table>
  </form>
<?php } ?>
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
