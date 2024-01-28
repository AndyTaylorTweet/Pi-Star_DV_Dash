<?php
function getMMDVMConfig() {
	// loads /etc/mmdvmhost into array for further use
	$conf = array();
	if ($configs = @fopen(MMDVMINIPATH."/".MMDVMINIFILENAME, 'r')) {
		while ($config = fgets($configs)) {
			array_push($conf, trim ( $config, " \t\n\r\0\x0B"));
		}
		fclose($configs);
	}
	return $conf;
}

function getYSFGatewayConfig() {
	// loads /etc/ysfgateway into array for further use
	$conf = array();
	if ($configs = @fopen(YSFGATEWAYINIPATH."/".YSFGATEWAYINIFILENAME, 'r')) {
		while ($config = fgets($configs)) {
			array_push($conf, trim ( $config, " \t\n\r\0\x0B"));
		}
		fclose($configs);
	}
	return $conf;
}

function getP25GatewayConfig() {
	// loads /etc/p25gateway into array for further use
	$conf = array();
	if ($configs = @fopen(P25GATEWAYINIPATH."/".P25GATEWAYINIFILENAME, 'r')) {
		while ($config = fgets($configs)) {
			array_push($conf, trim ( $config, " \t\n\r\0\x0B"));
		}
		fclose($configs);
	}
	return $conf;
}

function getNXDNGatewayConfig() {
	// loads /etc/nxdngateway into array for further use
	$conf = array();
	if ($configs = @fopen('/etc/nxdngateway', 'r')) {
		while ($config = fgets($configs)) {
			array_push($conf, trim ( $config, " \t\n\r\0\x0B"));
		}
		fclose($configs);
	}
	return $conf;
}

function getM17GatewayConfig() {
	// loads /etc/m17gateway into array for further use
	$conf = array();
	if ($configs = @fopen('/etc/m17gateway', 'r')) {
		while ($config = fgets($configs)) {
			array_push($conf, trim ( $config, " \t\n\r\0\x0B"));
		}
		fclose($configs);
	}
	return $conf;
}

function getDAPNETGatewayConfig() {
	// loads /etc/dapnetgateway into array for further use
	$conf = array();
	if ($configs = @fopen('/etc/dapnetgateway', 'r')) {
		while ($config = fgets($configs)) {
			array_push($conf, trim ( $config, " \t\n\r\0\x0B"));
		}
		fclose($configs);
	}
	return $conf;
}

function getConfigItem($section, $key, $configs) {
	// retrieves the corresponding config-entry within a [section]
	$sectionpos = array_search("[" . $section . "]", $configs) + 1;
	$len = count($configs);
	while(startsWith($configs[$sectionpos],$key."=") === false && $sectionpos <= ($len) ) {
		if (startsWith($configs[$sectionpos],"[")) {
			return null;
		}
		$sectionpos++;
	}

	return substr($configs[$sectionpos], strlen($key) + 1);
}

function getEnabled ($mode, $mmdvmconfigs) {
	// returns enabled/disabled-State of mode
	return getConfigItem($mode, "Enable", $mmdvmconfigs);
}

