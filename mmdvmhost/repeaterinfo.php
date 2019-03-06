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
                list($key,$value) = preg_split('/=/',$line);
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
                	list($key1,$value1) = preg_split('/=/',$line1);
                	$value1 = trim(str_replace('"','',$value1));
                	if (strlen($value1) > 0)
                	$configdstar[$key1] = $value1;
		}
        }
}

//Load the dmrgateway config file
$dmrGatewayConfigFile = '/etc/dmrgateway';
if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }

//Load the dapnetgateway config file
$dapnetGatewayConfigFile = '/etc/dapnetgateway';
if (fopen($dapnetGatewayConfigFile,'r')) { $configdapnetgateway = parse_ini_file($dapnetGatewayConfigFile, true); }

// Load the ysf2dmr config file
if (file_exists('/etc/ysf2dmr')) {
	$ysf2dmrConfigFile = '/etc/ysf2dmr';
	if (fopen($ysf2dmrConfigFile,'r')) { $configysf2dmr = parse_ini_file($ysf2dmrConfigFile, true); }
}
// Load the ysf2nxdn config file
if (file_exists('/etc/ysf2nxdn')) {
	$ysf2nxdnConfigFile = '/etc/ysf2nxdn';
	if (fopen($ysf2nxdnConfigFile,'r')) { $configysf2nxdn = parse_ini_file($ysf2nxdnConfigFile, true); }
}
// Load the ysf2p25 config file
if (file_exists('/etc/ysf2p25')) {
	$ysf2p25ConfigFile = '/etc/ysf2p25';
	if (fopen($ysf2p25ConfigFile,'r')) { $configysf2p25 = parse_ini_file($ysf2p25ConfigFile, true); }
}
// Load the dmr2ysf config file
if (file_exists('/etc/dmr2ysf')) {
	$dmr2ysfConfigFile = '/etc/dmr2ysf';
	if (fopen($dmr2ysfConfigFile,'r')) { $configdmr2ysf = parse_ini_file($dmr2ysfConfigFile, true); }
}
// Load the dmr2nxdn config file
if (file_exists('/etc/dmr2nxdn')) {
	$dmr2nxdnConfigFile = '/etc/dmr2nxdn';
	if (fopen($dmr2nxdnConfigFile,'r')) { $configdmr2nxdn = parse_ini_file($dmr2nxdnConfigFile, true); }
}
?>

<table>
  <tr><th colspan="2"><?php echo $lang['modes_enabled'];?></th></tr>
  <tr><?php showMode("D-Star", $mmdvmconfigs);?><?php showMode("DMR", $mmdvmconfigs);?></tr>
  <tr><?php showMode("System Fusion", $mmdvmconfigs);?><?php showMode("P25", $mmdvmconfigs);?></tr>
  <tr><?php showMode("YSF XMode", $mmdvmconfigs);?><?php showMode("NXDN", $mmdvmconfigs);?></tr>
  <tr><?php showMode("DMR XMode", $mmdvmconfigs);?><?php showMode("POCSAG", $mmdvmconfigs);?></tr>
</table>
<br />

