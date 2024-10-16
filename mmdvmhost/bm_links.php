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

  // Use BM API to get information about current TGs
  $jsonContext = stream_context_create(array('http'=>array('timeout' => 2, 'header' => 'User-Agent: Pi-Star Dashboard for '.$dmrID) )); // Add Timout and User Agent to include DMRID
  if (isset($bmAPIkeyV2)) {
    $json = json_decode(@file_get_contents("https://api.brandmeister.network/v2/device/$dmrID/profile", true, $jsonContext));
  } else {
    $json = json_decode(@file_get_contents("https://api.brandmeister.network/v1.0/repeater/?action=PROFILE&q=$dmrID", true, $jsonContext));
  }

  // Set some Variable
  $bmStaticTGList = "";
  $bmDynamicTGList = "";

  // Pull the information form JSON
  if (isset($json->staticSubscriptions)) { $bmStaticTGListJson = $json->staticSubscriptions;
                                          foreach($bmStaticTGListJson as $staticTG) {
                                            if (getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) && $staticTG->slot == "1") {
                                              $bmStaticTGList .= "TG".$staticTG->talkgroup."(".$staticTG->slot.") ";
                                            }
                                            else if (getConfigItem("DMR Network", "Slot2", $mmdvmconfigs) && $staticTG->slot == "2") {
                                              $bmStaticTGList .= "TG".$staticTG->talkgroup."(".$staticTG->slot.") ";
                                            }
                                            else if (getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == "0" && getConfigItem("DMR Network", "Slot2", $mmdvmconfigs) && $staticTG->slot == "0") {
                                              $bmStaticTGList .= "TG".$staticTG->talkgroup." ";
                                            }
                                          }
                                          $bmStaticTGList = wordwrap($bmStaticTGList, 15, "<br />\n");
                                          if (preg_match('/TG/', $bmStaticTGList) == false) { $bmStaticTGList = "None"; }
                                         } else { $bmStaticTGList = "None"; }
  if (isset($json->dynamicSubscriptions)) { $bmDynamicTGListJson = $json->dynamicSubscriptions;
                                           foreach($bmDynamicTGListJson as $dynamicTG) {
                                             if (getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) && $dynamicTG->slot == "1") {
                                               $bmDynamicTGList .= "TG".$dynamicTG->talkgroup."(".$dynamicTG->slot.") ";
                                             }
                                             else if (getConfigItem("DMR Network", "Slot2", $mmdvmconfigs) && $dynamicTG->slot == "2") {
                                               $bmDynamicTGList .= "TG".$dynamicTG->talkgroup."(".$dynamicTG->slot.") ";
                                             }
                                             else if (getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == "0" && getConfigItem("DMR Network", "Slot2", $mmdvmconfigs) && $dynamicTG->slot == "0") {
                                               $bmDynamicTGList .= "TG".$dynamicTG->talkgroup." ";
                                             }
                                           }
                                           $bmDynamicTGList = wordwrap($bmDynamicTGList, 15, "<br />\n");
                                           if (preg_match('/TG/', $bmDynamicTGList) == false) { $bmDynamicTGList = "None"; }
                                          } else { $bmDynamicTGList = "None"; }

  echo '<b>Active BrandMeister Connections</b>
  <table>
    <tr>
      <th><a class=tooltip href="#">'.$lang['bm_master'].'<span><b>Connected Master</b></span></a></th>
      <th><a class=tooltip href="#">Repeater ID<span><b>The ID for this Repeater/Hotspot</b></span></a></th>
      <th><a class=tooltip href="#">Static TGs<span><b>Statically linked talkgroups</b></span></a></th>
      <th><a class=tooltip href="#">Dynamic TGs<span><b>Dynamically linked talkgroups</b></span></a></th>
    </tr>'."\n";

  echo '    <tr>'."\n";
  echo '      <td>'.$dmrMasterHost.'</td>';
  echo '<td>'.$dmrID.'</td>';
  echo '<td>'.$bmStaticTGList.'</td>';
  echo '<td>'.$bmDynamicTGList.'</td>';
  echo '</tr>'."\n";
  echo '  </table>'."\n";
  echo '  <br />'."\n";
  }
}
?>
