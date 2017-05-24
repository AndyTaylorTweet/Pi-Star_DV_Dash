<?php
//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
//Load the Version Info
require_once('config/version.php');
// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/config_backup.php") {
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
    <title>Pi-Star Config Backup</title>
    <LINK REL="stylesheet" type="text/css" href="css/ircddb.css" />
  </head>
  <body>
  <div class="container">
  <div id="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star Digital Voice - Config Backup / Restore</h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" alt="Dashboard" style="color: #ffffff;">Dashboard</a> |
    <a href="/admin/" alt="Administration" style="color: #ffffff;">Admin</a> |
    <a href="/admin/power.php" alt="Power Control" style="color: #ffffff;">Power</a> |
    <a href="/admin/update.php" alt="System Update" style="color: #ffffff;">Update</a> |
    <a href="/admin/configure.php" alt="Configuration" style="color: #ffffff;">Config</a>
  </p>
  </div>
  <div id="contentwide">
<?php if (!empty($_POST)) {
  echo '<table width="100%">'."\n";

        if ( escapeshellcmd($_POST["action"]) == "download" ) {
          echo "<tr><th colspan=\"2\">Config Backup</th></tr>\n";

          $output = "Finding config files to be backed up\n";
          $backupDir = "/tmp/config_backup";
          $backupZip = "/tmp/config_backup.zip";
          
          $output .= shell_exec("sudo rm -rf $backupZip 2>&1");
          $output .= shell_exec("sudo rm -rf $backupDir 2>&1");
          $output .= shell_exec("sudo mkdir $backupDir 2>&1");
          $output .= shell_exec("sudo cp /etc/wpa_supplicant/wpa_supplicant.conf $backupDir 2>&1");
          $output .= shell_exec("sudo cp /etc/ircddbgateway $backupDir 2>&1");
          $output .= shell_exec("sudo cp /etc/mmdvmhost $backupDir 2>&1");
          $output .= shell_exec("sudo cp /etc/dstarrepeater $backupDir 2>&1");
          $output .= shell_exec("sudo cp /etc/p25gateway $backupDir 2>&1");
          $output .= shell_exec("sudo cp /etc/ysfgateway $backupDir 2>&1");
          $output .= shell_exec("sudo cp /etc/starnetserver $backupDir 2>&1");
          $output .= shell_exec("sudo cp /etc/timeserver $backupDir 2>&1");
          $output .= shell_exec("sudo cp /etc/dstar-radio.* $backupDir 2>&1");
          $output .= "Compressing backup files\n";
          $output .= shell_exec("sudo zip -j $backupZip $backupDir/* 2>&1");
          $output .= "Starting download\n";
          
          echo "<tr><td align=\"left\"><pre>$output</pre></td></tr>\n";
          
          if (file_exists($backupZip)) {
            $utc_time = gmdate();
            $utc_tz =  new DateTimeZone('UTC');
            $local_tz = new DateTimeZone(date_default_timezone_get ());
            $dt = new DateTime($utc_time, $utc_tz);
            $dt->setTimeZone($local_tz);
            $local_time = $dt->format('d-M-Y');
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename("Pi-Star_Config_$local_time.zip"));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backupZip));
            ob_clean();
            flush();
            readfile($backupZip);
            exit;
          }

        };
        if ( escapeshellcmd($_POST["action"]) == "restore" ) {
          echo "<tr><th colspan=\"2\">Config Restore</th></tr>\n";
          $output = "Uploading your Config data\n";

          $target_dir = "/tmp/config_restore/";
          shell_exec("sudo rm -rf $target_dir 2>&1");
          shell_exec("mkdir $target_dir 2>&1");
          if($_FILES["fileToUpload"]["name"]) {
                  $filename = $_FILES["fileToUpload"]["name"];
	  	  $source = $_FILES["fileToUpload"]["tmp_name"];
	          $type = $_FILES["fileToUpload"]["type"];
	
	          $name = explode(".", $filename);
	          $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
	          foreach($accepted_types as $mime_type) {
		          if($mime_type == $type) {
			          $okay = true;
			          break;
			  }
		  }
	  }
		$continue = strtolower($name[1]) == 'zip' ? true : false;
	        if(!$continue) {
		        $output .= "The file you are trying to upload is not a .zip file. Please try again.\n";
	        }
		$target_path = $target_dir.$filename;
          
		if(move_uploaded_file($source, $target_path)) {
			$zip = new ZipArchive();
		        $x = $zip->open($target_path);
		        if ($x === true) {
			        $zip->extractTo($target_dir); // change this to the correct site path
			        $zip->close();
			        unlink($target_path);
		        }
		        $output .= "Your .zip file was uploaded and unpacked.\n";
			$output .= "Stopping Services.\n";
			
			// Stop the DV Services
			shell_exec('sudo systemctl stop cron.service 2>&1')."\n";		//Cron
			shell_exec('sudo systemctl stop dstarrepeater.service 2>&1')."\n";	//D-Star Radio Service
			shell_exec('sudo systemctl stop mmdvmhost.service 2>&1')."\n";		//MMDVMHost Radio Service
			shell_exec('sudo systemctl stop ircddbgateway.service 2>&1')."\n";	//ircDDBGateway Service
			shell_exec('sudo systemctl stop timeserver.service 2>&1')."\n";		//Time Server Service
			shell_exec('sudo systemctl stop pistar-watchdog.service 2>&1')."\n";	//PiStar-Watchdog Service
			shell_exec('sudo systemctl stop ysfgateway.service 2>&1')."\n";		//YSFGateway
			shell_exec('sudo systemctl stop p25gateway.service 2>&1')."\n";		//P25Gateway
			
			// Make the disk Writable
			shell_exec('sudo mount -o remount,rw / 2>&1')."\n";
			
			// Overwrite the configs
			$output .= "Writing new Config\n";
			$output .= shell_exec("sudo mv -v -f /tmp/config_restore/wpa_supplicant.conf /etc/wpa_supplicant/ 2>&1")."\n";
			//$output .= shell_exec("sudo mv -f /tmp/config_restore/* /etc/ 2>&1")."\n";
			
			// Make the disk Read-Only
			shell_exec('sudo mount -o remount,ro / 2>&1');
			
			// Start the services
			$output .= "Starting Services.\n";
			shell_exec('sudo systemctl start dstarrepeater.service 2>&1')."\n";	//D-Star Radio Service
			shell_exec('sudo systemctl start mmdvmhost.service 2>&1')."\n";		//MMDVMHost Radio Service
			shell_exec('sudo systemctl start ircddbgateway.service 2>&1')."\n";	//ircDDBGateway Service
			shell_exec('sudo systemctl start timeserver.service 2>&1')."\n";	//Time Server Service
			shell_exec('sudo systemctl start pistar-watchdog.service 2>&1')."\n";	//PiStar-Watchdog Service
			shell_exec('sudo systemctl start pistar-upnp.service 2>&1')."\n";	//PiStar-UPnP Service
			shell_exec('sudo systemctl start ysfgateway.service 2>&1')."\n";	//YSFGateway
			shell_exec('sudo systemctl start p25gateway.service 2>&1')."\n";	//P25Gateway
			shell_exec('sudo systemctl start cron.service 2>&1')."\n";		//Cron
			
			// Complete
			$output .= "Configuration Restore Complete.\n";
		}
		else {
			$output .= "There was a problem with the upload. Please try again.";
		}
	  echo "<tr><td align=\"left\"><pre>$output</pre></td></tr>\n";
  };

  echo "</table>\n";
  } else { ?>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
  <table width="100%">
  <tr>
    <th colspan="2">Backup / Restore</th>
  </tr>
  <tr>
    <td align="center" width="50%">Download Configuration<br /><input type="image" src="/images/download.png" name="action" value="download" /></td>
    <td align="center">Restore Configuration<br /><input type="image"  src="/images/restore.png" name="action" value="restore" /><br />
    <input type="file" name="fileToUpload" id="fileToUpload"></td>
  </tr>
  </table>
  </form>
<?php } ?>
  </div>
  <div id="footer">
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
