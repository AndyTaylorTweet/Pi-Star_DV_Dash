<b>Service Status</b>
<table>
  <tr>
    <th><a class=tooltip href="#">DStarRepeater<span><b>DStarRepeater</b></span></th>
    <th><a class=tooltip href="#">MMDVMHost<span><b>DStarRepeater</b></span></th>
    <th><a class=tooltip href="#">ircDDBgateway<span><b>ircDDBgateway</b></span></th>
    <th><a class=tooltip href="#">timeserver<span><b>timeserver</b></span></th>
    <th><a class=tooltip href="#">pistar-watchdog<span><b>pistar-watchdog</b></span></th>
    <th><a class=tooltip href="#">pistar-keeper<span><b>pistar-keeper</b></span></th>
  </tr>
  <tr>
    <td><?php exec ("pgrep dstarrepeaterd", $dstarrepeaterpid); if (!empty($dstarrepeaterpid)) { echo "<img src=\"images/20green.png\">"; } else { echo "<img src=\"images/20red.png\">"; } ?></td>
    <td><?php exec ("pgrep MMDVMHost", $mmdvmhostpid); if (!empty($mmdvmhostpid)) { echo "<img src=\"images/20green.png\">"; } else { echo "<img src=\"images/20red.png\">"; } ?></td>
    <td><?php exec ("pgrep ircddbgatewayd", $ircddbgatewaypid); if (!empty($ircddbgatewaypid)) { echo "<img src=\"images/20green.png\">"; } else { echo "<img src=\"images/20red.png\">"; } ?></td>
    <td><?php exec ("pgrep timeserverd", $timeserverpid); if (!empty($timeserverpid)) { echo "<img src=\"images/20green.png\">"; } else { echo "<img src=\"images/20red.png\">"; } ?></td>
    <td><?php exec ("pgrep -f -a /usr/local/sbin/pistar-watchdog | sed '/pgrep/d'", $watchdogpid); if (!empty($watchdogpid)) { echo "<img src=\"images/20green.png\">"; } else { echo "<img src=\"images/20red.png\">"; } ?></td>
    <td><?php exec ("pgrep -f -a /usr/local/sbin/pistar-keeper | sed '/pgrep/d'", $keeperpid); if (!empty($keeperpid)) { echo "<img src=\"images/20green.png\">"; } else { echo "<img src=\"images/20red.png\">"; } ?></td>
  </tr>
</table>
<br />

<?php if (!empty($_POST)):
if (preg_match('/[^A-Z]/',$_POST["Link"])) { unset ($_POST["Link"]);}
if ($_POST["Link"] == "LINK") {
	if (preg_match('/[^A-Z0-9]/',$_POST["RefName"])) { unset ($_POST["RefName"]);}
	if (preg_match('/[^A-Z]/',$_POST["Letter"])) { unset ($_POST["Letter"]);}
	if (preg_match('/[^A-Z0-9 ]/',$_POST["Module"])) { unset ($_POST["Module"]);}
	}
if ($_POST["Link"] == "UNLINK") {
	if (preg_match('/[^A-Z0-9 ]/',$_POST["Module"])) { unset ($_POST["Module"]);}
	}
if (empty($_POST["RefName"]) || empty($_POST["Letter"]) || empty($_POST["Module"])) { echo "Somthing wrong with your input, try again";}


else {
	$targetRef = $_POST["RefName"]." ".$_POST["Letter"];
	$module = $_POST["Module"];

        if (strlen($module) != 8) {                                                     //Fix the length of the module information
                $moduleFixedCs= strlen($module) - 1;                                    //Length of the string, -1
                $moduleFixedBand = substr($module, -1);                                 //Single Band Letter in the 8th position
                $moduleFixedCallPad = str_pad(substr($module, 0, $moduleFixedCs), 7);   //Pad the callsign area to 7 chars
                $module = $moduleFixedCallPad.$moduleFixedBand;                         //Re add the band information
        };

	$unlinkCommand = "sudo remotecontrold \"".$module."\" unlink";
	$linkCommand = "sudo remotecontrold \"".$module."\" link never \"".$targetRef."\"";

	if ($_POST["Link"] == "LINK") {
		echo "<b>Reflector Connector</b>\n";
		echo "<table>\n<tr><th><a class=tooltip href=\"#\">Command Output<span><b>Command Output</b></span></th></tr>\n<tr><td>";
		echo exec($linkCommand);
		echo "</tr></td>\n</table>\n";
		}
	if ($_POST["Link"] == "UNLINK") {
		echo "<b>Reflector Connector</b>\n";
		echo "<table>\n<tr><th><a class=tooltip href=\"#\">Command Output<span><b>Command Output</b></span></th></tr>\n<tr><td>";
		echo exec($unlinkCommand);
		echo "</tr></td>\n</table>\n";
		}
	}