function checkDMRLogin ($dmrDaemon) {
        if ($dmrDaemon == "MMDVMHost") {
                if (file_exists(MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log")) {
                        $logPath = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log";
			$logCheckMMDVMHostDMRLogin = `tail -n 5 $logPath | awk '/master/ && /successfully/ || /master/ && /failed/' | tail -n 1`;
			if (strpos($logCheckMMDVMHostDMRLogin, "success")) { return 0; }
                        elseif (strpos($logCheckMMDVMHostDMRLogin, "fail")) { return 1; }
			else { return 0; }
                }
        }
        elseif ($dmrDaemon == "DMRGateway") {
                if (file_exists("/var/log/pi-star/DMRGateway-".gmdate("Y-m-d").".log")) {
                        $logPath = "/var/log/pi-star/DMRGateway-".gmdate("Y-m-d").".log";
			$logCheckDMRGatewayDMRLogin = `tail -n 5 $logPath | awk '/master/ && /successfully/ || /master/ && /failed/' | tail -n 1`;
			if (strpos($logCheckDMRGatewayDMRLogin, "success")) { return 0; }
                        elseif (strpos($logCheckDMRGatewayDMRLogin, "fail")) { return 1; }
			else { return 0; }
                }
        }
        else {
                return 0;
        }
}

function showMode($mode, $mmdvmconfigs) {
	// shows if mode is enabled or not.
	if (getEnabled($mode, $mmdvmconfigs) == 1) {
		if ($mode == "D-Star Network") {
			if (isProcessRunning("ircddbgatewayd")) {
				echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
			} else {
				echo "<td style=\"background:#b00; color:#500; width:50%;\">";
			}
		}
		elseif ($mode == "System Fusion Network") {
			if (isProcessRunning("YSFGateway")) {
				echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
			} else {
				echo "<td style=\"background:#b00; color:#500; width:50%;\">";
			}
		}
		elseif ($mode == "P25 Network") {
			if (isProcessRunning("P25Gateway")) {
				echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
			} else {
				echo "<td style=\"background:#b00; color:#500; width:50%;\">";
			}
		}
		elseif ($mode == "NXDN Network") {
			if (isProcessRunning("NXDNGateway")) {
				echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
			} else {
				echo "<td style=\"background:#b00; color:#500; width:50%;\">";
			}
		}
		elseif ($mode == "M17 Network") {
			if (isProcessRunning("M17Gateway")) {
				echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
			} else {
				echo "<td style=\"background:#b00; color:#500; width:50%;\">";
			}
		}
		elseif ($mode == "POCSAG Network") {
			if (isProcessRunning("DAPNETGateway")) {
				echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
			} else {
				echo "<td style=\"background:#b00; color:#500; width:50%;\">";
			}
		}
		elseif ($mode == "DMR Network") {
			if (getConfigItem("DMR Network", "Address", $mmdvmconfigs) == '127.0.0.1') {
				if (isProcessRunning("DMRGateway")) {
					if (checkDMRLogin("DMRGateway") > 0) { echo "<td style=\"background:#ff9; color:#030; width:50%;\">"; }
					else { echo "<td style=\"background:#0b0; color:#030; width:50%;\">"; }
				} else {
					echo "<td style=\"background:#b00; color:#500; width:50%;\">";
				}
			}
			else {
				if (isProcessRunning("MMDVMHost")) {
					if (checkDMRLogin("MMDVMHost") > 0) { echo "<td style=\"background:#ff9; color:#030; width:50%;\">"; }
					else { echo "<td style=\"background:#0b0; color:#030; width:50%;\">"; }
				} else {
					echo "<td style=\"background:#b00; color:#500; width:50%;\">";
				}
			}
		}
		else {
			if ($mode == "D-Star" || $mode == "DMR" || $mode == "System Fusion" || $mode == "P25" || $mode == "NXDN" || $mode == "M17" || $mode == "FM" || $mode == "POCSAG") {
				if (isProcessRunning("MMDVMHost")) {
					echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
				} else {
					echo "<td style=\"background:#b00; color:#500; width:50%;\">";
				}
			}
		}
	}
	elseif ( ($mode == "YSF XMode") && (getEnabled("System Fusion", $mmdvmconfigs) == 1) ) {
		if ( (isProcessRunning("MMDVMHost")) && (isProcessRunning("YSF2DMR") || isProcessRunning("YSF2NXDN") || isProcessRunning("YSF2P25")) ) {
			echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
		} else {
			echo "<td style=\"background:#606060; color:#b0b0b0;\" aria-disabled=\"true>\">";
		}
	}
	elseif ( ($mode == "DMR XMode") && (getEnabled("DMR", $mmdvmconfigs) == 1) ) {
		if ( (isProcessRunning("MMDVMHost")) && (isProcessRunning("DMR2YSF") || isProcessRunning("DMR2NXDN")) ) {
			echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
		} else {
			echo "<td style=\"background:#606060; color:#b0b0b0;\" aria-disabled=\"true>\">";
		}
	}
	elseif ( ($mode == "YSF2DMR Network") && (getEnabled("System Fusion", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("YSF2DMR")) {
			echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
		} else {
			echo "<td style=\"background:#606060; color:#b0b0b0;\" aria-disabled=\"true>\">";
		}
	}
	elseif ( ($mode == "YSF2NXDN Network") && (getEnabled("System Fusion", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("YSF2NXDN")) {
			echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
		} else {
			echo "<td style=\"background:#606060; color:#b0b0b0;\" aria-disabled=\"true>\">";
		}
	}
	elseif ( ($mode == "YSF2P25 Network") && (getEnabled("System Fusion", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("YSF2P25")) {
			echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
		} else {
			echo "<td style=\"background:#606060; color:#b0b0b0;\" aria-disabled=\"true>\">";
		}
	}
	elseif ( ($mode == "DMR2NXDN Network") && (getEnabled("DMR", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("DMR2NXDN")) {
			echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
		} else {
			echo "<td style=\"background:#606060; color:#b0b0b0;\" aria-disabled=\"true>\">";
		}
	}
	elseif ( ($mode == "DMR2YSF Network") && (getEnabled("DMR", $mmdvmconfigs) == 1) ) {
		if (isProcessRunning("DMR2YSF")) {
			echo "<td style=\"background:#0b0; color:#030; width:50%;\">";
		} else {
			echo "<td style=\"background:#606060; color:#b0b0b0;\" aria-disabled=\"true>\">";
		}
	}
	else {
		echo "<td style=\"background:#606060; color:#b0b0b0;\" aria-disabled=\"true>\">";
    }
    $mode = str_replace("System Fusion", "YSF", $mode);
    $mode = str_replace("Network", "Net", $mode);
    if (strpos($mode, 'YSF2') > -1) { $mode = str_replace(" Net", "", $mode); }
    if (strpos($mode, 'DMR2') > -1) { $mode = str_replace(" Net", "", $mode); }
    echo $mode."</td>\n";
}

function getMMDVMLog() {
	// Open Logfile and copy loglines into LogLines-Array()
	$logLines = array();
	$logLines1 = array();
	$logLines2 = array();
	if (file_exists(MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log")) {
		$logPath = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log";
		$logLines1 = explode("\n", `egrep -h "^M.*(from|end|watchdog|lost)" $logPath | sed '/\(CSBK\|overflow\|Downlink\)/d' | tail -250`);
	}
	$logLines1 = array_slice($logLines1, -250);
	if (sizeof($logLines1) < 250) {
		if (file_exists(MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log")) {
			$logPath = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
			$logLines2 = explode("\n", `egrep -h "^M.*(from|end|watchdog|lost)" $logPath | sed '/\(CSBK\|overflow\|Downlink\)/d' | tail -250`);
		}
	}
	$logLines2 = array_slice($logLines2, -250);
	$logLines = $logLines1 + $logLines2;
	$logLines = array_slice($logLines, -250);
	return $logLines;
}

function getYSFGatewayLog() {
	// Open Logfile and copy loglines into LogLines-Array()
	$logLines = array();
	$logLines1 = array();
	$logLines2 = array();
	if (file_exists(YSFGATEWAYLOGPATH."/".YSFGATEWAYLOGPREFIX."-".gmdate("Y-m-d").".log")) {
		$logPath1 = YSFGATEWAYLOGPATH."/".YSFGATEWAYLOGPREFIX."-".gmdate("Y-m-d").".log";
		$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(onnection to|onnect to|inked|isconnect|Opening YSF network)" $logPath1 | sed '/Linked to MMDVM/d' | sed '/Link successful to MMDVM/d' | sed '/*Link/d' | tail -1`);
	}
	$logLines1 = array_filter($logLines1);
	if (sizeof($logLines1) == 0) {
		if (file_exists(YSFGATEWAYLOGPATH."/".YSFGATEWAYLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log")) {
			$logPath2 = YSFGATEWAYLOGPATH."/".YSFGATEWAYLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
			$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(onnection to|onnect to|inked|isconnect|Opening YSF network)" $logPath2 | sed '/Linked to MMDVM/d' | sed '/Link successful to MMDVM/d' | sed '/*Link/d' | tail -1`);
		}
		$logLines2 = array_filter($logLines2);
	}
	if (sizeof($logLines1) == 0) { $logLines = $logLines2; } else { $logLines = $logLines1; }
        return array_filter($logLines);
}

function getP25GatewayLog() {
        // Open Logfile and copy loglines into LogLines-Array()
        $logLines = array();
	$logLines1 = array();
	$logLines2 = array();
        if (file_exists(P25GATEWAYLOGPATH."/".P25GATEWAYLOGPREFIX."-".gmdate("Y-m-d").".log")) {
		$logPath1 = P25GATEWAYLOGPATH."/".P25GATEWAYLOGPREFIX."-".gmdate("Y-m-d").".log";
		$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath1 | cut -d" " -f2- | tail -1`);
        }
	$logLines1 = array_filter($logLines1);
        if (sizeof($logLines1) == 0) {
                if (file_exists(P25GATEWAYLOGPATH."/".P25GATEWAYLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log")) {
                        $logPath2 = P25GATEWAYLOGPATH."/".P25GATEWAYLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
			$logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath2 | cut -d" " -f2- | tail -1`);
                }
		$logLines2 = array_filter($logLines2);
        }
	if (sizeof($logLines1) == 0) { $logLines = $logLines2; } else { $logLines = $logLines1; }
        return array_filter($logLines);
}

function getNXDNGatewayLog() {
        // Open Logfile and copy loglines into LogLines-Array()
        $logLines = array();
	$logLines1 = array();
	$logLines2 = array();
        if (file_exists("/var/log/pi-star/NXDNGateway-".gmdate("Y-m-d").".log")) {
		$logPath1 = "/var/log/pi-star/NXDNGateway-".gmdate("Y-m-d").".log";
		$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath1 | cut -d" " -f2- | tail -1`);
        }
	$logLines1 = array_filter($logLines1);
        if (sizeof($logLines1) == 0) {
                if (file_exists("/var/log/pi-star/NXDNGateway-".gmdate("Y-m-d", time() - 86340).".log")) {
			$logPath2 = "/var/log/pi-star/NXDNGateway-".gmdate("Y-m-d", time() - 86340).".log";
			$logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath2 | cut -d" " -f2- | tail -1`);
                }
		$logLines2 = array_filter($logLines2);
        }
	if (sizeof($logLines1) == 0) { $logLines = $logLines2; } else { $logLines = $logLines1; }
        return array_filter($logLines);
}

function getM17GatewayLog() {
        // Open Logfile and copy loglines into LogLines-Array()
        $logLines = array();
	$logLines1 = array();
	$logLines2 = array();
        if (file_exists("/var/log/pi-star/M17Gateway-".gmdate("Y-m-d").".log")) {
		$logPath1 = "/var/log/pi-star/M17Gateway-".gmdate("Y-m-d").".log";
		$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath1 | cut -d" " -f2- | tail -1`);
        }
	$logLines1 = array_filter($logLines1);
        if (sizeof($logLines1) == 0) {
                if (file_exists("/var/log/pi-star/M17Gateway-".gmdate("Y-m-d", time() - 86340).".log")) {
			$logPath2 = "/var/log/pi-star/M17Gateway-".gmdate("Y-m-d", time() - 86340).".log";
			$logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(ink|Starting|witched)" $logPath2 | cut -d" " -f2- | tail -1`);
                }
		$logLines2 = array_filter($logLines2);
        }
	if (sizeof($logLines1) == 0) { $logLines = $logLines2; } else { $logLines = $logLines1; }
        return array_filter($logLines);
}

function getDAPNETGatewayLog() {
        // Open Logfile and copy loglines into LogLines-Array()
        $logLines = array();
	$logLines1 = array();
	$logLines2 = array();
        if (file_exists("/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d").".log")) {
		$logPath1 = "/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d").".log";
		$logLines1 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(Sending message)" $logPath1 | cut -d" " -f2- | tail -n 20 | tac`);
        }
	$logLines1 = array_filter($logLines1);
        if (sizeof($logLines1) == 0) {
                if (file_exists("/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d", time() - 86340).".log")) {
			$logPath2 = "/var/log/pi-star/DAPNETGateway-".gmdate("Y-m-d", time() - 86340).".log";
			$logLines2 = preg_split('/\r\n|\r|\n/', `egrep -h "^M.*(Sending message)" $logPath2 | cut -d" " -f2- | tail -n 20 | tac`);
                }
		$logLines2 = array_filter($logLines2);
        }
	$logLines = $logLines1 + $logLines2;
	$logLines = array_slice($logLines, -20);
	return array_filter($logLines);
}

