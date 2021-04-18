<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
//include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code

require_once($_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php');

header('Content-type: application/json');

function checkMode($mode, $mmdvmconfigs) {
	
	$data = array ('enabled' => 0, 'running' => 0);
	
	// shows if mode is enabled or not.
	if (getEnabled($mode, $mmdvmconfigs) == 1) {
		$data['enabled'] = 1;
		if ($mode == "D-Star Network") {
			if (isProcessRunning("ircddbgatewayd")) {
				$data['running'] = 1;
			}
		}
		elseif ($mode == "System Fusion Network") {
			if (isProcessRunning("YSFGateway")) {
				$data['running'] = 1;
			} 
		}
		elseif ($mode == "P25 Network") {
			if (isProcessRunning("P25Gateway")) {
				$data['running'] = 1;
			} 
		}
		elseif ($mode == "NXDN Network") {
			if (isProcessRunning("NXDNGateway")) {
				$data['running'] = 1;
			}
		}
		elseif ($mode == "DAPNET Network") {
			if (isProcessRunning("DAPNETGateway")) {
				$data['running'] = 1;
			} 
		}
		elseif ($mode == "DMR Network") {
			if (getConfigItem("DMR Network", "Address", $mmdvmconfigs) == '127.0.0.1') {
				if (isProcessRunning("DMRGateway")) {
					if (!checkDMRLogin("DMRGateway") > 0) { 
						$data['running'] = 1;
					}
					
				} 
			}
			else {
				if (isProcessRunning("MMDVMHost")) {
					if (!checkDMRLogin("MMDVMHost") > 0) { 
						$data['running'] = 1;
					}
				}
			}				
		}
		else {
			if ($mode == "D-Star" || $mode == "DMR" || $mode == "System Fusion" || $mode == "P25" || $mode == "NXDN" || $mode == "POCSAG") {
				if (isProcessRunning("MMDVMHost")) {
					$data['running'] = 1;
				} 
			}
		}
	}
	elseif ( ($mode == "YSF XMode") && (getEnabled("System Fusion", $mmdvmconfigs) == 1) ) {
		if ( (isProcessRunning("MMDVMHost")) && (isProcessRunning("YSF2DMR") || isProcessRunning("YSF2NXDN") || isProcessRunning("YSF2P25")) ) {
			$data['running'] = 1;
		} 
	}
	elseif ( ($mode == "DMR XMode") && (getEnabled("DMR", $mmdvmconfigs) == 1) ) {
		if ( (isProcessRunning("MMDVMHost")) && (isProcessRunning("DMR2YSF") || isProcessRunning("DMR2NXDN")) ) {
			$data['running'] = 1;
		} 
	}
	elseif ( ($mode == "YSF2DMR Network") && (getEnabled("System Fusion", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("YSF2DMR")) {
			$data['running'] = 1;
		} 
	}
	elseif ( ($mode == "YSF2NXDN Network") && (getEnabled("System Fusion", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("YSF2NXDN")) {
			$data['running'] = 1;
		} 
	}
	elseif ( ($mode == "YSF2P25 Network") && (getEnabled("System Fusion", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("YSF2P25")) {
			$data['running'] = 1;
		} 
	}
	elseif ( ($mode == "DMR2NXDN Network") && (getEnabled("DMR", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("DMR2NXDN")) {
			$data['running'] = 1;
		} 
	}
	elseif ( ($mode == "DMR2YSF Network") && (getEnabled("DMR", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("DMR2YSF")) {
			$data['running'] = 1;
		} 
	}
	
    //$mode = str_replace("System Fusion", "YSF", $mode);
    //$mode = str_replace("Network", "Net", $mode);
    //if (strpos($mode, 'YSF2') > -1) { $mode = str_replace(" Net", "", $mode); }
    //if (strpos($mode, 'DMR2') > -1) { $mode = str_replace(" Net", "", $mode); }
    
	$data1[$mode] = $data;
	
	return $data1;
}



//Load the ircDDBGateway config file
$configs = array();
if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
                list($key,$value) = preg_split('/=/',$line);
                $value = trim(str_replace('"','',$value));
                if ($key != 'ircddbPassword' && strlen($value) > 0)
                $configs[$key] = $value;
        }

}

//Load the DStarRepeater config file
$configdstar = array();
if ($configdstarfile = fopen('/etc/dstarrepeater','r')) {
        while ($line1 = fgets($configdstarfile)) {
		if (strpos($line1, '=') !== false) {
                	list($key1,$value1) = preg_split('/=/',$line1);
                	$value1 = trim(str_replace('"','',$value1));
                	if (strlen($value1) > 0)
                	$configdstar[$key1] = $value1;
		}
        }
}

//Load the dmrgateway config file
$dmrGatewayConfigFile = '/etc/dmrgateway';
if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }

//Load the dapnetgateway config file
$dapnetGatewayConfigFile = '/etc/dapnetgateway';
if (fopen($dapnetGatewayConfigFile,'r')) { $configdapnetgateway = parse_ini_file($dapnetGatewayConfigFile, true); }

// Load the ysf2dmr config file
if (file_exists('/etc/ysf2dmr')) {
	$ysf2dmrConfigFile = '/etc/ysf2dmr';
	if (fopen($ysf2dmrConfigFile,'r')) { $configysf2dmr = parse_ini_file($ysf2dmrConfigFile, true); }
}
// Load the ysf2nxdn config file
if (file_exists('/etc/ysf2nxdn')) {
	$ysf2nxdnConfigFile = '/etc/ysf2nxdn';
	if (fopen($ysf2nxdnConfigFile,'r')) { $configysf2nxdn = parse_ini_file($ysf2nxdnConfigFile, true); }
}
// Load the ysf2p25 config file
if (file_exists('/etc/ysf2p25')) {
	$ysf2p25ConfigFile = '/etc/ysf2p25';
	if (fopen($ysf2p25ConfigFile,'r')) { $configysf2p25 = parse_ini_file($ysf2p25ConfigFile, true); }
}
// Load the dmr2ysf config file
if (file_exists('/etc/dmr2ysf')) {
	$dmr2ysfConfigFile = '/etc/dmr2ysf';
	if (fopen($dmr2ysfConfigFile,'r')) { $configdmr2ysf = parse_ini_file($dmr2ysfConfigFile, true); }
}
// Load the dmr2nxdn config file
if (file_exists('/etc/dmr2nxdn')) {
	$dmr2nxdnConfigFile = '/etc/dmr2nxdn';
	if (fopen($dmr2nxdnConfigFile,'r')) { $configdmr2nxdn = parse_ini_file($dmr2nxdnConfigFile, true); }
}

$data = array();

$data['time'] = time();
$data['configs'] = $configs;
$data = array_merge($data, checkMode("D-Star", $mmdvmconfigs));
$data = array_merge($data, checkMode("DMR", $mmdvmconfigs));
$data = array_merge($data, checkMode("System Fusion", $mmdvmconfigs));
$data = array_merge($data, checkMode("P25", $mmdvmconfigs));
$data = array_merge($data, checkMode("YSF XMode", $mmdvmconfigs));
$data = array_merge($data, checkMode("NXDN", $mmdvmconfigs));
$data = array_merge($data, checkMode("DMR XMode", $mmdvmconfigs));
$data = array_merge($data, checkMode("POCSAG", $mmdvmconfigs));


$data = array_merge($data, checkMode("D-Star Network", $mmdvmconfigs));
$data = array_merge($data, checkMode("DMR Network", $mmdvmconfigs));
$data = array_merge($data, checkMode("System Fusion Network", $mmdvmconfigs));
$data = array_merge($data, checkMode("P25 Network", $mmdvmconfigs));
$data = array_merge($data, checkMode("YSF2DMR Network", $mmdvmconfigs));
$data = array_merge($data, checkMode("NXDN Network", $mmdvmconfigs));
$data = array_merge($data, checkMode("YSF2NXDN Network", $mmdvmconfigs));
$data = array_merge($data, checkMode("YSF2P25 Network", $mmdvmconfigs));
$data = array_merge($data, checkMode("DMR2NXDN Network", $mmdvmconfigs));
$data = array_merge($data, checkMode("DMR2YSF Network", $mmdvmconfigs));

echo json_encode($data);

?>