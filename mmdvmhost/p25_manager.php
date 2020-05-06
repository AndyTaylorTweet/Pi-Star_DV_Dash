<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// Check if P25 is Enabled
$testMMDVModeP25 = getConfigItem("P25 Network", "Enable", $mmdvmconfigs);
if ( $testMMDVModeP25 == 1 ) {

  //Load the p25gateway config file
  $p25GatewayConfigFile = '/etc/p25gateway';
  if (fopen($p25GatewayConfigFile,'r')) { $configp25gateway = parse_ini_file($p25GatewayConfigFile, true); }

  // Check that the remote is enabled
  if ( $configp25gateway['Remote Commands']['Enable'] == 1 ) {
    $remotePort = $configp25gateway['Remote Commands']['Port'];
    if (!empty($_POST) && isset($_POST["p25MgrSubmit"])) {
      // Handle Posted Data
      if (preg_match('/[^A-Za-z0-9]/',$_POST['p25LinkHost'])) { unset ($_POST['p25LinkHost']);}
      if ($_POST["Link"] == "LINK") {
	if ($_POST['p25LinkHost'] == "none") {
	  $remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." TalkGroup9999";
	} else {
	  $remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." TalkGroup".$_POST['p25LinkHost'];
	}
      } elseif ($_POST["Link"] == "UNLINK") {
	$remoteCommand = "cd /var/log/pi-star && sudo /usr/local/bin/RemoteCommand ".$remotePort." TalkGroup9999";
      } else {
	echo "<b>P25 Link Manager</b>\n";
	echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
        echo "Somthing wrong with your input, (Neither Link nor Unlink Sent) - please try again";
	echo "</td></tr>\n</table>\n<br />\n";
	unset($_POST);
	echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
      }
      if (empty($_POST['p25LinkHost'])) {
	echo "<b>P25 Link Manager</b>\n";
        echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
	echo "Somthing wrong with your input, (No target specified) -  please try again";
	echo "</td></tr>\n</table>\n<br />\n";
	unset($_POST);
	echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
      }
      if (isset($remoteCommand)) {
        echo "<b>P25 Link Manager</b>\n";
	echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
	echo exec($remoteCommand);
	echo "</td></tr>\n</table>\n<br />\n";
	echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
      }
    } else {
      // Output HTML
      ?>
      <b>P25 Link Manager</b>
      <form action="http://<?php echo htmlentities($_SERVER['HTTP_HOST']).htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
      <table>
        <tr>
          <th width="150"><a class="tooltip" href="#">Reflector<span><b>Reflector</b></span></a></th>
          <th width="150"><a class="tooltip" href="#">Link / Un-Link<span><b>Link / Un-Link</b></span></a></th>
          <th width="150"><a class="tooltip" href="#">Action<span><b>Action</b></span></a></th>
        </tr>
        <tr>
          <td>
            <select name="p25LinkHost">
            <?php
	      if (isset($configp25gateway['Network']['Startup'])) { $testP25Host = $configp25gateway['Network']['Startup']; } else { $testP25Host = "none"; }
              if ($testP25Host == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
              else { echo "      <option value=\"none\">None</option>\n"; }
              if ($testP25Host == "10") { echo "      <option value=\"10\" selected=\"selected\">10 - Parrot</option>\n"; }
              else { echo "      <option value=\"10\">10 - Parrot</option>\n"; }
	      $p25Hosts = fopen("/usr/local/etc/P25Hosts.txt", "r");
              while (!feof($p25Hosts)) {
              	$p25HostsLine = fgets($p25Hosts);
                $p25Host = preg_split('/\s+/', $p25HostsLine);
                if ((strpos($p25Host[0], '#') === FALSE ) && ($p25Host[0] != '')) {
                	if ($testP25Host == $p25Host[0]) { echo "      <option value=\"$p25Host[0]\" selected=\"selected\">$p25Host[0] - $p25Host[1]</option>\n"; }
                        else { echo "      <option value=\"$p25Host[0]\">$p25Host[0] - $p25Host[1]</option>\n"; }
                }
              }
              fclose($p25Hosts);
              if (file_exists('/usr/local/etc/P25HostsLocal.txt')) {
              	$p25Hosts2 = fopen("/usr/local/etc/P25HostsLocal.txt", "r");
                while (!feof($p25Hosts2)) {
                	$p25HostsLine2 = fgets($p25Hosts2);
                        $p25Host2 = preg_split('/\s+/', $p25HostsLine2);
                        if ((strpos($p25Host2[0], '#') === FALSE ) && ($p25Host2[0] != '')) {
                        	if ($testP25Host == $p25Host2[0]) { echo "      <option value=\"$p25Host2[0]\" selected=\"selected\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                                else { echo "      <option value=\"$p25Host2[0]\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                        }
                }
                fclose($p25Hosts2);
              }
            ?>
          </td>
          <td>
            <input type="radio" name="Link" value="LINK" checked="checked" />Link
            <input type="radio" name="Link" value="UNLINK" />UnLink
          </td>
          <td>
            <input type="submit" name="p25MgrSubmit" value="Request Change" />
          </td>
        </tr>
      </table>
      </form>
      <br />
      <?php
    }
  }
}
?>