// 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
// 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: DVMEGA HR3.14
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_HS-ADF7021 20170414 (D-Star/DMR/YSF/P25) (Build: 20:16:25 May 20 2017)
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM 20170206 TCXO (D-Star/DMR/System Fusion/P25/RSSI/CW Id)
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: ZUMspot ADF7021 v1.0.0 20170728 (DStar/DMR/YSF/P25) GitID #c16dd5a
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_MDO ADF7021 v1.0.1 20170826 (DStar/DMR/YSF/P25) GitID #BD7KLE
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: ZUMspot-v1.0.3 20171226 ADF7021 FW by CA6JAU GitID #bfb82b4
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_HS_Hat-v1.0.3 20171226 ADF7021 FW by CA6JAU GitID #bfb82b4
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_HS-v1.0.3 20171226 ADF7021 FW by CA6JAU GitID #bfb82b4
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: MMDVM_HS_Dual_Hat-v1.3.6 20180521 dual ADF7021 FW by CA6JAU GitID #bd6217a
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: D2RG_MMDVM_HS-v1.4.17 20190529 14.7456MHz ADF7021 FW by CA6JAU GitID #cc451c4
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: Nano_hotSPOT-v1.3.3 20180224 ADF7021 FW by CA6JAU GitID #62323e7
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: Nano-Spot-v1.3.3 20180224 ADF7021 FW by CA6JAU GitID #62323e7
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: Nano_DV-v1.4.3 20180716 12.2880MHz ADF7021 FW by CA6JAU GitID #6729d23
// I: 1970-01-01 00:00:00.000 MMDVM protocol version: 1, description: SkyBridge-v1.5.2 20201108 14.7456MHz ADF7021 FW by CA6JAU GitID #89daa20

function getDVModemFirmware() {
	$logMMDVMNow = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log";
	$logMMDVMPrevious = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
	$logSearchString = "MMDVM protocol version";
	$logLine = '';
	$modemFirmware = '';

	$logLine = exec("grep \"".$logSearchString."\" ".$logMMDVMNow." | tail -1");
	if (!$logLine) { $logLine = exec("grep \"".$logSearchString."\" ".$logMMDVMPrevious." | tail -1"); }

	if ($logLine) {
		if (strpos($logLine, 'DVMEGA')) {
			$modemFirmware = substr($logLine, 67, 15);
		}
		if (strpos($logLine, 'description: MMDVM_HS')) {
			$modemFirmware = "MMDVM_HS:".ltrim(substr($logLine, 84, 8), 'v');
		}
		if (strpos($logLine, 'description: MMDVM ')) {
			$modemFirmware = "MMDVM:".substr($logLine, 73, 8);
		}
		if (strpos($logLine, 'description: ZUMspot ')) {
			$modemFirmware = "ZUMspot:".strtok(substr($logLine, 83, 12), ' ');
		}
		if (strpos($logLine, 'description: MMDVM_MDO ')) {
			$modemFirmware = "MMDVM_MDO:".ltrim(strtok(substr($logLine, 85, 12), ' '), 'v');
		}
		if (strpos($logLine, 'description: ZUMspot-')) {
			$modemFirmware = "ZUMspot:".strtok(substr($logLine, 75, 12), ' ');
		}
		if (strpos($logLine, 'description: MMDVM_HS_Hat-')) {
			$modemFirmware = "HS_Hat:".strtok(substr($logLine, 80, 12), ' ');
		}
		if (strpos($logLine, 'description: MMDVM_HS_Dual_Hat-')) {
			$modemFirmware = "HS_Hat:".strtok(substr($logLine, 85, 12), ' ');
		}
		if (strpos($logLine, 'description: D2RG_MMDVM_HS-')) {
			$modemFirmware = "HS_Hat:".strtok(substr($logLine, 81, 12), ' ');
		}
		if (strpos($logLine, 'description: MMDVM_HS-')) {
			$modemFirmware = "MMDVM_HS:".ltrim(strtok(substr($logLine, 76, 12), ' '), 'v');
		}
		if (strpos($logLine, 'description: Nano_hotSPOT-')) {
			$modemFirmware = "MMDVM_HS:".ltrim(strtok(substr($logLine, 80, 12), ' '), 'v');
		}
		if (strpos($logLine, 'description: Nano-Spot-')) {
			$modemFirmware = "NanoSpot:".strtok(substr($logLine, 77, 12), ' ');
		}
		if (strpos($logLine, 'description: Nano_DV-')) {
			$modemFirmware = "NanoDV:".strtok(substr($logLine, 75, 12), ' ');
		}
		if (strpos($logLine, 'description: OpenGD77 Hotspot')) {
			$modemFirmware = "OpenGD77:".strtok(substr($logLine, 83, 12), ' ');
		}
		if (strpos($logLine, 'description: OpenGD77_HS ')) {
			$modemFirmware = "OpenGD77:".strtok(substr($logLine, 79, 12), ' ');
		}
		if (strpos($logLine, 'description: SkyBridge-')) {
			$modemFirmware = "SkyBrg:".strtok(substr($logLine, 77, 12), ' ');
		}
	}
	return $modemFirmware;
}

