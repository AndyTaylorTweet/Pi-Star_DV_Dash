<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          //MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        //MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    //MMDVMDash Functions

require_once($_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php');
$configs = array();
if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
                list($key,$value) = split("=",$line);
                $value = trim(str_replace('"','',$value));
                if ($key != 'ircddbPassword' && strlen($value) > 0)
                $configs[$key] = $value;
        }

}
?>

<table>
  <tr><th colspan="2">Modes Enabled</th></tr>
  <tr><?php showMode("D-Star", $mmdvmconfigs);?><?php showMode("DMR", $mmdvmconfigs);?></tr>
  <tr><?php showMode("System Fusion", $mmdvmconfigs);?><?php showMode("P25", $mmdvmconfigs);?></tr>
</table>
<br />

<table>
  <tr><th colspan="2">Network Status</th></tr>
  <tr><?php showMode("D-Star Network", $mmdvmconfigs);?><?php showMode("DMR Network", $mmdvmconfigs);?></tr>
  <tr><?php showMode("System Fusion Network", $mmdvmconfigs);?><?php showMode("P25 Network", $mmdvmconfigs);?></tr>
  <tr><?php if (!$sock = @fsockopen('www.google.com', 80, $num, $error, 5)) { echo "<td colspan=\"2\" style=\"background:#b00; color:#300;\">Internet</td>\n"; } else { echo "<td colspan=\"2\" style=\"background:#0b0; color:#030;\">Internet</td>\n"; } ?></tr>
</table>
<br />

<table>
<tr><th colspan="2">Radio Info</th></tr>
<tr><th>Trx</th><?php
// TRX Status code
$listElem = $lastHeard[0];
if ( $listElem[2] && $listElem[6] == null && $listElem[5] !== 'RF') {
        echo "<td style=\"background:#f33;\">TX $listElem[1]</td>";
        }
        else {
        if (getActualMode($lastHeard, $mmdvmconfigs) === 'idle') {
                echo "<td style=\"background:#0b0; color:#030;\">Listening</td>";
                }
        elseif (getActualMode($lastHeard, $mmdvmconfigs) === null) {
                echo "<td style=\"background:#606060; color:#b0b0b0;\">OFFLINE</td>";
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
        else {
                echo "<td>".getActualMode($lastHeard, $mmdvmconfigs)."</td>";
                }
        }
?></tr>
<tr><th>Tx</th><td style="background: #ffffff;"><?php echo getMHZ(getConfigItem("Info", "TXFrequency", $mmdvmconfigs)); ?></td></tr>
<tr><th>Rx</th><td style="background: #ffffff;"><?php echo getMHZ(getConfigItem("Info", "RXFrequency", $mmdvmconfigs)); ?></td></tr>
</table>

<?php
$testMMDVModeDSTAR = getConfigItem("D-Star Network", "Enable", $mmdvmconfigs);
if ( $testMMDVModeDSTAR == 1 ) { //Hide the D-Star Reflector information when D-Star Network not enabled.
echo "<br />\n";
echo "<table>\n";
echo "<tr><th colspan=\"2\">D-Star Network</th></tr>\n";
echo "<tr><th>APRS</th><td style=\"background: #ffffff;\">".substr($configs['aprsHostname'], 0, 16)."</td></tr>\n";
echo "<tr><th>IRC</th><td style=\"background: #ffffff;\">".substr($configs['ircddbHostname'], 0 ,16)."</td></tr>\n";
echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".getActualLink($reverseLogLinesMMDVM, "D-Star")."</td></tr>\n";
echo "</table>\n";
}

$testMMDVModeDMR = getConfigItem("DMR", "Enable", $mmdvmconfigs);
if ( $testMMDVModeDMR == 1 ) { //Hide the DMR information when DMR mode not enabled.
$dmrMasterHost = getConfigItem("DMR Network", "Address", $mmdvmconfigs);
if (strlen($dmrMasterHost) > 21) { $dmrMasterHost = substr($dmrMasterHost, 0, 19) . '..'; }
echo "<br />\n";
echo "<table>\n";
echo "<tr><th colspan=\"2\">DMR Master</th></tr>\n";
if (getEnabled("DMR Network", $mmdvmconfigs) == 1) { echo "<tr><td  style=\"background: #ffffff;\" colspan=\"2\">".$dmrMasterHost."</td></tr>\n"; } else { echo "<tr><td colspan=\"2\" style=\"background:#606060; color:#b0b0b0;\">No DMR Network</td></tr>\n"; }
echo "<tr><th>DMR CC</th><td style=\"background: #ffffff;\">".getConfigItem("DMR", "ColorCode", $mmdvmconfigs)."</td></tr>\n";
echo "<tr><th>TS1</th>";
if (getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == 1) { echo "<td style=\"background:#0b0;\">enabled</td></tr>\n"; } else { echo "<td style=\"background:#606060; color:#b0b0b0;\">disabled</td></tr>\n"; }
if (getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == 1) { echo "<tr><td style=\"background: #ffffff;\" colspan=\"2\">".substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 1"), -10)."/".substr(getActualReflector($reverseLogLinesMMDVM, "DMR Slot 1"), -10)."</td></tr>\n"; }
echo "<tr><th>TS2</th>";
if (getConfigItem("DMR Network", "Slot2", $mmdvmconfigs) == 1) { echo "<td style=\"background:#0b0;\">enabled</td></tr>\n"; } else { echo "<td style=\"background:#606060; color:#b0b0b0;\">disabled</td></tr>\n"; }
if (getConfigItem("DMR Network", "Slot2", $mmdvmconfigs) == 1) { echo "<tr><td style=\"background: #ffffff;\" colspan=\"2\">".substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 2"), -10)."/".substr(getActualReflector($reverseLogLinesMMDVM, "DMR Slot 2"), -10)."</td></tr>\n"; }
echo "</table>\n";
}

$testMMDVModeYSF = getConfigItem("System Fusion Network", "Enable", $mmdvmconfigs);
if ( $testMMDVModeYSF == 1 ) { //Hide the YSF information when System Fusion Network mode not enabled.
echo "<br />\n";
echo "<table>\n";
echo "<tr><th colspan=\"2\">YSF Network</th></tr>\n";
echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".getActualLink($reverseLogLinesYSFGateway, "YSF")."</td></tr>\n";
echo "</table>\n";
}

$testMMDVModeP25 = getConfigItem("P25 Network", "Enable", $mmdvmconfigs);
if ( $testMMDVModeP25 == 1 ) { //Hide the P25 information when P25 Network mode not enabled.
echo "<br />\n";
echo "<table>\n";
echo "<tr><th colspan=\"2\">P25 Network</th></tr>\n";
echo "<tr><td colspan=\"2\"style=\"background: #ffffff;\">".getActualLink($reverseLogLinesP25Gateway, "P25")."</td></tr>\n";
echo "</table>\n";
}
?>
