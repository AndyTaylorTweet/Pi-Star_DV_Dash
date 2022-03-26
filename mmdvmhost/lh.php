<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code

// Check if the config file exists
if (file_exists('/etc/pistar-css.ini')) {
    // Use the values from the file
    $piStarCssFile = '/etc/pistar-css.ini';
    if (fopen($piStarCssFile,'r')) { $piStarCss = parse_ini_file($piStarCssFile, true); }

    // Set the Values from the config file
    if (isset($piStarCss['Lookup']['Service'])) { $callsignLookupSvc = $piStarCss['Lookup']['Service']; }		// Lookup Service "QRZ" or "RadioID"
    else { $callsignLookupSvc = "RadioID"; }										// Set the default if its missing										// Set the default if its missing
} else {
    // Default values
    $callsignLookupSvc = "RadioID";
}

// Safety net
if (($callsignLookupSvc != "RadioID") && ($callsignLookupSvc != "QRZ")) { $callsignLookupSvc = "RadioID"; }

// Setup the URL(s)
$idLookupUrl = "https://database.radioid.net/database/view?id=";
if ($callsignLookupSvc == "RadioID") { $callsignLookupUrl = "https://database.radioid.net/database/view?callsign="; }
if ($callsignLookupSvc == "QRZ") { $callsignLookupUrl = "https://www.qrz.com/db/"; }

?>
<b><?php echo $lang['last_heard_list'];?></b>
  <table>
    <tr>
      <th><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['mode'];?><span><b>Transmitted Mode</b></span></a></th>
      <th style="min-width:14ch"><a class="tooltip" href="#"><?php echo $lang['callsign'];?><span><b>Callsign</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['target'];?><span><b>Target, D-Star Reflector, DMR Talk Group etc</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['src'];?><span><b>Received from source</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['dur'];?>(s)<span><b>Duration in Seconds</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['loss'];?><span><b>Packet Loss</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['ber'];?><span><b>Bit Error Rate</b></span></a></th>
    </tr>
<?php
$i = 0;
for ($i = 0;  ($i <= 19); $i++) { //Last 20 calls
	if (isset($lastHeard[$i])) {
		$listElem = $lastHeard[$i];
		if ( $listElem[2] ) {
			$utc_time = $listElem[0];
                        $utc_tz =  new DateTimeZone('UTC');
                        $local_tz = new DateTimeZone(date_default_timezone_get ());
                        $dt = new DateTime($utc_time, $utc_tz);
                        $dt->setTimeZone($local_tz);
                        $local_time = $dt->format('H:i:s M jS');
		echo "<tr>";
		echo "<td align=\"left\">$local_time</td>";
		echo "<td align=\"left\">".str_replace('Slot ', 'TS', $listElem[1])."</td>";
		if (is_numeric($listElem[2])) {
			if ($listElem[2] > 9999) { echo "<td align=\"left\"><a href=\"".$idLookupUrl.$listElem[2]."\" target=\"_blank\">$listElem[2]</a></td>"; }
			else { echo "<td align=\"left\">".$listElem[2]."</td>"; }
		} elseif (!preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $listElem[2])) {
                        echo "<td align=\"left\">$listElem[2]</td>";
		} else {
			if (strpos($listElem[2],"-") > 0) { $listElem[2] = substr($listElem[2], 0, strpos($listElem[2],"-")); }
			if ( $listElem[3] && $listElem[3] != '    ' ) {
				echo "<td align=\"left\"><div style=\"float:left;\"><a href=\"".$callsignLookupUrl.$listElem[2]."\" target=\"_blank\">$listElem[2]</a>/$listElem[3]</div> <div style=\"text-align:right;\">&#40;<a href=\"https://aprs.fi/#!call=".$listElem[2]."*\" target=\"_blank\">GPS</a>&#41;</div></td>";
			} else {
				echo "<td align=\"left\"><div style=\"float:left;\"><a href=\"".$callsignLookupUrl.$listElem[2]."\" target=\"_blank\">$listElem[2]</a></div> <div style=\"text-align:right;\">&#40;<a href=\"https://aprs.fi/#!call=".$listElem[2]."*\" target=\"_blank\">GPS</a>&#41;</div></td>";
			}
		}

		if (strlen($listElem[4]) == 1) { $listElem[4] = str_pad($listElem[4], 8, " ", STR_PAD_LEFT); }
		if ( substr($listElem[4], 0, 6) === 'CQCQCQ' ) {
			echo "<td align=\"left\">$listElem[4]</td>";
		} else {
			echo "<td align=\"left\">".str_replace(" ","&nbsp;", $listElem[4])."</td>";
		}


		if ($listElem[5] == "RF"){
			echo "<td style=\"background:#1d1;\">RF</td>";
		}else{
			echo "<td>$listElem[5]</td>";
		}
		if ($listElem[6] == null) {
			// Live duration
			$utc_time = $listElem[0];
			$utc_tz =  new DateTimeZone('UTC');
			$now = new DateTime("now", $utc_tz);
			$dt = new DateTime($utc_time, $utc_tz);
			$duration = $now->getTimestamp() - $dt->getTimestamp();
			$duration_string = $duration<999 ? round($duration) . "+" : "&infin;";
			echo "<td colspan =\"3\" style=\"background:#f33;\">TX " . $duration_string . " sec</td>";
		} else if ($listElem[6] == "DMR Data") {
			echo "<td colspan =\"3\" style=\"background:#1d1;\">DMR Data</td>";
		} else if ($listElem[6] == "POCSAG Data") {
			echo "<td colspan =\"3\" style=\"background:#1d1;\">POCSAG Data</td>";
		} else {
			echo "<td>$listElem[6]</td>";

			// Colour the Loss Field
			if (floatval($listElem[7]) < 1) { echo "<td>$listElem[7]</td>"; }
			elseif (floatval($listElem[7]) == 1) { echo "<td style=\"background:#1d1;\">$listElem[7]</td>"; }
			elseif (floatval($listElem[7]) > 1 && floatval($listElem[7]) <= 3) { echo "<td style=\"background:#fa0;\">$listElem[7]</td>"; }
			else { echo "<td style=\"background:#f33;\">$listElem[7]</td>"; }

			// Colour the BER Field
			if (floatval($listElem[8]) == 0) { echo "<td>$listElem[8]</td>"; }
			elseif (floatval($listElem[8]) >= 0.0 && floatval($listElem[8]) <= 1.9) { echo "<td style=\"background:#1d1;\">$listElem[8]</td>"; }
			elseif (floatval($listElem[8]) >= 2.0 && floatval($listElem[8]) <= 4.9) { echo "<td style=\"background:#fa0;\">$listElem[8]</td>"; }
			else { echo "<td style=\"background:#f33;\">$listElem[8]</td>"; }
		}
		echo "</tr>\n";
		}
	}
}

?>
  </table>
