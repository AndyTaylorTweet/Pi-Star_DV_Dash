<?php
// Do some file wrangling...
exec('sudo cp /etc/ysfgateway /tmp/eXNmZ2F0ZXdheQ.tmp');
exec('sudo chown www-data:www-data /tmp/eXNmZ2F0ZXdheQ.tmp');
exec('sudo chmod 664 /tmp/eXNmZ2F0ZXdheQ.tmp');

// ini file to open
$filepath = '/tmp/eXNmZ2F0ZXdheQ.tmp';

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
			$section = str_replace("_", ".", $section);
			$content .= "[".$section."]\n";
			//append the values
			foreach($values as $key=>$value) {
				$content .= $key."=".$value."\n";
			}
			$content .= "\n";
		}

		// write it into file
		if (!$handle = fopen($filepath, 'w')) {
			return false;
		}

		$success = fwrite($handle, $content);
		fclose($handle);

		// Updates complete - copy the working file back to the proper location
		exec('sudo mount -o remount,rw /');				// Make rootfs writable
		exec('sudo cp /tmp/eXNmZ2F0ZXdheQ.tmp /etc/ysfgateway');	// Move the file back
		exec('sudo chmod 644 /etc/ysfgateway');				// Set the correct runtime permissions
		exec('sudo chown root:root /etc/ysfgateway');			// Set the owner
		exec('sudo mount -o remount,ro /');				// Make rootfs read-only

		// Reload the affected daemon
		exec('sudo systemctl restart ysfgateway.service');		// Reload the daemon
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

echo "</form>\n</body>\n</html>\n";
?>
