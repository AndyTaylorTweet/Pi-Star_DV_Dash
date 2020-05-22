<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// Set some Variable
$repeaterid = "";
$slot1tg = "";
$slot2tg = "";
$dmrID = "";

// Check if DMR is Enabled
$testMMDVModeDMR = getConfigItem("DMR", "Enable", $mmdvmconfigs);

if ( $testMMDVModeDMR == 1 ) {
  //Load the dmrgateway config file
  $dmrGatewayConfigFile = '/etc/dmrgateway';
  if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }

  // Get the current DMR Master from the config
  $dmrMasterHost = getConfigItem("DMR Network", "Address", $mmdvmconfigs);
  if ( $dmrMasterHost == '127.0.0.1' ) {
    // DMRGateway, need to check each config
    if (isset($configdmrgateway['DMR Network 1']['Address'])) {
      if (($configdmrgateway['DMR Network 1']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 1']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 1']['Id'];
      }
    }
    if (isset($configdmrgateway['DMR Network 2']['Address'])) {
      if (($configdmrgateway['DMR Network 2']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 2']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 2']['Id'];
      }
    }
    if (isset($configdmrgateway['DMR Network 3']['Address'])) {
      if (($configdmrgateway['DMR Network 3']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 3']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 3']['Id'];
      }
    }
    if (isset($configdmrgateway['DMR Network 4']['Address'])) {
      if (($configdmrgateway['DMR Network 4']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 4']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 4']['Id'];
      }
    }
    if (isset($configdmrgateway['DMR Network 5']['Address'])) {
      if (($configdmrgateway['DMR Network 5']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 5']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 5']['Id'];
      }
    }
  } else if ( $dmrMasterHost == 'tgif.network' ) {
    // MMDVMHost Connected directly to TGIF, get the ID form here
    if (getConfigItem("DMR", "Id", $mmdvmconfigs)) {
      $dmrID = getConfigItem("DMR", "Id", $mmdvmconfigs);
    } else {
      $dmrID = getConfigItem("General", "Id", $mmdvmconfigs);
    }
  }

  // Use TGIF API to get information about current TGs
  $jsonContext = stream_context_create(array('http'=>array('timeout' => 2, 'header' => 'User-Agent: Pi-Star Dashboard for '.$dmrID) )); // Add Timout and User Agent to include DMRID
  $json = json_decode(@file_get_contents("http://tgif.network/RPTRR/index.php?action=getsessions", true, $jsonContext));
  $jsonrows = count($json)-1;

  // Pull the information form JSON
  if ((count($json) > 0) && ($dmrID > 0)) {
    for ($counter = 0; $counter <= $jsonrows; $counter++) {
      $obj = $json[$counter];
      if ($obj->repeater_id == $dmrID) {
        $repeaterid = $obj->repeater_id;
        if ($obj->tg0 == "4000") { $slot1tg = "None"; } else { $slot1tg = "TG".$obj->tg0; }
        if ($obj->tg  == "4000") { $slot2tg = "None"; } else { $slot2tg = "TG".$obj->tg;  }
      }
    }

    echo '<b>Active TGIF Connections</b>
    <table>
      <tr>
        <th><a class=tooltip href="#">DMR Master<span><b>Connected Master</b></span></a></th>
        <th><a class=tooltip href="#">Repeater ID<span><b>The ID for this Repeater/Hotspot</b></span></a></th>
        <th><a class=tooltip href="#">Slot1 TG<span><b>TG linked to Slot 1</b></span></a></th>
        <th><a class=tooltip href="#">Slot2 TG<span><b>TG linked to Slot 2</b></span></a></th>
      </tr>'."\n";

    echo '    <tr>'."\n";
    echo '      <td>tgif.network</td>';
    echo '<td>'.$repeaterid.'</td>';
    echo '<td>'.$slot1tg.'</td>';
    echo '<td>'.$slot2tg.'</td>';
    echo '</tr>'."\n";
    echo '  </table>'."\n";
    echo '  <br />'."\n";
  }
}
?>
