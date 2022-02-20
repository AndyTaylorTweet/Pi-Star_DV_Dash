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
    <script type="text/javascript">
      function factoryReset()
	{
	  if (confirm('WARNING: This will set these settings back to factory defaults.\n\nAre you SURE you want to do this?\n\nPress OK to restore the factory configuration\nPress Cancel to go back.')) {
	    document.getElementById("factoryReset").submit();
	    } else {
	    return false;
	    }
	}
    </script>
  </head>
  <body>
  <div class="container">
  <?php include './header-menu.inc'; ?>
  <div class="contentwide">

<?php
if (!file_exists('/etc/pistar-css.ini')) {
	//The source file does not exist, lets create it....
	$outFile = fopen("/tmp/bW1kd4jg6b3N0DQo.tmp", "w") or die("Unable to open file!");
	$fileContent = "[Background]\nPage=edf0f5\nContent=ffffff\nBanners=dd4b39\n\n";
	$fileContent .= "[Text]\nBanners=ffffff\nBannersDrop=303030\n\n";
	$fileContent .= "[Tables]\nHeadDrop=8b0000\nBgEven=f7f7f7\nBgOdd=d0d0d0\n\n";
	$fileContent .= "[Content]\nText=000000\n\n";
	$fileContent .= "[BannerH1]\nEnabled=0\nText=\"Some Text\"\n\n";
	$fileContent .= "[BannerExtText]\nEnabled=0\nText=\"Some long text entry\"\n\n";
	$fileContent .= "[Lookup]\nService=\"RadioID\"\n";
	fwrite($outFile, $fileContent);
	fclose($outFile);
	
	// Put the file back where it should be
	exec('sudo mount -o remount,rw /');                             // Make rootfs writable
	exec('sudo cp /tmp/bW1kd4jg6b3N0DQo.tmp /etc/pistar-css.ini');  // Move the file back
	exec('sudo chmod 644 /etc/pistar-css.ini');                     // Set the correct runtime permissions
	exec('sudo chown root:root /etc/pistar-css.ini');               // Set the owner
	exec('sudo mount -o remount,ro /');                             // Make rootfs read-only
}

//Do some file wrangling...
exec('sudo cp /etc/pistar-css.ini /tmp/bW1kd4jg6b3N0DQo.tmp');
exec('sudo chown www-data:www-data /tmp/bW1kd4jg6b3N0DQo.tmp');
exec('sudo chmod 664 /tmp/bW1kd4jg6b3N0DQo.tmp');

//ini file to open
$filepath = '/tmp/bW1kd4jg6b3N0DQo.tmp';

//after the form submit
if($_POST) {
	$data = $_POST;
	// Factory Reset Handler Here
	if (empty($_POST['factoryReset']) != TRUE ) {
		echo "<br />\n";
		echo "<table>\n";
		echo "<tr><th>Factory Reset Config</th></tr>\n";
		echo "<tr><td>Loading fresh configuration file(s)...</td><tr>\n";
		echo "</table>\n";
		unset($_POST);
		//Reset the config
		exec('sudo mount -o remount,rw /');                             // Make rootfs writable
		exec('sudo rm -rf /etc/pistar-css.ini');                        // Delete the Config
		exec('sudo mount -o remount,ro /');                             // Make rootfs read-only
		echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},0);</script>';
		die();
	} else {
		//update ini file, call function
		update_ini_file($data, $filepath);
	}
}

	//this is the function going to update your ini file
	function update_ini_file($data, $filepath) {
		$content = "";

		//parse the ini file to get the sections
		//parse the ini file using default parse_ini_file() PHP function
		$parsed_ini = parse_ini_file($filepath, true);

		foreach($data as $section=>$values) {
			// UnBreak special cases
			$section = str_replace("_", " ", $section);
			$section = str_replace("BannerH2", "BannerH1", $section);
			$content .= "[".$section."]\n";
			//append the values
			foreach($values as $key=>$value) {
				if ($value == '') {
					$content .= $key."=none\n";
				}
				else {
					$content .= $key."=".$value."\n";
				}
			}
			$content .= "\n";
		}

		//write it into file
		if (!$handle = fopen($filepath, 'w')) {
			return false;
		}

		$success = fwrite($handle, $content);
		fclose($handle);

		// Updates complete - copy the working file back to the proper location
		exec('sudo mount -o remount,rw /');                             // Make rootfs writable
		exec('sudo cp /tmp/bW1kd4jg6b3N0DQo.tmp /etc/pistar-css.ini');  // Move the file back
		exec('sudo chmod 644 /etc/pistar-css.ini');                     // Set the correct runtime permissions
		exec('sudo chown root:root /etc/pistar-css.ini');               // Set the owner
		exec('sudo mount -o remount,ro /');                             // Make rootfs read-only

		return $success;
	}

//parse the ini file using default parse_ini_file() PHP function
$parsed_ini = parse_ini_file($filepath, true);
if (isset($parsed_ini['Lookup']['popupWidth']))  { unset($parsed_ini['Lookup']['popupWidth']); }
if (isset($parsed_ini['Lookup']['popupHeight'])) { unset($parsed_ini['Lookup']['popupHeight']); }

echo '<form action="" method="post">'."\n";
	foreach($parsed_ini as $section=>$values) {
		// keep the section as hidden text so we can update once the form submitted
		echo "<input type=\"hidden\" value=\"$section\" name=\"$section\" />\n";
		echo "<table>\n";
		echo "<tr><th colspan=\"2\">$section</th></tr>\n";
		// print all other values as input fields, so can edit. 
		// note the name='' attribute it has both section and key
		foreach($values as $key=>$value) {
		  if ( $section == "Lookup" && $key == "Service" ) {
		    echo "<tr><td align=\"right\" width=\"30%\">$key</td><td align=\"left\">\n";
		    echo "  <select name=\"{$section}[$key]\" />\n";
		    if ($value == "RadioID") {
		      echo "    <option value=\"RadioID\" selected=\"selected\">RadioID Callsign Lookup</option>\n";
		    } else {
		      echo "    <option value=\"RadioID\">RadioID Callsign Lookup</option>\n";
		    }
		    if ($value == "QRZ") {
		      echo "    <option value=\"QRZ\" selected=\"selected\">QRZ Callsign Lookup</option>\n";
		    } else {
		      echo "    <option value=\"QRZ\">QRZ Callsign Lookup</option>\n";
		    }
		    echo "  </select>\n";
		    echo "</td></tr>\n";
		  } else {
		    echo "<tr><td align=\"right\" width=\"30%\">$key</td><td align=\"left\"><input type=\"text\" name=\"{$section}[$key]\" value=\"$value\" /></td></tr>\n";			
		  }
		}
		echo "</table>\n";
		echo '<input type="submit" value="'.$lang['apply'].'" />'."\n";
		echo "<br /><br />\n";
	}
echo "</form>";
echo "<br />\n";
echo 'if you took it all too far and now it makes you feel sick, click below to reset the changes made on this page, this will ONLY reset the CSS settings above and will not change any other settings or configuration.'."\n";
echo '<form id="factoryReset" action="" method="post">'."\n";
echo '  <div><input type="hidden" name="factoryReset" value="1" /></div>'."\n";
echo '</form>'."\n";
echo '<input type="button" onclick="javascript:factoryReset();" value="'.$lang['factory_reset'].'" />'."\n";
?>
</div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_new">here</a>.<br />
</div>

</div>
</body>
</html>
