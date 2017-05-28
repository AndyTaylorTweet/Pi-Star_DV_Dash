<?php
//Do some file wrangling...
exec('sudo cp /etc/mmdvmhost /tmp/bW1kdm1ob3N0DQo.tmp');
exec('sudo chown www-data:www-data /tmp/bW1kdm1ob3N0DQo.tmp');
exec('sudo chmod 664 /tmp/bW1kdm1ob3N0DQo.tmp');

//ini file to open
$filepath = '/tmp/bW1kdm1ob3N0DQo.tmp';

//after the form submit
if($_POST) {
	$data = $_POST;
	//update ini file, call function
	update_ini_file($data, $filepath);
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
			$content .= "[".$section."]\n";
			//append the values
			foreach($values as $key=>$value) {
				if ($section == "DMR Network" && $key == "Options" && $value) {
					$content .= $key."=\"".$value."\"\n";
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
		exec('sudo mount -o remount,rw /');				// Make rootfs writable
		exec('sudo cp /tmp/bW1kdm1ob3N0DQo.tmp /etc/mmdvmhost');	// Move the file back
		exec('sudo chmod 644 /etc/mmdvmhost');				// Set the correct runtime permissions
		exec('sudo chown root:root /etc/mmdvmhost');			// Set the owner
		exec('sudo mount -o remount,ro /');				// Make rootfs read-only

		// Reload the affected daemon
		exec('sudo systemctl restart mmdvmhost.service');		// Reload the daemon
		return $success;
	}

echo "<html>\n<body>";

//parse the ini file using default parse_ini_file() PHP function
$parsed_ini = parse_ini_file($filepath, true);

echo '<form action="" method="post">'."\n";
	foreach($parsed_ini as $section=>$values) {
		echo "<h3>$section</h3>\n";
		//keep the section as hidden text so we can update once the form submitted
		echo "<input type=\"hidden\" value=\"$section\" name=\"$section\" />\n";
		//print all other values as input fields, so can edit. 
		//note the name='' attribute it has both section and key
		foreach($values as $key=>$value) {
			echo "$key: <input type=\"text\" size=\"35\" name=\"{$section}[$key]\" value=\"$value\" /><br />\n";
		}
		echo '<input type="submit" value="Save Changes" />'."\n";
		echo "<br />\n";
	}

echo "</form>\n</body>\n</html>\n";
?>
