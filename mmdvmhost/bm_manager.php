<?php
if ($_SERVER["PHP_SELF"] == "/admin/index.php") { // Stop this working outside of the admin page
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
      if ( strlen($bmAPIkey) >= 200 ) { $bmAPIkeyV2 = $bmAPIkey; unset($bmAPIkey); }
    }

    //Load the dmrgateway config file
    $dmrGatewayConfigFile = '/etc/dmrgateway';
    if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }

    // Get the current DMR Master from the config
    $dmrMasterHost = getConfigItem("DMR Network", "Address", $mmdvmconfigs);
    if ( $dmrMasterHost == '127.0.0.1' ) {
      $dmrMasterHost = $configdmrgateway['DMR Network 1']['Address'];
      if (isset($configdmrgateway['DMR Network 1']['Id'])) { $dmrID = $configdmrgateway['DMR Network 1']['Id']; }
    } elseif (getConfigItem("DMR", "Id", $mmdvmconfigs)) {
      $dmrID = getConfigItem("DMR", "Id", $mmdvmconfigs);
    } else {
      $dmrID = getConfigItem("General", "Id", $mmdvmconfigs);
    }

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
      if ( (isset($bmAPIkey)) && ( !empty($_POST) && ( isset($_POST["dropDyn"]) || isset($_POST["dropQso"]) || isset($_POST["tgSubmit"]) ) ) ): // Data has been posted for this page
          $bmAPIurl = 'https://api.brandmeister.network/v1.0/repeater/';
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
          if ( (isset($_POST["tgNr"])) && (isset($_POST["tgSubmit"])) ) { $targetTG = preg_replace("/[^0-9]/", "", $_POST["tgNr"]); }
          // Build the Data
          if ( (!isset($_POST["dropDyn"])) && (!isset($_POST["dropQso"])) && isset($targetTG) ) {
            $postDataTG = array(
              'talkgroup' => $targetTG,
              'timeslot' => $targetSlot,
            );
          }
          // Build the Query
          $postData = '';
          if (isset($_POST["tgSubmit"])) { $postData = http_build_query($postDataTG); }
          $postHeaders = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: '.strlen($postData),
            'Authorization: Basic '.base64_encode($bmAPIkey.':'),
            'User-Agent: Pi-Star Dashboard for '.$dmrID,
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
          if (isset($feeback)) { print "BrandMeister APIv1: ".$feeback->{'message'}; } else { print "BrandMeister APIv1: No Responce"; }
          echo "</td></tr>\n</table>\n";
          echo "<br />\n";
          // Clean up...
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';

      elseif ( (isset($bmAPIkeyV2)) && ( (isset($bmAPIkeyV2)) && ( !empty($_POST) && ( isset($_POST["dropDyn"]) || isset($_POST["dropQso"]) || isset($_POST["tgSubmit"]) ) ) ) ): // Data has been posted for this page
          $bmAPIurl = 'https://api.brandmeister.network/v2/device/';
          // Are we a repeater
          if ( getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == "0" ) {
              unset($_POST["TS"]);
              $targetSlot = "0";
            } else {
              $targetSlot = $_POST["TS"];
          }
          // Set the API URLs
          if (isset($_POST["dropDyn"])) { $bmAPIurl = $bmAPIurl.$dmrID."/action/dropDynamicGroups/".$targetSlot; $method = "GET"; }
          if (isset($_POST["dropQso"])) { $bmAPIurl = $bmAPIurl.$dmrID."/action/dropCallRoute/".$targetSlot; $method = "GET"; }
          if ( (isset($_POST["tgNr"])) && (isset($_POST["tgSubmit"])) ) { $targetTG = preg_replace("/[^0-9]/", "", $_POST["tgNr"]); }
          if ( ($_POST["TGmgr"] == "ADD") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl.$dmrID."/talkgroup/"; $method = "POST"; }
          if ( ($_POST["TGmgr"] == "DEL") && (isset($_POST["tgSubmit"])) ) { $bmAPIurl = $bmAPIurl.$dmrID."/talkgroup/".$targetSlot."/".$targetTG; $method = "DELETE"; }
          
          // Build the Data
          if ( (!isset($_POST["dropDyn"])) && (!isset($_POST["dropQso"])) && isset($targetTG) && $_POST["TGmgr"] == "ADD" ) {
            $postDataTG = array(
              'slot' => $targetSlot,
              'group' => $targetTG              
            );
          }
          // Build the Query
          $postData = '';
          if ($_POST["TGmgr"] == "ADD") { $postData = json_encode($postDataTG); }
          $postHeaders = array(
            'Content-Type: accept: application/json',
            'Content-Length: '.strlen($postData),
            'Authorization: Bearer '.$bmAPIkeyV2,
            'User-Agent: Pi-Star Dashboard for '.$dmrID,
          );

          $opts = array(
            'http' => array(
            'header'  => $postHeaders,
            'method'  => $method,
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
          //if (isset($feeback)) { print "BrandMeister APIv2: ".$feeback->{'message'}; } else { print "BrandMeister APIv2: No Responce"; }
          if (isset($feeback)) { print "BrandMeister APIv2: OK"; } else { print "BrandMeister APIv2: No Responce"; }
          echo "</td></tr>\n</table>\n";
          echo "<br />\n";
          // Clean up...
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';

      else: // Do this when we are not handling post data
        // If there is a BM API Key
        if (isset($bmAPIkey) || isset($bmAPIkeyV2)) {
          echo '<b>BrandMeister Manager</b>'."\n";
          echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">'."\n";
          echo '<table role="presentation">'."\n";
          echo '<tr>
            <th aria-hidden="true" id="lblTG" style="width:25%;"><a class=tooltip href="#">Static Talkgroup<span><b>Enter the Talkgroup number</b></span></a></th>
            <th aria-hidden="true" id="lblSlot" style="width:25%;"><a class=tooltip href="#">Slot<span><b>Where to link/unlink</b></span></a></th>
            <th aria-hidden="true" id="addRemove" style="width:25%;"><a class=tooltip href="#">Add / Remove<span><b>Add or Remove</b></span></a></th>
            <th><a class=tooltip href="#">Action<span><b>Take Action</b></span></a></th>
          </tr>'."\n";
          echo '    <tr>';
          echo '<td><input aria-labelledby="lblTG" type="text" name="tgNr" size="10" maxlength="7" /></td>';
          echo '<td role="radiogroup" aria-labelledby="lblTS"><input id="rbTS1" type="radio" name="TS" value="1" /><label for="rbTS1">TS1</label> <input id="rbTS2" type="radio" name="TS" value="2" checked="checked" /><label for="rbTS2">TS2</label></td>';
          echo '<td role="radiogroup" aria-labelledby="lblAddRemove"><input id="rbAdd" type="radio" name="TGmgr" value="ADD" checked="checked" /><label for="rbAdd">Add</label> <input id="rbDelete" type="radio" name="TGmgr" value="DEL" /><label for="rbDelete">Delete</label></td>';
          echo '<td><input type="submit" value="Modify Static" name="tgSubmit" /></td>';
          echo '</tr>'."\n";
          echo '    <tr>';
          echo '<td colspan="4" style="background: #ffffff;"><input type="submit" value="Drop QSO" name="dropQso" /> <input type="submit" value="Drop All Dynamic" name="dropDyn" /></td>';
          echo '</tr>'."\n";
          echo '  </table>'."\n";
          echo '  <br />'."\n";
        }
      endif;
    }
  }
}

