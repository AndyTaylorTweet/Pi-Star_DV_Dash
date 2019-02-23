<?php
// Most of the work here contributed by geeks4hire (Ben Horan)

include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// DAPNet API config 
if (! isset($configdapnetapi)) {

    if (file_exists('/etc/dapnetapi.key')) {
        
        $configDAPNetAPIConfigFile = '/etc/dapnetapi.key';
        
        if (fopen($configDAPNetAPIConfigFile,'r')) {
            $configdapnetapi = parse_ini_file($configDAPNetAPIConfigFile, true);
        }
    }
}

if (isset($configdapnetapi['DAPNETAPI']['USER']) && (empty($configdapnetapi['DAPNETAPI']['USER']) != TRUE)):

    $maxlength = (5 * (80 - (strlen($configdapnetapi['DAPNETAPI']['USER']) + 2 /* 'CALLSIGN: ' prefix */ + 4 /* 'x/n ' count */)));
    
    // Data has been posted for this page (POST)
    if ((empty($_POST) != TRUE) && (isset($_POST['dapSubmit']) && (empty($_POST['dapSubmit']) != TRUE)) && (isset($_POST['dapToCallsign']) && (empty($_POST['dapToCallsign']) != TRUE)) && (isset($_POST['dapMsgContent']) && (empty($_POST['dapMsgContent']) != TRUE))) {
        
        $dapnetTo = preg_replace('/[^,:space:[:alnum:]]/', "", trim($_POST['dapToCallsign'])); // Only A-Z a-z 0-9 and , allowed
        while (preg_match('/,,/', $dapnetTo)) { $dapnetTo = preg_replace('/,,/', ",", $dapnetTo); } // replace any double comma with single comma
        $dapnetTo = rtrim($dapnetTo, ","); // remove comma at the end of the string, if any.

        $filteredChars = array('\''=>'\\\'', '"'=>'\\\\\\"');
        $dapnetContent = strtr(str_replace(array("\r\n", "\n", "\r"), "", iconv('UTF-8','ASCII//TRANSLIT', $_POST['dapMsgContent'])), $filteredChars);

        // TRX AREA
        $dapnetTrx=((isset($_POST['dapToTrxArea']) && (empty($_POST['dapToTrxArea']) != TRUE)) ? preg_replace('/[^,:space:[:alnum:]-]/', "", trim(strtolower(trim($_POST['dapToTrxArea'])))) : "");
        while (preg_match('/,,/', $dapnetTrx)) { $dapnetTrx = preg_replace('/,,/', ",", $dapnetTrx); } // Replace any double comma with single comma
        while (preg_match('/--/', $dapnetTrx)) { $dapnetTrx = preg_replace('/--/', "-", $dapnetTrx); } // Replace any double dash with single dash
        $dapnetTrx = rtrim($dapnetTrx, ","); // Remove comma at the end of the string, if any.

        // if trx area is different that the one in api.key file, override it using DAPNET_TRXAREA envvar 
        if (strlen($dapnetTrx) > 0 && (strcmp($dapnetTrx, $configdapnetapi['DAPNETAPI']['TRXAREA']) != 0)) {
            $dapnetTrx = 'DAPNET_TRXAREA="'.$dapnetTrx.'"';
        }
        else {
            $dapnetTrx = "";
        }

        // Build command line
        $dapnetCmd = 'sudo '.$dapnetTrx.' /usr/local/sbin/pistar-dapnetapi '.$dapnetTo.' "'.$dapnetContent.'" nohost 2>&1';
        
        unset($dummy);
        
        // Send POCSAG Page
        $resultapi = exec($dapnetCmd, $dummy, $retValue);
        
        // Output to the browser
        echo '<b>DAPNET Messenger</b>'."\n";
        echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
        print $resultapi;
        echo "</td></tr>\n</table>\n";
        echo "<br />\n";

        unset($_POST); // Cleanup
        echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';

    }
    else {
        $dapnetTrxAreas=(isset($configdapnetapi['DAPNETAPI']['TRXAREA']) && (! empty($configdapnetapi['DAPNETAPI']['TRXAREA'])) ? $configdapnetapi['DAPNETAPI']['TRXAREA'] : "");
            
        echo '<b>DAPNET Messenger</b>'."\n";
        echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">'."\n";
        echo '<table>
		<tr>
			<th><a class=tooltip href="#">To<span><b>Enter the destination callsign and Trx Area(s)</b>You can set many callsigns and transmitter areas, comma separated</span></a></th>
			<th><a class=tooltip href="#">Message<span><b>Enter the message content</b>Length is limited to '.$maxlength.' characters (splitted in 5 POCSAG pages)</span></a></th>
			<th><a class=tooltip href="#">Action<span><b>Send the message</b></span></a></th>
		</tr>'."\n";
        echo '  <tr>';
        echo '    <td><input type="text" name="dapToCallsign" size="10" maxlength="70" value="" /></td>';
        echo '    <td rowspan="2"><textarea maxlength="'.$maxlength.'" name="dapMsgContent" cols="55" rows="3" style="overflow:scroll;" value="" /></textarea></td>';
        echo '    <td rowspan="2" style="vertical-align:bottom;padding:5px;"><input type="submit" value="Send" name="dapSubmit" /></td>';
        echo '  </tr>'."\n";
        echo '  <tr>';
        echo '    <td><input type="text" name="dapToTrxArea" size="5" maxlength="50" value="'.$dapnetTrxAreas.'" /></tr>';
        echo '  </tr>';
        echo '</table>'."\n";


    }

else:
    // Output to the browser
    echo '<b>DAPNET Messenger</b>'."\n";
    echo "<table>\n<tr><th>DISABLED</th></tr>\n<tr><td>";
    print "DAPNET API configuration is incomplete";
    echo "</td></tr>\n</table>\n";
    echo "<br />\n";
endif;
?>
