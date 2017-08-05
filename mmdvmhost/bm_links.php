<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// Check if DMR is Enabled
$testMMDVModeDMR = getConfigItem("DMR", "Enable", $mmdvmconfigs);

if ( $testMMDVModeDMR == 1 ) {
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
  $dmrID = getConfigItem("DMR", "Id", $mmdvmconfigs);

  // Connect to the master and get the JSON for this host on the correct DMR Master
  $json = json_decode(file_get_contents("http://$dmrMasterHostIP/status/status.php", true));
  foreach($json as $repeater) {
    if($repeater->number == $dmrID) {
      $taData = $repeater->caption;
      $values = $repeater->values;
    }
  }

  if ($values[18] == '0') { $linkedTG = "None"; } else { $linkedTG = $values[18]; }
  if (($values[19] >= '4001') && ($values[19] <= '4999')) { $linkedREF = $values[19]; } else { $linkedREF = "None"; }

  echo '<b>Active BrandMeister Connections</b>
  <table>
    <tr>
      <th><a class=tooltip href="#">DMR Master<span><b>Connected Master</b></span></a></th>
      <th><a class=tooltip href="#">Talker Alias Data<span><b>TA Data</b></span></a></th>
      <!-- <th><a class=tooltip href="#">Linked TG<span><b>Last Linked Talk Group</b></span></a></th> -->
      <th><a class=tooltip href="#">Linked Ref<span><b>Last Linked Reflector</b></span></a></th>
    </tr>'."\n";

  echo '    <tr>'."\n";
  echo '      <td>'.$dmrMasterHost.'</td>';
  echo '<td>'.$taData.'</td>';
  //echo '<td>'.$linkedTG.'</td>';
  echo '<td>'.$linkedREF.'</td>'."\n";
  echo '    </tr>'."\n";

  echo '  </table>'."\n";
  echo '  <br />'."\n";
  }
}
?>
