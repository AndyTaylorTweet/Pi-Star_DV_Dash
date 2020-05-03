<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// Check if YSF is Enabled
$testMMDVModeYSF = getConfigItem("System Fusion", "Enable", $mmdvmconfigs);
if ( $testMMDVModeYSF == 1 ) {

  //Load the ysfgateway config file
  $ysfGatewayConfigFile = '/etc/ysfgateway';
  if (fopen($ysfGatewayConfigFile,'r')) { $configysfgateway = parse_ini_file($ysfGatewayConfigFile, true); }

  // Check that the remote is enabled
  if ( $configysfgateway['Remote Commands']['Enable'] == 1 ) {
    $remotePort = $configysfgateway['Remote Commands']['Port'];
    if (!empty($_POST) && isset($_POST["ysfMgrSubmit"])) {
      // Handle Posted Data
      if ($_POST["Link"] == "LINK") {
	if (preg_match('/[^A-Za-z0-9]/',$_POST['ysfLinkHost'])) {
	  unset ($_POST['ysfLinkHost']);
	} elseif ($_POST['ysfLinkHost'] == "none") {
	  $remoteCommand = "sudo /usr/local/bin/RemoteCommand ".$remotePort." UnLink";
	} else {
	  $remoteCommand = "sudo /usr/local/bin/RemoteCommand ".$remotePort." ".$_POST['ysfLinkHost'];
	}
      } elseif ($_POST["Link"] == "UNLINK") {
	$remoteCommand = "sudo /usr/local/bin/RemoteCommand ".$remotePort." UnLink";
      } else {
	echo "<b>YSF Link Manager</b>\n";
	echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
        echo "Somthing wrong with your input, (Neither Link nor Unlink Sent) - please try again";
	echo "</td></tr>\n</table>\n<br />\n";
	unset($_POST);
	echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
      }
      if (empty($_POST['ysfLinkHost'])) {
	echo "<b>YSF Link Manager</b>\n";
        echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
	echo "Somthing wrong with your input, (No target specified) -  please try again";
	echo "</td></tr>\n</table>\n<br />\n";
	unset($_POST);
	echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';
      }
      if (isset($remoteCommand)) {
        echo "<b>YSF Link Manager</b>\n";
	echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
	echo exec($remoteCommand);
	echo "</td></tr>\n</table>\n<br />\n";
      }
    } else {
      // Output HTML
      ?>
      <b>YSF Link Manager</b>
      <form action="http://<?php echo htmlentities($_SERVER['HTTP_HOST']).htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
      <table>
        <tr>
          <th width="150"><a class="tooltip" href="#">Reflector<span><b>Reflector</b></span></a></th>
          <th width="150"><a class="tooltip" href="#">Link / Un-Link<span><b>Link / Un-Link</b></span></a></th>
          <th width="150"><a class="tooltip" href="#">Action<span><b>Action</b></span></a></th>
        </tr>
        <tr>
          <td>
            <select name="ysfLinkHost">
            <?php
	      if (isset($configysfgateway['Network']['Startup'])) {
                $testYSFHost = $configysfgateway['Network']['Startup'];
                echo "      <option value=\"none\">None</option>\n";
        	}
        else {
                $testYSFHost = "none";
                echo "      <option value=\"none\" selected=\"selected\">None</option>\n";
    		}
		if ($testYSFHost == "ZZ Parrot")  {
			echo "      <option value=\"YSF00001,ZZ Parrot\" selected=\"selected\">YSF00001 - Parrot</option>\n";
		} else {
			echo "      <option value=\"YSF00001,ZZ Parrot\">YSF00001 - Parrot</option>\n";
		}
		if ($testYSFHost == "YSF2DMR")  {
			echo "      <option value=\"YSF00002,YSF2DMR\"  selected=\"selected\">YSF00002 - Link YSF2DMR</option>\n";
		} else {
			echo "      <option value=\"YSF00002,YSF2DMR\">YSF00002 - Link YSF2DMR</option>\n";
		}
		if ($testYSFHost == "YSF2NXDN") {
			echo "      <option value=\"YSF00003,YSF2NXDN\" selected=\"selected\">YSF00003 - Link YSF2NXDN</option>\n";
		} else {
			echo "      <option value=\"YSF00003,YSF2NXDN\">YSF00003 - Link YSF2NXDN</option>\n";
		}
		if ($testYSFHost == "YSF2P25")  {
			echo "      <option value=\"YSF00004,YSF2P25\"  selected=\"selected\">YSF00004 - Link YSF2P25</option>\n";
		} else {
			echo "      <option value=\"YSF00004,YSF2P25\">YSF00004 - Link YSF2P25</option>\n";
		}
	      $ysfHosts = fopen("/usr/local/etc/YSFHosts.txt", "r");
              while (!feof($ysfHosts)) {
                $ysfHostsLine = fgets($ysfHosts);
                $ysfHost = preg_split('/;/', $ysfHostsLine);
                if ((strpos($ysfHost[0], '#') === FALSE ) && ($ysfHost[0] != '')) {
                  if ( ($testYSFHost == $ysfHost[0]) || ($testYSFHost == $ysfHost[1]) ) { echo "      <option value=\"YSF$ysfHost[0],$ysfHost[1]\" selected=\"selected\">YSF$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n"; }
			            else { echo "      <option value=\"YSF$ysfHost[0],$ysfHost[1]\">YSF$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n"; }
                }
              }
              fclose($ysfHosts);
	      if (file_exists("/usr/local/etc/FCSHosts.txt")) {
                $fcsHosts = fopen("/usr/local/etc/FCSHosts.txt", "r");
                while (!feof($fcsHosts)) {
                        $ysfHostsLine = fgets($fcsHosts);
                        $ysfHost = preg_split('/;/', $ysfHostsLine);
                        if (substr($ysfHost[0], 0, 3) == "FCS") {
                                if ( ($testYSFHost == $ysfHost[0]) || ($testYSFHost == $ysfHost[1]) ) { echo "      <option value=\"$ysfHost[0],$ysfHost[0]\" selected=\"selected\">$ysfHost[0] - ".htmlspecialchars($ysfHost[1])."</option>\n"; }
                                else { echo "      <option value=\"$ysfHost[0],$ysfHost[0]\">$ysfHost[0] - ".htmlspecialchars($ysfHost[1])."</option>\n"; }
                        }
                }
                fclose($fcsHosts);
              }
            ?>
          </td>
          <td>
            <input type="radio" name="Link" value="LINK" checked="checked" />Link
            <input type="radio" name="Link" value="UNLINK" />UnLink
          </td>
          <td>
            <input type="submit" name="ysfMgrSubmit" value="Request Change" />
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
