<?php
if ($_SERVER["PHP_SELF"] == "/admin/index.php") { // Stop this working outside of the admin page
	include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
	include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
	include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
	include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

	// Check if NXDN is Enabled
	$testMMDVModeNXDN = getConfigItem("NXDN Network", "Enable", $mmdvmconfigs);
	if ( $testMMDVModeNXDN == 1 ) {

	  //Load the nxdngateway config file
	  $nxdnGatewayConfigFile = '/etc/nxdngateway';
	  if (fopen($nxdnGatewayConfigFile,'r')) { $confignxdngateway = parse_ini_file($nxdnGatewayConfigFile, true); }

	  // Check that the remote is enabled
	  if ( $confignxdngateway['Remote Commands']['Enable'] == 1 ) {
	    $remotePort = $confignxdngateway['Remote Commands']['Port'];
	    if (!empty($_POST) && isset($_POST["nxdnMgrSubmit"])) {
	      // Handle Posted Data
	      if (preg_match('/[^A-Za-z0-9]/',$_POST['nxdnLinkHost'])) { unset ($_POST['nxdnLinkHost']);}
	      if ($_POST["Link"] == "LINK") {
		if ($_POST['nxdnLinkHost'] == "none") {
		  $remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." TalkGroup unlink";
		} else {
		  $remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." TalkGroup".$_POST['nxdnLinkHost'];
		}
	      } elseif ($_POST["Link"] == "UNLINK") {
		$remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." TalkGroup unlink";
	      } else {
		echo "<b>NXDN Link Manager</b>\n";
		echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		echo "Somthing wrong with your input, (Neither Link nor Unlink Sent) - please try again";
		echo "</td></tr>\n</table>\n<br />\n";
		unset($_POST);
		echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
	      }
	      if (empty($_POST['nxdnLinkHost'])) {
		echo "<b>NXDN Link Manager</b>\n";
		echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		echo "Somthing wrong with your input, (No target specified) -  please try again";
		echo "</td></tr>\n</table>\n<br />\n";
		unset($_POST);
		echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
	      }
	      if (isset($remoteCommand)) {
		echo "<b>NXDN Link Manager</b>\n";
		echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
		echo exec($remoteCommand);
		echo "</td></tr>\n</table>\n<br />\n";
		echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
	      }
	    } else {
	      // Output HTML
	      ?>
	      <b>NXDN Link Manager</b>
	      <form action="//<?php echo htmlentities($_SERVER['HTTP_HOST']).htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
	      <table>
		<tr>
		  <th width="150"><a class="tooltip" href="#">Reflector<span><b>Reflector</b></span></a></th>
		  <th width="150"><a class="tooltip" href="#">Link / Un-Link<span><b>Link / Un-Link</b></span></a></th>
		  <th width="150"><a class="tooltip" href="#">Action<span><b>Action</b></span></a></th>
		</tr>
		<tr>
		  <td>
		    <select name="nxdnLinkHost">
		    <?php
		    $nxdnHosts = fopen("/usr/local/etc/NXDNHosts.txt", "r");
		    if (isset($confignxdngateway['Network']['Static'])) { $testNXDNHost = $confignxdngateway['Network']['Static']; } else { $testNXDNHost = ""; }
		    if ($testNXDNHost == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
			  else { echo "      <option value=\"none\">None</option>\n"; }
		    if ($testNXDNHost == "10") { echo "      <option value=\"10\" selected=\"selected\">10 - Parrot</option>\n"; }
			  else { echo "      <option value=\"10\">10 - Parrot</option>\n"; }
			  while (!feof($nxdnHosts)) {
				  $nxdnHostsLine = fgets($nxdnHosts);
				  $nxdnHost = preg_split('/\s+/', $nxdnHostsLine);
				  if ((strpos($nxdnHost[0], '#') === FALSE ) && ($nxdnHost[0] != '')) {
					  if ($testNXDNHost == $nxdnHost[0]) { echo "		          <option value=\"$nxdnHost[0]\" selected=\"selected\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
					  else { echo "		          <option value=\"$nxdnHost[0]\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
				  }
			  }
			  fclose($nxdnHosts);
		    if (file_exists('/usr/local/etc/NXDNHostsLocal.txt')) {
		      $nxdnHosts2 = fopen("/usr/local/etc/NXDNHostsLocal.txt", "r");
		      while (!feof($nxdnHosts2)) {
				    $nxdnHostsLine2 = fgets($nxdnHosts2);
				    $nxdnHost2 = preg_split('/\s+/', $nxdnHostsLine2);
				    if ((strpos($nxdnHost2[0], '#') === FALSE ) && ($nxdnHost2[0] != '')) {
					    if ($testNXDNHost == $nxdnHost2[0]) { echo "		          <option value=\"$nxdnHost2[0]\" selected=\"selected\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
					    else { echo "		          <option value=\"$nxdnHost2[0]\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
				    }
		      }
		      fclose($nxdnHosts2);
		    }
		    ?>
		    </select>
		  </td>
		  <td>
		    <input type="radio" name="Link" value="LINK" checked="checked" />Link
		    <input type="radio" name="Link" value="UNLINK" />UnLink
		  </td>
		  <td>
		    <input type="submit" name="nxdnMgrSubmit" value="Request Change" />
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