function getDVModemTCXOFreq() {
	$logMMDVMNow = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d").".log";
	$logMMDVMPrevious = MMDVMLOGPATH."/".MMDVMLOGPREFIX."-".gmdate("Y-m-d", time() - 86340).".log";
	$logSearchString = "MMDVM protocol version";
	$logLine = '';
	$modemTCXOFreq = '';

	$logLine = exec("grep \"".$logSearchString."\" ".$logMMDVMNow." | tail -1");
	if (!$logLine) { $logLine = exec("grep \"".$logSearchString."\" ".$logMMDVMPrevious." | tail -1"); }

	if ($logLine) {
		if ((strpos($logLine, 'Mhz') !== false) or (strpos($logLine, 'MHz') !== false)) {
			$modemTCXOFreq = $logLine;
			$modemTCXOFreq = preg_replace('/.*(\d{2}\.\d{3,4}\s{0,1}M[Hh]z).*/', "$1", $modemTCXOFreq);
			$modemTCXOFreq = str_replace("MHz"," MHz", $modemTCXOFreq);
		}
	}
	return $modemTCXOFreq;
}

// 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
// 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
// M: 2000-00-00 00:00:00.000 D-Star, received network header from M1ABC   /ABCD to CQCQCQ   via REF000 A
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received network voice header from M1ABC to TG 1
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received RF voice header from M1ABC to 5000
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received RF end of voice transmission, 1.8 seconds, BER: 3.9%
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received network end of voice transmission from M1ABC to TG 2, 0.0 seconds, 0% packet loss, BER: 0.0%
// M: 2000-00-00 00:00:00.000 DMR Slot 2, RF voice transmission lost, 1.1 seconds, BER: 6.5%
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received RF CSBK Preamble CSBK (1 to follow) from M1ABC to TG 1
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received network Data Preamble VSBK (11 to follow) from 123456 to TG 123456
// M: 2000-00-00 00:00:00.000 DMR Talker Alias (Data Format 1, Received 24/24 char): 'Hide the bottle from Ont'
// M: 2000-00-00 00:00:00.000 0000:  07 00 20 4F 6E 74 00 00 00                         *.. Ont...*
// M: 2000-00-00 00:00:00.000 DMR Slot 2, Embedded Talker Alias Block 3
// M: 2000-00-00 00:00:00.000 DMR Slot 2, received network data header from 123999 to M1ABC, 0 blocks
// M: 2000-00-00 00:00:00.000 DMR Slot 2, ended network data transmission from 123999 to M1ABC
// M: 2000-00-00 00:00:00.000 P25, received RF transmission from M1ABC to TG 10200
// M: 2000-00-00 00:00:00.000 Debug: P25RX: pos/neg/centre/threshold 106 -105 0 106
// M: 2000-00-00 00:00:00.000 Debug: P25RX: sync found in Ldu pos/centre/threshold 3986 9 104
// M: 2000-00-00 00:00:00.000 Debug: P25RX: pos/neg/centre/threshold 267 -222 22 245
// M: 2000-00-00 00:00:00.000 Debug: P25RX: sync found in Ldu pos/centre/threshold 3986 10 112
// M: 2000-00-00 00:00:00.000 P25, received RF end of transmission, 0.4 seconds, BER: 0.0%
// M: 2000-00-00 00:00:00.000 P25, received network transmission from 10999 to TG 10200
// M: 2000-00-00 00:00:00.000 P25, network end of transmission, 1.8 seconds, 0% packet loss
// M: 2000-00-00 00:00:00.000 YSF, received RF data from MW0MWZ     to ALL
// M: 2000-00-00 00:00:00.000 YSF, received RF end of transmission, 5.1 seconds, BER: 3.8%
// M: 2000-00-00 00:00:00.000 YSF, received network data from M1ABC      to ALL        at MB6IBK
// M: 2000-00-00 00:00:00.000 YSF, network watchdog has expired, 5.0 seconds, 0% packet loss, BER: 0.0%
// M: 2000-00-00 00:00:00.000 NXDN, received RF transmission from MW0MWZ to TG 65000
// M: 2000-00-00 00:00:00.000 Debug: NXDNRX: pos/neg/centre/threshold 106 -105 0 106
// M: 2000-00-00 00:00:00.000 Debug: NXDNRX: sync found in Ldu pos/centre/threshold 3986 9 104
// M: 2000-00-00 00:00:00.000 Debug: NXDNRX: pos/neg/centre/threshold 267 -222 22 245
// M: 2000-00-00 00:00:00.000 Debug: NXDNRX: sync found in Ldu pos/centre/threshold 3986 10 112
// M: 2000-00-00 00:00:00.000 NXDN, received RF end of transmission, 0.4 seconds, BER: 0.0%
// M: 2000-00-00 00:00:00.000 NXDN, received network transmission from 10999 to TG 65000
// M: 2000-00-00 00:00:00.000 NXDN, network end of transmission, 1.8 seconds, 0% packet loss
// M: 2000-00-00 00:00:00.000 M17, received RF late entry voice transmission from M1ABC to INFO
// M: 2000-00-00 00:00:00.000 M17, received RF end of transmission from M1ABC to INFO, 2.1 seconds, BER: 0.2%, RSSI: -60/-60/-60 dBm
// M: 2000-00-00 00:00:00.000 M17, received network voice transmission from M1ABC to ECHO
// M: 2000-00-00 00:00:00.000 M17, received network end of transmission from M1ABC to ECHO, 0.0 seconds
// M: 2000-00-00 00:00:00.000 POCSAG, transmitted 1 frame(s) of data from 1 message(s)
function getHeardList($logLines) {
	//array_multisort($logLines,SORT_DESC);
	$heardList = array();
	$ts1duration	= "";
	$ts1loss	= "";
	$ts1ber		= "";
	$ts1rssi	= "";
	$ts2duration	= "";
	$ts2loss	= "";
	$ts2ber		= "";
	$ts2rssi	= "";
	$dstarduration	= "";
	$dstarloss	= "";
	$dstarber	= "";
	$dstarrssi	= "";
	$ysfduration	= "";
        $ysfloss	= "";
        $ysfber		= "";
	$ysfrssi	= "";
	$p25duration	= "";
        $p25loss	= "";
        $p25ber		= "";
	$p25rssi	= "";
	$nxdnduration	= "";
        $nxdnloss	= "";
        $nxdnber	= "";
	$nxdnrssi	= "";
	$pocsagduration	= "";
	foreach ($logLines as $logLine) {
		$duration	= "";
		$loss		= "";
		$ber		= "";
		$rssi		= "";
		//removing invalid lines
		if(strpos($logLine,"BS_Dwn_Act")) {
			continue;
		} else if(strpos($logLine,"invalid access")) {
			continue;
		} else if(strpos($logLine,"received RF header for wrong repeater")) {
			continue;
		} else if(strpos($logLine,"unable to decode the network CSBK")) {
			continue;
		} else if(strpos($logLine,"overflow in the DMR slot RF queue")) {
			continue;
		} else if(strpos($logLine,"non repeater RF header received")) {
			continue;
		} else if(strpos($logLine,"Embedded Talker Alias")) {
                        continue;
		} else if(strpos($logLine,"DMR Talker Alias")) {
			continue;
		} else if(strpos($logLine,"CSBK Preamble")) {
                        continue;
		} else if(strpos($logLine,"Preamble CSBK")) {
                        continue;
		}

		if(strpos($logLine, "end of") || strpos($logLine, "watchdog has expired") || strpos($logLine, "ended RF data") || strpos($logLine, "d network data") || strpos($logLine, "RF user has timed out") || strpos($logLine, "transmission lost") || strpos($logLine, "POCSAG")) {
			$lineTokens = explode(", ",$logLine);
			if (array_key_exists(2,$lineTokens)) {
				$duration = strtok($lineTokens[2], " ");
			}
			if (array_key_exists(3,$lineTokens)) {
				$loss = $lineTokens[3];
			}
			// The change to this code was causing all FCS traffic to always show TOut rather than the timer.
			// This version should still show time-out when needed, AND show the time if it exists.
			if (strpos($logLine,"RF user has timed out") || strpos($logLine,"watchdog has expired")) {
				if (array_key_exists(2,$lineTokens) && strpos($lineTokens[2], "seconds")) {
					$duration = strtok($lineTokens[2], " ");
				} else {
					$duration = "TOut";
				}
				$ber = "??%";
			}

			// if RF-Packet with no BER reported (e.g. YSF Wires-X commands) then RSSI is in LOSS position
			if (startsWith($loss,"RSSI")) {
				$lineTokens[4] = $loss; //move RSSI to the position expected on code below
				$loss = 'BER: ??%';
			}

			// if RF-Packet, no LOSS would be reported, so BER is in LOSS position
			if (startsWith($loss,"BER")) {
				$ber = substr($loss, 5);
				$loss = "0%";
				if (array_key_exists(4,$lineTokens) && startsWith($lineTokens[4],"RSSI")) {
					$rssi = substr($lineTokens[4], 6);
					$dBraw = substr($rssi, strrpos($rssi,'/')+1); //average only
					$relint = intval($dBraw) + 93;
					$signal = round(($relint/6)+9, 0);
					if ($signal < 0) $signal = 0;
					if ($signal > 9) $signal = 9;
					if ($relint > 0) {
						$rssi = "S{$signal}+{$relint}dB ({$dBraw})";
					} else {
						$rssi = "S{$signal} ({$dBraw})";
					}
				}
			} else {
				$loss = strtok($loss, " ");
				if (array_key_exists(4,$lineTokens)) {
					$ber = substr($lineTokens[4], 5);
				}
			}

			if (strpos($logLine,"ended RF data") || strpos($logLine,"d network data")) {
				switch (substr($logLine, 27, strpos($logLine,",") - 27)) {
					case "DMR Slot 1":
						$ts1duration = "DMR Data";
						break;
					case "DMR Slot 2":
						$ts2duration = "DMR Data";
						break;
				}
			} else {
				switch (substr($logLine, 27, strpos($logLine,",") - 27)) {
					case "D-Star":
						$dstarduration	= $duration;
						$dstarloss	= $loss;
						$dstarber	= $ber;
						$dstarrssi	= $rssi;
						break;
					case "DMR Slot 1":
						$ts1duration	= $duration;
						$ts1loss	= $loss;
						$ts1ber		= $ber;
						$ts1rssi	= $rssi;
						break;
					case "DMR Slot 2":
						$ts2duration	= $duration;
						$ts2loss	= $loss;
						$ts2ber		= $ber;
						$ts2rssi	= $rssi;
						break;
					case "YSF":
						$ysfduration	= $duration;
						$ysfloss	= $loss;
						$ysfber		= $ber;
						$ysfrssi	= $rssi;
						break;
					case "P25":
						$p25duration	= $duration;
						$p25loss	= $loss;
						$p25ber		= $ber;
						$p25rssi	= $rssi;
						break;
					case "NXDN":
						$nxdnduration	= $duration;
						$nxdnloss	= $loss;
						$nxdnber	= $ber;
						$nxdnrssi	= $rssi;
						break;
					case "M17":
						$m17duration	= $duration;
						$m17loss	= $loss;
						$m17ber		= $ber;
						$m17rssi	= $rssi;
						break;
					case "POCSAG":
						$pocsagduration	= "POCSAG Data";
						break;
				}
			}
		}

		$timestamp = substr($logLine, 3, 19);
		$mode = substr($logLine, 27, strpos($logLine,",") - 27);
		$callsign2 = substr($logLine, strpos($logLine,"from") + 5, strpos($logLine,"to") - strpos($logLine,"from") - 6);
		$callsign = $callsign2;
		if (strpos($callsign2,"/") > 0) {
			$callsign = substr($callsign2, 0, strpos($callsign2,"/"));
		}
		$callsign = trim($callsign);

		$id ="";
		if ($mode == "D-Star") {
			$id = substr($callsign2, strpos($callsign2,"/") + 1);
		}

		$target = trim(substr($logLine, strpos($logLine, "to") + 3));
		// Handle more verbose logging from MMDVMHost
                if (strpos($target,",") !== 'false') { $target = explode(",", $target)[0]; }
		
		$source = "RF";
		if (strpos($logLine,"network") > 0 || strpos($logLine,"POCSAG") > 0) {
			$source = "Net";
		}

		switch ($mode) {
			case "D-Star":
				$duration	= $dstarduration;
				$loss		= $dstarloss;
				$ber		= $dstarber;
				$rssi		= $dstarrssi;
				break;
			case "DMR Slot 1":
				$duration	= $ts1duration;
				$loss		= $ts1loss;
				$ber		= $ts1ber;
				$rssi		= $ts1rssi;
				break;
			case "DMR Slot 2":
				$duration	= $ts2duration;
				$loss		= $ts2loss;
				$ber		= $ts2ber;
				$rssi		= $ts2rssi;
				break;
			case "YSF":
                		$duration	= $ysfduration;
                		$loss		= $ysfloss;
                		$ber		= $ysfber;
				$rssi		= $ysfrssi;
				$target		= preg_replace('!\s+!', ' ', $target);
                		break;
			case "P25":
				if ($source == "Net" && $target == "TG 10") {$callsign = "PARROT";}
				if ($source == "Net" && $callsign == "10999") {$callsign = "MMDVM";}
                		$duration	= $p25duration;
                		$loss		= $p25loss;
                		$ber		= $p25ber;
				$rssi		= $p25rssi;
                		break;
			case "NXDN":
				if ($source == "Net" && $target == "TG 10") {$callsign = "PARROT";}
                		$duration	= $nxdnduration;
                		$loss		= $nxdnloss;
                		$ber		= $nxdnber;
				$rssi		= $nxdnrssi;
                		break;
			case "M17":
				$duration	= $m17duration;
				$loss		= $m17loss;
				$ber		= $m17ber;
				$rssi		= $m17rssi;
				break;
			case "POCSAG":
				$callsign	= "DAPNET";
				$target		= "DAPNET User";
				$duration	= "POCSAG Data";
				break;
		}

		// Callsign or ID should be less than 11 chars long, otherwise it could be errorneous
		if ( strlen($callsign) < 11 ) {
			array_push($heardList, array($timestamp, $mode, $callsign, $id, $target, $source, $duration, $loss, $ber, $rssi));
			$duration = "";
			$loss ="";
			$ber = "";
			$rssi = "";
		}
	}
	return $heardList;
}

