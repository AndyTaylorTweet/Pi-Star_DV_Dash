<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tgNames.php';      // TalkGroup Names
//$localTXList = getHeardList($reverseLogLinesMMDVM);
$localTXList = $lastHeard;

?>
<b><?php echo $lang['local_tx_list'];?></b>
  <table>
    <tr>
      <th><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['mode'];?><span><b>Transmitted Mode</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['callsign'];?><span><b>Callsign</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['target'];?><span><b>Target, D-Star Reflector, DMR Talk Group etc</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['src'];?><span><b>Recieved from source</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['dur'];?>(s)<span><b>Duration in Seconds</b></span></a></th>
      <th><a class="tooltip" href="#"><?php echo $lang['ber'];?><span><b>Bit Error Rate</b></span></a></th>
    </tr>
<?php
$counter = 0;
$i = 0;
for ($i = 0; $i < count($localTXList); $i++) {
		$listElem = $localTXList[$i];
		if ($listElem[5] == "RF" && ($listElem[1]=="D-Star" || startsWith($listElem[1], "DMR") || $listElem[1]=="YSF" || $listElem[1]=="P25")) {
			if ($counter <= 19) { //last 20 calls
				$utc_time = $listElem[0];
                        	$utc_tz =  new DateTimeZone('UTC');
                        	$local_tz = new DateTimeZone(date_default_timezone_get ());
                        	$dt = new DateTime($utc_time, $utc_tz);
                        	$dt->setTimeZone($local_tz);
                        	$local_time = $dt->format('H:i:s M jS');
			echo"<tr>";
			echo"<td align=\"left\">$local_time</td>";
			echo"<td align=\"left\">$listElem[1]</td>";
			if ($listElem[3] && $listElem[3] != '    ' ) {
				echo "<td align=\"left\"><a href=\"http://www.qrz.com/db/$listElem[2]\" target=\"_blank\">$listElem[2]</a>/$listElem[3]</td>";
			} else {
				echo "<td align=\"left\"><a href=\"http://www.qrz.com/db/$listElem[2]\" target=\"_blank\">$listElem[2]</a></td>";
			}
			//echo"<td align=\"left\">".str_replace(" ","&nbsp;", $listElem[4])."</td>";
			$TGid = explode(" ",$listElem[4])[1];
            if(substr($listElem[4],0,3)=="TG "){
                if($TGid=="9"||$TGid=="2"){
                    echo "<td align=\"left\">".str_replace(" ","&nbsp;", $listElem[4])." (".$tgNames[$TGid].")</td>";
                } else {
                    echo "<td align=\"left\"><a href=\"http://hose.brandmeister.network/$TGid/\" target=\"_blank\">$listElem[4]</a> (".$tgNames[$TGid].")</td>";
                }
            } elseif($listElem[4]=="9990"){
                echo "<td align=\"left\"><a href=\"http://hose.brandmeister.network/9990/\" target=\"_blank\">9990</a> (Parrot)</td>";
            } else {
                echo "<td align=\"left\">".str_replace(" ","&nbsp;", $listElem[4])."</td>";
            }
            if ($listElem[5] == "RF"){
				echo "<td style=\"background:#1d1;\">RF</td>";
			} else {
				echo "<td>$listElem[5]</td>";
			}
			if ($listElem[6] == null) {
				echo "<td style=\"background:#f33;\">TX</td><td></td>";
			} else if ($listElem[6] == "SMS") {
				echo "<td>SMS</td><td></td>";
			} else {
				echo"<td>$listElem[6]</td>";
				
				// Colour the BER Field
				if (floatval($listElem[8]) == 0) { echo "<td>$listElem[8]</td>"; }
				elseif (floatval($listElem[8]) >= 0.0 && floatval($listElem[8]) <= 1.9) { echo "<td style=\"background:#1d1;\">$listElem[8]</td>"; }
				elseif (floatval($listElem[8]) >= 2.0 && floatval($listElem[8]) <= 4.9) { echo "<td style=\"background:#fa0;\">$listElem[8]</td>"; }
				else { echo "<td style=\"background:#f33;\">$listElem[8]</td>"; }
			}
			echo"</tr>\n";
			$counter++; }
		}
	}

?>
  </table>
