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

    $maxlength = (5 * (80 - (strlen($configdapnetapi['DAPNETAPI']['USER']) + 2 /* 'CALLSIGN: ' prefix */)));
    
    // Data has been posted for this page (POST)
    if ((empty($_POST) != TRUE) && (isset($_POST["dapSubmit"]) && (empty($_POST["dapSubmit"]) != TRUE)) && (isset($_POST["dapToCallsign"]) && (empty($_POST["dapToCallsign"]) != TRUE)) && (isset($_POST["dapMsgContent"]) && (empty($_POST["dapMsgContent"]) != TRUE))) {
        
        $dapnetTo = escapeshellcmd($_POST['dapToCallsign']);
	$filteredChars = array('\''=>'\\\'', '"'=>'\\\\\\"');
	$dapnetContent = strtr(str_replace(array("\r\n", "\n", "\r"), "", iconv('UTF-8','ASCII//TRANSLIT', $_POST['dapMsgContent'])), $filteredChars);

        $dapnetCmd = 'sudo /usr/local/sbin/pistar-dapnetapi '.$dapnetTo.' "'.$dapnetContent.'" nohost 2>&1';
        
        unset($dummy);
        
        ## Send POCSAG Page
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
    
        echo '<b>DAPNET Messenger</b>'."\n";
        echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">'."\n";
        echo '<table>
		<tr>
			<th><a class=tooltip href="#">To<span><b>Enter the destination callsign</b></span></a></th>
			<th><a class=tooltip href="#">Message<span><b>Enter the message content</b></span></a></th>
			<th><a class=tooltip href="#">Action<span><b>Send the message</b></span></a></th>
		</tr>'."\n";
        echo '  <tr>';
        echo '    <td><input type="text" name="dapToCallsign" size="13" maxlength="12" value="" /></td>';
        echo '    <td><textarea maxlength="'.$maxlength.'" name="dapMsgContent" cols="60" rows="3" style="overflow:scroll;" value="" /></textarea></td>';
        echo '    <td><input type="submit" value="Send" name="dapSubmit" /></td>';
        echo '  </tr>'."\n";
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