function getLastHeard($logLines) {
	//returns last heard list from log
	$lastHeard = array();
	$heardCalls = array();
	$heardList = getHeardList($logLines);
	$counter = 0;
	foreach ($heardList as $listElem) {
		if ( ($listElem[1] == "D-Star") || ($listElem[1] == "YSF") || ($listElem[1] == "P25") || ($listElem[1] == "NXDN") || ($listElem[1] == "M17") || ($listElem[1] == "POCSAG") || (startsWith($listElem[1], "DMR")) ) {
			$callUuid = $listElem[2]."#".$listElem[1].$listElem[3].$listElem[5];
			if(!(array_search($callUuid, $heardCalls) > -1)) {
				array_push($heardCalls, $callUuid);
				array_push($lastHeard, $listElem);
				$counter++;
			}
		}
	}
	return $lastHeard;
}

function getActualMode($metaLastHeard, $mmdvmconfigs) {
	// returns mode of repeater actual working in
        $utc_tz =  new DateTimeZone('UTC');
        $local_tz = new DateTimeZone(date_default_timezone_get ());
        $listElem = $metaLastHeard[0];
        $timestamp = new DateTime($listElem[0], $utc_tz);
        $timestamp->setTimeZone($local_tz); 
        $mode = $listElem[1];
	if (startsWith($mode, "DMR")) {
		$mode = "DMR";
	}

	$now =  new DateTime();
	$hangtime = getConfigItem("General", "ModeHang", $mmdvmconfigs);

	if ($hangtime != "") {
		$timestamp->add(new DateInterval('PT' . $hangtime . 'S'));
	} else {
		$source = $listElem[5];
		if ($source == "RF" && $mode === "D-Star") {
			$hangtime = getConfigItem("D-Star", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "Net" && $mode === "D-Star") {
			$hangtime = getConfigItem("D-Star Network", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "RF" && $mode === "DMR") {
			$hangtime = getConfigItem("DMR", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "Net" && $mode === "DMR") {
			$hangtime = getConfigItem("DMR Network", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "RF" && $mode === "YSF") {
			$hangtime = getConfigItem("System Fusion", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "Net" && $mode === "YSF") {
			$hangtime = getConfigItem("System Fusion Network", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "RF" && $mode === "P25") {
			$hangtime = getConfigItem("P25", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "Net" && $mode === "P25") {
			$hangtime = getConfigItem("P25 Network", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "RF" && $mode === "NXDN") {
			$hangtime = getConfigItem("NXDN", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "Net" && $mode === "NXDN") {
			$hangtime = getConfigItem("NXDN Network", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "RF" && $mode === "M17") {
			$hangtime = getConfigItem("M17", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "Net" && $mode === "M17") {
			$hangtime = getConfigItem("M17 Network", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "RF" && $mode === "FM") {
			$hangtime = getConfigItem("FM", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "Net" && $mode === "FM") {
			$hangtime = getConfigItem("FM Network", "ModeHang", $mmdvmconfigs);
		}
		else if ($source == "Net" && $mode === "POCSAG") {
			$hangtime = getConfigItem("POCSAG Network", "ModeHang", $mmdvmconfigs);
		}
		else {
			$hangtime = getConfigItem("General", "RFModeHang", $mmdvmconfigs);
		}
		$timestamp->add(new DateInterval('PT' . $hangtime . 'S'));
	}
	if ($listElem[6] != null) { //if terminated, hangtime counts after end of transmission
		$timestamp->add(new DateInterval('PT' . ceil($listElem[6]) . 'S'));
	} else { //if not terminated, always return mode
		return $mode;
	}
	if ($now->format('U') > $timestamp->format('U')) {
		return "idle";
	} else {
		return $mode;
	}
}

function getDSTARLinks() {
	// returns link-states of all D-Star-modules
	if (filesize(LINKLOGPATH."/Links.log") == 0) {
		return "Not Linked";
	}
	if ($linkLog = fopen(LINKLOGPATH."/Links.log",'r')) {
		while ($linkLine = fgets($linkLog)) {
			$linkDate	= "&nbsp;";
			$protocol	= "&nbsp;";
			$linkType	= "&nbsp;";
			$linkSource	= "&nbsp;";
			$linkDest	= "&nbsp;";
			$linkDir	= "&nbsp;";
// Reflector-Link, sample:
// 2011-09-22 02:15:06: DExtra link - Type: Repeater Rptr: DB0LJ	B Refl: XRF023 A Dir: Outgoing
// 2012-04-03 08:40:07: DPlus link - Type: Dongle Rptr: DB0ERK B Refl: REF006 D Dir: Outgoing
// 2012-04-03 08:40:07: DCS link - Type: Repeater Rptr: DB0ERK C Refl: DCS001 C Dir: Outgoing
			if(preg_match_all('/^(.{19}).*(D[A-Za-z]*).*Type: ([A-Za-z]*).*Rptr: (.{8}).*Refl: (.{8}).*Dir: (.{8})/',$linkLine,$linx) > 0){
				$linkDate	= $linx[1][0];
				$protocol	= $linx[2][0];
				$linkType	= $linx[3][0];
				$linkSource	= $linx[4][0];
				$linkDest	= $linx[5][0];
				$linkDir	= $linx[6][0];
			}
// CCS-Link, sample:
// 2013-03-30 23:21:53: CCS link - Rptr: PE1AGO C Remote: PE1KZU	Dir: Incoming
			if(preg_match_all('/^(.{19}).*(CC[A-Za-z]*).*Rptr: (.{8}).*Remote: (.{8}).*Dir: (.{8})/',$linkLine,$linx) > 0){
				$linkDate	= $linx[1][0];
				$protocol	= $linx[2][0];
				$linkType	= $linx[2][0];
				$linkSource	= $linx[3][0];
				$linkDest	= $linx[4][0];
				$linkDir	= $linx[5][0];
			}
// Dongle-Link, sample: 
// 2011-09-24 07:26:59: DPlus link - Type: Dongle User: DC1PIA	Dir: Incoming
// 2012-03-14 21:32:18: DPlus link - Type: Dongle User: DC1PIA Dir: Incoming
			if(preg_match_all('/^(.{19}).*(D[A-Za-z]*).*Type: ([A-Za-z]*).*User: (.{6,8}).*Dir: (.*)$/',$linkLine,$linx) > 0){
				$linkDate	= $linx[1][0];
				$protocol	= $linx[2][0];
				$linkType	= $linx[3][0];
				$linkSource	= "&nbsp;";
				$linkDest	= $linx[4][0];
				$linkDir	= $linx[5][0];
			}
			if (strtolower(substr($linkDir, 0, 2)) == "in") { $linkDir = "In"; }
			if (strtolower(substr($linkDir, 0, 3)) == "out") { $linkDir = "Out"; }
			$out = $linkDest." ".$protocol."/".$linkDir;
		}
	}
	fclose($linkLog);
	return $out;
}

function getActualLink($logLines, $mode) {
	// returns actual link state of specific mode
	//M: 2016-05-02 07:04:10.504 D-Star link status set to "Verlinkt zu DCS002 S"
	//M: 2016-04-03 16:16:18.638 DMR Slot 2, received network voice header from 4000 to 2625094
	//M: 2016-04-03 19:30:03.099 DMR Slot 2, received network voice header from 4020 to 2625094
	//M: 2017-09-03 08:10:42.862 DMR Slot 2, received network data header from M6JQD to TG 9, 5 blocks
	switch ($mode) {
    case "D-Star":
    	if (isProcessRunning(IRCDDBGATEWAY)) {
			return getDSTARLinks();
    	} else {
    		return "No D-Star Network";
    	}
        break;
    case "DMR Slot 1":
        foreach ($logLines as $logLine) {
        	if(strpos($logLine,"unable to decode the network CSBK")) {
				continue;
			} else if(substr($logLine, 27, strpos($logLine,",") - 27) == "DMR Slot 1") {
				$to = "";
				if (strpos($logLine,"to")) {
					$to = trim(substr($logLine, strpos($logLine,"to") + 3));
				}
				if ($to !== "") {
					if (substr($to, 0, 3) !== 'TG ') {
						continue;
					}
					if ($to === "TG 4000") {
						return "No TG";
					}
					if (strpos($to, ',') !== false) {
						$to = substr($to, 0, strpos($to, ','));
					}
					return $to;
				}
	        	}
		}
		return "No TG";
        break;
    case "DMR Slot 2":
        foreach ($logLines as $logLine) {
        	if(strpos($logLine,"unable to decode the network CSBK")) {
				continue;
			} else if(substr($logLine, 27, strpos($logLine,",") - 27) == "DMR Slot 2") {
				$to = "";
				if (strpos($logLine,"to")) {
					$to = trim(substr($logLine, strpos($logLine,"to") + 3));
				}
				if ($to !== "") {
					if (substr($to, 0, 3) !== 'TG ') {
						continue;
					}
					if ($to === "TG 4000") {
						return "No TG";
					}
					if (strpos($to, ',') !== false) {
						$to = substr($to, 0, strpos($to, ','));
					}
					return $to;
				}
        		}
		}
		return "No TG";
        break;

    case "YSF":
	// 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
	// 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
	// M: 0000-00-00 00:00:00.000 Connect to 62829 has been requested by M1ABC
	// M: 0000-00-00 00:00:00.000 Automatic connection to 62829
	// New YSFGateway Format
	// M: 0000-00-00 00:00:00.000 Opening YSF network connection
	// M: 0000-00-00 00:00:00.000 Automatic (re-)connection to 16710 - "GB SOUTH WEST   "
	// M: 0000-00-00 00:00:00.000 Automatic (re-)connection to FCS00290
	// M: 0000-00-00 00:00:00.000 Linked to GB SOUTH WEST   
	// M: 0000-00-00 00:00:00.000 Linked to FCS002-90
	// M: 0000-00-00 00:00:00.000 Disconnect via DTMF has been requested by M1ABC
	// M: 0000-00-00 00:00:00.000 Connect to 00003 - "YSF2NXDN        " has been requested by M1ABC
	// M: 0000-00-00 00:00:00.000 Link has failed, polls lost

         if (isProcessRunning("YSFGateway")) {
            $to = "";
            foreach($logLines as $logLine) {
               if ( (strpos($logLine,"Linked to")) && (!strpos($logLine,"Linked to MMDVM")) ) {
                  $to = trim(substr($logLine, 37, 16));
		  if (substr($to, 0, 3) === "FCS") { $to = str_replace(' ', '', str_replace('-', '', $to)); }
               }
               if (strpos($logLine,"Automatic (re-)connection to")) {
		  if (strpos($logLine,"Automatic (re-)connection to FCS")) {
			$to = substr($logLine, 56, 8);
		  }
		  else {
                  	$to = substr($logLine, 56, 5);
		  }
               }
               if (strpos($logLine,"Connect to")) {
                  $to = substr($logLine, 38, 5);
               }
               if (strpos($logLine,"Automatic connection to")) {
                  $to = substr($logLine, 51, 5);
               }
               if (strpos($logLine,"Disconnect via DTMF")) {
                  $to = "Not Linked";
               }
               if (strpos($logLine,"Opening YSF network connection")) {
                  $to = "Not Linked";
               }
	       if (strpos($logLine,"Link has failed")) {
                  $to = "Not Linked";
               }
               if (strpos($logLine,"DISCONNECT Reply")) {
                  $to = "Not Linked";
               }
               if ($to !== "") {
                  return $to;
               }
            }
            return "Not Linked";
         } else {
            return "Service Not Started";
         }
         break;

     case "NXDN":
        // 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
        // 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
        // 2000-01-01 00:00:00.000 Linked at startup to reflector 10100
        // 2000-01-01 00:00:00.000 Unlinked from reflector 10100 by M1ABC
        // 2000-01-01 00:00:00.000 Linked to reflector 10100 by M1ABC
        // 2000-01-01 00:00:00.000 No response from 10200, unlinking
	// 2000-01-01 00:00:00.000 Switched to reflector 10100 by remote command
	// 2000-01-01 00:00:00.000 Unlinking from 10100 due to inactivity
	// 2000-01-01 00:00:00.000 Statically linked to reflector 10100
        if (isProcessRunning("NXDNGateway")) {
            foreach($logLines as $logLine) {
               $to = "";
               if (strpos($logLine,"Linked to")) {
                  $to = preg_replace('/[^0-9]/', '', substr($logLine, 44, 5));
                  $to = preg_replace('/[^0-9]/', '', $to);
                  return "TG ".$to;
               }
               if (strpos($logLine,"Linked at start")) {
                  $to = preg_replace('/[^0-9]/', '', substr($logLine, 55, 5));
                  $to = preg_replace('/[^0-9]/', '', $to);
                  return "TG ".$to;
               }
	       if (strpos($logLine,"Statically linked to reflector")) {
                  $to = preg_replace('/[^0-9]/', '', substr($logLine, 55, 5));
                  $to = preg_replace('/[^0-9]/', '', $to);
                  return "TG ".$to;
               }
	       if (strpos($logLine,"Switched to reflector")) {
                  $to = preg_replace('/[^0-9]/', '', substr($logLine, 46, 5));
                  $to = preg_replace('/[^0-9]/', '', $to);
                  return "TG ".$to;
               }
	       if (strpos($logLine,"Starting NXDNGateway")) {
                  return "Not Linked";
               }
               if (strpos($logLine,"unlinking")) {
                  return "Not Linked";
               }
               if (strpos($logLine,"Unlinked from")) {
                  return "Not Linked";
               }
	       if (strpos($logLine,"Unlinking from")) {
                  return "Not Linked";
               }
            }
	    return "Not Linked";
        } else {
            return "Service Not Started";
        }
        break;

    case "M17":
	// 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
	// 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
	if (isProcessRunning("M17Gateway")) {
	    foreach($logLines as $logLine) {
	        if(preg_match_all('/Linked .* reflector (M17-.{3} [A-Z])/',$logLine,$linx) > 0){
	            return $linx[1][0];
	        }
	        if (strpos($logLine,"Starting M17Gateway")) {
	            return "Not Linked";
	        }
	        if (strpos($logLine,"unlinking")) {
	            return "Not Linked";
	        }
	        if (strpos($logLine,"Unlinking")) {
	            return "Not Linked";
	        }
	        if (strpos($logLine,"Unlinked")) {
	            return "Not Linked";
	        }
	    }
	    return "Not Linked";
	} else {
            return "Service Not Started";
        }
	break;

    case "P25":
	// 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
	// 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
	// 2000-01-01 00:00:00.000 Linked at startup to reflector 10100
	// 2000-01-01 00:00:00.000 Unlinked from reflector 10100 by M1ABC
	// 2000-01-01 00:00:00.000 Linked to reflector 10100 by M1ABC
	// 2000-01-01 00:00:00.000 No response from 10100, unlinking
	// 2000-01-01 00:00:00.000 Switched to reflector 10100 due to RF activity from 12345
	// 2000-01-01 00:00:00.000 Unlinking from reflector 10100 by 12345
	// 2000-01-01 00:00:00.000 Switched to reflector 10100 by remote command
	if (isProcessRunning("P25Gateway")) {
	    foreach($logLines as $logLine) {
               $to = "";
               if (strpos($logLine,"Linked to")) {
		  $to = preg_replace('/[^0-9]/', '', substr($logLine, 44, 5));
		  $to = preg_replace('/[^0-9]/', '', $to);
		  return "TG ".$to;
               }
               if (strpos($logLine,"Linked at startup to")) {
		  $to = preg_replace('/[^0-9]/', '', substr($logLine, 55, 5));
		  $to = preg_replace('/[^0-9]/', '', $to);
		  return "TG ".$to;
               }
               if (strpos($logLine,"Statically linked to reflector")) {
                  $to = preg_replace('/[^0-9]/', '', substr($logLine, 55, 5));
                  $to = preg_replace('/[^0-9]/', '', $to);
                  return "TG ".$to;
               }
	       if (strpos($logLine,"Switched to reflector")) {
		  $to = preg_replace('/[^0-9]/', '', substr($logLine, 46, 5));
		  $to = preg_replace('/[^0-9]/', '', $to);
		  return "TG ".$to;
               }
	       if (strpos($logLine,"Starting P25Gateway")) {
                  return "Not Linked";
               }
	       if (strpos($logLine,"unlinking")) {
                  return "Not Linked";
               }
	       if (strpos($logLine,"Unlinking")) {
                  return "Not Linked";
               }
               if (strpos($logLine,"Unlinked")) {
                  return "Not Linked";
               }
	    }
	    return "Not Linked";
	} else {
            return "Service Not Started";
        }
	break;
	}
	return "Service Not Started";
}

function getActualReflector($logLines, $mode) {
	// 00000000001111111111222222222233333333334444444444555555555566666666667777777777888888888899999999990000000000111111111122
	// 01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901
	// M: 2016-05-02 07:04:10.504 D-Star link status set to "Verlinkt zu DCS002 S"
	// M: 2016-04-03 16:16:18.638 DMR Slot 2, received network voice header from 4000 to 2625094
	// M: 2016-04-03 19:30:03.099 DMR Slot 2, received network voice header from 4020 to 2625094
	foreach ($logLines as $logLine) {
		if (substr($logLine, 27, strpos($logLine,",") - 27) == $mode) {
			$from = substr($logLine, strpos($logLine,"from") + 5, strpos($logLine,"to") - strpos($logLine,"from") - 6);
			if (strlen($from) == 4 && startsWith($from,"4")) {
				if ($from == "4000") {
					return "No Ref";
				} else {
					return "Ref ".$from;
				}
			}
		}
	}
	return "No Ref";
}

//Some basic inits
$mmdvmconfigs = getMMDVMConfig();
if (!in_array($_SERVER["PHP_SELF"],array('/mmdvmhost/bm_links.php','/mmdvmhost/bm_manager.php'),true)) {
	$logLinesMMDVM = getMMDVMLog();
	$reverseLogLinesMMDVM = $logLinesMMDVM;
	array_multisort($reverseLogLinesMMDVM,SORT_DESC);
	$lastHeard = getLastHeard($reverseLogLinesMMDVM);

	// Only need these in repeaterinfo.php
	if (strpos($_SERVER["PHP_SELF"], 'repeaterinfo.php') !== false || strpos($_SERVER["PHP_SELF"], 'index.php') !== false) {
		$logLinesYSFGateway = getYSFGatewayLog();
		$reverseLogLinesYSFGateway = $logLinesYSFGateway;
		array_multisort($reverseLogLinesYSFGateway,SORT_DESC);
		$logLinesP25Gateway = getP25GatewayLog();
		$logLinesNXDNGateway = getNXDNGatewayLog();
		$logLinesM17Gateway = getM17GatewayLog();
	}
	// Only need these in index.php
	if (strpos($_SERVER["PHP_SELF"], 'index.php') !== false || strpos($_SERVER["PHP_SELF"], 'pages.php') !== false) {
		$logLinesDAPNETGateway = getDAPNETGatewayLog();
	}
}
?>
