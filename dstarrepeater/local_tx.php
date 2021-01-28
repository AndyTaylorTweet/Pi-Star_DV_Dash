<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code
$configs = array();

if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
                list($key,$value) = preg_split('/=/',$line);
                $value = trim(str_replace('"','',$value));
                if ($key != 'ircddbPassword' && strlen($value) > 0)
                $configs[$key] = $value;
        }

}
$progname = basename($_SERVER['SCRIPT_FILENAME'],".php");
$rev="20141101";
$MYCALL=strtoupper($callsign);

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
if ($callsignLookupSvc == "RadioID") { $callsignLookupUrl = "https://database.radioid.net/database/view?callsign="; }
if ($callsignLookupSvc == "QRZ") { $callsignLookupUrl = "https://www.qrz.com/db/"; }

?>
    <b><?php echo $lang['local_tx_list'];?></b>
    <table>
    <tr>
    <th><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)</a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['callsign'];?></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['target'];?></a></th>
    <th><a class="tooltip" href="#">RPT 1</a></th>
    <th><a class="tooltip" href="#">RPT 2</a></th>
    </tr>
<?php
// Headers.log sample:
// 0000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
// 1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
// 2012-05-29 20:31:53: Repeater header - My: PE1AGO  /HANS  Your: CQCQCQ    Rpt1: PI1DEC B  Rpt2: PI1DEC G  Flags: 00 00 00
//
    exec('(grep "Repeater header" '.$hdrLogPath.'|sort -r -k7,7|sort -u -k7,8|sort -r >/tmp/worked.log) 2>&1 &');
    $ci = 0;
    if ($WorkedLog = fopen("/tmp/worked.log",'r')) {
	while ($linkLine = fgets($WorkedLog)) {
            if(preg_match_all('/^(.{19}).*My: (.*).*Your: (.*).*Rpt1: (.*).*Rpt2: (.*).*Flags: (.*)$/',$linkLine,$linx) > 0){
		$ci++;
		if($ci > 1) { $ci = 0; }
                print "<tr>";
		$QSODate = date("d-M-Y H:i:s", strtotime(substr($linx[1][0],0,19)));
                $MyCall = str_replace(' ', '', substr($linx[2][0],0,8));
		$MyCallLink = strtok(substr($linx[2][0],0,8), " ");
                $MyId = str_replace(' ', '', substr($linx[2][0],9,4));
                $YourCall = str_replace(' ', '&nbsp;', substr($linx[3][0],0,8));
                $Rpt1 = str_replace(' ', '&nbsp;', substr($linx[4][0],0,8));
                $Rpt2 = str_replace(' ', '&nbsp;', substr($linx[5][0],0,8));
		    $utc_time = $QSODate;
                    $utc_tz =  new DateTimeZone('UTC');
                    $local_tz = new DateTimeZone(date_default_timezone_get ());
                    $dt = new DateTime($utc_time, $utc_tz);
                    $dt->setTimeZone($local_tz);
                    $local_time = $dt->format('H:i:s M jS');
                print "<td align=\"left\">$local_time</td>";
		print "<td align=\"left\" width=\"180\"><div style=\"float:left;\"><a href=\"".$callsignLookupUrl.$MyCallLink."\" target=\"_blank\">$MyCall</a>";
                if($MyId) { print "/".$MyId."</div> <div style=\"text-align:right;\">&#40;<a href=\"https://aprs.fi/#!call=".$MyCallLink."*\" target=\"_blank\">dPRS</a>&#41;</div></td>"; }
		     else { print "</div> <div style=\"text-align:right;\">&#40;<a href=\"https://aprs.fi/#!call=".$MyCallLink."*\" target=\"_blank\">dPRS</a>&#41;</div></td>"; }
                print "<td align=\"left\" width=\"100\">$YourCall</td>";
                print "<td align=\"left\" width=\"100\">$Rpt1</td>";
                print "<td align=\"left\" width=\"100\">$Rpt2</td>";
                print "</tr>\n";
	    }
	}
	fclose($WorkedLog);
    }
?>
</table>
