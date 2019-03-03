<?php
// Most of the work here contributed by geeks4hire (Ben Horan)

include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// Get origin of the page loading
$origin = (isset($_GET['origin']) ? $_GET['origin'] : (isset($myOrigin) ? $myOrigin : "unknown"));

//
// Fill table entries with DAPNETGW messages, stops to <MY_RIC> marker if tillMYRIC is true
//
function listDAPNETGWMessages($logLinesDAPNETGateway, $tillMYRIC) {
    foreach($logLinesDAPNETGateway as $dapnetMessageLine) {
	
	if ($tillMYRIC) {
	    // After this, only messages for my RIC are stored
	    if (strcmp($dapnetMessageLine, '<MY_RIC>') == 0)
		break;
	}
	
	$dapnetMessageArr = explode(" ", $dapnetMessageLine);
	$utc_time = $dapnetMessageArr["0"]." ".substr($dapnetMessageArr["1"], 0, -4);
	$utc_tz = new DateTimeZone('UTC');
	$local_tz = new DateTimeZone(date_default_timezone_get());
	$dt = new DateTime($utc_time, $utc_tz);
	$dt->setTimeZone($local_tz);
	$local_time = $dt->format('H:i:s M jS');
	$pocsag_timeslot = $dapnetMessageArr["6"];
	$pocsag_ric = str_replace(',', '', $dapnetMessageArr["8"]);

	// Extract message, but since dblquote is valid character,
	// we're searching for the first one as beginning of the
	// message, till the end of the message lines (minus dbquote themselves)
	$pos = strpos($dapnetMessageLine, '"');
	$len = strlen($dapnetMessageLine);
	$pocsag_msg = substr($dapnetMessageLine, ($pos - $len) + 1, ($len - $pos) - 2);
	
	// Formatting long messages without spaces
	if (strpos($pocsag_msg, ' ') == 0 && strlen($pocsag_msg) >= 45) {
	    $pocsag_msg = wordwrap($pocsag_msg, 45, ' ', true);
	}
	
	echo "<tr>";
	echo "<td style=\"width: 140px; vertical-align: top; text-align: left;\">".$local_time."</td>";
	echo "<td style=\"width: 70px; vertical-align: top; text-align: center;\">Slot ".$pocsag_timeslot."</td>";
	echo "<td style=\"width: 90px; vertical-align: top; text-align: center;\">".$pocsag_ric."</td>";
	echo "<td style=\"width: max-content; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal !important;\">".$pocsag_msg."</td>";
	echo "</tr>";	
    }
}