unset($_POST);
echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},2000);</script>';

else: ?>
<b>Reflector Connector</b>
<form action="//<?php echo htmlentities($_SERVER['HTTP_HOST']).htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
<table>
  <tr>
    <th id="lblModule" width="150"><a class=tooltip href="#">Radio Module<span><b>Radio Module</b></span></th>
    <th id="lblReflector" width="180"><a class=tooltip href="#">Reflector<span><b>Reflector</b></span></th>
    <th id="lblLinkUnlink" width="150"><a class=tooltip href="#">Link / Un-Link<span><b>Link / Un-Link</b></span></th>
    <th><a class=tooltip href="#">Action<span><b>Action</b></span></th>
  </tr>
  <tr>
    <td>
    <select aria-labelledby="lblModule" name="Module">
    <?php for($i = 1;$i < 5; $i++){
      $param="repeaterBand" . $i;
      if((isset($configs[$param])) && strlen($configs[$param]) == 1) {
        $ci++;
        if($ci > 1) { $ci = 0; }
        $module = $configs[$param];
        $rcall = sprintf("%-7.7s%-1.1s",$MYCALL,$module);
        $param="repeaterCall" . $i;
        if(isset($configs[$param])) { $rptrcall=sprintf("%-7.7s%-1.1s",$configs[$param],$module); } else { $rptrcall = $rcall;}
        print "<option>$rptrcall</option>\n";
      }
    } ?>
    </select>
    </td>
    <td role="group" aria-labelledby="lblReflector">
    <select aria-label="Number" name="RefName">
<?php
$dcsFile = fopen("/usr/local/etc/DCS_Hosts.txt", "r");
$dplusFile = fopen("/usr/local/etc/DPlus_Hosts.txt", "r");
$dextraFile = fopen("/usr/local/etc/DExtra_Hosts.txt", "r");

while (!feof($dcsFile)) {
	$dcsLine = fgets($dcsFile);
	if (strpos($dcsLine, 'DCS') !== FALSE && strpos($dcsLine, '#') === FALSE)
		echo "	<option>".substr($dcsLine, 0, 6)."</option>\n";
}
fclose($dcsFile);

echo "	<option selected>REF001</option>\n";

while (!feof($dplusFile)) {
	$dplusLine = fgets($dplusFile);
	if (strpos($dplusLine, 'REF') !== FALSE && strpos($dplusLine, '#') === FALSE && strpos($dplusLine, 'REF001') === FALSE)
		echo "	<option>".substr($dplusLine, 0, 6)."</option>\n";
}
fclose($dplusFile);

while (!feof($dextraFile)) {
	$dextraLine = fgets($dextraFile);
	if (strpos($dextraLine, 'XRF') !== FALSE && strpos($dextraLine, '#') === FALSE)
		echo "	<option>".substr($dextraLine, 0, 6)."</option>\n";
}
fclose($dextraFile);

?>
    </select>
    <select aria-label="Module" name="Letter">
	<option>A</option>
	<option>B</option>
	<option selected>C</option>
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
    <td role="radiogroup" aria-labelledby="lblLinkUnlink">
	    <input id="rbLink" type="radio" name="Link" value="LINK" checked><label for="rbLink">Link</label>
        <input id="rbUnlink" type="radio" name="Link" value="UNLINK"><label for="rbUnlink">UnLink</label>
    </td>
    <td>
    <input type="submit" value="Request Change">
    </td>
  </tr>
</table>
</form>
<?php endif; ?>

<?php
exec ("pgrep pistar-keeper", $pids);
if (!empty($pids))
	{
	echo "<br />\n";
	echo "<b>PiStar-Keeper Logbook</b><input type=button onClick=\"location.href='/admin/pistar-keeper-download.php'\" value=\"Download Logbook\">\n";
	echo "<table>\n";
	echo "  <tr>\n";
	echo "    <th><a class=tooltip href=\"#\">PiStar-Keeper Log Entries (UTC)<span><b>PiStar-Keeper Log Entries (UTC)</b></span></th>\n";
	echo "  </tr>\n";

	exec ("tail -n 5 /var/pistar-keeper/pistar-keeper.log", $lines);
		$counter = 0;
		foreach ($lines as $line) {
			echo "<tr><td align=\"left\">".$lines[$counter]."</td></tr>\n";
			$counter++;
		}

	echo "</table>\n";
	}
?>
