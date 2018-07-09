<?

function GetDistString($input,$string,$offset,$separator) {
	$string = substr($input,strpos($input,$string)+$offset,strpos(substr($input,strpos($input,$string)+$offset),$separator));
	return $string;
}

function ParseConfig($arrConfig) {
	$config = array();
	foreach($arrConfig as $line) {
		if($line[0] != "#") {
			$arrLine = explode("=",$line);
			$config[$arrLine[0]] = $arrLine[1];
		}
	}
	return $config;
}

function ConvertToChannel($freq) {
//	$base = 2412;
//	$channel = 1;
//	for($x = 0; $x < 13; $x++) {
//		if($freq != $base) {
//			$base = $base + 5;
//			$channel++;
//		} else {
//			return $channel;
//		}
//	}
	if ($wifiFreqToChan[$freq]) { return $wifiFreqToChan[$freq]; }
	else { return "Invalid Channel"; }
}

function ConvertToSecurity($security) {
	switch($security) {
		case "[WPA2-PSK-CCMP][ESS]":
			return "WPA2-PSK (AES)";
		break;
		case "[WPA2-PSK-CCMP-preauth][ESS]":
			return "WPA2-PSK (AES) with Preauth";
		break;
		case "[WPA2-PSK-TKIP][ESS]":
			return "WPA2-PSK (TKIP)";
		break;
		case "[WPA2-PSK-CCMP][WPS][ESS]":
			return "WPA2-PSK (TKIP) with WPS";
		break;
		case "[WPA-PSK-TKIP+CCMP][WPS][ESS]":
			return "WPA-PSK (TKIP/AES) with WPS";
		break;
		case "[WPA-PSK-TKIP][WPA2-PSK-CCMP][WPS][ESS]":
			return "WPA/WPA2-PSK (TKIP)";
		break;
		case "[WPA-PSK-TKIP+CCMP][WPA2-PSK-TKIP+CCMP][ESS]":
			return "WPA/WPA2-PSK (TKIP/AES)";
		break;
		case "[WPA-EAP-CCMP+TKIP][WPA2-EAP-CCMP+TKIP-preauth][ESS]":
			return "WPA/WPA2-PSK (TKIP/AES) with Preauth";
		break;
		case "[WPA-PSK-CCMP+TKIP][WPA2-PSK-CCMP+TKIP][WPS][ESS]":
			return "WPA/WPA2-PSK (TKIP/AES) with WPS";
		break;
		case "[WPA-PSK-CCMP][WPA2-PSK-CCMP][WPS][ESS]":
			return "WPA/WPA2-PSK (AES) with WPS";
		break;
		case "[WPA-PSK-TKIP][ESS]":
			return "WPA-PSK (TKIP)";
		break;
		case "[WEP][ESS]":
			return "WEP";
		break;
		case "[ESS]":
			return "None";
		break;
		default:
			return $security;
		break;
	}
}

$wifiFreqToChan = array (
	"2412" => "2.4GHz Ch1",
	"2417" => "2.4GHz Ch2",
	"2422" => "2.4GHz Ch3",
	"2427" => "2.4GHz Ch4",
	"2432" => "2.4GHz Ch5",
	"2437" => "2.4GHz Ch6",
	"2442" => "2.4GHz Ch7",
	"2447" => "2.4GHz Ch8",
	"2452" => "2.4GHz Ch9",
	"2457" => "2.4GHz Ch10",
	"2462" => "2.4GHz Ch11",
	"2467" => "2.4GHz Ch12",
	"2472" => "2.4GHz Ch13",
	"2484" => "2.4GHz Ch14",
);

/*
1*	2412	Yes	Yes	YesD
2	2417	Yes	Yes	YesD
3	2422	Yes	Yes	YesD
4	2427	Yes	Yes	YesD
5*	2432	Yes	Yes	Yes
6	2437	Yes	Yes	Yes
7	2442	Yes	Yes	Yes
8	2447	Yes	Yes	Yes
9*	2452	Yes	Yes	Yes
10	2457	Yes	Yes	Yes
11	2462	Yes	Yes	Yes
12	2467	NoB	Yes	Yes
13*	2472	NoB	Yes	Yes
*/

?>

