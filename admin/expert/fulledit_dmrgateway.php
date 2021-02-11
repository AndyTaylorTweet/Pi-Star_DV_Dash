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
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php" />
  </head>
  <body>
  <div class="container">
  <?php include './header-menu.inc'; ?>
  <div class="contentwide">
  <?php
if(isset($_POST['data'])) {
        // File Wrangling
        exec('sudo cp /etc/dmrgateway /tmp/fmehg65694eg.tmp');
        exec('sudo chown www-data:www-data /tmp/fmehg65694eg.tmp');
        exec('sudo chmod 664 /tmp/fmehg65694eg.tmp');

        // Open the file and write the data
        $filepath = '/tmp/fmehg65694eg.tmp';
        $fh = fopen($filepath, 'w');
        fwrite($fh, $_POST['data']);
        fclose($fh);
        exec('sudo mount -o remount,rw /');
        exec('sudo cp /tmp/fmehg65694eg.tmp /etc/dmrgateway');
        exec('sudo chmod 644 /etc/dmrgateway');
        exec('sudo chown root:root /etc/dmrgateway');
        exec('sudo mount -o remount,ro /');
  
        // Reload the affected daemon
	exec('sudo systemctl restart mmdvmhost.service');		    // Reload MMDVMHost
	exec('sudo systemctl restart dmrgateway.service');		    // Reload DMRGateway

        // Re-open the file and read it
        $fh = fopen($filepath, 'r');
        $theData = fread($fh, filesize($filepath));

} else {
        // File Wrangling
        exec('sudo cp /etc/dmrgateway /tmp/fmehg65694eg.tmp');
        exec('sudo chown www-data:www-data /tmp/fmehg65694eg.tmp');
        exec('sudo chmod 664 /tmp/fmehg65694eg.tmp');

        // Open the file and read it
        $filepath = '/tmp/fmehg65694eg.tmp';
        $fh = fopen($filepath, 'r');
        $theData = fread($fh, filesize($filepath));
}
fclose($fh);

?>
<form name="test" method="post" action="">
<textarea name="data" cols="80" rows="45"><?php echo $theData; ?></textarea><br />
<input type="submit" name="submit" value="<?php echo $lang['apply']; ?>" />
</form>

</div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_new">here</a>.<br />
</div>

</div>
</body>
</html>
