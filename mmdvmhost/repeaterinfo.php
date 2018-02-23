<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code

require_once($_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php');

//Load the ircDDBGateway config file
$configs = array();
if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
                list($key,$value) = split("=",$line);
                $value = trim(str_replace('"','',$value));
                if ($key != 'ircddbPassword' && strlen($value) > 0)
                $configs[$key] = $value;
        }

}

//Load the DStarRepeater config file
$configdstar = array();
if ($configdstarfile = fopen('/etc/dstarrepeater','r')) {
        while ($line1 = fgets($configdstarfile)) {
		if (strpos($line1, '=') !== false) {
                	list($key1,$value1) = split("=",$line1);
                	$value1 = trim(str_replace('"','',$value1));
                	if (strlen($value1) > 0)
                	$configdstar[$key1] = $value1;
		}
        }
}

//Load the dmrgateway config file
$dmrGatewayConfigFile = '/etc/dmrgateway';
if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }
?>

<table>
  <tr><th colspan="2"><?php echo $lang['modes_enabled'];?></th></tr>
  <tr><?php showMode("D-Star", $mmdvmconfigs);?><?php showMode("DMR", $mmdvmconfigs);?></tr>
  <tr><?php showMode("System Fusion", $mmdvmconfigs);?><?php showMode("P25", $mmdvmconfigs);?></tr>
  <tr><?php showMode("YSF2DMR", $mmdvmconfigs);?><?php showMode("NXDN", $mmdvmconfigs);?></tr>
</table>
<br />

<table>
  <tr><th colspan="2"><?php echo $lang['net_status'];?></th></tr>
  <tr><?php showMode("D-Star Network", $mmdvmconfigs);?><?php showMode("DMR Network", $mmdvmconfigs);?></tr>
  <tr><?php showMode("System Fusion Network", $mmdvmconfigs);?><?php showMode("P25 Network", $mmdvmconfigs);?></tr>
  <tr><?php showMode("YSF2DMR Network", $mmdvmconfigs);?><?php showMode("NXDN Network", $mmdvmconfigs);?></tr>
  <tr><?php if (!$sock = @fsockopen('www.pistar.uk', 80, $num, $error, 5)) { echo "<td colspan=\"2\" style=\"background:#b00; color:#300;\">".$lang['internet']."</td>\n"; } else { echo "<td colspan=\"2\" style=\"background:#0b0; color:#030;\">".$lang['internet']."</td>\n"; } ?></tr>
</table>
<br />

<table>
<tr><th colspan="2"><?php echo $lang['radio_info'];?></th></tr>
<tr><th>Trx</th><?php
// TRX Status code
if (isset($lastHeard[0])) {
	$listElem = $lastHeard[0];
	if ( $listElem[2] && $listElem[6] == null && $listElem[5] !== 'RF') {
	        echo "<td style=\"background:#f33;\">TX $listElem[1]</td>";
	        }
	        else {
	        if (getActualMode($lastHeard, $mmdvmconfigs) === 'idle') {
	                echo "<td style=\"background:#0b0; color:#030;\">Listening</td>";
	                }
	        elseif (getActualMode($lastHeard, $mmdvmconfigs) === NULL) {
	                exec ("pgrep MMDVMHost", $mmdvmhostpid); if (!empty($mmdvmhostpid)) { echo "<td style=\"background:#0b0; color:#030;\">Listening</td>"; } else { echo "<td style=\"background:#606060; color:#b0b0b0;\">OFFLINE</td>"; }
	                }
	        elseif ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $mmdvmconfigs) === 'D-Star') {
	                echo "<td style=\"background:#4aa361;\">RX D-Star</td>";
	                }
	        elseif (getActualMode($lastHeard, $mmdvmconfigs) === 'D-Star') {
	                echo "<td style=\"background:#ade;\">Listening D-Star</td>";
	                }
	        elseif ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $mmdvmconfigs) === 'DMR') {
	                echo "<td style=\"background:#4aa361;\">RX DMR</td>";
	                }
	        elseif (getActualMode($lastHeard, $mmdvmconfigs) === 'DMR') {
	                echo "<td style=\"background:#f93;\">Listening DMR</td>";
	                }
	        elseif ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $mmdvmconfigs) === 'YSF') {
	                echo "<td style=\"background:#4aa361;\">RX YSF</td>";
	                }
	        elseif (getActualMode($lastHeard, $mmdvmconfigs) === 'YSF') {
	                echo "<td style=\"background:#ff9;\">Listening YSF</td>";
	                }
	        elseif ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $mmdvmconfigs) === 'P25') {
        	        echo "<td style=\"background:#4aa361;\">RX P25</td>";
        	        }
        	elseif (getActualMode($lastHeard, $mmdvmconfigs) === 'P25') {
        	        echo "<td style=\"background:#f9f;\">Listening P25</td>";
        	        }
		elseif ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $mmdvmconfigs) === 'NXDN') {
        	        echo "<td style=\"background:#4aa361;\">RX NXDN</td>";
        	        }
        	elseif (getActualMode($lastHeard, $mmdvmconfigs) === 'NXDN') {
        	        echo "<td style=\"background:#c9f;\">Listening NXDN</td>";
        	        }
        	else {
        	        echo "<td>".getActualMode($lastHeard, $mmdvmconfigs)."</td>";
        	        }
		}
	}
