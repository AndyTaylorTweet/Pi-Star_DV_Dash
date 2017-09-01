<?php
// Get the CPU temp and colour the box accordingly...
$cpuTempC = exec('awk \'{printf("%.1f\n",$1/1e3)}\' /sys/class/thermal/thermal_zone0/temp');
$cpuTempF = round(+$cpuTempC * 9 / 5 + 32, 1);
if ($cpuTempC < 50) { $cpuTempHTML = "<td style=\"background: #1d1\">".$cpuTempC."&deg;C / ".$cpuTempF."&deg;F</td>\n"; }
if ($cpuTempC >= 50) { $cpuTempHTML = "<td style=\"background: #fa0\">".$cpuTempC."&deg;C / ".$cpuTempF."&deg;F</td>\n"; }
if ($cpuTempC >= 69) { $cpuTempHTML = "<td style=\"background: #f00\">".$cpuTempC."&deg;C / ".$cpuTempF."&deg;F</td>\n"; }

// Pull in some config
require_once('config/version.php');
require_once('config/ircddblocal.php');
require_once('config/language.php');
$cpuLoad = sys_getloadavg();

// Load the pistar-release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);

// Load the dstarrepeater config file
$configdstar = array();
if ($configdstarfile = fopen('/etc/dstarrepeater','r')) {
        while ($line1 = fgets($configdstarfile)) {
		if (strpos($line1, '=') !== false) {
                	list($key1,$value1) = split("=",$line1);
                	$value1 = trim(str_replace('"','',$value1));
                	if (strlen($value1) > 0)
                	$configdstar[$key1] = $value1;
		}
        }
}

// Load the ircDDBGateway config file
$configs = array();
if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
		if (strpos($line, '=') !== false) {
                	list($key,$value) = split("=",$line);
                	$value = trim(str_replace('"','',$value));
                	if ($key != 'ircddbPassword' && strlen($value) > 0)
                	$configs[$key] = $value;
		}
        }
}

// Load the mmdvmhost config file
$mmdvmConfigFile = '/etc/mmdvmhost';
$configmmdvm = parse_ini_file($mmdvmConfigFile, true);

// Load the ysfgateway config file
$ysfgatewayConfigFile = '/etc/ysfgateway';
$configysfgateway = parse_ini_file($ysfgatewayConfigFile, true);

// Load the p25gateway config file
$p25gatewayConfigFile = '/etc/p25gateway';
$configp25gateway = parse_ini_file($p25gatewayConfigFile, true);

// Load the dmrgateway config file
$dmrGatewayConfigFile = '/etc/dmrgateway';
if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }

// Load the modem config information
if (file_exists('/etc/dstar-radio.dstarrepeater')) {
	$modemConfigFileDStarRepeater = '/etc/dstar-radio.dstarrepeater';
	if (fopen($modemConfigFileDStarRepeater,'r')) { $configModem = parse_ini_file($modemConfigFileDStarRepeater, true); }
	}

if (file_exists('/etc/dstar-radio.mmdvmhost')) {
	$modemConfigFileMMDVMHost = '/etc/dstar-radio.mmdvmhost';
	if (fopen($modemConfigFileMMDVMHost,'r')) { $configModem = parse_ini_file($modemConfigFileMMDVMHost, true); }
}

function aprspass ($callsign) { 
	$stophere = strpos($callsign, '-'); 
	if ($stophere) $callsign = substr($callsign, 0, $stophere); 
	$realcall = strtoupper(substr($callsign, 0, 10)); 
	// initialize hash 
	$hash = 0x73e2; 
	$i = 0; 
	$len = strlen($realcall); 
	// hash callsign two bytes at a time 
	while ($i < $len) { 
		$hash ^= ord(substr($realcall, $i, 1))<<8; 
		$hash ^= ord(substr($realcall, $i + 1, 1)); 
		$i += 2; 
	} 
	// mask off the high bit so number is always positive 
	return $hash & 0x7fff; 
}

$progname = basename($_SERVER['SCRIPT_FILENAME'],".php");
$rev=$version;
$MYCALL=strtoupper($callsign);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
<head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php echo "<meta name=\"GENERATOR\" content=\"$progname $rev\" />\n"; ?>
    <meta name="Author" content="Andrew Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Configuration" />
    <meta name="KeyWords" content="Pi-Star, MW0MWZ" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title><?php echo "$MYCALL"." - ".$lang['digital_voice']." ".$lang['dashboard']." - ".$lang['configuration'];?></title>
    <link rel="stylesheet" type="text/css" href="css/ircddb.css?version=1.3" />
    <script type="text/javascript">
	function submitform()
	{
	  document.getElementById("config").submit();
	}
	function submitPassform()
	{
	  document.getElementById("adminPassForm").submit();
	}
	function factoryReset()
	{
	  if (confirm('WARNING: This will set all your settings back to factory defaults. WiFi setup will be retained to maintain network access to this Pi.\n\nAre you SURE you want to do this?\n\nPress OK to restore the factory configuration\nPress Cancel to go back.')) {
	    document.getElementById("factoryReset").submit();
	    } else {
	    return false;
	    }
	}
	function resizeIframe(obj) {
	  var numpix = parseInt(obj.contentWindow.document.body.scrollHeight, 10);
	  obj.style.height = numpix + 'px';
	}
    </script>
    <script type="text/javascript" src="/functions.js"></script>
</head>
<body>
<div class="container">
<div class="header">
<div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / <?php echo $lang['dashboard'].": ".$version; ?></div>
<h1>Pi-Star <?php echo $lang['digital_voice']." - ".$lang['configuration'];?></h1>
<p style="padding-right: 5px; text-align: right; color: #ffffff;">
 <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
 <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
 <a href="/admin/power.php" style="color: #ffffff;"><?php echo $lang['power'];?></a> |
 <a href="/admin/update.php" style="color: #ffffff;"><?php echo $lang['update'];?></a> |
 <a href="/admin/config_backup.php" style="color: #ffffff;"><?php echo $lang['backup_restore'];?></a> |
 <a href="javascript:factoryReset();" style="color: #ffffff;"><?php echo $lang['factory_reset'];?></a>
