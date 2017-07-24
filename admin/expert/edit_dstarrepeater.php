<?php
require_once('config/version.php');

//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
<head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php echo "<meta name=\"generator\" content=\"$progname $rev\" />\n"; ?>
    <meta name="Author" content="Hans-J. Barthen (DL5DI), Kim Huebel (DV9VH) and Andy Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Dashboard" />
    <meta name="KeyWords" content="MW0MWZ,MMDVMHost,ircDDBGateway,D-Star,ircDDB,Pi-Star,Blackwood,Wales,DL5DI,DG9VH" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="pragma" content="no-cache" />
    <title><?php echo "$MYCALL" ?> - Digital Voice Dashboard</title>
<?php include_once "config/browserdetect.php"; ?>
    <script type="text/javascript" src="/jquery.min.js"></script>
    <script type="text/javascript" src="/functions.js"></script>
    <script type="text/javascript">
      $.ajaxSetup({ cache: false });
    </script>
</head>
<body>
<div class="container">
<div class="header">
<div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
<h1>Pi-Star Digital Voice Dashboard for <?php echo $MYCALL; ?></h1>
<p style="padding-right: 5px; text-align: right; color: #ffffff;">
 <a href="/" style="color: #ffffff;">Dashboard</a> |
 <a href="/admin/" style="color: #ffffff;">Admin</a> |
<?php if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
  echo ' <a href="/admin/live_modem_log.php" style="color: #ffffff;">Live Logs</a> |'."\n";
  echo ' <a href="/admin/power.php" style="color: #ffffff;">Power</a> |'."\n";
  echo ' <a href="/admin/update.php" style="color: #ffffff;">Update</a> |'."\n";
  } ?>
 <a href="/admin/configure.php" style="color: #ffffff;">Config</a>
</p>
</div>

<?php
// Do some file wrangling...
exec('sudo cp /etc/dstarrepeater /tmp/ZHN0YXJyZXBlYXRlcg.tmp');
exec('sudo chown www-data:www-data /tmp/ZHN0YXJyZXBlYXRlcg.tmp');
exec('sudo chmod 664 /tmp/ZHN0YXJyZXBlYXRlcg.tmp');

// ini file to open
$filepath = '/tmp/ZHN0YXJyZXBlYXRlcg.tmp';

// Mangle the input
$file_content = "[dstarrepeater]\n".preg_replace('~\r\n?~', "\n", file_get_contents($filepath));
file_put_contents($filepath, $file_content);

// after the form submit
if($_POST) {
	$data = $_POST;
	//update ini file, call function
	update_ini_file($data, $filepath);
}

// this is the function going to update your ini file
	function update_ini_file($data, $filepath) {
		$content = "";

		// parse the ini file to get the sections
		// parse the ini file using default parse_ini_file() PHP function
		$parsed_ini = parse_ini_file($filepath, true);

		foreach($data as $section=>$values) {
			// UnBreak special cases
			$section = str_replace("_", " ", $section);
			$content .= "[".$section."]\n";
                        //append the values
                        foreach($values as $key=>$value) {
                                if ($value == '') { 
                                        $content .= $key."= \n"; 
                                        }
                                else {
                                        $content .= $key."=".$value."\n";
                                        }
                        }
		}

		// write it into file
		if (!$handle = fopen($filepath, 'w')) {
			return false;
		}

		$success = fwrite($handle, $content);
		fclose($handle);

		// Updates complete - copy the working file back to the proper location
		exec('sudo mount -o remount,rw /');					// Make rootfs writable
		exec('sudo cp /tmp/ZHN0YXJyZXBlYXRlcg.tmp /etc/dstarrepeater');		// Move the file back
		exec('sudo sed -i \'/\\[dstarrepeater\\]/d\' /etc/dstarrepeater');	// Clean up file mangling
		exec('sudo chmod 644 /etc/dstarrepeater');				// Set the correct runtime permissions
		exec('sudo chown root:root /etc/dstarrepeater');			// Set the owner
		exec('sudo mount -o remount,ro /');					// Make rootfs read-only

		// Reload the affected daemon
		exec('sudo systemctl restart timeserver.service');			// Reload the daemon
		return $success;
	}

echo "<html>\n<body>";

// parse the ini file using default parse_ini_file() PHP function
$parsed_ini = parse_ini_file($filepath, true);

echo '<form action="" method="post">'."\n";
	foreach($parsed_ini as $section=>$values) {
		echo "<h3>$section</h3>\n";
		// keep the section as hidden text so we can update once the form submitted
		echo "<input type='hidden' value='$section' name='$section' />\n";
		// print all other values as input fields, so can edit. 
		// note the name='' attribute it has both section and key
		foreach($values as $key=>$value) {
			echo "$key: <input type='text' name='{$section}[$key]' value='$value' /><br />\n";
		}
		echo '<input type="submit" value="Save Changes" />'."\n";
		echo "<br />\n";
	}

echo "</form>";

?>
</div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
ircDDBGateway Dashboard by Hans-J. Barthen (DL5DI),<br />
MMDVMDash developed by Kim Huebel (DG9VH), <br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistar/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.mw0mwz.co.uk/pi-star/" target="_new">here</a>.<br />
</div>

</div>
</body>
</html>