else {
	echo "<td></td>";
}
?></tr>
<tr><th>Tx</th><td style="background: #ffffff;"><?php echo getMHZ(getConfigItem("Info", "TXFrequency", $mmdvmconfigs)); ?></td></tr>
<tr><th>Rx</th><td style="background: #ffffff;"><?php echo getMHZ(getConfigItem("Info", "RXFrequency", $mmdvmconfigs)); ?></td></tr>
<?php
if (getDVModemFirmware()) {
echo '<tr><th>FW</th><td style="background: #ffffff;">'.getDVModemFirmware().'</td></tr>'."\n";
} ?>
</table>

<?php
$testMMDVModeDSTAR = getConfigItem("D-Star", "Enable", $mmdvmconfigs);
if ( $testMMDVModeDSTAR == 1 ) { //Hide the D-Star Reflector information when D-Star Network not enabled.
echo "<br />\n";
echo "<table>\n";
echo "<tr><th colspan=\"2\">".$lang['dstar_repeater']."</th></tr>\n";
echo "<tr><th>RPT1</th><td style=\"background: #ffffff;\">".str_replace(' ', '&nbsp;', $configdstar['callsign'])."</td></tr>\n";
echo "<tr><th>RPT2</th><td style=\"background: #ffffff;\">".str_replace(' ', '&nbsp;', $configdstar['gateway'])."</td></tr>\n";
echo "<tr><th colspan=\"2\">".$lang['dstar_net']."</th></tr>\n";
echo "<tr><th>APRS</th><td style=\"background: #ffffff;\">".substr($configs['aprsHostname'], 0, 16)."</td></tr>\n";
echo "<tr><th>IRC</th><td style=\"background: #ffffff;\">".substr($configs['ircddbHostname'], 0 ,16)."</td></tr>\n";
echo "<tr><td colspan=\"2\" style=\"background: #ffffff;\">".getActualLink($reverseLogLinesMMDVM, "D-Star")."</td></tr>\n";
echo "</table>\n";
}