</p>
</div>
<div class="contentwide">
<?php
// Hardware Detail
if ($_SERVER["PHP_SELF"] == "/admin/configure.php") {
//HTML output starts here 
?>
    <b><?php echo $lang['hardware_info'];?></b>
    <table style="table-layout: fixed;">
    <tr>
    <th><a class="tooltip" href="#"><?php echo $lang['hostname'];?><span><b>Hostname</b>The name of host<br />running the Pi-Star Software.</span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['kernel'];?><span><b>Release</b>This is the version<br />number of the Linux Kernel running<br />on this Raspberry Pi.</span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['platform'];?><span><b>Uptime:<br /><?php echo str_replace(',', ',<br />', exec('uptime -p'));?></b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['cpu_load'];?><span><b>CPU Load</b>This is the standard Linux<br />system load indicator.</span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['cpu_temp'];?><span><b>CPU Temp</b></span></a></th>
    </tr>
    <tr>
    <td><?php echo php_uname('n');?></td>
    <td><?php echo php_uname('r');?></td>
    <td colspan="2"><?php echo exec('platformDetect.sh');?></td>
    <td><?php echo $cpuLoad[0];?> / <?php echo $cpuLoad[1];?> / <?php echo $cpuLoad[2];?></td>
    <?php echo $cpuTempHTML; ?>
    </tr>
    </table>
<br />
<?php if (!empty($_POST)):
	// Make the root filesystem writable
	system('sudo mount -o remount,rw /');

	// Stop Cron (occasionally remounts root as RO - would be bad if it did this at the wrong time....)
	system('sudo systemctl stop cron.service > /dev/null 2>/dev/null &');			//Cron

	// Stop the DV Services
	system('sudo systemctl stop dstarrepeater.service > /dev/null 2>/dev/null &');		// D-Star Radio Service
	system('sudo systemctl stop mmdvmhost.service > /dev/null 2>/dev/null &');		// MMDVMHost Radio Service
	system('sudo systemctl stop ircddbgateway.service > /dev/null 2>/dev/null &');		// ircDDBGateway Service
	system('sudo systemctl stop timeserver.service > /dev/null 2>/dev/null &');		// Time Server Service
	system('sudo systemctl stop pistar-watchdog.service > /dev/null 2>/dev/null &');	// PiStar-Watchdog Service
	system('sudo systemctl stop pistar-remote.service > /dev/null 2>/dev/null &');		// PiStar-Remote Service
	system('sudo systemctl stop ysfgateway.service > /dev/null 2>/dev/null &');		// YSFGateway
	system('sudo systemctl stop ysfparrot.service > /dev/null 2>/dev/null &');		// YSFParrot
	system('sudo systemctl stop p25gateway.service > /dev/null 2>/dev/null &');		// P25Gateway
	system('sudo systemctl stop p25parrot.service > /dev/null 2>/dev/null &');		// P25Parrot
	system('sudo systemctl stop dmrgateway.service > /dev/null 2>/dev/null &');		// DMRGateway

	echo "<table>\n";
	echo "<tr><th>Working...</th></tr>\n";
	echo "<tr><td>Stopping services and applying your configuration changes...</td></tr>\n";
	echo "</table>\n";

	// Let the services actualy stop
	sleep(1);


	// Factory Reset Handler Here
	if (empty($_POST['factoryReset']) != TRUE ) {
	  echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>Factory Reset Config</th></tr>\n";
          echo "<tr><td>Loading fresh configuration file(s)...</td><tr>\n";
          echo "</table>\n";
          unset($_POST);

	  // Over-write the config files with the clean copies
	  exec('sudo unzip -o /usr/local/bin/config_clean.zip -d /etc/');
	  exec('sudo rm -rf /etc/dstar-radio.*');
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
	  // Make the root filesystem read-only
          system('sudo mount -o remount,ro /');
	  echo "<br />\n</div>\n";
          echo "<div class=\"footer\">\nPi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-".date("Y").".<br />\n";
          echo "Need help? Click <a style=\"color: #ffffff;\" href=\"https://www.facebook.com/groups/pistar/\" target=\"_new\">here for the Support Group</a><br />\n";
          echo "Get your copy of Pi-Star from <a style=\"color: #ffffff;\" href=\"http://www.mw0mwz.co.uk/pi-star/\" target=\"_blank\">here</a>.<br />\n";
          echo "<br />\n</div>\n</div>\n</body>\n</html>\n";
	  die();
	  }

	// Handle the case where the config is not read correctly
	if (count($configmmdvm) <= 18) {
	  echo "<br />\n";
	  echo "<table>\n";
	  echo "<tr><th>ERROR</th></tr>\n";
	  echo "<tr><td>Unable to read source configuration file(s)...</td><tr>\n";
	  echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
	  echo "</table>\n";
	  unset($_POST);
	  echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
	  die();
	}

	// Change Radio Control Software
	if (empty($_POST['controllerSoft']) != TRUE ) {
	  system('sudo rm -rf /etc/dstar-radio.*');
	  if (escapeshellcmd($_POST['controllerSoft']) == 'DSTAR') { system('sudo touch /etc/dstar-radio.dstarrepeater'); }
	  if (escapeshellcmd($_POST['controllerSoft']) == 'MMDVM') { system('sudo touch /etc/dstar-radio.mmdvmhost'); }
	  }

	// Change Dashboard Language
	if (empty($_POST['dashboardLanguage']) != TRUE ) {
	  $rollDashLang = 'sudo sed -i "/pistarLanguage=/c\\$pistarLanguage=\''.escapeshellcmd($_POST['dashboardLanguage']).'\';" /var/www/dashboard/config/language.php';
	  system($rollDashLang);
	  }

	// Admin Password Change
	if (empty($_POST['adminPassword']) != TRUE ) {
	  $rollAdminPass0 = 'htpasswd -b /var/www/.htpasswd pi-star '.escapeshellcmd($_POST['adminPassword']);
	  system($rollAdminPass0);
	  $rollAdminPass2 = 'sudo echo -e "'.escapeshellcmd($_POST['adminPassword']).'\n'.escapeshellcmd($_POST['adminPassword']).'" | sudo passwd pi-star';
	  system($rollAdminPass2);
	  }

	// Set the ircDDBGAteway Remote Password
	if (empty($_POST['confPassword']) != TRUE ) {
	  $rollConfPassword0 = 'sudo sed -i "/remotePassword=/c\\remotePassword='.escapeshellcmd($_POST['confPassword']).'" /etc/ircddbgateway';
	  $rollConfPassword1 = 'sudo sed -i "/password=/c\\password='.escapeshellcmd($_POST['confPassword']).'" /root/.Remote\ Control';
	  system($rollConfPassword0);
	  system($rollConfPassword1);
	  }

	// Set the ircDDBGateway Defaut Reflector
	if (empty($_POST['confDefRef']) != TRUE ) {
	  if (stristr(strtoupper(escapeshellcmd($_POST['confDefRef'])), strtoupper(escapeshellcmd($_POST['confCallsign']))) != TRUE ) {
	    if (strlen($_POST['confDefRef']) != 7) {
		$targetRef = strtoupper(escapeshellcmd(str_pad($_POST['confDefRef'], 7, " ")));
	        } else {
		$targetRef = strtoupper(escapeshellcmd($_POST['confDefRef']));
	        }
	    $rollconfDefRef = 'sudo sed -i "/reflector1=/c\\reflector1='.$targetRef.escapeshellcmd($_POST['confDefRefLtr']).'" /etc/ircddbgateway';
	    system($rollconfDefRef);
	    }
	  }

	// Set the ircDDBGAteway Defaut Reflector Autostart
	if (empty($_POST['confDefRefAuto']) != TRUE ) {
	  if (escapeshellcmd($_POST['confDefRefAuto']) == 'ON') {
	    $rollconfDefRefAuto = 'sudo sed -i "/atStartup1=/c\\atStartup1=1" /etc/ircddbgateway';
	  }
	  if (escapeshellcmd($_POST['confDefRefAuto']) == 'OFF') {
	    $rollconfDefRefAuto = 'sudo sed -i "/atStartup1=/c\\atStartup1=0" /etc/ircddbgateway';
	  }
	  system($rollconfDefRefAuto);
	  }

	// Set the Latitude
	if (empty($_POST['confLatitude']) != TRUE ) {
	  $newConfLatitude = preg_replace('/[^0-9\.\-]/', '', $_POST['confLatitude']);
	  $rollConfLat0 = 'sudo sed -i "/latitude=/c\\latitude='.$newConfLatitude.'" /etc/ircddbgateway';
	  $rollConfLat1 = 'sudo sed -i "/latitude1=/c\\latitude1='.$newConfLatitude.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Latitude'] = $newConfLatitude;
	  $configysfgateway['Info']['Latitude'] = $newConfLatitude;
	  system($rollConfLat0);
	  system($rollConfLat1);
	  }

	// Set the Longitude
	if (empty($_POST['confLongitude']) != TRUE ) {
	  $newConfLongitude = preg_replace('/[^0-9\.\-]/', '', $_POST['confLongitude']);
	  $rollConfLon0 = 'sudo sed -i "/longitude=/c\\longitude='.$newConfLongitude.'" /etc/ircddbgateway';
	  $rollConfLon1 = 'sudo sed -i "/longitude1=/c\\longitude1='.$newConfLongitude.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Longitude'] = $newConfLongitude;
	  $configysfgateway['Info']['Longitude'] = $newConfLongitude;
	  system($rollConfLon0);
	  system($rollConfLon1);
	  }

	// Set the Town
	if (empty($_POST['confDesc1']) != TRUE ) {
	  $newConfDesc1 = preg_replace('/[^A-Za-z0-9\.\s\,\-]/', '', $_POST['confDesc1']);
	  $rollDesc1 = 'sudo sed -i "/description1=/c\\description1='.$newConfDesc1.'" /etc/ircddbgateway';
	  $rollDesc11 = 'sudo sed -i "/description1_1=/c\\description1_1='.$newConfDesc1.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Location'] = $newConfDesc1;
          $configysfgateway['Info']['Name'] = $newConfDesc1;
	  system($rollDesc1);
	  system($rollDesc11);
	  }

	// Set the Country
	if (empty($_POST['confDesc2']) != TRUE ) {
	  $newConfDesc2 = preg_replace('/[^A-Za-z0-9\.\s\,\-]/', '', $_POST['confDesc2']);
	  $rollDesc2 = 'sudo sed -i "/description2=/c\\description2='.$newConfDesc2.'" /etc/ircddbgateway';
	  $rollDesc22 = 'sudo sed -i "/description1_2=/c\\description1_2='.$newConfDesc2.'" /etc/ircddbgateway';
          $configmmdvm['Info']['Description'] = $newConfDesc2;
          $configysfgateway['Info']['Description'] = $newConfDesc2;
	  system($rollDesc2);
	  system($rollDesc22);
	  }

	// Set the URL
	if (empty($_POST['confURL']) != TRUE ) {
	  $newConfURL = strtolower(preg_replace('/[^A-Za-z0-9\.\s\,\-\/\:]/', '', $_POST['confURL']));
	  if (escapeshellcmd($_POST['urlAuto']) == 'auto') { $txtURL = "http://www.qrz.com/db/".strtoupper(escapeshellcmd($_POST['confCallsign'])); }
	  if (escapeshellcmd($_POST['urlAuto']) == 'man')  { $txtURL = $newConfURL; }
	  if (escapeshellcmd($_POST['urlAuto']) == 'auto') { $rollURL0 = 'sudo sed -i "/url=/c\\url=http://www.qrz.com/db/'.strtoupper(escapeshellcmd($_POST['confCallsign'])).'" /etc/ircddbgateway';  }
	  if (escapeshellcmd($_POST['urlAuto']) == 'man') { $rollURL0 = 'sudo sed -i "/url=/c\\url='.$newConfURL.'" /etc/ircddbgateway'; }
          $configmmdvm['Info']['URL'] = $txtURL;
	  system($rollURL0);
	  }

	// Set the APRS Host for ircDDBGateway
	if (empty($_POST['selectedAPRSHost']) != TRUE ) {
	  $rollAPRSHost = 'sudo sed -i "/aprsHostname=/c\\aprsHostname='.escapeshellcmd($_POST['selectedAPRSHost']).'" /etc/ircddbgateway';
	  system($rollAPRSHost);
	  $configysfgateway['aprs.fi']['Server'] = escapeshellcmd($_POST['selectedAPRSHost']);
	  }

	// Set ircDDBGateway and TimeServer language
	if (empty($_POST['ircDDBGatewayAnnounceLanguage']) != TRUE) {
	  $ircDDBGatewayAnnounceLanguageArr = explode(',', escapeshellcmd($_POST['ircDDBGatewayAnnounceLanguage']));
	  $rollIrcDDBGatewayLang = 'sudo sed -i "/language=/c\\language='.escapeshellcmd($ircDDBGatewayAnnounceLanguageArr[0]).'" /etc/ircddbgateway';
	  $rollTimeserverLang = 'sudo sed -i "/language=/c\\language='.escapeshellcmd($ircDDBGatewayAnnounceLanguageArr[1]).'" /etc/timeserver';
	  system($rollIrcDDBGatewayLang);
	  system($rollTimeserverLang);
	}

	// Clear timeserver modules
	$rollTimeserverBandA = 'sudo sed -i "/sendA=/c\\sendA=0" /etc/timeserver';
	$rollTimeserverBandB = 'sudo sed -i "/sendB=/c\\sendB=0" /etc/timeserver';
	$rollTimeserverBandC = 'sudo sed -i "/sendC=/c\\sendC=0" /etc/timeserver';
	$rollTimeserverBandD = 'sudo sed -i "/sendD=/c\\sendD=0" /etc/timeserver';
	$rollTimeserverBandE = 'sudo sed -i "/sendE=/c\\sendE=0" /etc/timeserver';
	system($rollTimeserverBandA);
	system($rollTimeserverBandB);
	system($rollTimeserverBandC);
	system($rollTimeserverBandD);
	system($rollTimeserverBandE);
	
	// Set the Frequency for Duplex
	if (empty($_POST['confFREQtx']) != TRUE && empty($_POST['confFREQrx']) != TRUE ) {
	  if (empty($_POST['confHardware']) != TRUE ) { $confHardware = escapeshellcmd($_POST['confHardware']); }
	  $newConfFREQtx = preg_replace('/[^0-9\.]/', '', $_POST['confFREQtx']);
	  $newConfFREQrx = preg_replace('/[^0-9\.]/', '', $_POST['confFREQrx']);
	  $newFREQtx = str_pad(str_replace(".", "", $newConfFREQtx), 9, "0");
	  $newFREQtx = mb_strimwidth($newFREQtx, 0, 9);
	  $newFREQrx = str_pad(str_replace(".", "", $newConfFREQrx), 9, "0");
	  $newFREQrx = mb_strimwidth($newFREQrx, 0, 9);
	  $newFREQirc = substr_replace($newFREQtx, '.', '3', 0);
	  $newFREQirc = mb_strimwidth($newFREQirc, 0, 9);
	  $rollFREQirc = 'sudo sed -i "/frequency1=/c\\frequency1='.$newFREQirc.'" /etc/ircddbgateway';
	  $rollFREQdvap = 'sudo sed -i "/dvapFrequency=/c\\dvapFrequency='.$newFREQrx.'" /etc/dstarrepeater';
	  $rollFREQdvmegaRx = 'sudo sed -i "/dvmegaRXFrequency=/c\\dvmegaRXFrequency='.$newFREQrx.'" /etc/dstarrepeater';
	  $rollFREQdvmegaTx = 'sudo sed -i "/dvmegaTXFrequency=/c\\dvmegaTXFrequency='.$newFREQtx.'" /etc/dstarrepeater';
	  $configmmdvm['Info']['RXFrequency'] = $newFREQrx;
	  $configmmdvm['Info']['TXFrequency'] = $newFREQtx;
	  $configysfgateway['Info']['RXFrequency'] = $newFREQrx;
	  $configysfgateway['Info']['TXFrequency'] = $newFREQtx;

	  system($rollFREQirc);
	  system($rollFREQdvap);
	  system($rollFREQdvmegaRx);
	  system($rollFREQdvmegaTx);

	// Set RPT1 and RPT2
	  if (empty($_POST['confDStarModuleSuffix'])) {
	    if ($newFREQtx >= 1240000000 && $newFREQtx <= 1300000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQtx >= 420000000 && $newFREQtx <= 450000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."B";
		$confIRCrepeaterBand1 = "B";
		$configmmdvm['D-Star']['Module'] = "B";
		$rollTimeserverBand = 'sudo sed -i "/sendB=/c\\sendB=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQtx >= 218000000 && $newFREQtx <= 226000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQtx >= 144000000 && $newFREQtx <= 148000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."C";
		$confIRCrepeaterBand1 = "C";
		$configmmdvm['D-Star']['Module'] = "C";
		$rollTimeserverBand = 'sudo sed -i "/sendC=/c\\sendC=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	  }
	  else {
	     $confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ").strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $confIRCrepeaterBand1 = strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $configmmdvm['D-Star']['Module'] = strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $rollTimeserverBand = 'sudo sed -i "/send'.strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix'])).'=/c\\send'.strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix'])).'=1" /etc/timeserver';
	     system($rollTimeserverBand);
	  }

	  $newCallsignUpper = strtoupper(escapeshellcmd($_POST['confCallsign']));
	  $confRPT2 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."G";

	  $confRPT1 = strtoupper($confRPT1);
	  $confRPT2 = strtoupper($confRPT2);

	  $rollRPT1 = 'sudo sed -i "/callsign=/c\\callsign='.$confRPT1.'" /etc/dstarrepeater';
	  $rollRPT2 = 'sudo sed -i "/gateway=/c\\gateway='.$confRPT2.'" /etc/dstarrepeater';
	  $rollBEACONTEXT = 'sudo sed -i "/beaconText=/c\\beaconText='.$confRPT1.'" /etc/dstarrepeater';
	  $rollIRCrepeaterBand1 = 'sudo sed -i "/repeaterBand1=/c\\repeaterBand1='.$confIRCrepeaterBand1.'" /etc/ircddbgateway';
	  $rollIRCrepeaterCall1 = 'sudo sed -i "/repeaterCall1=/c\\repeaterCall1='.$newCallsignUpper.'" /etc/ircddbgateway';

	  system($rollRPT1);
	  system($rollRPT2);
	  system($rollBEACONTEXT);
	  system($rollIRCrepeaterBand1);
	  system($rollIRCrepeaterCall1);
	}

	// Set the Frequency for Simplex
	if (empty($_POST['confFREQ']) != TRUE ) {
	  if (empty($_POST['confHardware']) != TRUE ) { $confHardware = escapeshellcmd($_POST['confHardware']); }
	  $newConfFREQ = preg_replace('/[^0-9\.]/', '', $_POST['confFREQ']);
	  $newFREQ = str_pad(str_replace(".", "", $newConfFREQ), 9, "0");
	  $newFREQ = mb_strimwidth($newFREQ, 0, 9);
	  $newFREQirc = substr_replace($newFREQ, '.', '3', 0);
	  $newFREQirc = mb_strimwidth($newFREQirc, 0, 9);
	  $rollFREQirc = 'sudo sed -i "/frequency1=/c\\frequency1='.$newFREQirc.'" /etc/ircddbgateway';
	  $rollFREQdvap = 'sudo sed -i "/dvapFrequency=/c\\dvapFrequency='.$newFREQ.'" /etc/dstarrepeater';
	  $rollFREQdvmegaRx = 'sudo sed -i "/dvmegaRXFrequency=/c\\dvmegaRXFrequency='.$newFREQ.'" /etc/dstarrepeater';
	  $rollFREQdvmegaTx = 'sudo sed -i "/dvmegaTXFrequency=/c\\dvmegaTXFrequency='.$newFREQ.'" /etc/dstarrepeater';
	  $configmmdvm['Info']['RXFrequency'] = $newFREQ;
	  $configmmdvm['Info']['TXFrequency'] = $newFREQ;
	  $configysfgateway['Info']['RXFrequency'] = $newFREQ;
	  $configysfgateway['Info']['TXFrequency'] = $newFREQ;

	  system($rollFREQirc);
	  system($rollFREQdvap);
	  system($rollFREQdvmegaRx);
	  system($rollFREQdvmegaTx);

	// Set RPT1 and RPT2
	  if (empty($_POST['confDStarModuleSuffix'])) {
	    if ($newFREQ >= 1240000000 && $newFREQ <= 1300000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQ >= 420000000 && $newFREQ <= 450000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."B";
		$confIRCrepeaterBand1 = "B";
		$configmmdvm['D-Star']['Module'] = "B";
		$rollTimeserverBand = 'sudo sed -i "/sendB=/c\\sendB=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQ >= 218000000 && $newFREQ <= 226000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	    if ($newFREQ >= 144000000 && $newFREQ <= 148000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."C";
		$confIRCrepeaterBand1 = "C";
		$configmmdvm['D-Star']['Module'] = "C";
		$rollTimeserverBand = 'sudo sed -i "/sendA=/c\\sendA=1" /etc/timeserver';
		system($rollTimeserverBand);
		}
	  }
	  else {
	     $confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ").strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $confIRCrepeaterBand1 = strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $configmmdvm['D-Star']['Module'] = strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix']));
	     $rollTimeserverBand = 'sudo sed -i "/send'.strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix'])).'=/c\\send'.strtoupper(escapeshellcmd($_POST['confDStarModuleSuffix'])).'=1" /etc/timeserver';
	     system($rollTimeserverBand);
	  }

	  $newCallsignUpper = strtoupper(escapeshellcmd($_POST['confCallsign']));
	  $confRPT2 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."G";

	  $confRPT1 = strtoupper($confRPT1);
	  $confRPT2 = strtoupper($confRPT2);

	  $rollRPT1 = 'sudo sed -i "/callsign=/c\\callsign='.$confRPT1.'" /etc/dstarrepeater';
	  $rollRPT2 = 'sudo sed -i "/gateway=/c\\gateway='.$confRPT2.'" /etc/dstarrepeater';
	  $rollBEACONTEXT = 'sudo sed -i "/beaconText=/c\\beaconText='.$confRPT1.'" /etc/dstarrepeater';
	  $rollIRCrepeaterBand1 = 'sudo sed -i "/repeaterBand1=/c\\repeaterBand1='.$confIRCrepeaterBand1.'" /etc/ircddbgateway';
	  $rollIRCrepeaterCall1 = 'sudo sed -i "/repeaterCall1=/c\\repeaterCall1='.$newCallsignUpper.'" /etc/ircddbgateway';

	  system($rollRPT1);
	  system($rollRPT2);
	  system($rollBEACONTEXT);
	  system($rollIRCrepeaterBand1);
	  system($rollIRCrepeaterCall1);
	  }

	// Set Callsign
	if (empty($_POST['confCallsign']) != TRUE ) {
	  $newCallsignUpper = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $_POST['confCallsign']));
	  if (preg_match("/^[0-9]/", $newCallsignUpper)) { $newCallsignUpperIRC = 'r'.$newCallsignUpper; } else { $newCallsignUpperIRC = $newCallsignUpper; }

	  $rollGATECALL = 'sudo sed -i "/gatewayCallsign=/c\\gatewayCallsign='.$newCallsignUpper.'" /etc/ircddbgateway';
	  $rollIRCUSER = 'sudo sed -i "/ircddbUsername=/c\\ircddbUsername='.$newCallsignUpperIRC.'" /etc/ircddbgateway';
	  $rollDPLUSLOGIN = 'sudo sed -i "/dplusLogin=/c\\dplusLogin='.$newCallsignUpper.'" /etc/ircddbgateway';
	  $rollDASHBOARDcall = 'sudo sed -i "/callsign=/c\\$callsign=\''.$newCallsignUpper.'\';" /var/www/dashboard/config/ircddblocal.php';
	  $rollTIMESERVERcall = 'sudo sed -i "/callsign=/c\\callsign='.$newCallsignUpper.'" /etc/timeserver';
	  $rollSTARNETSERVERcall = 'sudo sed -i "/callsign=/c\\callsign='.$newCallsignUpper.'" /etc/starnetserver';
	  $rollSTARNETSERVERirc = 'sudo sed -i "/ircddbUsername=/c\\ircddbUsername='.$newCallsignUpperIRC.'" /etc/starnetserver';
	  $rollP25GATEWAY = 'sudo sed -i "/Callsign=/c\\Callsign='.$newCallsignUpper.'" /etc/p25gateway';

	  $configmmdvm['General']['Callsign'] = $newCallsignUpper;
	  $configysfgateway['General']['Callsign'] = $newCallsignUpper;
	  $configysfgateway['aprs.fi']['Password'] = aprspass($newCallsignUpper);

	  system($rollGATECALL);
	  system($rollIRCUSER);
	  system($rollDPLUSLOGIN);
	  system($rollDASHBOARDcall);
	  system($rollTIMESERVERcall);
	  system($rollSTARNETSERVERcall);
	  system($rollSTARNETSERVERirc);
	  system($rollP25GATEWAY);

	}

	// Set the P25 Startup Host
	if (empty($_POST['p25StartupHost']) != TRUE ) {
          $newP25StartupHost = strtoupper(escapeshellcmd($_POST['p25StartupHost']));
          if ($newP25StartupHost === "NONE") { $rollP25Startup = 'sudo sed -i "/Startup=/c\\#Startup=" /etc/p25gateway'; }
          else { $rollP25Startup = 'sudo sed -i "/Startup=/c\\Startup='.$newP25StartupHost.'" /etc/p25gateway'; }
          system($rollP25Startup);
	}

	// Set P25 NAC
	if (empty($_POST['p25nac']) != TRUE ) {
	  $p25nacNew = strtolower(escapeshellcmd($_POST['p25nac']));
	  if (preg_match('/[a-f0-9]{3}/', $p25nacNew)) {
	    $configmmdvm['P25']['NAC'] = $p25nacNew;
	  }
	}

	// Set the YSF Startup Host
	if (empty($_POST['ysfStartupHost']) != TRUE ) {
	  $newYSFStartupHost = strtoupper(escapeshellcmd($_POST['ysfStartupHost']));
	  if ($newYSFStartupHost == "NONE") { unset($configysfgateway['Network']['Startup']); }
	  else { $configysfgateway['Network']['Startup'] = $newYSFStartupHost; }
	}

	// Set Duplex
	if (empty($_POST['trxMode']) != TRUE ) {
	  if ($configmmdvm['Info']['RXFrequency'] === $configmmdvm['Info']['TXFrequency'] && $_POST['trxMode'] == "DUPLEX" ) {
	    $configmmdvm['Info']['RXFrequency'] = $configmmdvm['Info']['TXFrequency'] - 1;
	    }
	  if ($configmmdvm['Info']['RXFrequency'] !== $configmmdvm['Info']['TXFrequency'] && $_POST['trxMode'] == "SIMPLEX" ) {
	    $configmmdvm['Info']['RXFrequency'] = $configmmdvm['Info']['TXFrequency'];
	    }
	  if ($_POST['trxMode'] == "DUPLEX") {
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['DMR Network']['Slot1'] = '1';
	    $configmmdvm['DMR Network']['Slot2'] = '1';  
	  }
	  if ($_POST['trxMode'] == "SIMPLEX") {
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = '0';
	    $configmmdvm['DMR Network']['Slot2'] = '1';
	  }
	}

	// Set DMR / CCS7 ID
	if (empty($_POST['dmrId']) != TRUE ) {
	  $newPostDmrId = preg_replace('/[^0-9]/', '', $_POST['dmrId']);
	  $configmmdvm['DMR']['Id'] = $newPostDmrId;
	  $configmmdvm['General']['Id'] = $newPostDmrId;
	  $configdmrgateway['XLX Network 1']['Id'] = substr($newPostDmrId,0,7);
	}

	// Set DMR Master Server
	if (empty($_POST['dmrMasterHost']) != TRUE ) {
	  $dmrMasterHostArr = explode(',', escapeshellcmd($_POST['dmrMasterHost']));
	  $configmmdvm['DMR Network']['Address'] = $dmrMasterHostArr[0];
	  $configmmdvm['DMR Network']['Password'] = $dmrMasterHostArr[1];
	  $configmmdvm['DMR Network']['Port'] = $dmrMasterHostArr[2];

		if (substr($dmrMasterHostArr[3], 0, 2) == "BM") {
			unset ($configmmdvm['DMR Network']['Options']);
			$configdmrgateway['DMR Network 2']['Options'] = "";
			unset ($configmmdvm['DMR Network']['Local']);
		}

		if ($dmrMasterHostArr[0] == '127.0.0.1') {
			unset ($configmmdvm['DMR Network']['Options']);
			$configdmrgateway['DMR Network 2']['Options'] = "";
			$configmmdvm['DMR Network']['Local'] = "62032";
		}

		// Set the DMR+ Options= line
		if (substr($dmrMasterHostArr[3], 0, 4) == "DMR+") {
			unset ($configmmdvm['DMR Network']['Local']);
			if (empty($_POST['dmrNetworkOptions']) != TRUE ) {
				$dmrOptionsLineStripped = str_replace('"', "", $_POST['dmrNetworkOptions']);
				$configmmdvm['DMR Network']['Options'] = '"'.$dmrOptionsLineStripped.'"';
				$configdmrgateway['DMR Network 2']['Options'] = "";
			}
			else {
				unset ($configmmdvm['DMR Network']['Options']);
				$configdmrgateway['DMR Network 2']['Options'] = "";
			}
		}
	}
	if (empty($_POST['dmrMasterHost']) == TRUE ) {
		unset ($configmmdvm['DMR Network']['Options']);
		$configdmrgateway['DMR Network 2']['Options'] = "";
	}
	if (empty($_POST['dmrMasterHost1']) != TRUE ) {
	  $dmrMasterHostArr1 = explode(',', escapeshellcmd($_POST['dmrMasterHost1']));
	  $configdmrgateway['DMR Network 1']['Address'] = $dmrMasterHostArr1[0];
	  $configdmrgateway['DMR Network 1']['Password'] = $dmrMasterHostArr1[1];
	  $configdmrgateway['DMR Network 1']['Port'] = $dmrMasterHostArr1[2];
	}
	if (empty($_POST['dmrMasterHost2']) != TRUE ) {
	  $dmrMasterHostArr2 = explode(',', escapeshellcmd($_POST['dmrMasterHost2']));
	  $configdmrgateway['DMR Network 2']['Address'] = $dmrMasterHostArr2[0];
	  $configdmrgateway['DMR Network 2']['Password'] = $dmrMasterHostArr2[1];
	  $configdmrgateway['DMR Network 2']['Port'] = $dmrMasterHostArr2[2];
	  if (empty($_POST['dmrNetworkOptions']) != TRUE ) {
	    $dmrOptionsLineStripped = str_replace('"', "", $_POST['dmrNetworkOptions']);
	    $configdmrgateway['DMR Network 2']['Options'] = '"'.$dmrOptionsLineStripped.'"';
	  }
	  else { 
		$configdmrgateway['DMR Network 2']['Options'] = "";
	       }
	}
	if (empty($_POST['dmrMasterHost3']) != TRUE ) {
	  $dmrMasterHostArr3 = explode(',', escapeshellcmd($_POST['dmrMasterHost3']));
	  $configdmrgateway['XLX Network 1']['Address'] = $dmrMasterHostArr3[0];
	  $configdmrgateway['XLX Network 1']['Password'] = $dmrMasterHostArr3[1];
	  $configdmrgateway['XLX Network 1']['Port'] = $dmrMasterHostArr3[2];
	}

	// Set Talker Alias Option
	if (empty($_POST['dmrEmbeddedLCOnly']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrEmbeddedLCOnly']) == 'ON' ) { $configmmdvm['DMR']['EmbeddedLCOnly'] = "1"; }
	  if (escapeshellcmd($_POST['dmrEmbeddedLCOnly']) == 'OFF' ) { $configmmdvm['DMR']['EmbeddedLCOnly'] = "0"; }
	}

	// Set Dump TA Data Option for GPS support
	if (empty($_POST['dmrDumpTAData']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrDumpTAData']) == 'ON' ) { $configmmdvm['DMR']['DumpTAData'] = "1"; }
	  if (escapeshellcmd($_POST['dmrDumpTAData']) == 'OFF' ) { $configmmdvm['DMR']['DumpTAData'] = "0"; }
	}

	// Set the XLX DMRGateway Master On or Off 
	if (empty($_POST['dmrGatewayXlxEn']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrGatewayXlxEn']) == 'ON' ) { $configdmrgateway['XLX Network 1']['Enabled'] = "1"; }
	  if (escapeshellcmd($_POST['dmrGatewayXlxEn']) == 'OFF' ) { $configdmrgateway['XLX Network 1']['Enabled'] = "0"; }
	}

	// Remove old settings
	if (isset($configmmdvm['General']['ModeHang'])) { unset($configmmdvm['General']['ModeHang']); }
	if (isset($configmmdvm['General']['RFModeHang'])) { $configmmdvm['General']['RFModeHang'] = 300; }
	if (isset($configmmdvm['General']['NetModeHang'])) { $configmmdvm['General']['NetModeHang'] = 300; }
	
	// Set DMR Hang Timers
	if (empty($_POST['dmrRfHangTime']) != TRUE ) {
	  $configmmdvm['DMR']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dmrRfHangTime']);
	}
	if (empty($_POST['dmrNetHangTime']) != TRUE ) {
	  $configmmdvm['DMR Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dmrNetHangTime']);
	  $configdmrgateway['General']['Timeout'] = preg_replace('/[^0-9]/', '', $_POST['dmrNetHangTime']);
	}
	// Set D-Star Hang Timers
	if (empty($_POST['dstarRfHangTime']) != TRUE ) {
	  $configmmdvm['D-Star']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dstarRfHangTime']);
	}
	if (empty($_POST['dstarNetHangTime']) != TRUE ) {
	  $configmmdvm['D-Star Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dstarNetHangTime']);
	}
	// Set YSF Hang Timers
	if (empty($_POST['ysfRfHangTime']) != TRUE ) {
	  $configmmdvm['System Fusion']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['ysfRfHangTime']);
	}
	if (empty($_POST['ysfNetHangTime']) != TRUE ) {
	  $configmmdvm['System Fusion Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['ysfNetHangTime']);
	}
	// Set P25 Hang Timers
	if (empty($_POST['dmrRfHangTime']) != TRUE ) {
	  $configmmdvm['P25']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['p25RfHangTime']);
	}
	if (empty($_POST['dmrNetHangTime']) != TRUE ) {
	  $configmmdvm['P25 Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['p25NetHangTime']);
	}

	// Set the hardware type
	if (empty($_POST['confHardware']) != TRUE ) {
	$confHardware = escapeshellcmd($_POST['confHardware']);
	$configModem['Modem']['Hardware'] = $confHardware;

	  if ( $confHardware == 'dvmpis' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=2" /etc/dstarrepeater';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	  }

	  if ( $confHardware == 'dvmpid' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	  }

	  if ( $confHardware == 'dvmuad' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyACM0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	  }

	  if ( $confHardware == 'dvmbss' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=2" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	  }

	  if ( $confHardware == 'dvmbsd' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	  }

	  if ( $confHardware == 'dvmuagmsk' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	  }

	  if ( $confHardware == 'dvmuagmsko' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyACM0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	  }

	  if ( $confHardware == 'dvrptr1' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V1" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    system($rollModemType);
	    system($rollDVRPTRPort);
	  }

	  if ( $confHardware == 'dvrptr2' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V2" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    system($rollModemType);
	    system($rollDVRPTRPort);
	  }

	  if ( $confHardware == 'dvrptr3' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V3" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    system($rollModemType);
	    system($rollDVRPTRPort);
	  }

	  if ( $confHardware == 'gmsk_modem' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=GMSK Modem" /etc/dstarrepeater';
	    system($rollModemType);
	  }

	  if ( $confHardware == 'dvap' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVAP" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    system($rollModemType);
	  }

	  if ( $confHardware == 'zumspotlibre' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    system($rollModemType);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	  }

	  if ( $confHardware == 'zumspotusb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    system($rollModemType);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	  }

	  if ( $confHardware == 'zumspotgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    system($rollModemType);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	  }

	  if ( $confHardware == 'zumradiopigpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    system($rollModemType);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	  }

	  if ( $confHardware == 'zum' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    system($rollModemType);
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	  }

	  if ( $confHardware == 'stm32dvm' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    system($rollModemType);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	  }

	  if ( $confHardware == 'stm32usb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    system($rollModemType);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	  }
	}

	// Set the Dashboard Public
	if (empty($_POST['dashAccess']) != TRUE ) {
	  $publicDashboard = 'sudo sed -i \'/$DAEMON -a $ipVar 80/c\\\t\t$DAEMON -a $ipVar 80 80 TCP > /dev/null 2>&1 &\' /usr/local/sbin/pistar-upnp.service';
	  $privateDashboard = 'sudo sed -i \'/$DAEMON -a $ipVar 80/ s/^#*/#/\' /usr/local/sbin/pistar-upnp.service';

	  if (escapeshellcmd($_POST['dashAccess']) == 'PUB' ) { system($publicDashboard); }
	  if (escapeshellcmd($_POST['dashAccess']) == 'PRV' ) { system($privateDashboard); }
	}

	// Set the ircDDBGateway Remote Public
	if (empty($_POST['ircRCAccess']) != TRUE ) {
	  $publicRCirc = 'sudo sed -i \'/$DAEMON -a $ipVar 10022/c\\\t\t$DAEMON -a $ipVar 10022 10022 UDP > /dev/null 2>&1 &\' /usr/local/sbin/pistar-upnp.service';
	  $privateRCirc = 'sudo sed -i \'/$DAEMON -a $ipVar 10022/ s/^#*/#/\' /usr/local/sbin/pistar-upnp.service';

	  if (escapeshellcmd($_POST['ircRCAccess']) == 'PUB' ) { system($publicRCirc); }
	  if (escapeshellcmd($_POST['ircRCAccess']) == 'PRV' ) { system($privateRCirc); }
	}

	// Set SSH Access Public
	if (empty($_POST['sshAccess']) != TRUE ) {
	  $publicSSH = 'sudo sed -i \'/$DAEMON -a $ipVar 22/c\\\t\t$DAEMON -a $ipVar 22 22 TCP > /dev/null 2>&1 &\' /usr/local/sbin/pistar-upnp.service';
	  $privateSSH = 'sudo sed -i \'/$DAEMON -a $ipVar 22/ s/^#*/#/\' /usr/local/sbin/pistar-upnp.service';

	  if (escapeshellcmd($_POST['sshAccess']) == 'PUB' ) { system($publicSSH); }
	  if (escapeshellcmd($_POST['sshAccess']) == 'PRV' ) { system($privateSSH); }
	}

	// D-Star Time Announce
	if (empty($_POST['confTimeAnnounce']) != TRUE ) {
	  if (escapeshellcmd($_POST['confTimeAnnounce']) == 'ON' )  { system('sudo rm -rf /etc/timeserver.dissable'); }
	  if (escapeshellcmd($_POST['confTimeAnnounce']) == 'OFF' )  { system('sudo touch /etc/timeserver.dissable'); }
	}
	
	// Set MMDVMHost DMR Mode
	if (empty($_POST['MMDVMModeDMR']) != TRUE ) {
	  if (escapeshellcmd($_POST['MMDVMModeDMR']) == 'ON' )  { $configmmdvm['DMR']['Enable'] = "1"; $configmmdvm['DMR Network']['Enable'] = "1"; }
	  if (escapeshellcmd($_POST['MMDVMModeDMR']) == 'OFF' ) { $configmmdvm['DMR']['Enable'] = "0"; $configmmdvm['DMR Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost D-Star Mode
	if (empty($_POST['MMDVMModeDSTAR']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDSTAR']) == 'ON' )  { $configmmdvm['D-Star']['Enable'] = "1"; $configmmdvm['D-Star Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeDSTAR']) == 'OFF' ) { $configmmdvm['D-Star']['Enable'] = "0"; $configmmdvm['D-Star Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost Fusion Mode
	if (empty($_POST['MMDVMModeFUSION']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeFUSION']) == 'ON' )  { $configmmdvm['System Fusion']['Enable'] = "1"; $configmmdvm['System Fusion Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeFUSION']) == 'OFF' ) { $configmmdvm['System Fusion']['Enable'] = "0"; $configmmdvm['System Fusion Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost P25 Mode
	if (empty($_POST['MMDVMModeP25']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeP25']) == 'ON' )  { $configmmdvm['P25']['Enable'] = "1"; $configmmdvm['P25 Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeP25']) == 'OFF' ) { $configmmdvm['P25']['Enable'] = "0"; $configmmdvm['P25 Network']['Enable'] = "0"; }
	}

	// Set the MMDVMHost Display Type
	if  (empty($_POST['mmdvmDisplayType']) != TRUE ) {
	  $configmmdvm['General']['Display'] = escapeshellcmd($_POST['mmdvmDisplayType']);
	}

	// Set the MMDVMHost Display Type
	if  (empty($_POST['mmdvmDisplayPort']) != TRUE ) {
	  $configmmdvm['TFT Serial']['Port'] = $_POST['mmdvmDisplayPort'];
	  $configmmdvm['Nextion']['Port'] = $_POST['mmdvmDisplayPort'];
	}

	// Set MMDVMHost DMR Colour Code
	if (empty($_POST['dmrColorCode']) != TRUE ) {
          $configmmdvm['DMR']['ColorCode'] = escapeshellcmd($_POST['dmrColorCode']);
	}

	// Set Node Lock Status
	if (empty($_POST['nodeMode']) != TRUE ) {
	  if (escapeshellcmd($_POST['nodeMode']) == 'prv' ) {
            $configmmdvm['DMR']['SelfOnly'] = 1;
            $configmmdvm['D-Star']['SelfOnly'] = 1;
	    $configmmdvm['P25']['SelfOnly'] = 1;
            system('sudo sed -i "/restriction=/c\\restriction=1" /etc/dstarrepeater');
          }
	  if (escapeshellcmd($_POST['nodeMode']) == 'pub' ) {
            $configmmdvm['DMR']['SelfOnly'] = 0;
            $configmmdvm['D-Star']['SelfOnly'] = 0;
	    $configmmdvm['P25']['SelfOnly'] = 0;
            system('sudo sed -i "/restriction=/c\\restriction=0" /etc/dstarrepeater');
          }
	}

	// Set the Hostname
	if (empty($_POST['confHostame']) != TRUE ) {
	  $newHostnameLower = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['confHostame']));
	  $currHostname = exec('cat /etc/hostname');
	  $rollHostname = 'sudo sed -i "s/'.$currHostname.'/'.$newHostnameLower.'/" /etc/hostname';
	  $rollHosts = 'sudo sed -i "s/'.$currHostname.'/'.$newHostnameLower.'/" /etc/hosts';
	  system($rollHostname);
	  system($rollHosts);
	}

	// Continue Page Output
	echo "<br />";
	echo "<table>\n";
	echo "<tr><th>Done...</th></tr>\n";
	echo "<tr><td>Changes applied, starting services...</td></tr>\n";
	echo "</table>\n";

	// MMDVMHost config file wrangling
	$mmdvmContent = "";
	foreach($configmmdvm as $mmdvmSection=>$mmdvmValues) {
		// UnBreak special cases
		$mmdvmSection = str_replace("_", " ", $mmdvmSection);
		$mmdvmContent .= "[".$mmdvmSection."]\n";
                // append the values
                foreach($mmdvmValues as $mmdvmKey=>$mmdvmValue) {
			$mmdvmContent .= $mmdvmKey."=".$mmdvmValue."\n";
			}
			$mmdvmContent .= "\n";
		}

	if (!$handleMMDVMHostConfig = fopen('/tmp/bW1kdm1ob3N0DQo.tmp', 'w')) {
		return false;
	}
	if (!is_writable('/tmp/bW1kdm1ob3N0DQo.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
	}
	else {
		$success = fwrite($handleMMDVMHostConfig, $mmdvmContent);
		fclose($handleMMDVMHostConfig);
		if (intval(exec('cat /tmp/bW1kdm1ob3N0DQo.tmp | wc -l')) > 140 ) {
			exec('sudo mv /tmp/bW1kdm1ob3N0DQo.tmp /etc/mmdvmhost');		// Move the file back
			exec('sudo chmod 644 /etc/mmdvmhost');					// Set the correct runtime permissions
			exec('sudo chown root:root /etc/mmdvmhost');				// Set the owner
		}
	}

        // ysfgateway config file wrangling
	$ysfgwContent = "";
        foreach($configysfgateway as $ysfgwSection=>$ysfgwValues) {
                // UnBreak special cases
                $ysfgwSection = str_replace("_", " ", $ysfgwSection);
                $ysfgwContent .= "[".$ysfgwSection."]\n";
                // append the values
                foreach($ysfgwValues as $ysfgwKey=>$ysfgwValue) {
                        $ysfgwContent .= $ysfgwKey."=".$ysfgwValue."\n";
                        }
                        $ysfgwContent .= "\n";
                }

        if (!$handleYSFGWconfig = fopen('/tmp/eXNmZ2F0ZXdheQ.tmp', 'w')) {
                return false;
        }

	if (!is_writable('/tmp/eXNmZ2F0ZXdheQ.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
	}
	else {
	        $success = fwrite($handleYSFGWconfig, $ysfgwContent);
	        fclose($handleYSFGWconfig);
		if (intval(exec('cat /tmp/eXNmZ2F0ZXdheQ.tmp | wc -l')) > 35 ) {
			exec('sudo mv /tmp/eXNmZ2F0ZXdheQ.tmp /etc/ysfgateway');		// Move the file back
			exec('sudo chmod 644 /etc/ysfgateway');					// Set the correct runtime permissions
			exec('sudo chown root:root /etc/ysfgateway');				// Set the owner
		}
	}

	// dmrgateway config file wrangling
	$dmrgwContent = "";
        foreach($configdmrgateway as $dmrgwSection=>$dmrgwValues) {
                // UnBreak special cases
                $dmrgwSection = str_replace("_", " ", $dmrgwSection);
                $dmrgwContent .= "[".$dmrgwSection."]\n";
                // append the values
                foreach($dmrgwValues as $dmrgwKey=>$dmrgwValue) {
                        $dmrgwContent .= $dmrgwKey."=".$dmrgwValue."\n";
                        }
                        $dmrgwContent .= "\n";
                }
        if (!$handledmrGWconfig = fopen('/tmp/k4jhdd34jeFr8f.tmp', 'w')) {
                return false;
        }
	if (!is_writable('/tmp/k4jhdd34jeFr8f.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
	}
	else {
	        $success = fwrite($handledmrGWconfig, $dmrgwContent);
	        fclose($handledmrGWconfig);
		if (fopen($dmrGatewayConfigFile,'r')) {
			if (intval(exec('cat /tmp/k4jhdd34jeFr8f.tmp | wc -l')) > 55 ) {
          			exec('sudo mv /tmp/k4jhdd34jeFr8f.tmp /etc/dmrgateway');	// Move the file back
          			exec('sudo chmod 644 /etc/dmrgateway');				// Set the correct runtime permissions
	 			exec('sudo chown root:root /etc/dmrgateway');			// Set the owner
			}
		}
	}

	// modem config file wrangling
        $configModemContent = "";
        foreach($configModem as $configModemSection=>$configModemValues) {
                // UnBreak special cases
                $configModemSection = str_replace("_", " ", $configModemSection);
                $configModemContent .= "[".$configModemSection."]\n";
                // append the values
                foreach($configModemValues as $modemKey=>$modemValue) {
                        $configModemContent .= $modemKey."=".$modemValue."\n";
                        }
                        $configModemContent .= "\n";
                }

        if (!$handleModemConfig = fopen('/tmp/sja7hFRkw4euG7.tmp', 'w')) {
                return false;
        }

        if (!is_writable('/tmp/sja7hFRkw4euG7.tmp')) {
          echo "<br />\n";
          echo "<table>\n";
          echo "<tr><th>ERROR</th></tr>\n";
          echo "<tr><td>Unable to write configuration file(s)...</td><tr>\n";
          echo "<tr><td>Please wait a few seconds and retry...</td></tr>\n";
          echo "</table>\n";
          unset($_POST);
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
          die();
        }
	else {
                $success = fwrite($handleModemConfig, $configModemContent);
                fclose($handleModemConfig);
		if (file_exists('/etc/dstar-radio.dstarrepeater')) {
                    if (fopen($modemConfigFileDStarRepeater,'r')) {
                        exec('sudo mv /tmp/sja7hFRkw4euG7.tmp '.$modemConfigFileDStarRepeater);	// Move the file back
                        exec('sudo chmod 644 $modemConfigFileDStarRepeater');			// Set the correct runtime permissions
                        exec('sudo chown root:root $modemConfigFileDStarRepeater');			// Set the owner
                    }
		}
		if (file_exists('/etc/dstar-radio.mmdvmhost')) {
                    if (fopen($modemConfigFileMMDVMHost,'r')) {
                        exec('sudo mv /tmp/sja7hFRkw4euG7.tmp '.$modemConfigFileMMDVMHost);		// Move the file back
                        exec('sudo chmod 644 $modemConfigFileMMDVMHost');				// Set the correct runtime permissions
                        exec('sudo chown root:root $modemConfigFileMMDVMHost');			// Set the owner
                    }
		}
        }

	// Start the DV Services
	system('sudo systemctl daemon-reload > /dev/null 2>/dev/null &');			// Restart Systemd to account for any service changes
	system('sudo systemctl start dstarrepeater.service > /dev/null 2>/dev/null &');		// D-Star Radio Service
	system('sudo systemctl start mmdvmhost.service > /dev/null 2>/dev/null &');		// MMDVMHost Radio Service
	system('sudo systemctl start ircddbgateway.service > /dev/null 2>/dev/null &');		// ircDDBGateway Service
	system('sudo systemctl start timeserver.service > /dev/null 2>/dev/null &');		// Time Server Service
	system('sudo systemctl start pistar-watchdog.service > /dev/null 2>/dev/null &');	// PiStar-Watchdog Service
	system('sudo systemctl start pistar-remote.service > /dev/null 2>/dev/null &');		// PiStar-Remote Service
	system('sudo systemctl start pistar-upnp.service > /dev/null 2>/dev/null &');		// PiStar-UPnP Service
	system('sudo systemctl start ysfgateway.service > /dev/null 2>/dev/null &');		// YSFGateway
	system('sudo systemctl start ysfparrot.service > /dev/null 2>/dev/null &');		// YSFParrot
	system('sudo systemctl start p25gateway.service > /dev/null 2>/dev/null &');		// P25Gateway
	system('sudo systemctl start p25parrot.service > /dev/null 2>/dev/null &');		// P25Parrot
	system('sudo systemctl start dmrgateway.service > /dev/null 2>/dev/null &');		// DMRGateway

	// Set the system timezone
	$rollTimeZone = 'sudo timedatectl set-timezone '.escapeshellcmd($_POST['systemTimezone']);
	system($rollTimeZone);
	$rollTimeZoneConfig = 'sudo sed -i "/date_default_timezone_set/c\\date_default_timezone_set(\''.escapeshellcmd($_POST['systemTimezone']).'\')\;" /var/www/dashboard/config/config.php';   
	system($rollTimeZoneConfig);

	// Start Cron (occasionally remounts root as RO - would be bad if it did this at the wrong time....)
	system('sudo systemctl start cron.service > /dev/null 2>/dev/null &');			//Cron

	unset($_POST);
	echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},7500);</script>';

	// Make the root filesystem read-only
	system('sudo mount -o remount,ro /');

else:
	// Output the HTML Form here
	if ((file_exists('/etc/dstar-radio.mmdvmhost') || file_exists('/etc/dstar-radio.dstarrepeater')) && !$configModem['Modem']['Hardware']) { echo "<script type\"text/javascript\">\n\talert(\"WARNING:\\nThe Modem selection section has been updated,\\nPlease re-select your modem from the list.\")\n</script>\n"; }
?>
<form id="factoryReset" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<div><input type="hidden" name="factoryReset" value="1" /></div>
</form>

<form id="config" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<div><b><?php echo $lang['control_software'];?></b></div>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from<br />the configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['controller_software'];?>:<span><b>Radio Control Software</b>Choose the software used<br />to control the DV Radio Module<br />PLease note that DV Mega hardware<br />will require a firmware upgrade.</span></a></td>
    <?php
	if (file_exists('/etc/dstar-radio.mmdvmhost')) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"controllerSoft\" value=\"DSTAR\" onclick=\"alert('After applying your Configuration Settings, you will need to powercycle your Pi.');\" />DStarRepeater <input type=\"radio\" name=\"controllerSoft\" value=\"MMDVM\" checked=\"checked\" />MMDVMHost (DV-Mega Minimum Firmware 3.07 Required)</td>\n";
		}
	elseif (file_exists('/etc/dstar-radio.dstarrepeater')) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"controllerSoft\" value=\"DSTAR\" checked=\"checked\" />DStarRepeater <input type=\"radio\" name=\"controllerSoft\" value=\"MMDVM\" onclick=\"alert('After applying your Configuration Settings, you will need to powercycle your Pi.');\" />MMDVMHost (DV-Mega Minimum Firmware 3.07 Required)</td>\n";
	}
	else { // Not set - default to MMDVMHost
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"controllerSoft\" value=\"DSTAR\" onclick=\"alert('After applying your Configuration Settings, you will need to powercycle your Pi.');\" />DStarRepeater <input type=\"radio\" name=\"controllerSoft\" value=\"MMDVM\" checked=\"checked\" />MMDVMHost (DV-Mega Minimum Firmware 3.07 Required)</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['controller_mode'];?>:<span><b>TRX Mode</b>Choose the mode type<br />Simplex node or<br />Duplex repeater.</span></a></td>
    <?php
	if ($configmmdvm['Info']['RXFrequency'] === $configmmdvm['Info']['TXFrequency']) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"trxMode\" value=\"SIMPLEX\" checked=\"checked\" />Simplex Node <input type=\"radio\" name=\"trxMode\" value=\"DUPLEX\" />Duplex Repeater</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"trxMode\" value=\"SIMPLEX\" />Simplex Node <input type=\"radio\" name=\"trxMode\" value=\"DUPLEX\" checked=\checked\" />Duplex Repeater</td>\n";
		}
    ?>
    </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php if (file_exists('/etc/dstar-radio.mmdvmhost')) { ?>
    <input type="hidden" name="MMDVMModeDMR" value="OFF" />
    <input type="hidden" name="MMDVMModeDSTAR" value="OFF" />
    <input type="hidden" name="MMDVMModeFUSION" value="OFF" />
    <input type="hidden" name="MMDVMModeP25" value="OFF" />
	<div><b><?php echo $lang['mmdvmhost_config'];?></b></div>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from<br />the configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_mode'];?>:<span><b>DMR Mode</b>Turn on DMR Features</span></a></td>
    <?php
	if ( $configmmdvm['DMR']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmr\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR\" value=\"ON\" /><label for=\"toggle-dmr\"></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="dmrRfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['DMR']['ModeHang'])) { echo $configmmdvm['DMR']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="dmrNetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['DMR Network']['ModeHang'])) { echo $configmmdvm['DMR Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['d-star_mode'];?>:<span><b>D-Star Mode</b>Turn on D-Star Features</span></a></td>
    <?php
	if ( $configmmdvm['D-Star']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dstar\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDSTAR\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dstar\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dstar\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDSTAR\" value=\"ON\" /><label for=\"toggle-dstar\"></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="dstarRfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['D-Star']['ModeHang'])) { echo $configmmdvm['D-Star']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="dstarNetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['D-Star Network']['ModeHang'])) { echo $configmmdvm['D-Star Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['ysf_mode'];?>:<span><b>YSF Mode</b>Turn on YSF Features</span></a></td>
    <?php
	if ( $configmmdvm['System Fusion']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeFUSION\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-ysf\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeFUSION\" value=\"ON\" /><label for=\"toggle-ysf\"></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="ysfRfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['System Fusion']['ModeHang'])) { echo $configmmdvm['System Fusion']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="ysfNetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['System Fusion Network']['ModeHang'])) { echo $configmmdvm['System Fusion Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_mode'];?>:<span><b>P25 Mode</b>Turn on P25 Features</span></a></td>
    <?php
	if ( $configmmdvm['P25']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeP25\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-p25\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeP25\" value=\"ON\" /><label for=\"toggle-p25\"></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="p25RfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['P25']['ModeHang'])) { echo $configmmdvm['P25']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="p25NetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['P25 Network']['ModeHang'])) { echo $configmmdvm['P25 Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['mmdvm_display'];?>:<span><b>Display Type</b>Choose your display<br />type if you have one.</span></a></td>
    <td align="left" colspan="2"><select name="mmdvmDisplayType">
	    <option <?php if (($configmmdvm['General']['Display'] == "None") || ($configmmdvm['General']['Display'] == "") ) {echo 'selected="selected" ';}; ?>value="None">None</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "OLED") {echo 'selected="selected" ';}; ?>value="OLED">OLED</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "Nextion") {echo 'selected="selected" ';}; ?>value="Nextion">Nextion</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "HD44780") {echo 'selected="selected" ';}; ?>value="HD44780">HD44780</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "TFT Serial") {echo 'selected="selected" ';}; ?>value="TFT Serial">TFT Serial</option>
	    </select>
	    Port: <select name="mmdvmDisplayPort">
	    <option <?php if (($configmmdvm['General']['Display'] == "None") || ($configmmdvm['General']['Display'] == "") ) {echo 'selected="selected" ';}; ?>value="None">None</option>
	    <option <?php if ($configmmdvm['Nextion']['Port'] == "modem") {echo 'selected="selected" ';}; ?>value="modem">Modem</option>
	    <option <?php if ($configmmdvm['Nextion']['Port'] == "/dev/ttyAMA0") {echo 'selected="selected" ';}; ?>value="/dev/ttyAMA0">/dev/ttyAMA0</option>
	    <option <?php if ($configmmdvm['Nextion']['Port'] == "/dev/ttyUSB0") {echo 'selected="selected" ';}; ?>value="/dev/ttyUSB0">/dev/ttyUSB0</option>
	    </select>
    </td></tr>
    <!--<tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['mode_hangtime'];?>:<span><b>Net Hang Time</b>Stay in the last mode for<br />this many seconds</span></a></td>
    <td align="left" colspan="2"><input type="text" name="hangTime" size="13" maxlength="3" value="<?php echo $configmmdvm['General']['RFModeHang']; ?>" /> in seconds (90 secs works well for Multi-Mode)</td>
    </tr>-->
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
    <?php } ?>
	<div><b><?php echo $lang['general_config'];?></b></div>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">Hostname:<span><b>System Hostname</b>This is the system<br />hostname, used for access<br />to the dashboard etc.</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confHostame" size="13" maxlength="15" value="<?php echo exec('cat /etc/hostname'); ?>" />Do not add suffixes such as .local</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['node_call'];?>:<span><b>Gateway Callsign</b>This is your licenced callsign for use<br />on this gateway, do not append<br />the "G"</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confCallsign" size="13" maxlength="6" value="<?php echo $configs['gatewayCallsign'] ?>" /></td>
    </tr>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && (($configmmdvm['DMR']['Enable'] == 1) || ($configmmdvm['P25']['Enable'] == 1 ))) {
    $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r"); ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_id'];?>:<span><b>CCS7/DMR ID</b>Enter your CCS7 / DMR ID here</span></a></td>
    <td align="left" colspan="2"><input type="text" name="dmrId" size="13" maxlength="9" value="<?php echo $configmmdvm['DMR']['Id']; ?>" /></td>
    </tr><?php } ?>
<?php if ($configmmdvm['Info']['TXFrequency'] === $configmmdvm['Info']['RXFrequency']) {
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['radio_freq'].":<span><b>Radio Frequency</b>This is the Frequency your<br />Pi-Star is on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" name=\"confFREQ\" size=\"13\" maxlength=\"12\" value=\"".number_format($configmmdvm['Info']['RXFrequency'], 0, '.', '.')."\" />MHz</td>\n";
	echo "    </tr>\n";
	}
	else {
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['radio_freq']." RX:<span><b>Radio Frequency</b>This is the Frequency your<br />repeater will listen on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" name=\"confFREQrx\" size=\"13\" maxlength=\"12\" value=\"".number_format($configmmdvm['Info']['RXFrequency'], 0, '.', '.')."\" />MHz</td>\n";
	echo "    </tr>\n";
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['radio_freq']." TX:<span><b>Radio Frequency</b>This is the Frequency your<br />repeater will transmit on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" name=\"confFREQtx\" size=\"13\" maxlength=\"12\" value=\"".number_format($configmmdvm['Info']['TXFrequency'], 0, '.', '.')."\" />MHz</td>\n";
	echo "    </tr>\n";
	}
?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['lattitude'];?>:<span><b>Gateway Latitude</b>This is the latitude where the<br />gateway is located (positive<br />number for North, negative<br />number for South)</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confLatitude" size="13" maxlength="9" value="<?php echo $configs['latitude'] ?>" />degrees (positive value for North, negative for South)</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['longitude'];?>:<span><b>Gateway Longitude</b>This is the longitude where the<br />gateway is located (positive<br />number for East, negative<br />number for West)</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confLongitude" size="13" maxlength="9" value="<?php echo $configs['longitude'] ?>" />degrees (positive value for East, negative for West)</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['town'];?>:<span><b>Gateway Town</b>The town where the gateway<br />is located</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confDesc1" size="30" maxlength="30" value="<?php echo $configs['description1'] ?>" /></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['country'];?>:<span><b>Gateway Country</b>The country where the gateway<br />is located</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confDesc2" size="30" maxlength="30" value="<?php echo $configs['description2'] ?>" /></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['url'];?>:<span><b>Gateway URL</b>The URL used to access<br />this dashboard</span></a></td>
    <td align="left"><input type="text" name="confURL" size="30" maxlength="30" value="<?php echo $configs['url'] ?>" /></td>
    <td width="300">
    <input type="radio" name="urlAuto" value="auto"<?php if (strpos($configs['url'], 'www.qrz.com/db/'.$configmmdvm['General']['Callsign']) !== FALSE) {echo ' checked="checked"';} ?> />Auto
    <input type="radio" name="urlAuto" value="man"<?php if (strpos($configs['url'], 'www.qrz.com/db/'.$configmmdvm['General']['Callsign']) == FALSE) {echo ' checked="checked"';} ?> />Manual</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['radio_type'];?>:<span><b>Radio/Modem</b>What kind of radio or modem<br />hardware do you have ?</span></a></td>
    <td align="left" colspan="2"><select name="confHardware">
		<option<?php if (!$configModem['Modem']['Hardware']) { echo ' selected="selected"';}?> value="">--</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpis') { echo ' selected="selected"';}?> value="dvmpis">DV-Mega Raspberry Pi Hat (GPIO) - Single Band (70cm)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpid') { echo ' selected="selected"';}?> value="dvmpid">DV-Mega Raspberry Pi Hat (GPIO) - Dual Band</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmuad') { echo ' selected="selected"';}?> value="dvmuad">DV-Mega on Arduino (USB) - Dual Band</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmuagmsko') { echo ' selected="selected"';}?> value="dvmuagmsko">DV-Mega on Arduino (USB) - GMSK Modem (Old Firmware)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmuagmsk') { echo ' selected="selected"';}?> value="dvmuagmsk">DV-Mega on Arduino (USB) - GMSK Modem (New Firmware)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmbss') { echo ' selected="selected"';}?> value="dvmbss">DV-Mega on Bluestack (USB) - Single Band (70cm)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmbsd') { echo ' selected="selected"';}?> value="dvmbsd">DV-Mega on Bluestack (USB) - Dual Band</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'gmsk_modem') { echo ' selected="selected"';}?> value="gmsk_modem">GMSK Modem (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'dvrptr1') { echo ' selected="selected"';}?> value="dvrptr1">DV-RPTR V1 (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvrptr2') { echo ' selected="selected"';}?> value="dvrptr2">DV-RPTR V2 (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvrptr3') { echo ' selected="selected"';}?> value="dvrptr3">DV-RPTR V3 (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvap') { echo ' selected="selected"';}?> value="dvap">DVAP (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zum') { echo ' selected="selected"';}?> value="zum">MMDVM / MMDVM_HS / Teensy / ZUM (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'stm32dvm') { echo ' selected="selected"';}?> value="stm32dvm">STM32-DVM / MMDVM_HS - Raspberry Pi Hat (GPIO)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'stm32usb') { echo ' selected="selected"';}?> value="stm32usb">STM32-DVM (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumspotlibre') { echo ' selected="selected"';}?> value="zumspotlibre">ZumSpot Libre (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zumspotusb') { echo ' selected="selected"';}?> value="zumspotusb">ZumSpot - USB Stick</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zumspotgpio') { echo ' selected="selected"';}?> value="zumspotgpio">ZumSpot - Raspberry Pi Hat (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumradiopigpio') { echo ' selected="selected"';}?> value="zumradiopigpio">ZUM Radio-MMDVM for Pi (GPIO)</option>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['node_type'];?>:<span><b>Node Lock</b>Set the public / private<br />node type. Public should<br />only be used with the correct<br />licence.</span></a></td>
    <td align="left" colspan="2">
    <input type="radio" name="nodeMode" value="prv"<?php if ($configmmdvm['DMR']['SelfOnly'] == 1) {echo ' checked="checked"';} ?> />Private
    <input type="radio" name="nodeMode" value="pub"<?php if ($configmmdvm['DMR']['SelfOnly'] == 0) {echo ' checked="checked"';} ?> />Public</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['timezone'];?>:<span><b>System TimeZone</b>Set the system timezone</span></a></td>
    <td style="text-align: left;" colspan="2"><select name="systemTimezone">
<?php
  exec('timedatectl list-timezones', $tzList);
  exec('cat /etc/timezone', $tzCurrent);
    foreach ($tzList as $timeZone) {
      if ($timeZone == $tzCurrent[0]) { echo "      <option selected=\"selected\" value=\"".$timeZone."\">".$timeZone."</option>\n"; }
      else { echo "      <option value=\"".$timeZone."\">".$timeZone."</option>\n"; }
    }
?>
    </select></td>
    </tr>
<?php
    $lang_dir = './lang';
    if (is_dir($lang_dir)) {
	echo '    <tr>'."\n";
	echo '    <td align="left"><a class="tooltip2" href="#">'.$lang['dash_lang'].':<span><b>Dashboard Language</b>Set the language for<br />the dashboard.</span></a></td>'."\n";
	echo '    <td align="left" colspan="2"><select name="dashboardLanguage">'."\n";
	    if ($dh = opendir($lang_dir)) {
		    while (($file = readdir($dh)) !== false) {
			    if (($file != 'index.php') && ($file != '.') && ($file != '..')) {
				    $file = substr($file, 0, -4);
				    if ($file == $pistarLanguage) { echo "      <option selected=\"selected\" value=\"".$file."\">".$file."</option>\n"; }
				    else { echo "      <option value=\"".$file."\">".$file."</option>\n"; }
			    }
		    }
		    closedir($dh);
	    }
	echo '    </select></td></tr>'."\n";
    }
?>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['DMR']['Enable'] == 1) {
    $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r"); ?>
	<div><b><?php echo $lang['dmr_config'];?></b></div>
    <input type="hidden" name="dmrEmbeddedLCOnly" value="OFF" />
    <input type="hidden" name="dmrDumpTAData" value="OFF" />
    <input type="hidden" name="dmrGatewayXlxEn" value="OFF" />
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_master'];?>:<span><b>DMR Master (MMDVMHost)</b>Set your prefered DMR<br /> master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost">
<?php
        $testMMDVMdmrMaster = $configmmdvm['DMR Network']['Address'];
        while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHost = preg_split('/\s+/', $dmrMasterLine);
                if ((strpos($dmrMasterHost[0], '#') === FALSE ) && (substr($dmrMasterHost[0], 0, 3) != "XLX") && ($dmrMasterHost[0] != '')) {
                        if ($testMMDVMdmrMaster == $dmrMasterHost[2]) { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\" selected=\"selected\">$dmrMasterHost[0]</option>\n"; $dmrMasterNow = $dmrMasterHost[0]; }
                        else { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\">$dmrMasterHost[0]</option>\n"; }
                }
        }
        fclose($dmrMasterFile);
        ?>
    </select></td>
    </tr>
<?php if ($dmrMasterNow == "DMRGateway") { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_master'];?>:<span><b>BrandMeister Master</b>Set your prefered DMR<br /> master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost1">
<?php
	$dmrMasterFile1 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	$testMMDVMdmrMaster1 = $configdmrgateway['DMR Network 1']['Address'];
	while (!feof($dmrMasterFile1)) {
		$dmrMasterLine1 = fgets($dmrMasterFile1);
                $dmrMasterHost1 = preg_split('/\s+/', $dmrMasterLine1);
                if ((strpos($dmrMasterHost1[0], '#') === FALSE ) && (substr($dmrMasterHost1[0], 0, 2) == "BM") && ($dmrMasterHost1[0] != '')) {
                        if ($testMMDVMdmrMaster1 == $dmrMasterHost1[2]) { echo "      <option value=\"$dmrMasterHost1[2],$dmrMasterHost1[3],$dmrMasterHost1[4],$dmrMasterHost1[0]\" selected=\"selected\">$dmrMasterHost1[0]</option>\n"; }
                        else { echo "      <option value=\"$dmrMasterHost1[2],$dmrMasterHost1[3],$dmrMasterHost1[4],$dmrMasterHost1[0]\">$dmrMasterHost1[0]</option>\n"; }
                }
	}
	fclose($dmrMasterFile1);
?>
    </select></td></tr>
    <!-- <tr>
    <td align="left"><a class="tooltip2" href="#">BrandMeister Password:<span><b>BrandMeister Password</b>Override the Password<br />for BrandMeister</span></a></td>
    <td align="left"><input type="text" name="bmPasswordOverride" size="30" maxlength="30" value="<?php echo $configdmrgateway['DMR Network 1']['Password']; ?>"></input></td>
    </tr> -->
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_network'];?>:<span><b>BrandMeister Dashboards</b>Direct links to your<br />BrandMeister Dashboards</span></a></td>
    <td>
      <a href="https://brandmeister.network/?page=hotspot&amp;id=<?php echo $configmmdvm['DMR']['Id']; ?>" target="_new" style="color: #000;">Repeater Information</a> | 
      <a href="https://brandmeister.network/?page=hotspot-edit&amp;id=<?php echo $configmmdvm['DMR']['Id']; ?>" target="_new" style="color: #000;">Edit Repeater (BrandMeister Selfcare)</a>
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_master'];?>:<span><b>DMR+ Master</b>Set your prefered DMR<br /> master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost2">
<?php
	$dmrMasterFile2 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	$testMMDVMdmrMaster2= $configdmrgateway['DMR Network 2']['Address'];
	while (!feof($dmrMasterFile2)) {
		$dmrMasterLine2 = fgets($dmrMasterFile2);
                $dmrMasterHost2 = preg_split('/\s+/', $dmrMasterLine2);
                if ((strpos($dmrMasterHost2[0], '#') === FALSE ) && (substr($dmrMasterHost2[0], 0, 4) == "DMR+") && ($dmrMasterHost2[0] != '')) {
                        if ($testMMDVMdmrMaster2 == $dmrMasterHost2[2]) { echo "      <option value=\"$dmrMasterHost2[2],$dmrMasterHost2[3],$dmrMasterHost2[4],$dmrMasterHost2[0]\" selected=\"selected\">$dmrMasterHost2[0]</option>\n"; }
                        else { echo "      <option value=\"$dmrMasterHost2[2],$dmrMasterHost2[3],$dmrMasterHost2[4],$dmrMasterHost2[0]\">$dmrMasterHost2[0]</option>\n"; }
                }
	}
	fclose($dmrMasterFile2);
?>
    </select></td></tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_network'];?>:<span><b>DMR+ Network</b>Set your options=<br />for DMR+ here</span></a></td>
    <td align="left">
    Options=<input type="text" name="dmrNetworkOptions" size="68" maxlength="100" value="<?php echo $configdmrgateway['DMR Network 2']['Options'];?>">
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['xlx_master'];?>:<span><b>XLX Master</b>Set your prefered XLX<br /> master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost3">
<?php
	$dmrMasterFile3 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	$testMMDVMdmrMaster3= $configdmrgateway['XLX Network 1']['Address'];
	while (!feof($dmrMasterFile3)) {
		$dmrMasterLine3 = fgets($dmrMasterFile3);
                $dmrMasterHost3 = preg_split('/\s+/', $dmrMasterLine3);
                if ((strpos($dmrMasterHost3[0], '#') === FALSE ) && (substr($dmrMasterHost3[0], 0, 3) == "XLX") && ($dmrMasterHost3[0] != '')) {
                        if ($testMMDVMdmrMaster3 == $dmrMasterHost3[2]) { echo "      <option value=\"$dmrMasterHost3[2],$dmrMasterHost3[3],$dmrMasterHost3[4],$dmrMasterHost3[0]\" selected=\"selected\">$dmrMasterHost3[0]</option>\n"; }
                        else { echo "      <option value=\"$dmrMasterHost3[2],$dmrMasterHost3[3],$dmrMasterHost3[4],$dmrMasterHost3[0]\">$dmrMasterHost3[0]</option>\n"; }
                }
	}
	fclose($dmrMasterFile3);
?>
    </select></td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['xlx_enable'];?>:<span><b>XLX Master Enable</b></span></a></td>
    <td align="left">
    <?php if ($configdmrgateway['XLX Network 1']['Enabled'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayXlxEn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayXlxEn\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrGatewayXlxEn\"></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayXlxEn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayXlxEn\" value=\"ON\" /><label for=\"toggle-dmrGatewayXlxEn\"></label></div>\n"; } ?>
    </td></tr>
<?php }
    if (substr($dmrMasterNow, 0, 2) == "BM") { echo '    <!-- <tr>
    <td align="left"><a class="tooltip2" href="#">BrandMeister Password:<span><b>BrandMeister Password</b>Override the Password<br />for BrandMeister</span></a></td>
    <td align="left"><input type="text" name="bmPasswordOverride" size="30" maxlength="30" value="'.$configmmdvm['DMR Network']['Password'].'"></input></td>
    </tr> -->
    <tr>
    <td align="left"><a class="tooltip2" href="#">'.$lang['bm_network'].':<span><b>BrandMeister Dashboards</b>Direct links to your<br />BrandMeister Dashboards</span></a></td>
    <td>
      <a href="https://brandmeister.network/?page=hotspot&amp;id='.$configmmdvm['DMR']['Id'].'" target="_new" style="color: #000;">Repeater Information</a> | 
      <a href="https://brandmeister.network/?page=hotspot-edit&amp;id='.$configmmdvm['DMR']['Id'].'" target="_new" style="color: #000;">Edit Repeater (BrandMeister Selfcare)</a>
    </td>
    </tr>'."\n";}
    if (substr($dmrMasterNow, 0, 4) == "DMR+") { echo '    <tr>
    <td align="left"><a class="tooltip2" href="#">'.$lang['dmr_plus_network'].':<span><b>DMR+ Network</b>Set your options=<br />for DMR+ here</span></a></td>
    <td align="left">
    Options=<input type="text" name="dmrNetworkOptions" size="68" maxlength="100" value="'.$configmmdvm['DMR Network']['Options'].'" />
    </td>
    </tr>'."\n";}
?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_cc'];?>:<span><b>DMR Color Code</b>Set your DMR Color Code here</span></a></td>
    <td style="text-align: left;"><select name="dmrColorCode">
	<?php for ($dmrColorCodeInput = 1; $dmrColorCodeInput <= 15; $dmrColorCodeInput++) {
		if ($configmmdvm['DMR']['ColorCode'] == $dmrColorCodeInput) { echo "<option selected=\"selected\" value=\"$dmrColorCodeInput\">$dmrColorCodeInput</option>\n"; }
		else {echo "<option value=\"$dmrColorCodeInput\">$dmrColorCodeInput</option>\n"; }
	} ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_embeddedlconly'];?>:<span><b>DMR EmbeddedLCOnly</b>Set EmbeddedLCOnly to ON<br />to help reduce problems<br />with some DMR Radios</span></a></td>
    <td align="left">
    <?php if ($configmmdvm['DMR']['EmbeddedLCOnly'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrEmbeddedLCOnly\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrEmbeddedLCOnly\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrEmbeddedLCOnly\"></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrEmbeddedLCOnly\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrEmbeddedLCOnly\" value=\"ON\" /><label for=\"toggle-dmrEmbeddedLCOnly\"></label></div>\n"; } ?>
    </td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_dumptadata'];?>:<span><b>DMR DumpTAData</b>Turn on for extended<br />message support, including<br />GPS.</span></a></td>
    <td align="left">
    <?php if ($configmmdvm['DMR']['DumpTAData'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrDumpTAData\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDumpTAData\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrDumpTAData\"></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrDumpTAData\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDumpTAData\" value=\"ON\" /><label for=\"toggle-dmrDumpTAData\"></label></div>\n"; } ?>
    </td></tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>

<?php if (file_exists('/etc/dstar-radio.dstarrepeater') || $configmmdvm['D-Star']['Enable'] == 1) { ?>
	<div><b><?php echo $lang['dstar_config'];?></b></div>
	<input type="hidden" name="confTimeAnnounce" value="OFF" />
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_rpt1'];?>:<span><b>RPT1 Callsign</b>This is the RPT1 field for your radio</span></a></td>
    <td align="left" colspan="2"><?php echo str_replace(' ', '&nbsp;', substr($configdstar['callsign'], 0, 7)) ?>
	<select name="confDStarModuleSuffix">
	<?php echo "  <option value=\"".substr($configdstar['callsign'], 7)."\" selected=\"selected\">".substr($configdstar['callsign'], 7)."</option>\n"; ?>
        <option>A</option>
        <option>B</option>
        <option>C</option>
        <option>D</option>
        <option>E</option>
        <option>F</option>
        <option>G</option>
        <option>H</option>
        <option>I</option>
        <option>J</option>
        <option>K</option>
        <option>L</option>
        <option>M</option>
        <option>N</option>
        <option>O</option>
        <option>P</option>
        <option>Q</option>
        <option>R</option>
        <option>S</option>
        <option>T</option>
        <option>U</option>
        <option>V</option>
        <option>W</option>
        <option>X</option>
        <option>Y</option>
        <option>Z</option>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_rpt2'];?>:<span><b>RPT2 Callsign</b>This is the RPT2 field for your radio</span></a></td>
    <td align="left" colspan="2"><?php echo str_replace(' ', '&nbsp;', $configdstar['gateway']) ?></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_irc_password'];?>:<span><b>Gateway Password</b>Used for any kind of remote<br />access to this system</span></a></td>
    <td align="left" colspan="2"><input type="password" name="confPassword" size="30" maxlength="30" value="<?php echo $configs['remotePassword'] ?>" /></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_default_ref'];?>:<span><b>Default Refelctor</b>Used for setting the<br />default reflector.</span></a></td>
    <td align="left" colspan="1"><select name="confDefRef"
	onchange="if (this.options[this.selectedIndex].value == 'customOption') {
	  toggleField(this,this.nextSibling);
	  this.selectedIndex='0';
	  } ">
<?php
$dcsFile = fopen("/usr/local/etc/DCS_Hosts.txt", "r");
$dplusFile = fopen("/usr/local/etc/DPlus_Hosts.txt", "r");
$dextraFile = fopen("/usr/local/etc/DExtra_Hosts.txt", "r");

echo "    <option value=\"".substr($configs['reflector1'], 0, 6)."\" selected=\"selected\">".substr($configs['reflector1'], 0, 6)."</option>\n";
echo "    <option value=\"customOption\">Text Entry</option>\n";

while (!feof($dcsFile)) {
	$dcsLine = fgets($dcsFile);
	if (strpos($dcsLine, 'DCS') !== FALSE && strpos($dcsLine, '#') === FALSE)
		echo "	<option value=\"".substr($dcsLine, 0, 6)."\">".substr($dcsLine, 0, 6)."</option>\n";
}
fclose($dcsFile);
while (!feof($dplusFile)) {
	$dplusLine = fgets($dplusFile);
	if (strpos($dplusLine, 'REF') !== FALSE && strpos($dplusLine, '#') === FALSE) {
		echo "	<option value=\"".substr($dplusLine, 0, 6)."\">".substr($dplusLine, 0, 6)."</option>\n";
	}
	if (strpos($dplusLine, 'XRF') !== FALSE && strpos($dplusLine, '#') === FALSE) {
		echo "	<option value=\"".substr($dplusLine, 0, 6)."\">".substr($dplusLine, 0, 6)."</option>\n";
	}
}
fclose($dplusFile);
while (!feof($dextraFile)) {
	$dextraLine = fgets($dextraFile);
	if (strpos($dextraLine, 'XRF') !== FALSE && strpos($dextraLine, '#') === FALSE)
		echo "	<option value=\"".substr($dextraLine, 0, 6)."\">".substr($dextraLine, 0, 6)."</option>\n";
}
fclose($dextraFile);

?>
    </select><input name="confDefRef" style="display:none;" disabled="disabled" type="text" size="7" maxlength="7"
            onblur="if(this.value==''){toggleField(this,this.previousSibling);}" />
    <select name="confDefRefLtr">
	<?php echo "  <option value=\"".substr($configs['reflector1'], 7)."\" selected=\"selected\">".substr($configs['reflector1'], 7)."</option>\n"; ?>
        <option>A</option>
        <option>B</option>
        <option>C</option>
        <option>D</option>
        <option>E</option>
        <option>F</option>
        <option>G</option>
        <option>H</option>
        <option>I</option>
        <option>J</option>
        <option>K</option>
        <option>L</option>
        <option>M</option>
        <option>N</option>
        <option>O</option>
        <option>P</option>
        <option>Q</option>
        <option>R</option>
        <option>S</option>
        <option>T</option>
        <option>U</option>
        <option>V</option>
        <option>W</option>
        <option>X</option>
        <option>Y</option>
        <option>Z</option>
    </select>
    </td>
    <td width="300">
    <input type="radio" name="confDefRefAuto" value="ON"<?php if ($configs['atStartup1'] == '1') {echo ' checked="checked"';} ?> />Startup
    <input type="radio" name="confDefRefAuto" value="OFF"<?php if ($configs['atStartup1'] == '0') {echo ' checked="checked"';} ?> />Manual</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['aprs_host'];?>:<span><b>APRS Host</b>Set your prefered APRS host here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="selectedAPRSHost">
<?php
        $testAPSRHost = $configs['aprsHostname'];
    	$aprsHostFile = fopen("/usr/local/etc/APRSHosts.txt", "r");
        while (!feof($aprsHostFile)) {
                $aprsHostFileLine = fgets($aprsHostFile);
                $aprsHost = preg_split('/:/', $aprsHostFileLine);
                if ((strpos($aprsHost[0], ';') === FALSE ) && ($aprsHost[0] != '')) {
                        if ($testAPSRHost == $aprsHost[0]) { echo "      <option value=\"$aprsHost[0]\" selected=\"selected\">$aprsHost[0]</option>\n"; }
                        else { echo "      <option value=\"$aprsHost[0]\">$aprsHost[0]</option>\n"; }
                }
        }
        fclose($aprsHostFile);
        ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_irc_lang'];?>:<span><b>Language</b>Set your prefered<br /> language here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="ircDDBGatewayAnnounceLanguage">
<?php
        $testIrcLanguage = $configs['language'];
	if (is_readable("/var/www/dashboard/config/ircddbgateway_languages.inc")) {
	  $ircLanguageFile = fopen("/var/www/dashboard/config/ircddbgateway_languages.inc", "r");
        while (!feof($ircLanguageFile)) {
                $ircLanguageFileLine = fgets($ircLanguageFile);
                $ircLanguage = preg_split('/;/', $ircLanguageFileLine);
                if ((strpos($ircLanguage[0], '#') === FALSE ) && ($ircLanguage[0] != '')) {
                        if ($testIrcLanguage == $ircLanguage[1]) { echo "      <option value=\"$ircLanguage[1],$ircLanguage[2]\" selected=\"selected\">".htmlspecialchars($ircLanguage[0])."</option>\n"; }
                        else { echo "      <option value=\"$ircLanguage[1],$ircLanguage[2]\">".htmlspecialchars($ircLanguage[0])."</option>\n"; }
                }
        }
          fclose($ircLanguageFile);
	}
        ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_irc_time'];?>:<span><b>Time Announce</b>Announce time<br />hourly</span></a></td>
    <?php
	if ( !file_exists('/etc/timeserver.dissable') ) {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-timeAnnounce\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confTimeAnnounce\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-timeAnnounce\"></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-timeAnnounce\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confTimeAnnounce\" value=\"ON\" /><label for=\"toggle-timeAnnounce\"></label></div></td>\n";
	}
    ?>
    </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['System Fusion Network']['Enable'] == 1) {
$ysfHosts = fopen("/usr/local/etc/YSFHosts.txt", "r"); ?>
	<div><b><?php echo $lang['ysf_config'];?></b></div>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['ysf_startup_host'];?>:<span><b>YSF Host</b>Set your prefered<br /> YSF Host here</span></a></td>
    <td style="text-align: left;"><select name="ysfStartupHost">
<?php
        if (isset($configysfgateway['Network']['Startup'])) {
                $testYSFHost = $configysfgateway['Network']['Startup'];
                echo "      <option value=\"none\">None</option>\n";
                }
        else {
                $testYSFHost = "none";
                echo "      <option value=\"none\" selected=\"selected\">None</option>\n";
                }
        while (!feof($ysfHosts)) {
                $ysfHostsLine = fgets($ysfHosts);
                $ysfHost = preg_split('/;/', $ysfHostsLine);
                if ((strpos($ysfHost[0], '#') === FALSE ) && ($ysfHost[0] != '')) {
                        if ($testYSFHost == $ysfHost[0]) { echo "      <option value=\"$ysfHost[0]\" selected=\"selected\">$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n"; }
			else { echo "      <option value=\"$ysfHost[0]\">$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n"; }
                }
        }
        fclose($ysfHosts);
        ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['aprs_host'];?>:<span><b>APRS Host</b>Set your prefered APRS host here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="selectedAPRSHost">
<?php
        $testAPSRHost = $configs['aprsHostname'];
    	$aprsHostFile = fopen("/usr/local/etc/APRSHosts.txt", "r");
        while (!feof($aprsHostFile)) {
                $aprsHostFileLine = fgets($aprsHostFile);
                $aprsHost = preg_split('/:/', $aprsHostFileLine);
                if ((strpos($aprsHost[0], ';') === FALSE ) && ($aprsHost[0] != '')) {
                        if ($testAPSRHost == $aprsHost[0]) { echo "      <option value=\"$aprsHost[0]\" selected=\"selected\">$aprsHost[0]</option>\n"; }
                        else { echo "      <option value=\"$aprsHost[0]\">$aprsHost[0]</option>\n"; }
                }
        }
        fclose($aprsHostFile);
        ?>
    </select></td>
    </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['P25 Network']['Enable'] == 1) {
$p25Hosts = fopen("/usr/local/etc/P25Hosts.txt", "r"); ?>
	<div><b><?php echo $lang['p25_config'];?></b></div>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_startup_host'];?>:<span><b>P25 Host</b>Set your prefered<br /> P25 Host here</span></a></td>
    <td style="text-align: left;"><select name="p25StartupHost">
<?php
	$testP25Host = $configp25gateway['Network']['Startup'];
	if ($testP25Host == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
        else { echo "      <option value=\"none\">None</option>\n"; }
	if ($testP25Host == "10") { echo "      <option value=\"10\" selected=\"selected\">10 - Parrot</option>\n"; }
        else { echo "      <option value=\"10\">10 - Parrot</option>\n"; }
        while (!feof($p25Hosts)) {
                $p25HostsLine = fgets($p25Hosts);
                $p25Host = preg_split('/\s+/', $p25HostsLine);
                if ((strpos($p25Host[0], '#') === FALSE ) && ($p25Host[0] != '')) {
                        if ($testP25Host == $p25Host[0]) { echo "      <option value=\"$p25Host[0]\" selected=\"selected\">$p25Host[0] - $p25Host[1]</option>\n"; }
                        else { echo "      <option value=\"$p25Host[0]\">$p25Host[0] - $p25Host[1]</option>\n"; }
                }
        }
        fclose($p25Hosts);
        ?>
    </select></td>
    </tr>
<?php if ($configmmdvm['P25']['NAC']) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_nac'];?>:<span><b>P25 NAC</b>Set your NAC<br /> code here</span></a></td>
    <td align="left"><input type="text" name="p25nac" size="13" maxlength="3" value="<?php echo $configmmdvm['P25']['NAC'];?>" /></td>
    </tr>
<?php } ?>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>
	<div><b><?php echo $lang['fw_config'];?></b></div>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['fw_dash'];?>:<span><b>Dashboard Access</b>Do you want the dashboard access<br />to be publicly available? This<br />modifies the uPNP firewall<br />Configuration.</span></a></td>
    <?php
	$testPrvPubDash = exec('sudo sed -n 32p /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubDash, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"dashAccess\" value=\"PRV\" checked=\"checked\" />Private <input type=\"radio\" name=\"dashAccess\" value=\"PUB\" />Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"dashAccess\" value=\"PRV\" />Private <input type=\"radio\" name=\"dashAccess\" value=\"PUB\" checked=\"checked\" />Public</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['fw_irc'];?>:<span><b>ircDDBGateway Remote Access</b>Do you want the ircDDBGateway<br />remote controll access to be<br />publicly available? This modifies<br />the uPNP firewall Configuration.</span></a></td>
    <?php
	$testPrvPubIRC = exec('sudo sed -n 33p /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubIRC, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"ircRCAccess\" value=\"PRV\" checked=\"checked\" />Private <input type=\"radio\" name=\"ircRCAccess\" value=\"PUB\" />Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"ircRCAccess\" value=\"PRV\" />Private <input type=\"radio\" name=\"ircRCAccess\" value=\"PUB\" checked=\"checked\" />Public</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['fw_ssh'];?>:<span><b>SSH Access</b>Do you want access to be<br />publicly available over SSH (used<br />for support issues)? This modifies<br />the uPNP firewall Configuration.</span></a></td>
    <?php
	$testPrvPubSSH = exec('sudo sed -n 31p /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubSSH, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"sshAccess\" value=\"PRV\" checked=\"checked\" />Private <input type=\"radio\" name=\"sshAccess\" value=\"PUB\" />Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"sshAccess\" value=\"PRV\" />Private <input type=\"radio\" name=\"sshAccess\" value=\"PUB\" checked=\"checked\" />Public</td>\n";
	}
    ?>
    </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /></div>
    </form>

<?php
	exec('ifconfig wlan0',$return);
	exec('iwconfig wlan0',$return);
	$strWlan0 = implode(" ",$return);
	$strWlan0 = preg_replace('/\s\s+/', ' ', $strWlan0);
	if (strpos($strWlan0,'HWaddr') !== false) {
		preg_match('/HWaddr ([0-9a-f:]+)/i',$strWlan0,$result);
	}
	elseif (strpos($strWlan0,'ether') !== false) {
		preg_match('/ether ([0-9a-f:]+)/i',$strWlan0,$result);
	}
	$strHWAddress = $result['1'];

	if ( isset($strHWAddress) ) {
echo '
<br />
    <b>'.$lang['wifi_config'].'</b>
    <table><tr><td>
    <iframe frameborder="0" scrolling="auto" name="wifi" src="wifi.php?page=wlan0_info" width="100%" onload="javascript:resizeIframe(this);">If you can see this message, your browser does not support iFrames, however if you would like to see the content please click <a href="wifi.php?page=wlan0_info">here</a>.</iframe>
    </td></tr></table>'; } ?>

<br />
	<div><b><?php echo $lang['remote_access_pw'];?></b></div>
    <form id="adminPassForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <table>
    <tr><th width="200"><?php echo $lang['user'];?></th><th colspan="3"><?php echo $lang['password'];?></th></tr>
    <tr>
    <td align="left"><b>pi-star</b></td>
    <td align="left"><input type="password" name="adminPassword" size="30" value="" /></td>
    <td align="right"><input type="button" value="<?php echo $lang['set_password'];?>" onclick="submitPassform()" /></td>
    </tr>
    <tr><td colspan="3"><b>WARNING:</b> This changes the password for this admin page<br />AND the "pi-star" SSH account</td></tr>
    </table>
    </form>
<?php endif; ?>
<br />
</div>
<div class="footer">
Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistar/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.mw0mwz.co.uk/pi-star/" target="_blank">here</a>.<br />
<br />
</div>
</div>
</body>
</html>

<?php } else { ?>
<br />
<br />
</div>
<div class="footer">
Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistar/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.mw0mwz.co.uk/pi-star/" target="_blank">here</a>.<br />
<br />
</div>
</div>
</body>
</html>
<?php } ?>