//
if (strcmp($origin, "admin") == 0) {
    $myRIC = getConfigItem("DAPNETAPI", "MY_RIC", getDAPNETAPIConfig());
    
    // Display personnal messages only if RIC has been defined, and some personnal messages are available
    if ($myRIC && (array_search('<MY_RIC>', $logLinesDAPNETGateway) != FALSE)) {
?>
    
    <input type="hidden" name="pocsag-autorefresh" value="OFF" />

    <!-- Personnal messages-->
    
    <div>

    <b><?php echo $lang['pocsag_persolist'];?></b>
	
	<div>
	    <table>
		<thread>
		    <tr>
			<th style="width: 140px;" ><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
			<th style="width: max-content;" ><a class="tooltip" href="#"><?php echo $lang['pocsag_msg'];?><span><b>Message contents</b></span></a></th>
		    </tr>
		</thread>
	    </table>
	</div>
	
	<div style="max-height:190px; overflow-y:auto;" >
	    <table>
		<thread>
		    <tr>
			<th></th>
			<th></th>
		    </tr>
		</thread>
		
		<tbody>
		    
		    <?php
		    
		    $found = false;
		    
		    foreach ($logLinesDAPNETGateway as $dapnetMessageLine) {
			
			// After this, only messages for my RIC are stored
			if (!$found && strcmp($dapnetMessageLine, '<MY_RIC>') == 0) {
			    $found = true;
			    continue;
			}
			
			if ($found) {
			    $dapnetMessageArr = explode(" ", $dapnetMessageLine);
			    $utc_time = $dapnetMessageArr["0"]." ".substr($dapnetMessageArr["1"],0,-4);
			    $utc_tz = new DateTimeZone('UTC');
			    $local_tz = new DateTimeZone(date_default_timezone_get ());
			    $dt = new DateTime($utc_time, $utc_tz);
			    $dt->setTimeZone($local_tz);
			    $local_time = $dt->format('H:i:s M jS');
			    
			    $pos = strpos($dapnetMessageLine, '"');
			    $len = strlen($dapnetMessageLine);
			    $pocsag_msg = substr($dapnetMessageLine, ($pos - $len) + 1, ($len - $pos) - 2);
			    
			    // Formatting long messages without spaces
			    if (strpos($pocsag_msg, ' ') == 0 && strlen($pocsag_msg) >= 70) {
				$pocsag_msg = wordwrap($pocsag_msg, 70, ' ', true);
			    }
			    
		    ?>
		    
		            <tr>
				<td style="width: 140px; vertical-align: top; text-align: left;"><?php echo $local_time; ?></td>
				<td style="width: max-content; vertical-align: top; text-align: left; word-wrap: break-word; white-space: normal !important;"><?php echo $pocsag_msg; ?></td>
			    </tr>
		    
<?php
                        } // $found
                    } // foreach
?>

		</tbody>
	    </table>
	    
	</div>
	
	<br />
	
<?php
    } // $myRIC
?>

<div>
    
    <div>
	
	<!-- Activity -->
	<b><?php echo $lang['pocsag_list'];?></b>
	
	<div>
	    <table>
		<thread>
		    <tr>
			<th style="width: 140px;" ><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
			<th style="width: 70px;" ><a class="tooltip" href="#"><?php echo $lang['pocsag_timeslot'];?><span><b>Message Mode</b></span></a></th>
			<th style="width: 90px;" ><a class="tooltip" href="#"><?php echo $lang['target'];?><span><b>RIC / CapCode of the receiving Pager</b></span></a></th>
			<th style="width: max-content;" ><a class="tooltip" href="#"><?php echo $lang['pocsag_msg'];?><span><b>Message contents</b></span></a></th>
		    </tr>
		</thread>
	    </table>
	</div>
	
	<div style="max-height:190px; overflow-y:auto;" >
	    <table>
		<thread>
		    <tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		    </tr>
		</thread>
		<tbody>
		    
		    <?php listDAPNETGWMessages($logLinesDAPNETGateway, true); ?>
		    
		</tbody>
	    </table>
	    
	</div>
        
    </div>
    
    <div style="float: right; vertical-align: bottom; padding-top: 5px;">
	<div class="grid-container" style="display: inline-grid; grid-template-columns: auto 40px; padding: 1px; grid-column-gap: 5px;">
	    <div class="grid-item" style="padding-top: 3px;" >POCSAG Auto Refresh
	    </div>
	    <div class="grid-item" >
		<div> <input id="toggle-pocsag-autorefresh" class="toggle toggle-round-flat" type="checkbox" name="pocsag-autorefresh" value="ON" checked="checked" aria-checked="true" aria-label="POCSAG Auto Refresh" onchange="setAutorefresh(this)" /><label for="toggle-pocsag-autorefresh" ></label>
		</div>
	    </div>
	</div>
    </div>
    
</div>

<?php

}
else { // origin == "admin"

?>
    
    <b><?php echo $lang['pocsag_list'];?></b>
    
    <table>
	<tr>
	    <th><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
	    <th><a class="tooltip" href="#"><?php echo $lang['pocsag_timeslot'];?><span><b>Message Mode</b></span></a></th>
	    <th><a class="tooltip" href="#"><?php echo $lang['target'];?><span><b>RIC / CapCode of the receiving Pager</b></span></a></th>
	    <th><a class="tooltip" href="#"><?php echo $lang['pocsag_msg'];?><span><b>Message contents</b></span></a></th>
	</tr>
	
	<?php listDAPNETGWMessages($logLinesDAPNETGateway, false); ?>
	
    </table>
    
<?php
} // origin == "admin"
?>
