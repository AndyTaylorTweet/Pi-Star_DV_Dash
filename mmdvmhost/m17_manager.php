<?php
if ($_SERVER["PHP_SELF"] == "/admin/index.php") { // Stop this working outside of the admin page
	include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
	include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
	include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
	include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

	// Check if M17 is Enabled
	$testMMDVModeM17 = getConfigItem("M17 Network", "Enable", $mmdvmconfigs);
	if ( $testMMDVModeM17 == 1 ) {

	  //Load the m17gateway config file
	  $m17GatewayConfigFile = '/etc/m17gateway';
	  if (fopen($m17GatewayConfigFile,'r')) { $configm17gateway = parse_ini_file($m17GatewayConfigFile, true); }

	  // Check that the remote is enabled
	  if ( $configm17gateway['Remote Commands']['Enable'] == 1 ) {
	    $remotePort = $configm17gateway['Remote Commands']['Port'];
	    if (!empty($_POST) && isset($_POST["m17MgrSubmit"])) {
	      // Handle Posted Data
	      if (preg_match('/[^A-Za-z0-9-]/',$_POST['m17LinkHost'])) { unset ($_POST['m17LinkHost']); unset ($_POST['m17LinkRoom']); }
	      if (preg_match('/[^A-Z]/',$_POST['m17LinkRoom'])) { unset ($_POST['m17LinkHost']); unset ($_POST['m17LinkRoom']); }
	      if ($_POST["Link"] == "LINK") {
		if ($_POST['m17LinkHost'] == "none") {
		  $remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." Reflector unlink";
		} else {
		  $remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." Reflector ".$_POST['m17LinkHost']." ".$_POST['m17LinkRoom'];
		}
	      } elseif ($_POST["Link"] == "UNLINK") {
		$remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." Reflector unlink";
	      } else {
		echo "<b>M17 Link Manager</b>\n";
		echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		echo "Somthing wrong with your input, (Neither Link nor Unlink Sent) - please try again";
		echo "</td></tr>\n</table>\n<br />\n";
		unset($_POST);
		echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
	      }
	      if (empty($_POST['m17LinkHost'])) {
		echo "<b>M17 Link Manager</b>\n";
		echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		echo "Somthing wrong with your input, (No target specified) -  please try again";
		echo "</td></tr>\n</table>\n<br />\n";
		unset($_POST);
		echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
	      }
	      if (isset($remoteCommand)) {
		echo "<b>M17 Link Manager</b>\n";
		echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		echo exec($remoteCommand);
		echo "</td></tr>\n</table>\n<br />\n";
		echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
	      }
	    } else {
	      // Output HTML
	      ?>
	      <b>M17 Link Manager</b>
	      <form action="//<?php echo htmlentities($_SERVER['HTTP_HOST']).htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
	      <table>
		<tr>
		  <th width="150"><a class="tooltip" href="#">Reflector<span><b>Reflector</b></span></a></th>
		  <th width="150"><a class="tooltip" href="#">Link / Un-Link<span><b>Link / Un-Link</b></span></a></th>
		  <th width="150"><a class="tooltip" href="#">Action<span><b>Action</b></span></a></th>
		</tr>
		<tr>
		  <td>
		    <select name="m17LinkHost">
		    <?php
		    $m17Hosts = fopen("/usr/local/etc/M17Hosts.txt", "r");
		    if (isset($configm17gateway['Network']['Startup'])) { $testM17Host = explode("_", $configm17gateway['Network']['Startup'])[0]; } else { $testM17Host = ""; }
		    if ($testM17Host == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
		    else { echo "      <option value=\".$testM17Host.\">None</option>\n"; }
			  while (!feof($m17Hosts)) {
				  $m17HostsLine = fgets($m17Hosts);
				  $m17Host = preg_split('/\s+/', $m17HostsLine);
				  if ((strpos($m17Host[0], '#') === FALSE ) && ($m17Host[0] != '')) {
					  if ($testM17Host == $m17Host[0]) { echo "		          <option value=\"$m17Host[0]\" selected=\"selected\">$m17Host[0]</option>\n"; }
					  else { echo "		          <option value=\"$m17Host[0]\">$m17Host[0]</option>\n"; }
				  }
			  }
			  fclose($m17Hosts);
		    if (file_exists('/root/M17Hosts.txt')) {
		      $m17Hosts2 = fopen("/root/M17Hosts.txt", "r");
		      while (!feof($m17Hosts2)) {
				    $m17HostsLine2 = fgets($m17Hosts2);
				    $m17Host2 = preg_split('/\s+/', $m17HostsLine2);
				    if ((strpos($m17Host2[0], '#') === FALSE ) && ($m17Host2[0] != '')) {
					    if ($testM17Host == $m17Host2[0]) { echo "		          <option value=\"$m17Host2[0]\" selected=\"selected\">$m17Host2[0]</option>\n"; }
					    else { echo "		          <option value=\"$m17Host2[0]\">$m17Host2[0]</option>\n"; }
				    }
		      }
		      fclose($m17Hosts2);
		    }
		    ?>
		    </select>
		    <select name="m17LinkRoom">
		      <?php if (isset($configm17gateway['Network']['Startup'])) { echo "<option value=\"".substr($configm17gateway['Network']['Startup'], -1)."\" selected=\"selected\">".substr($configm17gateway['Network']['Startup'], -1)."</option>"; } ?>
		      <option>A</option>
		      <option>B</option>
		      <option>C</option>
		      <option>D</option>
		      <option>E</option>
		      <option>F</option>
		      <option>G</option>
		      <option>H</option>
		      <option>I</option>
		      <option>J</option>
		      <option>K</option>
		      <option>L</option>
		      <option>M</option>
		      <option>N</option>
		      <option>O</option>
		      <option>P</option>
		      <option>Q</option>
		      <option>R</option>
		      <option>S</option>
		      <option>T</option>
		      <option>U</option>
		      <option>V</option>
		      <option>W</option>
		      <option>X</option>
		      <option>Y</option>
		      <option>Z</option>
		    </select>
		  </td>
		  <td>
		    <input type="radio" name="Link" value="LINK" checked="checked" />Link
		    <input type="radio" name="Link" value="UNLINK" />UnLink
		  </td>
		  <td>
		    <input type="submit" name="m17MgrSubmit" value="Request Change" />
		  </td>
		</tr>
	      </table>
	      </form>
	      <br />
	      <?php
	    }
	  }
	}
}
?>
