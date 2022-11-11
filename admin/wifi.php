<?php
include('wifi/phpincs.php');
$output = $return = 0;
$page = $_GET['page'];


echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="Author" content="Andrew Taylor (MW0MWZ)" />
<meta name="Description" content="Pi-Star Configuration" />
<meta name="KeyWords" content="Pi-Star, MW0MWZ" />
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="pragma" content="no-cache" />
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<meta http-equiv="Expires" content="0" />
<link rel="stylesheet" type="text/css" href="css/pistar-css.php" />
<link rel="stylesheet" type="text/css" href="wifi/styles.php" />
<script type="text/Javascript" src="wifi/functions.js?version=1.6"></script>
<title>Pi-Star - Digital Voice Dashboard - WiFi Config</title>
</head>
<body>'."\n";
switch($page) {
	case "wlan0_info":
		//Declare a pile of variables
		$strIPAddress = NULL;
		$strNetMask = NULL;
		$strRxPackets = NULL;
		$strRxBytes = NULL;
		$strTxPackets = NULL;
		$strTxBytes = NULL;
		$strSSID = NULL;
		$strBSSID = NULL;
		$strBitrate = NULL;
		$strTxPower = NULL;
		$strLinkQuality = NULL;
		$strSignalLevel = NULL;
		$strWifiFreq = NULL;
		$strWifiChan = NULL;

		exec('ifconfig wlan0',$return);
		exec('iwconfig wlan0',$return);
		exec('iw dev wlan0 link',$return);
		$strWlan0 = implode(" ",$return);
		$strWlan0 = preg_replace('/\s\s+/', ' ', $strWlan0);
		if (strpos($strWlan0,'HWaddr') !== false) {
			preg_match('/HWaddr ([0-9a-f:]+)/i',$strWlan0,$result);
			$strHWAddress = $result[1];
		}
		if (strpos($strWlan0,'ether') !== false) {
			preg_match('/ether ([0-9a-f:]+)/i',$strWlan0,$result);
			$strHWAddress = $result[1];
		}
		if(strpos($strWlan0, "UP") !== false && strpos($strWlan0, "RUNNING") !== false) {
			$strStatus = '<span style="color:green">Interface is up</span>';
				//Cant get these unless we are connected :)
				if (strpos($strWlan0,'inet addr:') !== false) {
					preg_match('/inet addr:([0-9.]+)/i',$strWlan0,$result);
					$strIPAddress = $result[1];
				} else {
					preg_match('/inet ([0-9.]+)/i',$strWlan0,$result);
					$strIPAddress = $result[1];
				}
				if (strpos($strWlan0,'Mask:') !== false) {
					preg_match('/Mask:([0-9.]+)/i',$strWlan0,$result);
					$strNetMask = $result[1];
				} else {
					preg_match('/netmask ([0-9.]+)/i',$strWlan0,$result);
					$strNetMask = $result[1];
				}
				preg_match('/RX packets.(\d+)/',$strWlan0,$result);
				$strRxPackets = $result[1];
				preg_match('/TX packets.(\d+)/',$strWlan0,$result);
				$strTxPackets = $result[1];
				if (strpos($strWlan0,'RX bytes') !== false) {
					preg_match('/RX [B|b]ytes:(\d+ \(\d+.\d+ [K|M|G]iB\))/i',$strWlan0,$result);
					$strRxBytes = $result[1];
				} else {
					preg_match('/RX packets \d+ bytes (\d+ \(\d+.\d+ [K|M|G]iB\))/i',$strWlan0,$result);
					$strRxBytes = $result[1];
				}
				if (strpos($strWlan0,'TX bytes') !== false) {
					preg_match('/TX [B|b]ytes:(\d+ \(\d+.\d+ [K|M|G]iB\))/i',$strWlan0,$result);
					$strTxBytes = $result[1];
				} else {
					preg_match('/TX packets \d+ bytes (\d+ \(\d+.\d+ [K|M|G]iB\))/i',$strWlan0,$result);
					$strTxBytes = $result[1];
				}
				//preg_match('/TX Bytes:(\d+ \(\d+.\d+ [K|M|G]iB\))/i',$strWlan0,$result);
				//$strTxBytes = $result[1];
				if (preg_match('/Access Point: ([0-9a-f:]+)/i',$strWlan0,$result)) { 
				$strBSSID = $result[1]; }
				if (preg_match('/Connected to\ ([0-9a-f:]+)/i',$strWlan0,$result)) { 
				$strBSSID = $result[1]; }
				if (preg_match('/Bit Rate([=:0-9\.]+ Mb\/s)/i',$strWlan0,$result)) {
				$strBitrate = str_replace(':', '', str_replace('=', '', $result[1])); }
				if (preg_match('/tx bitrate:\ ([0-9\.]+ Mbit\/s)/i',$strWlan0,$result)) {
				$strBitrate = str_replace(':', '', str_replace('=', '', $result[1])); }
				if (preg_match('/Tx-Power=([0-9]+ dBm)/i',$strWlan0,$result)) {
				$strTxPower = $result[1]; }
				if (preg_match('/ESSID:\"([a-zA-Z0-9-_\s]+)\"/i',$strWlan0,$result)) {
				$strSSID = str_replace('"','',$result[1]); }
				if (preg_match('/SSID:\ ([a-zA-Z0-9-_\s]+)/i',$strWlan0,$result)) {
				$strSSID = str_replace(' freq','',$result[1]); }
				if (preg_match('/Link Quality=([0-9]+\/[0-9]+)/i',$strWlan0,$result)) {
				        $strLinkQuality = $result[1];
                                        if (strpos($strLinkQuality, "/")) {
                                                $arrLinkQuality = explode("/", $strLinkQuality);
                                                $strLinkQuality = number_format(($arrLinkQuality[0] / $arrLinkQuality[1]) * 100)." &#37;";
                                        }
                                }
				if (preg_match('/Signal Level=(-[0-9]+ dBm)/i',$strWlan0,$result)) {
				$strSignalLevel = $result[1]; }
				if (preg_match('/Signal Level=([0-9]+\/[0-9]+)/i',$strWlan0,$result)) {
				$strSignalLevel = $result[1]; }
				if (preg_match('/signal:\ (-[0-9]+ dBm)/i',$strWlan0,$result)) {
				$strSignalLevel = $result[1]; }
				if (preg_match('/Frequency:([0-9.]+ GHz)/i',$strWlan0,$result)) {
                                $strWifiFreq = $result[1];
				$strWifiChan = str_replace(" GHz", "", $strWifiFreq);
                                $strWifiChan = str_replace(".", "", $strWifiChan);
				$strWifiChan = ConvertToChannel(str_replace(".", "", $strWifiChan)); }
		}
		else {
			$strStatus = '<span style="color:red">Interface is down</span>';
		}
		if(isset($_POST['ifdown_wlan0'])) {
			exec('ifconfig wlan0 | grep -i running | wc -l',$test);
			if($test[0] == 1) {
				exec('sudo ifdown wlan0',$return);
			} 
			else {
				echo 'Interface already down';
			}
		} 
		elseif(isset($_POST['ifup_wlan0'])) {
			exec('ifconfig wlan0 | grep -i running | wc -l',$test);
			if($test[0] == 0) {
				exec('sudo ifup wlan0',$return);
			} 
			else {
				echo 'Interface already up';
			}
		}
		elseif(isset($_POST['reset_wlan0'])) {
			exec('sudo wpa_cli reconfigure wlan0 && sudo ifdown wlan0 && sleep 3 && sudo ifup wlan0 && sudo wpa_cli scan',$test);
			echo '<script>window.location.href=\'wifi.php?page=wlan0_info\';</script>';
		}

	echo '<script type="text/javascript">setTimeout(function () { location.reload(1); }, 15000);</script>
<div class="infobox">
<form action="'.$_SERVER['PHP_SELF'].'?page=wlan0_info" method="post">
<!-- <input type="submit" value="ifdown wlan0" name="ifdown_wlan0" /> -->
<!-- <input type="submit" value="ifup wlan0" name="ifup_wlan0" /> -->
<!-- <input type="button" value="Refresh" onclick="document.location.reload(true)" /> -->
<input type="button" value="Refresh" onclick="window.location.href=\'wifi.php?page=wlan0_info\'" />
<input type="submit" value="Reset WiFi Adapter" name="reset wlan0" />
<input type="button" value="Configure WiFi" name="wpa_conf" onclick="document.location=\'?page=\'+this.name" />
</form>
<div class="infoheader">Wireless Information and Statistics</div>
<div class="intinfo"><div class="intheader">Interface Information</div>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Interface Name : wlan0<br />
&nbsp;&nbsp;&nbsp;&nbsp;Interface Status : ' . $strStatus . '<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;IP Address : ' . $strIPAddress . '<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Subnet Mask : ' . $strNetMask . '<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mac Address : ' . $strHWAddress . '<br />
<br />
<div class="intheader">Interface Statistics</div>
&nbsp;&nbsp;&nbsp;&nbsp;Received Packets : ' . $strRxPackets . '<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Received Bytes : ' . $strRxBytes . '<br />
&nbsp;Transferred Packets : ' . $strTxPackets . '<br />
&nbsp;&nbsp;&nbsp;Transferred Bytes : ' . $strTxBytes . '<br />
<br />
</div>
<div class="wifiinfo">
<div class="intheader">Wireless Information</div>
&nbsp;&nbsp;&nbsp;Connected To : ' . $strSSID . '<br />
&nbsp;AP Mac Address : ' . $strBSSID . '<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bitrate : ' . $strBitrate . '<br />
&nbsp;&nbsp;&nbsp;Signal Level : ' . $strSignalLevel . '<br />
<br />';
if ($strTxPower) { echo '&nbsp;Transmit Power : ' . $strTxPower .'<br />'."\n"; } else { echo "<br />\n"; }
if ($strLinkQuality) { echo '&nbsp;&nbsp;&nbsp;Link Quality : ' . $strLinkQuality . '<br />'."\n"; } else { echo "<br />\n"; }
if (($strWifiFreq) && ($strWifiChan) && ($strWifiChan != "Invalid Channel")) {
	echo '&nbsp;&nbsp;&nbsp;Channel Info : ' . $strWifiChan . ' (' . $strWifiFreq . ')<br />'."\n";
} else {
	echo "<br />\n";
}
if (file_exists('/etc/wpa_supplicant/wpa_supplicant.conf')) {
        exec('sudo grep "country" /etc/wpa_supplicant/wpa_supplicant.conf', $wifiCountryArr);
        }
if (isset($wifiCountryArr[0])) {
        $wifiCountry = explode("=", $wifiCountryArr[0]);
        if (isset($wifiCountry[1])) {
                echo '&nbsp;&nbsp;&nbsp;WiFi Country : '.$wifiCountry[1]."<br />\n";
                }
        }
echo '<br />
<br />
</div>
<br />
</div>
<div class="intfooter">Information provided by ifconfig and iwconfig</div>';
	break;

	case "wpa_conf":
		exec('sudo cat /etc/wpa_supplicant/wpa_supplicant.conf',$return);
		$ssid = array();
		$psk = array();
		foreach($return as $a) {
			if(preg_match('/country=/i',$a)) {
				$wifiCountryArr = explode("=",$a);
				$wifiCountry = $wifiCountryArr[1];
			}

			// Make sure we only put ONE SSID and matching PSK into the arrays
                        if ( ( isset($curssidplain) || isset($curssidalt) ) && ( isset($curpskplain) || isset($curpskalt) ) ) {
                                if (isset($curssidplain)) { $ssid[] = $curssidplain; unset($curssidplain); unset($curssidalt); }
                                if (isset($curssidalt))   { $ssid[] = $curssidalt;   unset($curssidplain); unset($curssidalt); }
                                if (isset($curpskplain))  { $psk[]  = $curpskplain;  unset($curpskplain);  unset($curpskalt);  }
                                if (isset($curpskalt))    { $psk[]  = $curpskalt;    unset($curpskplain);  unset($curpskalt);  }
                        }

                        // Handle the case of the old file format, and the new...
                        if(preg_match('/\#SSID=/i',$a) && !preg_match('/scan_ssid/i',$a)) {
                                $arrssid = explode("=",$a);
                                //$ssid[] = str_replace('"','',$arrssid[1]);
                                $curssidplain = str_replace('"','',$arrssid[1]);
                        }
                        elseif(preg_match('/SSID="/i',$a) && !preg_match('/scan_ssid/i',$a)) {
                                $arrssid = explode("=",$a);
                                //$ssid[] = str_replace('"','',$arrssid[1]);
                                if (!isset($curssidplain)) { $curssidalt = str_replace('"','',$arrssid[1]); }
                        }
                        if (isset($curssidplain) || isset($curssidalt)) {
                                if(preg_match('/\#psk="/i',$a)) {
                                        $arrpsk = explode("=",$a);
                                        //$psk[] = str_replace('"','',$arrpsk[1]);
                                        $curpskplain = str_replace('"','',$arrpsk[1]);
                                }
                                elseif(preg_match('/psk=/i',$a)) {
                                        $arrpsk = explode("=",$a);
                                        //$psk[] = str_replace('"','',$arrpsk[1]);
                                        if (!isset($curpskplain)) { $curpskalt = str_replace('"','',$arrpsk[1]); }
                                }
                        }
		}
		$numSSIDs = count($ssid);
		$output = '<form method="post" action="'.$_SERVER['PHP_SELF'].'?page=wpa_conf" id="wpa_conf_form">
<input type="button" value="WiFi Info" name="wlan0_info" onclick="document.location=\'?page=\'+this.name" /><br />
<input type="hidden" id="Networks" name="Networks" />
<div class="network" id="networkbox">'."\n";
		if (!isset($wifiCountry)) { $wifiCountry = "JP"; }
		$output .= 'WiFi Regulatory Domain (Country Code) : <select name="wifiCountryCode">'."\n";
		exec('regdbdump /lib/crda/regulatory.bin | fgrep country | cut -b 9-10', $regDomains);
		foreach($regDomains as $regDomain) {
			if ($regDomain == $wifiCountry) {
				$output .= '<option value="'.$regDomain.'" selected>'.$regDomain.'</option>'."\n";
			} else {
				$output .= '<option value="'.$regDomain.'">'.$regDomain.'</option>'."\n";
			}
		}
		$output .= '</select><br />'."\n";

		for($ssids = 0; $ssids < $numSSIDs; $ssids++) {
			$output .= '<div id="Networkbox'.$ssids.'" class="NetworkBoxes">Network '.$ssids."\n";
			$output .= '<input type="button" value="Delete" onclick="DeleteNetwork('.$ssids.')" /><br />'."\n";
			$output .= '<span class="tableft" id="lssid'.$ssids.'">SSID :</span><input type="text" id="ssid'.$ssids.'" name="ssid'.$ssids.'" value="'.$ssid[$ssids].'" onkeyup="CheckSSID(this)" /><br />'."\n";
			$output .= '<span class="tableft" id="lpsk'.$ssids.'">PSK :</span><input type="password" id="psk'.$ssids.'" name="psk'.$ssids.'" value="'.$psk[$ssids].'" onkeyup="CheckPSK(this)" /><br /><br /></div>'."\n";
		}
		$output .= '</div>'."\n";
		$output .= '<div class="infobox">'."\n";
		$output .= '<input type="submit" value="Scan for Networks (10 secs)" name="Scan" />'."\n";
		$output .= '<input type="button" value="Add Network" onclick="AddNetwork();" />'."\n";
		$output .= '<input type="submit" value="Save (and connect)" name="SaveWPAPSKSettings" onmouseover="UpdateNetworks(this)" />'."\n";
		$output .= '</div>'."\n";
		$output .= '</form>'."\n";


	echo $output;
	echo '<script type="text/Javascript">UpdateNetworks()</script>';

	if(isset($_POST['SaveWPAPSKSettings'])) {
		$config = "ctrl_interface=DIR=/var/run/wpa_supplicant GROUP=netdev\nupdate_config=1\nap_scan=1\nfast_reauth=1\ncountry=".$_POST['wifiCountryCode']."\n\n";
		$networks = $_POST['Networks'];

		//Reworked WiFi Starts Here
		for($x = 0; $x < $networks; $x++) {
			//$network = '';
			$ssid = $_POST['ssid'.$x];
			$psk = $_POST['psk'.$x];
			$priority = 100 - $x;
			if ($ssid == "*" && !$psk) { $config .= "network={\n\t#ssid=\"$ssid\"\n\t#psk=\"\"\n\tkey_mgmt=NONE\n\tid_str=\"$x\"\n\tpriority=$priority\n\tscan_ssid=1\n}\n\n"; }
			elseif ($ssid && !$psk) { $config .= "network={\n\tssid=\"$ssid\"\n\t#psk=\"\"\n\tkey_mgmt=NONE\n\tid_str=\"$x\"\n\tpriority=$priority\n\tscan_ssid=1\n}\n\n"; }
			elseif ($ssid && $psk) {
				$pskSalted = hash_pbkdf2("sha1",$psk, $ssid, 4096, 64);
				$ssidHex = bin2hex("$ssid");
				$config .= "network={\n\t#ssid=\"$ssid\"\n\tssid=$ssidHex\n\t#psk=\"$psk\"\n\tpsk=$pskSalted\n\tid_str=\"$x\"\n\tpriority=$priority\n\tscan_ssid=1\n}\n\n";
		}
		}
		file_put_contents('/tmp/wifidata', $config);
		system('sudo mount -o remount,rw / && sudo cp -f /tmp/wifidata /etc/wpa_supplicant/wpa_supplicant.conf && sudo sync && sudo sync && sudo sync && sudo mount -o remount,ro /');
		echo "Wifi Settings Updated Successfully\n";
		// If Auto AP is on, dont restart the WiFi Card
		if (!file_exists('/sys/class/net/wlan0_ap')) {
			exec('sudo ip link set wlan0 down && sleep 3 && sudo ip link set wlan0 up');
		}
		echo "<script>document.location='?page=\wlan0_info'</script>";

	} elseif(isset($_POST['Scan'])) {
		$return = '';
		exec('ifconfig wlan0 | grep -i running | wc -l',$test);
		exec('sudo wpa_cli scan -i wlan0',$return);
		sleep(8);
		exec('sudo wpa_cli scan_results -i wlan0',$return);
		unset($return['0']); // This is a better way to clean up;
		unset($return['1']); // This is a better way to clean up;
		echo "<br />\n";
		echo "Networks found : <br />\n";
		echo "<table>\n";
		echo "<tr><th>Connect</th><th>SSID</th><th>Channel</th><th>Signal</th><th>Security</th></tr>";
		foreach($return as $network) {
			$arrNetwork = preg_split("/[\t]+/",$network);
			$bssid = $arrNetwork[0];
			$channel = ConvertToChannel($arrNetwork[1]);
			$signal = $arrNetwork[2] . " dBm";
			$security = ConvertToSecurity($arrNetwork[3]);
			$ssid = $arrNetwork[4];

			echo '<tr>';
			echo '<td style="text-align: left;"><input type="button" value="Select" onclick="AddScanned(\''.$ssid.'\')" /></td>';
			echo '<td style="text-align: left;">'.$ssid.'</td>';
			echo '<td style="text-align: left;">'.$channel.'</td>';
			echo '<td>'.$signal.'</td>';
			echo '<td style="text-align: left;">'.$security.'</td>';
			echo '</tr>'."\n";

		}
		echo "</table>\n";
	}

	break;
}


echo '
<div class="tail">.</div>
</body>
</html>';
?>