$testMMDVModeDMR = getConfigItem("DMR", "Enable", $mmdvmconfigs);
if ( $testMMDVModeDMR == 1 ) { //Hide the DMR information when DMR mode not enabled.
$dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
$dmrMasterHost = getConfigItem("DMR Network", "Address", $mmdvmconfigs);
if ($dmrMasterHost == '127.0.0.1') {
	if (isset($configdmrgateway['XLX Network 1']['Address'])) { $xlxMasterHost1 = $configdmrgateway['XLX Network 1']['Address']; }
	else { $xlxMasterHost1 = ""; }
	$dmrMasterHost1 = $configdmrgateway['DMR Network 1']['Address'];
	$dmrMasterHost2 = $configdmrgateway['DMR Network 2']['Address'];
	while (!feof($dmrMasterFile)) {
		$dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
		if ((strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
			if ((strpos($dmrMasterHostF[0], 'XLX_') === 0) && ($xlxMasterHost1 == $dmrMasterHostF[2])) { $xlxMasterHost1 = str_replace('_', ' ', $dmrMasterHostF[0]); }
			if ((strpos($dmrMasterHostF[0], 'BM_') === 0) && ($dmrMasterHost1 == $dmrMasterHostF[2])) { $dmrMasterHost1 = str_replace('_', ' ', $dmrMasterHostF[0]); }
			if ((strpos($dmrMasterHostF[0], 'DMR+_') === 0) && ($dmrMasterHost2 == $dmrMasterHostF[2])) { $dmrMasterHost2 = str_replace('_', ' ', $dmrMasterHostF[0]); }
		}
	}
	if (strlen($xlxMasterHost1) > 21) { $xlxMasterHost1 = substr($xlxMasterHost1, 0, 19) . '..'; }
	if (strlen($dmrMasterHost1) > 21) { $dmrMasterHost1 = substr($dmrMasterHost1, 0, 19) . '..'; }
	if (strlen($dmrMasterHost2) > 21) { $dmrMasterHost2 = substr($dmrMasterHost2, 0, 19) . '..'; }
}
else {
	while (!feof($dmrMasterFile)) {
		$dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
		if ((strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
			if ($dmrMasterHost == $dmrMasterHostF[2]) { $dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]); }
		}
	}
	if (strlen($dmrMasterHost) > 21) { $dmrMasterHost = substr($dmrMasterHost, 0, 19) . '..'; }
}
fclose($dmrMasterFile);

echo "<br />\n";
echo "<table>\n";
echo "<tr><th colspan=\"2\">".$lang['dmr_repeater']."</th></tr>\n";
echo "<tr><th>DMR ID</th><td style=\"background: #ffffff;\">".getConfigItem("General", "Id", $mmdvmconfigs)."</td></tr>\n";
echo "<tr><th>DMR CC</th><td style=\"background: #ffffff;\">".getConfigItem("DMR", "ColorCode", $mmdvmconfigs)."</td></tr>\n";
echo "<tr><th>TS1</th>";
if (getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == 1) { echo "<td style=\"background:#0b0;\">enabled</td></tr>\n"; } else { echo "<td style=\"background:#606060; color:#b0b0b0;\">disabled</td></tr>\n"; }
if (getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == 1) { echo "<tr><td style=\"background: #ffffff;\" colspan=\"2\">".substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 1"), -10)."/".substr(getActualReflector($reverseLogLinesMMDVM, "DMR Slot 1"), -10)."</td></tr>\n"; }
echo "<tr><th>TS2</th>";
if (getConfigItem("DMR Network", "Slot2", $mmdvmconfigs) == 1) { echo "<td style=\"background:#0b0;\">enabled</td></tr>\n"; } else { echo "<td style=\"background:#606060; color:#b0b0b0;\">disabled</td></tr>\n"; }
if (getConfigItem("DMR Network", "Slot2", $mmdvmconfigs) == 1) { echo "<tr><td style=\"background: #ffffff;\" colspan=\"2\">".substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 2"), -10)."/".substr(getActualReflector($reverseLogLinesMMDVM, "DMR Slot 2"), -10)."</td></tr>\n"; }
echo "<tr><th colspan=\"2\">".$lang['dmr_master']."</th></tr>\n";
if (getEnabled("DMR Network", $mmdvmconfigs) == 1) {
		if ($dmrMasterHost == '127.0.0.1') {
			if ((isset($configdmrgateway['XLX Network 1']['Enabled'])) && ($configdmrgateway['XLX Network 1']['Enabled'] == 1)) {
				echo "<tr><td  style=\"background: #ffffff;\" colspan=\"2\">".$xlxMasterHost1."</td></tr>\n";
			}
                        if ((!isset($configdmrgateway['XLX Network 1']['Enabled'])) && (isset($configdmrgateway['XLX Network']['Enabled']))) {
				if (file_exists("/var/log/pi-star/DMRGateway-".gmdate("Y-m-d").".log")) { $xlxMasterHost1 = exec('grep \'XLX, Linking\|Unlinking\' /var/log/pi-star/DMRGateway-'.gmdate("Y-m-d").'.log | tail -1 | awk \'{print $5 " " $8 " " $9}\''); }
                                else { $xlxMasterHost1 = exec('grep \'XLX, Linking\|Unlinking\' /var/log/pi-star/DMRGateway-'.gmdate("Y-m-d", time() - 86340).'.log | tail -1 | awk \'{print $5 " " $8 " " $9}\''); }
				//$xlxMasterHost1 = exec('grep \'XLX, Linking\|Unlinking\' /var/log/pi-star/DMRGateway-'.gmdate("Y-m-d").'.log | tail -1 | awk \'{print $5 " " $8 " " $9}\'');
				if ( strpos($xlxMasterHost1, 'Linking') !== false ) { $xlxMasterHost1 = str_replace('Linking ', '', $xlxMasterHost1); }
				else if ( strpos($xlxMasterHost1, 'Unlinking') !== false ) { $xlxMasterHost1 = "XLX Not Linked"; }
				echo "<tr><td  style=\"background: #ffffff;\" colspan=\"2\">".$xlxMasterHost1."</td></tr>\n";
                        }
			if ($configdmrgateway['DMR Network 1']['Enabled'] == 1) {
				echo "<tr><td  style=\"background: #ffffff;\" colspan=\"2\">".$dmrMasterHost1."</td></tr>\n";
			}
			if ($configdmrgateway['DMR Network 2']['Enabled'] == 1) {
				echo "<tr><td  style=\"background: #ffffff;\" colspan=\"2\">".$dmrMasterHost2."</td></tr>\n";
			}
		}
		else {
			echo "<tr><td  style=\"background: #ffffff;\" colspan=\"2\">".$dmrMasterHost."</td></tr>\n";
		}
	}
	else {
		echo "<tr><td colspan=\"2\" style=\"background:#606060; color:#b0b0b0;\">No DMR Network</td></tr>\n";
	}
echo "</table>\n";
}

$testMMDVModeYSF = getConfigItem("System Fusion Network", "Enable", $mmdvmconfigs);
if ( $testMMDVModeYSF == 1 ) { //Hide the YSF information when System Fusion Network mode not enabled.
        $ysfHostFile = fopen("/usr/local/etc/YSFHosts.txt", "r");
        $ysfLinkedTo = getActualLink($reverseLogLinesYSFGateway, "YSF");
        $ysfLinkedToTxt = "null";
        while (!feof($ysfHostFile)) {
                $ysfHostFileLine = fgets($ysfHostFile);
                $ysfRoomTxtLine = preg_split('/;/', $ysfHostFileLine);
                if ($ysfRoomTxtLine[0] == $ysfLinkedTo) {
                        $ysfLinkedToTxt = $ysfRoomTxtLine[1];
                }
		if ($ysfLinkedTo == "00002") {
			$ysfLinkedToTxt = "YSF2DMR";
		}
        }
        if ($ysfLinkedToTxt != "null") { $ysfLinkedToTxt = "Room: ".$ysfLinkedToTxt; } else { $ysfLinkedToTxt = "Linked to: ".$ysfLinkedTo; }
        $ysfLinkedToTxt = str_replace('_', ' ', $ysfLinkedToTxt);
        if (strlen($ysfLinkedToTxt) > 21) { $ysfLinkedToTxt = substr($ysfLinkedToTxt, 0, 19) . '..'; }
        echo "<br />\n";
        echo "<table>\n";
        echo "<tr><th colspan=\"2\">".$lang['ysf_net']."</th></tr>\n";
        echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".$ysfLinkedToTxt."</td></tr>\n";
        echo "</table>\n";
}

$testMMDVModeP25 = getConfigItem("P25 Network", "Enable", $mmdvmconfigs);
if ( $testMMDVModeP25 == 1 ) { //Hide the P25 information when P25 Network mode not enabled.
	echo "<br />\n";
	echo "<table>\n";
	if (getConfigItem("P25", "NAC", $mmdvmconfigs)) {
		echo "<tr><th colspan=\"2\">".$lang['p25_radio']."</th></tr>\n";
		echo "<tr><th style=\"width:70px\">NAC</th><td>".getConfigItem("P25", "NAC", $mmdvmconfigs)."</td></tr>\n";
	}
	echo "<tr><th colspan=\"2\">".$lang['p25_net']."</th></tr>\n";
	echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".getActualLink($reverseLogLinesP25Gateway, "P25")."</td></tr>\n";
	echo "</table>\n";
}

$testMMDVModeNXDN = getConfigItem("NXDN Network", "Enable", $mmdvmconfigs);
if ( $testMMDVModeNXDN == 1 ) { //Hide the NXDN information when NXDN Network mode not enabled.
	echo "<br />\n";
	echo "<table>\n";
	if (getConfigItem("NXDN", "RAN", $mmdvmconfigs)) {
		echo "<tr><th colspan=\"2\">".$lang['nxdn_radio']."</th></tr>\n";
		echo "<tr><th style=\"width:70px\">RAN</th><td>".getConfigItem("NXDN", "RAN", $mmdvmconfigs)."</td></tr>\n";
	}
	echo "<tr><th colspan=\"2\">".$lang['nxdn_net']."</th></tr>\n";
	echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">Linked to: TG65000</td></tr>\n";
	//echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".getActualLink($reverseLogLinesP25Gateway, "P25")."</td></tr>\n";
	echo "</table>\n";
}
?>
