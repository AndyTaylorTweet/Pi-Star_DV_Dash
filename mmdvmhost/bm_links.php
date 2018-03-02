<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// Check if DMR is Enabled
$testMMDVModeDMR = getConfigItem("DMR", "Enable", $mmdvmconfigs);

if ( $testMMDVModeDMR == 1 ) {
  //setup BM API Key
  $bmAPIkeyFile = '/etc/bmapi.key';
  if (file_exists($bmAPIkeyFile) && fopen($bmAPIkeyFile,'r')) { $configBMapi = parse_ini_file($bmAPIkeyFile, true);
    $bmAPIkey = $configBMapi['key']['apikey']; }
  
  //Load the dmrgateway config file
  $dmrGatewayConfigFile = '/etc/dmrgateway';
  if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }

  // Get the current DMR Master from the config
  $dmrMasterHost = getConfigItem("DMR Network", "Address", $mmdvmconfigs);
  if ( $dmrMasterHost == '127.0.0.1' ) { $dmrMasterHost = $configdmrgateway['DMR Network 1']['Address']; }

  // Store the DMR Master IP, we will need this for the JSON lookup
  $dmrMasterHostIP = $dmrMasterHost;

  // Make sure the master is a BrandMeister Master
  $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
  while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
                if ((strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
                        if ($dmrMasterHost == $dmrMasterHostF[2]) { $dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]); }
                }
  }

  if (substr($dmrMasterHost, 0, 2) == "BM") {
  // DMR ID, we will need this for the JSON lookup
  $dmrID = getConfigItem("General", "Id", $mmdvmconfigs);

  // Use BM API to get information about current TGs
  $json = json_decode(file_get_contents("https://api.brandmeister.network/v1.0/repeater/?action=profile&q=$dmrID", true));

  // Set some Variable
  $bmStaticTGList = "";
  $bmDynamicTGList = "";

  // Pull the information form JSON
  if (isset($json->reflector->reflector)) { $bmReflectorDef = "REF".$json->reflector->reflector; } else { $bmReflectorDef = "Not Set"; }
  if (isset($json->reflector->interval)) { $bmReflectorInterval = $json->reflector->interval."(s)"; } else {$bmReflectorInterval = "Not Set"; }
  if ((isset($json->reflector->active)) && ($json->reflector->active != "4000")) { $bmReflectorActive = "REF".$json->reflector->active; } else { $bmReflectorActive = "None"; }
  if (isset($json->staticSubscriptions)) { $bmStaticTGListJson = $json->staticSubscriptions;
                                          foreach($bmStaticTGListJson as $staticTG) {
                                            $bmStaticTGList .= "TG".$staticTG->talkgroup." ";
                                          }
                                          $bmStaticTGList = wordwrap($bmStaticTGList, 15, "<br />\n");
                                          if (preg_match('/TG/', $bmStaticTGList) == false) { $bmStaticTGList = "None"; }
                                         } else { $bmStaticTGList = "None"; }
  if (isset($json->dynamicSubscriptions)) { $bmDynamicTGListJson = $json->dynamicSubscriptions;
                                           foreach($bmDynamicTGListJson as $dynamicTG) {
                                             $bmDynamicTGList .= "TG".$dynamicTG->talkgroup." ";
                                           }
                                           $bmDynamicTGList = wordwrap($bmDynamicTGList, 15, "<br />\n");
                                           if (preg_match('/TG/', $bmDynamicTGList) == false) { $bmDynamicTGList = "None"; }
                                          } else { $bmDynamicTGList = "None"; }

  echo '<b>Active BrandMeister Connections</b>
  <table>
    <tr>
      <th><a class=tooltip href="#">'.$lang['bm_master'].'<span><b>Connected Master</b></span></a></th>
      <th><a class=tooltip href="#">Default Ref<span><b>Default Reflector</b></span></a></th>
      <th><a class=tooltip href="#">Timeout(s)<span><b>Configured Timeout</b></span></a></th>
      <th><a class=tooltip href="#">Active Ref<span><b>Active Reflector</b></span></a></th>
      <th><a class=tooltip href="#">Static TGs<span><b>Statically linked talkgroups</b></span></a></th>
      <th><a class=tooltip href="#">Dynamic TGs<span><b>Dynamically linked talkgroups</b></span></a></th>
    </tr>'."\n";

  echo '    <tr>'."\n";
  echo '      <td>'.$dmrMasterHost.'</td>';
  echo '<td>'.$bmReflectorDef.'</td>';
  echo '<td>'.$bmReflectorInterval.'</td>';
  echo '<td>'.$bmReflectorActive.'</td>';
  echo '<td>'.$bmStaticTGList.'</td>';
  echo '<td>'.$bmDynamicTGList.'</td>';
  echo '</tr>'."\n";
  echo '  </table>'."\n";
  echo '  <br />'."\n";

  // If there is a BM API Key
  $bmAPIurl = 'https://api.brandmeister.network/v1.0/repeater/';
  if (!empty($_POST)): // Data has been posted
    // Are we a repeater
    if ( getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == "0" ) {
        unset($_POST["TS"]);
        $targetSlot = "0";
      } else {
        $targetSlot = $_POST["TS"];
      }

    // Figure out what has been posted
    if ( ($_POST["Action"] == "ADD") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl."talkgroup/?action=ADD&id=".$dmrID; }
    if ( ($_POST["Action"] == "DEL") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl."talkgroup/?action=DEL&id=".$dmrID; }
    if (isset($_POST["tgNr"])) { $targetTG = $_POST["tgNr"]; }
    
    // Build the JSON
    $postHeaders = array(
      'Content-Type:application/json',
      'Authorization: Basic '.base64_encode($bmAPIkey.":")
    );

    $jsonData = array(
      'talkgroup' => $targetTG,
      'timeslot' => $targetSlot
    );

    $opts = array(
      'http' => array(
      'header'  => $postHeaders,
      'method'  => 'POST',
      'content' => json_encode($jsonData)
      //'content' => http_build_query($jsonData)
      )
    );

    $context = stream_context_create($opts);
    $result = file_get_contents($bmAPIurl, false, $context);

    // Output to the browser
    echo '<b>BrandMeister Manager</b>'."\n";
    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
    //echo "SOME OUTPUT";
    echo $result;
    echo "</td></tr>\n</table>\n";
    echo "<br />\n";

    // Clean up...
    fclose($result);
    unset($_POST);
    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';

  else: // Do this when we are not handling post data
    if (isset($bmAPIkey)) {
      echo '<b>BrandMeister Manager</b>'."\n";
      echo '<form action="http://'.htmlentities($_SERVER['HTTP_HOST']).htmlentities($_SERVER['PHP_SELF']).'" method="post">'."\n";
      echo '<table>
      <tr>
        <th><a class=tooltip href="#">DMR ID<span><b>DMR ID</b></span></a></th>
        <th><a class=tooltip href="#">Active Ref<span><b>Active Reflector</b></span></a></th>
        <th><a class=tooltip href="#">Link / Unlink<span><b>Link or unlink</b></span></a></th>
        <th><a class=tooltip href="#">Action<span><b>Take Action</b></span></a></th>
      </tr>'."\n";
      echo '    <tr>';
      echo '<td>'.$dmrID.'</td>';
      echo '<td><select name="reflectorNr">'."\n";
      if ( $bmReflectorActive == "None" || $bmReflectorActive == "REF0" ) { echo '        <option selected="selected" value="0">None</option>'."\n"; } else { echo '        <option value="0">None</option>'."\n"; }
        for ($refNrBase = 1; $refNrBase <= 999; $refNrBase++) {
          $refNr = 4000 + $refNrBase;
          if ( "REF".$refNr == $bmReflectorActive ) { echo '        <option selected="selected" value="'.$refNr.'">REF'.$refNr.'</option>'."\n"; }
          else { echo '        <option value="'.$refNr.'">REF'.$refNr.'</option>'."\n"; }
        }
      echo '        </td>'."\n";
      echo '      <td><input type="radio" name="Link" value="LINK" checked="checked" />Link <input type="radio" name="Link" value="UNLINK" />UnLink</td>';
      echo '<td><input type="submit" value="Request Change" name="refSubmit" /></td>';
      echo '</tr>'."\n";
      //echo '  </table>'."\n";
      echo '<tr>
        <th><a class=tooltip href="#">Talk Group<span><b>Enter the Talkgroup number</b></span></a></th>
        <th><a class=tooltip href="#">Slot<span><b>Where to link/unlink</b></span></a></th>
        <th><a class=tooltip href="#">Add / Remove<span><b>Add or Remove</b></span></a></th>
        <th><a class=tooltip href="#">Action<span><b>Take Action</b></span></a></th>
      </tr>'."\n";
      echo '    <tr>';
      echo '<td><input type="text" name="tgNr" size="10" maxlength="7" /></td>';
      echo '<td><input type="radio" name="TS" value="1" />TS1 <input type="radio" name="TS" value="2" checked="checked" />TS2</td>';
      echo '<td><input type="radio" name="Action" value="ADD" checked="checked" />Add <input type="radio" name="Action" value="DEL" />Delete</td>';
      echo '<td><input type="submit" value="Modify TalkGroup" name="tgSubmit" /></td>';
      echo '</tr>'."\n";
      echo '  </table>'."\n";
      echo '  <br />'."\n";
      }

  endif;

  }
}
?>