<table>
  <tr><th colspan="2"><?php echo $lang['net_status'];?></th></tr>
  <tr><?php showMode("D-Star Network", $mmdvmconfigs);?><?php showMode("DMR Network", $mmdvmconfigs);?></tr>
  <tr><?php showMode("System Fusion Network", $mmdvmconfigs);?><?php showMode("P25 Network", $mmdvmconfigs);?></tr>
  <tr><?php showMode("YSF2DMR Network", $mmdvmconfigs);?><?php showMode("NXDN Network", $mmdvmconfigs);?></tr>
  <tr><?php showMode("YSF2NXDN Network", $mmdvmconfigs);?><?php showMode("YSF2P25 Network", $mmdvmconfigs);?></tr>
  <tr><?php showMode("DMR2NXDN Network", $mmdvmconfigs);?><?php showMode("DMR2YSF Network", $mmdvmconfigs);?></tr>
  <tr><!-- empty "cell": dummy but nicer --><td style="background:#606060; color:#b0b0b0;"></td><?php showMode("POCSAG Network", $mmdvmconfigs);?></tr>
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
	                if (isProcessRunning("MMDVMHost")) { echo "<td style=\"background:#0b0; color:#030;\">Listening</td>"; } else { echo "<td style=\"background:#606060; color:#b0b0b0;\">OFFLINE</td>"; }
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
		elseif (getActualMode($lastHeard, $mmdvmconfigs) === 'POCSAG') {
        	        echo "<td style=\"background:#4aa361;\">POCSAG</td>";
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
<?php
if (getDVModemTCXOFreq()) {
echo '<tr><th>TCXO</th><td style="background: #ffffff;">'.getDVModemTCXOFreq().'</td></tr>'."\n";
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
	$dmrMasterHost3 = str_replace('_', ' ', $configdmrgateway['DMR Network 3']['Name']);
	while (!feof($dmrMasterFile)) {
		$dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
		if ((strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
			if ((strpos($dmrMasterHostF[0], 'XLX_') === 0) && ($xlxMasterHost1 == $dmrMasterHostF[2])) { $xlxMasterHost1 = str_replace('_', ' ', $dmrMasterHostF[0]); }
			if ((strpos($dmrMasterHostF[0], 'BM_') === 0) && ($dmrMasterHost1 == $dmrMasterHostF[2])) { $dmrMasterHost1 = str_replace('_', ' ', $dmrMasterHostF[0]); }
			if ((strpos($dmrMasterHostF[0], 'DMR+_') === 0) && ($dmrMasterHost2 == $dmrMasterHostF[2])) { $dmrMasterHost2 = str_replace('_', ' ', $dmrMasterHostF[0]); }
		}
	}
	if (strlen($xlxMasterHost1) > 19) { $xlxMasterHost1 = substr($xlxMasterHost1, 0, 17) . '..'; }
	if (strlen($dmrMasterHost1) > 19) { $dmrMasterHost1 = substr($dmrMasterHost1, 0, 17) . '..'; }
	if (strlen($dmrMasterHost2) > 19) { $dmrMasterHost2 = substr($dmrMasterHost2, 0, 17) . '..'; }
	if (strlen($dmrMasterHost3) > 19) { $dmrMasterHost3 = substr($dmrMasterHost3, 0, 17) . '..'; }
}
else {
	while (!feof($dmrMasterFile)) {
		$dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
		if ((strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
			if ($dmrMasterHost == $dmrMasterHostF[2]) { $dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]); }
		}
	}
	if (strlen($dmrMasterHost) > 19) { $dmrMasterHost = substr($dmrMasterHost, 0, 17) . '..'; }
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
                        if ( !isset($configdmrgateway['XLX Network 1']['Enabled']) && isset($configdmrgateway['XLX Network']['Enabled']) && $configdmrgateway['XLX Network']['Enabled'] == 1) {
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
			if ($configdmrgateway['DMR Network 3']['Enabled'] == 1) {
				echo "<tr><td  style=\"background: #ffffff;\" colspan=\"2\">".$dmrMasterHost3."</td></tr>\n";
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
if ( isset($configdmr2ysf['Enabled']['Enabled']) ) { $testDMR2YSF = $configdmr2ysf['Enabled']['Enabled']; }
if ( $testMMDVModeYSF == 1 || $testDMR2YSF ) { //Hide the YSF information when System Fusion Network mode not enabled.
        $ysfLinkedTo = getActualLink($reverseLogLinesYSFGateway, "YSF");
        if ($ysfLinkedTo == 'not linked' || $ysfLinkedTo == 'Service Not Started') {
                $ysfLinkedToTxt = $ysfLinkedTo;
        } else {
                $ysfHostFile = fopen("/usr/local/etc/YSFHosts.txt", "r");
                $ysfLinkedToTxt = "null";
                while (!feof($ysfHostFile)) {
                        $ysfHostFileLine = fgets($ysfHostFile);
                        $ysfRoomTxtLine = preg_split('/;/', $ysfHostFileLine);
                        if (empty($ysfRoomTxtLine[0]) || empty($ysfRoomTxtLine[1])) continue;
                        if (($ysfRoomTxtLine[0] == $ysfLinkedTo) || ($ysfRoomTxtLine[1] == $ysfLinkedTo)) {
                                $ysfLinkedToTxt = $ysfRoomTxtLine[1];
                                break;
                        }
                }
                if ($ysfLinkedToTxt != "null") { $ysfLinkedToTxt = "Room: ".$ysfLinkedToTxt; } else { $ysfLinkedToTxt = "Linked to: ".$ysfLinkedTo; }
                $ysfLinkedToTxt = str_replace('_', ' ', $ysfLinkedToTxt);
        }
        if (strlen($ysfLinkedToTxt) > 19) { $ysfLinkedToTxt = substr($ysfLinkedToTxt, 0, 17) . '..'; }
        echo "<br />\n";
        echo "<table>\n";
        echo "<tr><th colspan=\"2\">".$lang['ysf_net']."</th></tr>\n";
        echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".$ysfLinkedToTxt."</td></tr>\n";
        echo "</table>\n";
}

if ( isset($configysf2dmr['Enabled']['Enabled']) ) { $testYSF2DMR = $configysf2dmr['Enabled']['Enabled']; }
if ( $testYSF2DMR ) { //Hide the YSF2DMR information when YSF2DMR Network mode not enabled.
        $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
        $dmrMasterHost = $configysf2dmr['DMR Network']['Address'];
        while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
                if ((strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
                        if ($dmrMasterHost == $dmrMasterHostF[2]) { $dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]); }
                }
        }
        if (strlen($dmrMasterHost) > 19) { $dmrMasterHost = substr($dmrMasterHost, 0, 17) . '..'; }
        fclose($dmrMasterFile);

        echo "<br />\n";
        echo "<table>\n";
        echo "<tr><th colspan=\"2\">YSF2DMR</th></tr>\n";
	echo "<tr><th>DMR ID</th><td style=\"background: #ffffff;\">".$configysf2dmr['DMR Network']['Id']."</td></tr>\n";
	echo "<tr><th colspan=\"2\">YSF2".$lang['dmr_master']."</th></tr>\n";
        echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".$dmrMasterHost."</td></tr>\n";
        echo "</table>\n";
}

$testMMDVModeP25 = getConfigItem("P25 Network", "Enable", $mmdvmconfigs);
if ( isset($configysf2p25['Enabled']['Enabled']) ) { $testYSF2P25 = $configysf2p25['Enabled']['Enabled']; }
if ( $testMMDVModeP25 == 1 || $testYSF2P25 ) { //Hide the P25 information when P25 Network mode not enabled.
	echo "<br />\n";
	echo "<table>\n";
	if (getConfigItem("P25", "NAC", $mmdvmconfigs)) {
		echo "<tr><th colspan=\"2\">".$lang['p25_radio']."</th></tr>\n";
		echo "<tr><th style=\"width:70px\">NAC</th><td>".getConfigItem("P25", "NAC", $mmdvmconfigs)."</td></tr>\n";
	}
	echo "<tr><th colspan=\"2\">".$lang['p25_net']."</th></tr>\n";
	echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".getActualLink($logLinesP25Gateway, "P25")."</td></tr>\n";
	echo "</table>\n";
}

$testMMDVModeNXDN = getConfigItem("NXDN Network", "Enable", $mmdvmconfigs);
if ( isset($configysf2nxdn['Enabled']['Enabled']) ) { if ($configysf2nxdn['Enabled']['Enabled'] == 1) { $testYSF2NXDN = 1; } }
if ( isset($configdmr2nxdn['Enabled']['Enabled']) ) { if ($configdmr2nxdn['Enabled']['Enabled'] == 1) { $testDMR2NXDN = 1; } }
if ( $testMMDVModeNXDN == 1 || isset($testYSF2NXDN) || isset($testDMR2NXDN) ) { //Hide the NXDN information when NXDN Network mode not enabled.
	echo "<br />\n";
	echo "<table>\n";
	if (getConfigItem("NXDN", "RAN", $mmdvmconfigs)) {
		echo "<tr><th colspan=\"2\">".$lang['nxdn_radio']."</th></tr>\n";
		echo "<tr><th style=\"width:70px\">RAN</th><td>".getConfigItem("NXDN", "RAN", $mmdvmconfigs)."</td></tr>\n";
	}
	echo "<tr><th colspan=\"2\">".$lang['nxdn_net']."</th></tr>\n";
	if (file_exists('/etc/nxdngateway')) {
		echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".getActualLink($logLinesNXDNGateway, "NXDN")."</td></tr>\n";
	} else {
		echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">Linked to: TG65000</td></tr>\n";
	}
	echo "</table>\n";
}

$testMMDVModePOCSAG = getConfigItem("POCSAG Network", "Enable", $mmdvmconfigs);
if ( $testMMDVModePOCSAG == 1 ) { //Hide the POCSAG information when POCSAG Network mode not enabled.
	echo "<br />\n";
	echo "<table>\n";
	echo "<tr><th colspan=\"2\">POCSAG</th></tr>\n";
	echo "<tr><th>Tx</th><td>".getMHZ(getConfigItem("POCSAG", "Frequency", $mmdvmconfigs))."</td></tr>\n";
	if (isset($configdapnetgateway['DAPNET']['Address'])) {
		$dapnetGatewayRemoteAddr = $configdapnetgateway['DAPNET']['Address'];
		if (strlen($dapnetGatewayRemoteAddr) > 19) { $dapnetGatewayRemoteAddr = substr($dapnetGatewayRemoteAddr, 0, 17) . '..'; }
		echo "<tr><th colspan=\"2\">POCSAG Master</th></tr>\n";
		echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".$dapnetGatewayRemoteAddr."</td></tr>\n";
	}
	echo "</table>\n";
}
?>
