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
  if (file_exists($bmAPIkeyFile) && fopen($bmAPIkeyFile,'r')) {
    $configBMapi = parse_ini_file($bmAPIkeyFile, true);
    $bmAPIkey = $configBMapi['key']['apikey'];
    // Check the BM API Key
    if ( strlen($bmAPIkey) <= 20 ) { unset($bmAPIkey); }
  }

  //Load the dmrgateway config file
  $dmrGatewayConfigFile = '/etc/dmrgateway';
  if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }

  // Get the current DMR Master from the config
  $dmrMasterHost = getConfigItem("DMR Network", "Address", $mmdvmconfigs);
  if ( $dmrMasterHost == '127.0.0.1' ) { $dmrMasterHost = $configdmrgateway['DMR Network 1']['Address']; $dmrGWID = $configdmrgateway['DMR Network 1']['Id']; }

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
    // OK this is Brandmeister, get some config and output the HTML
    $dmrID = ( ! empty($dmrGWID) ? $dmrGWID : getConfigItem("General", "Id", $mmdvmconfigs));

  // If there is a BM API Key
  $bmAPIurl = 'https://api.brandmeister.network/v1.0/repeater/';
  if ( !empty($_POST) && ( isset($_POST["dropDyn"]) || isset($_POST["dropQso"]) || isset($_POST["refSubmit"]) || isset($_POST["tgSubmit"]) ) ): // Data has been posted for this page
    // Are we a repeater
    if ( getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == "0" ) {
        unset($_POST["TS"]);
        $targetSlot = "0";
      } else {
        $targetSlot = $_POST["TS"];
      }
    // Figure out what has been posted
    if (isset($_POST["dropDyn"])) { $bmAPIurl = $bmAPIurl."setRepeaterTarantool.php?action=dropDynamicGroups&slot=".$targetSlot."&q=".$dmrID; }
    if (isset($_POST["dropQso"])) { $bmAPIurl = $bmAPIurl."setRepeaterDbus.php?action=dropCallRoute&slot=".$targetSlot."&q=".$dmrID; }
    if ( ($_POST["TGmgr"] == "ADD") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl."talkgroup/?action=ADD&id=".$dmrID; }
    if ( ($_POST["TGmgr"] == "DEL") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl."talkgroup/?action=DEL&id=".$dmrID; }
    if ( ($_POST["REFmgr"] == "LINK") && (isset($_POST["refSubmit"])) ) { $bmAPIurl = $bmAPIurl."reflector/setActiveReflector.php?id=".$dmrID; }
    if ( ($_POST["REFmgr"] == "UNLINK") && (isset($_POST["refSubmit"])) ) { $bmAPIurl = $bmAPIurl."reflector/setActiveReflector.php?id=".$dmrID; $targetREF = "4000"; }
    if ( (isset($_POST["tgNr"])) && (isset($_POST["tgSubmit"])) ) { $targetTG = preg_replace("/[^0-9]/", "", $_POST["tgNr"]); }
    if ( (isset($_POST["reflectorNr"])) && (isset($_POST["refSubmit"])) && ($_POST["REFmgr"] == "LINK")) { $targetREF = preg_replace("/[^0-9]/", "", $_POST["reflectorNr"]); }
    // Build the Data
    if ( (!isset($_POST["dropDyn"])) && (!isset($_POST["dropQso"])) ) {
      $postDataTG = array(
        'talkgroup' => $targetTG,
        'timeslot' => $targetSlot,
      );
      $postDataREF = array(
        'reflector' => $targetREF,
      );
    }
    // Build the Query
    $postData = '';
    if (isset($_POST["refSubmit"])) { $postData = http_build_query($postDataREF); }
    if (isset($_POST["tgSubmit"])) { $postData = http_build_query($postDataTG); }
    $postHeaders = array(
      'Content-Type: application/x-www-form-urlencoded',
      'Content-Length: '.strlen($postData),
      'Authorization: Basic '.base64_encode($bmAPIkey.':'),
    );
    
    $opts = array(
      'http' => array(
      'header'  => $postHeaders,
      'method'  => 'POST',
      'content' => $postData,
      'password' => '',
      'success' => '',
      'timeout' => 2,
      ),
    );
    $context = stream_context_create($opts);
    $result = @file_get_contents($bmAPIurl, false, $context);
    $feeback=json_decode($result);
    // Output to the browser
    echo '<b>BrandMeister Manager</b>'."\n";
    echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
    //echo "Sending command to BrandMeister API";
    print "BrandMeister API: ".$feeback->{'message'};
    echo "</td></tr>\n</table>\n";
    echo "<br />\n";
    // Clean up...
    unset($_POST);
    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
  else: // Do this when we are not handling post data
    if (isset($bmAPIkey)) {
      echo '<b>BrandMeister Manager</b>'."\n";
      echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">'."\n";
      echo '<table>
      <tr>
        <th><a class=tooltip href="#">Tools<span><b>Tools</b></span></a></th>
        <th><a class=tooltip href="#">Active Ref<span><b>Active Reflector</b></span></a></th>
        <th><a class=tooltip href="#">Link / Unlink<span><b>Link or unlink</b></span></a></th>
        <th><a class=tooltip href="#">Action<span><b>Take Action</b></span></a></th>
      </tr>'."\n";
      echo '    <tr>';
      echo '<td><input type="submit" value="Drop QSO" name="dropQso" /><input type="submit" value="Drop All Dynamic" name="dropDyn" /></td>';
      echo '<td><select name="reflectorNr">'."\n";
      if ( $bmReflectorActive == "None" || $bmReflectorActive == "REF0" ) { echo '        <option selected="selected" value="0">None</option>'."\n"; } else { echo '        <option value="0">None</option>'."\n"; }
        for ($refNrBase = 1; $refNrBase <= 999; $refNrBase++) {
          $refNr = 4000 + $refNrBase;
          if ( "REF".$refNr == $bmReflectorActive ) { echo '        <option selected="selected" value="'.$refNr.'">REF'.$refNr.'</option>'."\n"; }
          else { echo '        <option value="'.$refNr.'">REF'.$refNr.'</option>'."\n"; }
        }
      echo '        </td>'."\n";
      echo '      <td><input type="radio" name="REFmgr" value="LINK" />Link <input type="radio" name="REFmgr" value="UNLINK" checked="checked" />UnLink</td>';
      echo '<td><input type="submit" value="Modify Reflector" name="refSubmit" /></td>';
      echo '</tr>'."\n";
      echo '<tr>
        <th><a class=tooltip href="#">Static Talkgroup<span><b>Enter the Talkgroup number</b></span></a></th>
        <th><a class=tooltip href="#">Slot<span><b>Where to link/unlink</b></span></a></th>
        <th><a class=tooltip href="#">Add / Remove<span><b>Add or Remove</b></span></a></th>
        <th><a class=tooltip href="#">Action<span><b>Take Action</b></span></a></th>
      </tr>'."\n";
      echo '    <tr>';
      echo '<td><input type="text" name="tgNr" size="10" maxlength="7" /></td>';
      echo '<td><input type="radio" name="TS" value="1" />TS1 <input type="radio" name="TS" value="2" checked="checked" />TS2</td>';
      echo '<td><input type="radio" name="TGmgr" value="ADD" checked="checked" />Add <input type="radio" name="TGmgr" value="DEL" />Delete</td>';
      echo '<td><input type="submit" value="Modify Static" name="tgSubmit" /></td>';
      echo '</tr>'."\n";
      echo '  </table>'."\n";
      echo '  <br />'."\n";
      }
  endif;
  }
}

?>
