<?php
// Get the CPU temp and colour the box accordingly...
$cpuTemp = exec('awk \'{printf("%.1f\n",$1/1e3)}\' /sys/class/thermal/thermal_zone0/temp');
if ($cpuTemp < 50) { $cpuTempHTML = "<td bgcolor=\"#1d1\">".$cpuTemp."&degC</td>\n"; }
if ($cpuTemp >= 50) { $cpuTempHTML = "<td bgcolor=\"#fa0\">".$cpuTemp."&degC</td>\n"; }
if ($cpuTemp >= 69) { $cpuTempHTML = "<td bgcolor=\"#f00\">".$cpuTemp."&degC</td>\n"; }

// Pull in some config
require_once('config/version.php');
require_once('config/ircddblocal.php');
$cpuLoad = sys_getloadavg();

//Load the pistar-release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);

//Load the dstarrepeater config file
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

//Load the ircDDBGateway config file
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

//Load the mmdvmhost config file
$mmdvmConfigFile = '/etc/mmdvmhost';
$configmmdvm = parse_ini_file($mmdvmConfigFile, true);

//Load the ysfgateway config file
$ysfgatewayConfigFile = '/etc/ysfgateway';
$configysfgateway = parse_ini_file($ysfgatewayConfigFile, true);

//Load the p25gateway config file
$p25gatewayConfigFile = '/etc/p25gateway';
$configp25gateway = parse_ini_file($p25gatewayConfigFile, true);

$progname = basename($_SERVER['SCRIPT_FILENAME'],".php");
$rev=$version;
$MYCALL=strtoupper($callsign);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
    <title><?php echo "$MYCALL" ?> Hotspot Configuration Dashboard</title>
    <link rel="stylesheet" type="text/css" href="css/ircddb.css" />
    <script>
	function submitform()
	{
	  document.config.submit();
	}
	function submitPassform()
	{
	  document.adminPassForm.submit();
	}
	function factoryReset()
	{
	  if (confirm('WARNING: This will set all your settings back to factory defaults. WiFi setup will be retained to maintain network access to this Pi.\n\nAre you SURE you want to do this?\n\nPress OK to restore the factory configuration\nPress Cancel to go back.')) {
	    document.factoryReset.submit();
	    } else {
	    return false;
	    }
	}
	function resizeIframe(obj) {
	  obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
	}
    </script>
    <script type="text/javascript" src="/functions.js"></script>
</head>
<body>
<div class="container">
<div id="header">
<div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
<h1>Pi-Star Digital Voice - Configuration</h1>
<p style="padding-right: 5px; text-align: right; color: #ffffff;">
 <a href="/" alt="Dashboard" style="color: #ffffff;">Dashboard</a> |
 <a href="/admin/" style="color: #ffffff;">Admin</a> |
 <a href="/admin/power.php" style="color: #ffffff;">Power</a> |
 <a href="/admin/update.php" style="color: #ffffff;">Update</a> |
 <a href="/admin/config_backup.php" style="color: #ffffff;">Backup/Restore</a> |
 <a href="javascript:factoryReset();" style="color: #ffffff;">Factory Reset</a>
</p>
</div>
<div id="contentwide">
<?php
// Hardware Detail
if ($_SERVER["PHP_SELF"] == "/admin/configure.php") {
//HTML output starts here 
?>
    <b>Gateway Hardware Information</b>
    <table>
    <tr>
    <th width="16.66%"><a class=tooltip href="#">Hostname<span><b>Hostname</b>The name of host<br />running the Pi-Star Software.</span></a></th>
    <th width="16.66%"><a class=tooltip href="#">Kernel<span><b>Release</b>This is the version<br />number of the Linux Kernel running<br />on this Raspberry Pi.</span></a></th>
    <th><a class=tooltip href="#">Platform<span><b>Pi-Hardware Revision</b>This shows you the revision<br />of your Pi.</span></a></th>
    <th><a class=tooltip href="#">CPU Load<span><b>CPU Load</b>This is the standard Linux<br />system load indicator.</span></a></th>
    <th><a class=tooltip href="#">CPU Temp<span><b>CPU Temp</b></span></a></th>
    </tr>
    <tr>
    <td><?php echo php_uname('n');?></td>
    <td><?php echo php_uname('r');?></td>
    <td><?php echo exec('platformDetect.sh');?></td>
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
	system('sudo systemctl stop dstarrepeater.service > /dev/null 2>/dev/null &');		//D-Star Radio Service
	system('sudo systemctl stop mmdvmhost.service > /dev/null 2>/dev/null &');		//MMDVMHost Radio Service
	system('sudo systemctl stop ircddbgateway.service > /dev/null 2>/dev/null &');		//ircDDBGateway Service
	system('sudo systemctl stop timeserver.service > /dev/null 2>/dev/null &');		//Time Server Service
	system('sudo systemctl stop pistar-watchdog.service > /dev/null 2>/dev/null &');	//PiStar-Watchdog Service
	system('sudo systemctl stop ysfgateway.service > /dev/null 2>/dev/null &');		//YSFGateway
	system('sudo systemctl stop p25gateway.service > /dev/null 2>/dev/null &');		//P25Gateway

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
	  exec('sudo unzip -o /usr/local/etc/config_clean.zip -d /etc/');
	  exec('sudo rm -rf /etc/dstar-radio.*');
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
	  // Make the root filesystem read-only
          system('sudo mount -o remount,ro /');
	  echo "<br />\n</div>\n";
          echo "<div id=\"footer\">\nPi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-".date("Y").".<br />\n";
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
	  $rollConfLat0 = 'sudo sed -i "/latitude=/c\\latitude='.escapeshellcmd($_POST['confLatitude']).'" /etc/ircddbgateway';
	  $rollConfLat1 = 'sudo sed -i "/latitude1=/c\\latitude1='.escapeshellcmd($_POST['confLatitude']).'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Latitude'] = escapeshellcmd($_POST['confLatitude']);
	  $configysfgateway['Info']['Latitude'] = escapeshellcmd($_POST['confLatitude']);
	  system($rollConfLat0);
	  system($rollConfLat1);
	  }

	// Set the Longitude
	if (empty($_POST['confLongitude']) != TRUE ) {
	  $rollConfLon0 = 'sudo sed -i "/longitude=/c\\longitude='.escapeshellcmd($_POST['confLongitude']).'" /etc/ircddbgateway';
	  $rollConfLon1 = 'sudo sed -i "/longitude1=/c\\longitude1='.escapeshellcmd($_POST['confLongitude']).'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Longitude'] = escapeshellcmd($_POST['confLongitude']);
	  $configysfgateway['Info']['Longitude'] = escapeshellcmd($_POST['confLongitude']);
	  system($rollConfLon0);
	  system($rollConfLon1);
	  }

	// Set the Town
	if (empty($_POST['confDesc1']) != TRUE ) {
	  $rollDesc1 = 'sudo sed -i "/description1=/c\\description1='.escapeshellcmd($_POST['confDesc1']).'" /etc/ircddbgateway';
	  $rollDesc11 = 'sudo sed -i "/description1_1=/c\\description1_1='.escapeshellcmd($_POST['confDesc1']).'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Location'] = escapeshellcmd($_POST['confDesc1']);
          $configysfgateway['Info']['Name'] = escapeshellcmd($_POST['confDesc1']);
	  system($rollDesc1);
	  system($rollDesc11);
	  }

	// Set the Country
	if (empty($_POST['confDesc2']) != TRUE ) {
	  $rollDesc2 = 'sudo sed -i "/description2=/c\\description2='.escapeshellcmd($_POST['confDesc2']).'" /etc/ircddbgateway';
	  $rollDesc22 = 'sudo sed -i "/description1_2=/c\\description1_2='.escapeshellcmd($_POST['confDesc2']).'" /etc/ircddbgateway';
          $configmmdvm['Info']['Description'] = escapeshellcmd($_POST['confDesc2']);
          $configysfgateway['Info']['Description'] = escapeshellcmd($_POST['confDesc2']);
	  system($rollDesc2);
	  system($rollDesc22);
	  }

	// Set the URL
	if (empty($_POST['confURL']) != TRUE ) {
	  if (escapeshellcmd($_POST['urlAuto']) == 'auto') { $txtURL = "http://www.qrz.com/db/".strtoupper(escapeshellcmd($_POST['confCallsign'])); }
	  if (escapeshellcmd($_POST['urlAuto']) == 'man')  { $txtURL = escapeshellcmd($_POST['confURL']); }
	  if (escapeshellcmd($_POST['urlAuto']) == 'auto') { $rollURL0 = 'sudo sed -i "/url=/c\\url=http://www.qrz.com/db/'.strtoupper(escapeshellcmd($_POST['confCallsign'])).'" /etc/ircddbgateway';  }
	  if (escapeshellcmd($_POST['urlAuto']) == 'man') { $rollURL0 = 'sudo sed -i "/url=/c\\url='.escapeshellcmd($_POST['confURL']).'" /etc/ircddbgateway'; }
          $configmmdvm['Info']['URL'] = $txtURL;
	  system($rollURL0);
	  }

	// Set the APRS Host for ircDDBGateway
	if (empty($_POST['selectedAPRSHost']) != TRUE ) {
	  $rollAPRSHost = 'sudo sed -i "/aprsHostname=/c\\aprsHostname='.escapeshellcmd($_POST['selectedAPRSHost']).'" /etc/ircddbgateway';
	  system($rollAPRSHost);
	  $configysfgateway['aprs.fi']['Server'] = escapeshellcmd($_POST['selectedAPRSHost']);
	  }

	// Set the Frequency for Duplex
	if (empty($_POST['confFREQtx']) != TRUE && empty($_POST['confFREQrx']) != TRUE ) {
	  $newFREQtx = str_pad(str_replace(".", "", escapeshellcmd($_POST['confFREQtx'])), 9, "0");
	  $newFREQtx = mb_strimwidth($newFREQtx, 0, 9);
	  $newFREQrx = str_pad(str_replace(".", "", escapeshellcmd($_POST['confFREQrx'])), 9, "0");
	  $newFREQrx = mb_strimwidth($newFREQrx, 0, 9);
	  $newFREQirc = substr_replace($newFREQrx, '.', '3', 0);
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
	  if ($newFREQtx >= 1240000000 && $newFREQtx <= 1300000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		if ( $confHardware != 'DVM-USB' ) { $confDVVariant = 0; }
		if ( $confHardware == 'DVM-USB' ) { $confDVVariant = 1; }
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		}
	  if ($newFREQtx >= 420000000 && $newFREQtx <= 450000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."B";
		if ( $confHardware != 'DVM-USB' ) { $confDVVariant = 2; }
		if ( $confHardware == 'DVM-USB' ) { $confDVVariant = 1; }
		$confIRCrepeaterBand1 = "B";
		$configmmdvm['D-Star']['Module'] = "B";
		}
	  if ($newFREQtx >= 144000000 && $newFREQtx <= 148000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."C";
		if ( $confHardware != 'DVM-USB' ) { $confDVVariant = 3; }
		if ( $confHardware == 'DVM-USB' ) { $confDVVariant = 1; }
		$confIRCrepeaterBand1 = "C";
		$configmmdvm['D-Star']['Module'] = "C";
		}
	  $newCallsignUpper = strtoupper(escapeshellcmd($_POST['confCallsign']));
	  $confRPT2 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."G";

	  $confRPT1 = strtoupper($confRPT1);
	  $confRPT2 = strtoupper($confRPT2);

	  $rollRPT1 = 'sudo sed -i "/callsign=/c\\callsign='.$confRPT1.'" /etc/dstarrepeater';
	  $rollRPT2 = 'sudo sed -i "/gateway=/c\\gateway='.$confRPT2.'" /etc/dstarrepeater';
	  $rollBEACONTEXT = 'sudo sed -i "/beaconText=/c\\beaconText='.$confRPT1.'" /etc/dstarrepeater';
	  $rollDVVARIANT = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant='.$confDVVariant.'" /etc/dstarrepeater';
	  $rollIRCrepeaterBand1 = 'sudo sed -i "/repeaterBand1=/c\\repeaterBand1='.$confIRCrepeaterBand1.'" /etc/ircddbgateway';
	  $rollIRCrepeaterCall1 = 'sudo sed -i "/repeaterCall1=/c\\repeaterCall1='.$newCallsignUpper.'" /etc/ircddbgateway';

	  system($rollRPT1);
	  system($rollRPT2);
	  system($rollBEACONTEXT);
	  system($rollDVVARIANT);
	  system($rollIRCrepeaterBand1);
	  system($rollIRCrepeaterCall1);
	}

	// Set the Frequency for Simplex
	if (empty($_POST['confFREQ']) != TRUE ) {
	  $newFREQ = str_pad(str_replace(".", "", escapeshellcmd($_POST['confFREQ'])), 9, "0");
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
	  if ($newFREQ >= 1240000000 && $newFREQ <= 1300000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."A";
		if ( $confHardware != 'DVM-USB' ) { $confDVVariant = 0; }
		if ( $confHardware == 'DVM-USB' ) { $confDVVariant = 1; }
		$confIRCrepeaterBand1 = "A";
		$configmmdvm['D-Star']['Module'] = "A";
		}
	  if ($newFREQ >= 420000000 && $newFREQ <= 450000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."B";
		if ( $confHardware != 'DVM-USB' ) { $confDVVariant = 2; }
		if ( $confHardware == 'DVM-USB' ) { $confDVVariant = 1; }
		$confIRCrepeaterBand1 = "B";
		$configmmdvm['D-Star']['Module'] = "B";
		}
	  if ($newFREQ >= 144000000 && $newFREQ <= 148000000) {
		$confRPT1 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."C";
		if ( $confHardware != 'DVM-USB' ) { $confDVVariant = 3; }
		if ( $confHardware == 'DVM-USB' ) { $confDVVariant = 1; }
		$confIRCrepeaterBand1 = "C";
		$configmmdvm['D-Star']['Module'] = "C";
		}
	  $newCallsignUpper = strtoupper(escapeshellcmd($_POST['confCallsign']));
	  $confRPT2 = str_pad(escapeshellcmd($_POST['confCallsign']), 7, " ")."G";

	  $confRPT1 = strtoupper($confRPT1);
	  $confRPT2 = strtoupper($confRPT2);

	  $rollRPT1 = 'sudo sed -i "/callsign=/c\\callsign='.$confRPT1.'" /etc/dstarrepeater';
	  $rollRPT2 = 'sudo sed -i "/gateway=/c\\gateway='.$confRPT2.'" /etc/dstarrepeater';
	  $rollBEACONTEXT = 'sudo sed -i "/beaconText=/c\\beaconText='.$confRPT1.'" /etc/dstarrepeater';
	  $rollDVVARIANT = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant='.$confDVVariant.'" /etc/dstarrepeater';
	  $rollIRCrepeaterBand1 = 'sudo sed -i "/repeaterBand1=/c\\repeaterBand1='.$confIRCrepeaterBand1.'" /etc/ircddbgateway';
	  $rollIRCrepeaterCall1 = 'sudo sed -i "/repeaterCall1=/c\\repeaterCall1='.$newCallsignUpper.'" /etc/ircddbgateway';

	  system($rollRPT1);
	  system($rollRPT2);
	  system($rollBEACONTEXT);
	  system($rollDVVARIANT);
	  system($rollIRCrepeaterBand1);
	  system($rollIRCrepeaterCall1);
	  }

	// Set Callsign
	if (empty($_POST['confCallsign']) != TRUE ) {
	  $newCallsignUpper = strtoupper(escapeshellcmd($_POST['confCallsign']));

	  $rollGATECALL = 'sudo sed -i "/gatewayCallsign=/c\\gatewayCallsign='.$newCallsignUpper.'" /etc/ircddbgateway';
	  $rollIRCUSER = 'sudo sed -i "/ircddbUsername=/c\\ircddbUsername='.$newCallsignUpper.'" /etc/ircddbgateway';
	  $rollDPLUSLOGIN = 'sudo sed -i "/dplusLogin=/c\\dplusLogin='.$newCallsignUpper.'" /etc/ircddbgateway';
	  $rollDASHBOARDcall = 'sudo sed -i "/callsign=/c\\$callsign=\''.$newCallsignUpper.'\';" /var/www/dashboard/config/ircddblocal.php';
	  $rollTIMESERVERcall = 'sudo sed -i "/callsign=/c\\callsign='.$newCallsignUpper.'" /etc/timeserver';
	  $rollSTARNETSERVERcall = 'sudo sed -i "/callsign=/c\\callsign='.$newCallsignUpper.'" /etc/starnetserver';
	  $rollSTARNETSERVERirc = 'sudo sed -i "/ircddbUsername=/c\\ircddbUsername='.$newCallsignUpper.'" /etc/starnetserver';
	  $rollP25GATEWAY = 'sudo sed -i "/Callsign=/c\\Callsign='.$newCallsignUpper.'" /etc/p25gateway';

	  $configmmdvm['General']['Callsign'] = $newCallsignUpper;
	  $configysfgateway['General']['Callsign'] = $newCallsignUpper;

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
	  $rollP25Startup = 'sudo sed -i "/Startup=/c\\Startup='.$newP25StartupHost.'" /etc/p25gateway';
	  system($rollP25Startup);
	}
	
	// Set the YSF Startup Host
	if (empty($_POST['ysfStartupHost']) != TRUE ) {
	  $newYSFStartupHost = strtoupper(escapeshellcmd($_POST['ysfStartupHost']));
	  $configysfgateway['Network']['Startup'] = $newYSFStartupHost;
	}
	
	// Set Duplex
	if (empty($_POST['trxMode']) != TRUE ) {
	  if ($configmmdvm['Info']['RXFrequency'] === $configmmdvm['Info']['TXFrequency'] && $_POST['trxMode'] == "DUPLEX" ) {
	    $configmmdvm['Info']['RXFrequency'] = $configmmdvm['Info']['TXFrequency'] - 1;
	    $configmmdvm['General']['Duplex'] = 1;
	    }
	  if ($configmmdvm['Info']['RXFrequency'] !== $configmmdvm['Info']['TXFrequency'] && $_POST['trxMode'] == "SIMPLEX" ) {
	    $configmmdvm['Info']['RXFrequency'] = $configmmdvm['Info']['TXFrequency'];
            $configmmdvm['General']['Duplex'] = 0;
	    }
	  }

	// Set DMR / CCS7 ID
	if (empty($_POST['dmrId']) != TRUE ) {
	  $configmmdvm['DMR']['Id'] = escapeshellcmd($_POST['dmrId']);
	}

	// Set DMR Master Server
	if (empty($_POST['dmrMasterHost']) != TRUE ) {
	  $dmrMasterHostArr = explode(',', escapeshellcmd($_POST['dmrMasterHost']));
	  $configmmdvm['DMR Network']['Address'] = $dmrMasterHostArr[0];
	  $configmmdvm['DMR Network']['Password'] = $dmrMasterHostArr[1];
	  $configmmdvm['DMR Network']['Port'] = $dmrMasterHostArr[2];

		if (substr($dmrMasterHostArr[3], 0, 2) == "BM") {
			$configmmdvm['DMR Network']['Options'] = "";
		}
		
		// Set the DMR+ Options= line
		if (substr($dmrMasterHostArr[3], 0, 4) == "DMR+") {
			if (empty($_POST['dmrNetworkOptions']) != TRUE ) {
				$configmmdvm['DMR Network']['Options'] = '"'.$_POST['dmrNetworkOptions'].'"';
			}
			else { $configmmdvm['DMR Network']['Options'] = ""; }
		}
	}
	if (empty($_POST['dmrMasterHost']) == TRUE ) {
		$configmmdvm['DMR Network']['Options'] = "";
	}
		
	// Set Talker Alias Option
	if (empty($_POST['dmrEmbeddedLCOnly']) != TRUE ) {
	  $configmmdvm['DMR']['EmbeddedLCOnly'] = escapeshellcmd($_POST['dmrEmbeddedLCOnly']);
	}
	
	// Set Dump TA Data Option for GPS support
	if (empty($_POST['dmrDumpTAData']) != TRUE ) {
	  $configmmdvm['DMR']['DumpTAData'] = escapeshellcmd($_POST['dmrDumpTAData']);
	}

	// Set MMDVM Hang Time
	if (empty($_POST['hangTime']) != TRUE ) {
	  $configmmdvm['General']['RFModeHang'] = escapeshellcmd($_POST['hangTime']);
	  $configmmdvm['General']['NetModeHang'] = escapeshellcmd($_POST['hangTime']);
	}

	// Set the hardware type
	if (empty($_POST['confHardware']) != TRUE ) {
	$confHardware = escapeshellcmd($_POST['confHardware']);

	  if ( $confHardware == 'DVM-RPI' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	  }

	  if ( $confHardware == 'DVM-USB' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	  }

	  if ( $confHardware == 'DVM-GMSK' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	  }

	  if ( $confHardware == 'DV-RPTR1' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V1" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    system($rollModemType);
	    system($rollDVRPTRPort);
	  }

	  if ( $confHardware == 'DV-RPTR2' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V2" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    system($rollModemType);
	    system($rollDVRPTRPort);
	  }

	  if ( $confHardware == 'DV-RPTR3' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V3" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    system($rollModemType);
	    system($rollDVRPTRPort);
	  }

	  if ( $confHardware == 'DVAP' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVAP" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    system($rollModemType);
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

	// Set MMDVMHost DMR Mode
	if (empty($_POST['MMDVMModeDMRon']) != TRUE ) {
	  if (escapeshellcmd($_POST['MMDVMModeDMRon']) == 'ON' ) { $configmmdvm['DMR']['Enable'] = "1"; }
	  if (escapeshellcmd($_POST['MMDVMModeDMRon']) == 'OFF' ) { $configmmdvm['DMR']['Enable'] = "0"; }
	}

	// Set MMDVMHost DMR Network
	if (empty($_POST['MMDVMModeDMRneton']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDMRneton']) == 'ON' ) { $configmmdvm['DMR Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeDMRneton']) == 'OFF' ) { $configmmdvm['DMR Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost D-Star Mode
	if (empty($_POST['MMDVMModeDSTARon']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDSTARon']) == 'ON' ) { $configmmdvm['D-Star']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeDSTARon']) == 'OFF' ) { $configmmdvm['D-Star']['Enable'] = "0"; }
	}

	// Set MMDVMHost D-Star Network
	if (empty($_POST['MMDVMModeDSTARneton']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDSTARneton']) == 'ON' ) { $configmmdvm['D-Star Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeDSTARneton']) == 'OFF' ) { $configmmdvm['D-Star Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost Fusion Mode
	if (empty($_POST['MMDVMModeFUSIONon']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeFUSIONon']) == 'ON' ) { $configmmdvm['System Fusion']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeFUSIONon']) == 'OFF' ) { $configmmdvm['System Fusion']['Enable'] = "0"; }
	}

	// Set MMDVMHost Fusion Network
	if (empty($_POST['MMDVMModeFUSIONneton']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeFUSIONneton']) == 'ON' ) { $configmmdvm['System Fusion Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeFUSIONneton']) == 'OFF' ) { $configmmdvm['System Fusion Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost P25 Mode
	if (empty($_POST['MMDVMModeP25on']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeP25on']) == 'ON' ) { $configmmdvm['P25']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeP25on']) == 'OFF' ) { $configmmdvm['P25']['Enable'] = "0"; }
	}

	// Set MMDVMHost P25 Network
	if (empty($_POST['MMDVMModeP25neton']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeP25neton']) == 'ON' ) { $configmmdvm['P25 Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeP25neton']) == 'OFF' ) { $configmmdvm['P25 Network']['Enable'] = "0"; }
	}


	// Set MMDVMHost P25 Network
	if (empty($_POST['dmrColorCode']) != TRUE ) {
          $configmmdvm['DMR']['ColorCode'] = escapeshellcmd($_POST['dmrColorCode']);
	}

	// Set Node Lock Status
	if (empty($_POST['nodeMode']) != TRUE ) {
	  if (escapeshellcmd($_POST['nodeMode']) == 'prv' ) {
            $configmmdvm['DMR']['SelfOnly'] = 1;
            $configmmdvm['D-Star']['SelfOnly'] = 1;
            system('sudo sed -i "/restriction=/c\\restriction=1" /etc/dstarrepeater');
          }
	  if (escapeshellcmd($_POST['nodeMode']) == 'pub' ) {
            $configmmdvm['DMR']['SelfOnly'] = 0;
            $configmmdvm['D-Star']['SelfOnly'] = 0;
            system('sudo sed -i "/restriction=/c\\restriction=0" /etc/dstarrepeater');
          }
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
		exec('sudo mv /tmp/bW1kdm1ob3N0DQo.tmp /etc/mmdvmhost');        // Move the file back
		exec('sudo chmod 644 /etc/mmdvmhost');                          // Set the correct runtime permissions
		exec('sudo chown root:root /etc/mmdvmhost');                    // Set the owner
	}

        // ysfgateway config file wrangling
	$ysfgwContent = "";
        foreach($configysfgateway as $yfsgwSection=>$ysfgwValues) {
                // UnBreak special cases
                $yfsgwSection = str_replace("_", " ", $yfsgwSection);
                $ysfgwContent .= "[".$yfsgwSection."]\n";
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
		exec('sudo mv /tmp/eXNmZ2F0ZXdheQ.tmp /etc/ysfgateway');        // Move the file back
		exec('sudo chmod 644 /etc/ysfgateway');                         // Set the correct runtime permissions
		exec('sudo chown root:root /etc/ysfgateway');                   // Set the owner
	}

	// Start the DV Services
	system('sudo systemctl start dstarrepeater.service > /dev/null 2>/dev/null &');		//D-Star Radio Service
	system('sudo systemctl start mmdvmhost.service > /dev/null 2>/dev/null &');		//MMDVMHost Radio Service
	system('sudo systemctl start ircddbgateway.service > /dev/null 2>/dev/null &');		//ircDDBGateway Service
	system('sudo systemctl start timeserver.service > /dev/null 2>/dev/null &');		//Time Server Service
	system('sudo systemctl start pistar-watchdog.service > /dev/null 2>/dev/null &');	//PiStar-Watchdog Service
	system('sudo systemctl start pistar-upnp.service > /dev/null 2>/dev/null &');		//PiStar-UPnP Service
	system('sudo systemctl start ysfgateway.service > /dev/null 2>/dev/null &');		//YSFGateway
	system('sudo systemctl start p25gateway.service > /dev/null 2>/dev/null &');		//P25Gateway

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
?>
<form name="factoryReset" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
	<input type="hidden" name="factoryReset" value="1">
</form>

<form name="config" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
    <b>Control Software</b>
    <table>
    <tr>
    <th width="200"><a class=tooltip href="#">Setting<span><b>Setting</b></span></a></th>
    <th><a class=tooltip href="#">Value<span><b>Value</b>The current value from<br />the configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Controller Software:<span><b>Radio Control Software</b>Choose the software used<br />to control the DV Radio Module<br />PLease note that DV Mega hardware<br />will require a firmware upgrade.</span></a></td>
    <?php
	if (file_exists('/etc/dstar-radio.mmdvmhost')) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"controllerSoft\" value=\"DSTAR\" onclick=\"alert('After applying your Configuration Settings, you will need to powercycle your Pi.');\" />DStarRepeater <input type=\"radio\" name=\"controllerSoft\" value=\"MMDVM\" checked>MMDVMHost (DV-Mega Minimum Firmware 3.07 Required)</td>\n";
		}
	elseif (file_exists('/etc/dstar-radio.dstarrepeater')) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"controllerSoft\" value=\"DSTAR\" checked>DStarRepeater <input type=\"radio\" name=\"controllerSoft\" value=\"MMDVM\" onclick=\"alert('After applying your Configuration Settings, you will need to powercycle your Pi.');\" />MMDVMHost (DV-Mega Minimum Firmware 3.07 Required)</td>\n";
	}
	else { // Not set - default to MMDVMHost
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"controllerSoft\" value=\"DSTAR\" onclick=\"alert('After applying your Configuration Settings, you will need to powercycle your Pi.');\" />DStarRepeater <input type=\"radio\" name=\"controllerSoft\" value=\"MMDVM\" checked>MMDVMHost (DV-Mega Minimum Firmware 3.07 Required)</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Controller Mode:<span><b>TRX Mode</b>Choose the mode type<br />Simplex node or<br />Duplex repeater.</span></a></td>
    <?php
	if ($configmmdvm['Info']['RXFrequency'] === $configmmdvm['Info']['TXFrequency']) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"trxMode\" value=\"SIMPLEX\" checked>Simplex Node <input type=\"radio\" name=\"trxMode\" value=\"DUPLEX\">Duplex Repeater</td></tr>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"trxMode\" value=\"SIMPLEX\">Simplex Node <input type=\"radio\" name=\"trxMode\" value=\"DUPLEX\" checked>Duplex Repeater</td></tr>\n";
		}
    ?>
    </tr>
    </table>
    <input type="button" value="Apply Changes" onclick="submitform()" /><br />
<br />
<?php if (file_exists('/etc/dstar-radio.mmdvmhost')) { ?>
    <b>MMDVMHost Configuration</b>
    <table>
    <tr>
    <th width="200"><a class=tooltip href="#">Setting<span><b>Setting</b></span></a></th>
    <th><a class=tooltip href="#">Value<span><b>Value</b>The current value from<br />the configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">DMR Mode Enable:<span><b>DMR Mode Enable</b>Turn on DMR Features</span></a></td>
    <?php
	$testMMDVModeDMR = $configmmdvm['DMR']['Enable'];
	if ( $testMMDVModeDMR == 1 ) {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeDMRon\" value=\"ON\" checked>ON <input type=\"radio\" name=\"MMDVMModeDMRon\" value=\"OFF\">OFF</td>\n";
		}
	else {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeDMRon\" value=\"ON\">ON <input type=\"radio\" name=\"MMDVMModeDMRon\" value=\"OFF\" checked>OFF</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">DMR Network:<span><b>DMR Network</b>Turn on DMR Network</span></a></td>
    <?php
	$testMMDVModeDMRnet = $configmmdvm['DMR Network']['Enable'];
	if ( $testMMDVModeDMRnet == 1 ) {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeDMRneton\" value=\"ON\" checked>ON <input type=\"radio\" name=\"MMDVMModeDMRneton\" value=\"OFF\">OFF</td>\n";
		}
	else {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeDMRneton\" value=\"ON\">ON <input type=\"radio\" name=\"MMDVMModeDMRneton\" value=\"OFF\" checked>OFF</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">D-Star Mode Enable:<span><b>D-Star Mode Enable</b>Turn on D-Star Features</span></a></td>
    <?php
	$testMMDVModeDSTAR = $configmmdvm['D-Star']['Enable'];
	if ( $testMMDVModeDSTAR == 1 ) {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeDSTARon\" value=\"ON\" checked>ON <input type=\"radio\" name=\"MMDVMModeDSTARon\" value=\"OFF\">OFF</td>\n";
		}
	else {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeDSTARon\" value=\"ON\">ON <input type=\"radio\" name=\"MMDVMModeDSTARon\" value=\"OFF\" checked>OFF</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">D-Star Network:<span><b>D-Star Network</b>Turn on D-Star Network</span></a></td>
    <?php
	$testMMDVModeDSTARnet = $configmmdvm['D-Star Network']['Enable'];
	if ( $testMMDVModeDSTARnet == 1 ) {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeDSTARneton\" value=\"ON\" checked>ON <input type=\"radio\" name=\"MMDVMModeDSTARneton\" value=\"OFF\">OFF</td>\n";
		}
	else {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeDSTARneton\" value=\"ON\">ON <input type=\"radio\" name=\"MMDVMModeDSTARneton\" value=\"OFF\" checked>OFF</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Fusion Mode Enable:<span><b>Fusion Mode Enable</b>Turn on Fusion Features</span></a></td>
    <?php
	$testMMDVModeFUSION = $configmmdvm['System Fusion']['Enable'];
	if ( $testMMDVModeFUSION == 1 ) {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeFUSIONon\" value=\"ON\" checked>ON <input type=\"radio\" name=\"MMDVMModeFUSIONon\" value=\"OFF\">OFF</td>\n";
		}
	else {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeFUSIONon\" value=\"ON\">ON <input type=\"radio\" name=\"MMDVMModeFUSIONon\" value=\"OFF\" checked>OFF</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Fusion Network:<span><b>Fusion Network</b>Turn on Fusion Network</span></a></td>
    <?php
	$testMMDVModeFUSIONnet = $configmmdvm['System Fusion Network']['Enable'];
	if ( $testMMDVModeFUSIONnet == 1 ) {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeFUSIONneton\" value=\"ON\" checked>ON <input type=\"radio\" name=\"MMDVMModeFUSIONneton\" value=\"OFF\">OFF</td>\n";
		}
	else {
		echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeFUSIONneton\" value=\"ON\">ON <input type=\"radio\" name=\"MMDVMModeFUSIONneton\" value=\"OFF\" checked>OFF</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">P25 Mode Enable:<span><b>P25 Mode Enable</b>Turn on P25 Features</span></a></td>
    <?php
        $testMMDVModeP25 = $configmmdvm['P25']['Enable'];
        if ( $testMMDVModeP25 == 1 ) {
                echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeP25on\" value=\"ON\" checked>ON <input type=\"radio\" name=\"MMDVMModeP25on\" value=\"OFF\">OFF</td>\n";
                }
        else {
                echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeP25on\" value=\"ON\">ON <input type=\"radio\" name=\"MMDVMModeP25on\" value=\"OFF\" checked>OFF</td>\n";
        }
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">P25 Network:<span><b>P25 Network</b>Turn on P25 Network</span></a></td>
    <?php
        $testMMDVModeP25net = $configmmdvm['P25 Network']['Enable'];
        if ( $testMMDVModeP25net == 1 ) {
                echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeP25neton\" value=\"ON\" checked>ON <input type=\"radio\" name=\"MMDVMModeP25neton\" value=\"OFF\">OFF</td>\n";
                }
        else {
                echo "<td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"MMDVMModeP25neton\" value=\"ON\">ON <input type=\"radio\" name=\"MMDVMModeP25neton\" value=\"OFF\" checked>OFF</td>\n";
        }
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Mode Hangtime:<span><b>Mode Hang Time</b>Stay in the last mode for<br />this many seconds</span></a></td>
    <td align="left"><input type="text" name="hangTime" size="13" maxlength="3" value="<?php echo $configmmdvm['General']['RFModeHang']; ?>"> in seconds (20 secs works well)</td>
    </tr>
    </table>
    <input type="button" value="Apply Changes" onclick="submitform()" /><br />
<br />
    <?php } ?>
    <b>General Configuration</b>
    <table>
    <tr>
    <th width="200"><a class=tooltip href="#">Setting<span><b>Setting</b></span></a></th>
    <th colspan="2"><a class=tooltip href="#">Value<span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Node Callsign:<span><b>Gateway Callsign</b>This is your licenced callsign for use<br />on this gateway, do not append<br />the "G"</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confCallsign" size="13" maxlength="6" value="<?php echo $configs['gatewayCallsign'] ?>"></td>
    </tr>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['DMR']['Enable'] == 1) {
    $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r"); ?>
    <tr>
    <td align="left"><a class=tooltip2 href="#">CCS7/DMR ID:<span><b>CCS7/DMR ID</b>Enter your CCS7 / DMR ID here</span></a></td>
    <td align="left" colspan="2"><input type="text" name="dmrId" size="13" maxlength="9" value="<?php echo $configmmdvm['DMR']['Id']; ?>"></td>
    </tr><?php } ?>
<?php if ($configmmdvm['Info']['TXFrequency'] === $configmmdvm['Info']['RXFrequency']) {
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=tooltip2 href=\"#\">Radio Frequency:<span><b>Radio Frequency</b>This is the Frequency your<br />Pi-Star is on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" name=\"confFREQ\" size=\"13\" maxlength=\"12\" value=\"".number_format($configmmdvm['Info']['RXFrequency'], 0, '.', '.')."\">MHz</td>\n";
	echo "    </tr>\n";
	}
	else {
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=tooltip2 href=\"#\">Radio Frequency RX:<span><b>Radio Frequency</b>This is the Frequency your<br />repeater will listen on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" name=\"confFREQrx\" size=\"13\" maxlength=\"12\" value=\"".number_format($configmmdvm['Info']['RXFrequency'], 0, '.', '.')."\">MHz</td>\n";
	echo "    </tr>\n";
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=tooltip2 href=\"#\">Radio Frequency TX:<span><b>Radio Frequency</b>This is the Frequency your<br />repeater will transmit on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" name=\"confFREQtx\" size=\"13\" maxlength=\"12\" value=\"".number_format($configmmdvm['Info']['TXFrequency'], 0, '.', '.')."\">MHz</td>\n";
	echo "    </tr>\n";
	}
?>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Latitude:<span><b>Gateway Latitude</b>This is the latitude where the<br />gateway is located (positive<br />number for North, negative<br />number for South)</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confLatitude" size="13" maxlength="9" value="<?php echo $configs['latitude'] ?>">degrees (positive value for North, negative for South)</td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Longitude:<span><b>Gateway Longitude</b>This is the longitude where the<br />gateway is located (positive<br />number for East, negative<br />number for West)</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confLongitude" size="13" maxlength="9" value="<?php echo $configs['longitude'] ?>">degrees (positive value for East, negative for West)</td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Town:<span><b>Gateway Town</b>The town where the gateway<br />is located</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confDesc1" size="30" maxlength="30" value="<?php echo $configs['description1'] ?>"></td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Country:<span><b>Gateway Country</b>The country where the gateway<br />is located</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confDesc2" size="30" maxlength="30" value="<?php echo $configs['description2'] ?>"></td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">URL:<span><b>Gateway URL</b>The URL used to access<br />this dashboard</span></a></td>
    <td align="left"><input type="text" name="confURL" size="30" maxlength="30" value="<?php echo $configs['url'] ?>"></td>
    <td width="300">
    <input type="radio" name="urlAuto" value="auto"<?php if (strpos($configs['url'], 'www.qrz.com') !== FALSE) {echo ' checked';} ?>>Auto
    <input type="radio" name="urlAuto" value="man"<?php if (strpos($configs['url'], 'www.qrz.com') == FALSE) {echo ' checked';} ?>>Manual</td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Radio/Modem Type:<span><b>Radio/Modem</b>What kind of radio or modem<br />hardware do you have ?</span></a></td>
    <td align="left" colspan="2"><select name="confHardware">
                <option<?php if ($configdstar['dvmegaPort'] === '/dev/ttyAMA0') { echo ' selected';}?> value="DVM-RPI">DV-Mega RPi Radio</option>
                <option<?php if ($configdstar['dvmegaPort'] === '/dev/ttyACM0' && $configdstar[dvmegaVariant] >= 1 ) { echo ' selected';}?> value="DVM-USB">Zum Board / DV-Mega USB Radio / DV-Mega USB GMSK Node (Old Firmware)</option>
                <option<?php if ($configdstar['dvmegaVariant'] === '0') { echo ' selected';}?> value="DVM-GMSK">Blue-DV / Bluestack / DV-Mega USB GMSK Node (New Firmware)</option>
                <option<?php if ($configdstar['modemType'] === 'DV-RPTR V1') { echo ' selected';}?> value="DV-RPTR1">DV-RPTR V1</option>
                <option<?php if ($configdstar['modemType'] === 'DV-RPTR V2') { echo ' selected';}?> value="DV-RPTR2">DV-RPTR V2</option>
                <option<?php if ($configdstar['modemType'] === 'DV-RPTR V3') { echo ' selected';}?> value="DV-RPTR3">DV-RPTR V3</option>
                <option<?php if ($configdstar['modemType'] === 'DVAP') { echo ' selected';}?> value="DVAP">DVAP</option>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Node Type:<span><b>Node Lock</b>Set the public / private<br />node type. Public should<br />only be used with the correct<br />licence.</span></a></td>
    <td align="left" colspan="2">
    <input type="radio" name="nodeMode" value="prv"<?php if ($configmmdvm['DMR']['SelfOnly'] == 1) {echo ' checked';} ?>>Private
    <input type="radio" name="nodeMode" value="pub"<?php if ($configmmdvm['DMR']['SelfOnly'] == 0) {echo ' checked';} ?>>Public</td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">System Time Zone:<span><b>System TimeZone</b>Set the system timezone</span></a></td>
    <td style="text-align: left;" colspan="2"><select name="systemTimezone">
<?php
  exec('timedatectl list-timezones', $tzList);
  exec('cat /etc/timezone', $tzCurrent);
    foreach ($tzList as $timeZone) {
      if ($timeZone == $tzCurrent[0]) { echo "      <option selected value=\"".$timeZone."\">".$timeZone."</option>\n"; }
      else { echo "      <option value=\"".$timeZone."\">".$timeZone."</option>\n"; }
    }
?>
    </select></td>
    </tr>	
    </table>
    <input type="button" value="Apply Changes" onclick="submitform()" /><br />
<br />
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['DMR']['Enable'] == 1) {
    $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r"); ?>
    <b>DMR Configuration</b>
    <table>
    <tr>
    <th width="200"><a class=tooltip href="#">Setting<span><b>Setting</b></span></a></th>
    <th><a class=tooltip href="#">Value<span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">DMR Master:<span><b>DMR Master</b>Set your prefered DMR<br /> master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost">
<?php
        $testMMDVMdmrMaster = $configmmdvm['DMR Network']['Address'];
        while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHost = preg_split('/\s+/', $dmrMasterLine);
                if ((strpos($dmrMasterHost[0], '#') === FALSE ) && ($dmrMasterHost[0] != '')) {
                        if ($testMMDVMdmrMaster == $dmrMasterHost[2]) { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\" selected>$dmrMasterHost[0]</option>\n"; $dmrMasterNow = $dmrMasterHost[0]; }
                        else { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\">$dmrMasterHost[0]</option>\n"; }
                }
        }
        fclose($dmrMasterFile);
        ?>
    </select></td>
    </tr>
<?php
    if (substr($dmrMasterNow, 0, 2) == "BM") { echo '    <tr>
    <td align="left"><a class=tooltip2 href="#">BrandMeister Network:<span><b>BrandMeister Dashboards</b>Direct links to your<br />BrandMeister Dashboards</span></a></td>
    <td>
      <a href="https://brandmeister.network/?page=hotspot&amp;id='.$configmmdvm['DMR']['Id'].'" target="_new" style="color: #000;">Repeater Information</a> | 
      <a href="https://brandmeister.network/?page=hotspot-edit&amp;id='.$configmmdvm['DMR']['Id'].'" target="_new" style="color: #000;">Edit Repeater (BrandMeister Selfcare)</a>
    </td>
    </tr>'."\n";}
    if (substr($dmrMasterNow, 0, 4) == "DMR+") { echo '    <tr>
    <td align="left"><a class=tooltip2 href="#">DMR+ Network:<span><b>DMR+ Network</b>Set your options=<br />for DMR+ here</span></a></td>
    <td align="left">
    Options=<input type="text" name="dmrNetworkOptions" size="75" maxlength="100" value="'.$configmmdvm['DMR Network']['Options'].'">
    </td>
    </tr>'."\n";}
?>
    <tr>
    <td align="left"><a class=tooltip2 href="#">DMR Color Code:<span><b>DMR Color Code</b>Set your DMR Color Code here</span></a></td>
    <td style="text-align: left;"><select name="dmrColorCode">
	<?php for ($dmrColorCodeInput = 1; $dmrColorCodeInput <= 15; $dmrColorCodeInput++) {
		if ($configmmdvm['DMR']['ColorCode'] == $dmrColorCodeInput) { echo "<option selected value=\"$dmrColorCodeInput\">$dmrColorCodeInput</option>\n"; }
		else {echo "<option value=\"$dmrColorCodeInput\">$dmrColorCodeInput</option>\n"; }
	} ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">DMR EmbeddedLCOnly:<span><b>DMR EmbeddedLCOnly</b>Set EmbeddedLCOnly to ON<br />to help reduce problems<br />with some DMR Radios</span></a></td>
    <td align="left">
    <input type="radio" name="dmrEmbeddedLCOnly" value="1"<?php if ($configmmdvm['DMR']['EmbeddedLCOnly'] == 1) {echo ' checked';} ?>>Enabled
    <input type="radio" name="dmrEmbeddedLCOnly" value="0"<?php if ($configmmdvm['DMR']['EmbeddedLCOnly'] == 0) {echo ' checked';} ?>>Disabled
    </td></tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">DMR DumpTAData:<span><b>DMR DumpTAData</b>Turn on for extended<br />message support, including<br />GPS.</span></a></td>
    <td align="left">
    <input type="radio" name="dmrDumpTAData" value="1"<?php if ($configmmdvm['DMR']['DumpTAData'] == 1) {echo ' checked';} ?>>Enabled
    <input type="radio" name="dmrDumpTAData" value="0"<?php if ($configmmdvm['DMR']['DumpTAData'] == 0) {echo ' checked';} ?>>Disabled
    </td></tr>
    </table>
    <input type="button" value="Apply Changes" onclick="submitform()" /><br />
<br /><?php } ?>

<?php if (file_exists('/etc/dstar-radio.dstarrepeater') || $configmmdvm['D-Star']['Enable'] == 1) { ?>
    <b>D-Star Configuration</b>
    <table>
    <tr>
    <th width="200"><a class=tooltip href="#">Setting<span><b>Setting</b></span></a></th>
    <th colspan="2"><a class=tooltip href="#">Value<span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">RPT1 Callsign:<span><b>RPT1 Callsign</b>This is the RPT1 field for your radio</span></a></td>
    <td align="left" colspan="2"><?php echo str_replace(' ', '&nbsp;', $configdstar['callsign']) ?></td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">RPT2 Callsign:<span><b>RPT2 Callsign</b>This is the RPT2 field for your radio</span></a></td>
    <td align="left" colspan="2"><?php echo str_replace(' ', '&nbsp;', $configdstar['gateway']) ?></td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">ircDDBGateway Password:<span><b>Gateway Password</b>Used for any kind of remote<br />access to this system</span></a></td>
    <td align="left" colspan="2"><input type="password" name="confPassword" size="30" maxlength="30" value="<?php echo $configs['remotePassword'] ?>"></td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Default Reflector:<span><b>Default Refelctor</b>Used for setting the<br />default reflector.</span></a></td>
    <td align="left" colspan="1"><select name="confDefRef"
	onchange="if (this.options[this.selectedIndex].value == 'customOption') {
	  toggleField(this,this.nextSibling);
	  this.selectedIndex='0';
	  } ">
<?php
$dcsFile = fopen("/usr/local/etc/DCS_Hosts.txt", "r");
$dplusFile = fopen("/usr/local/etc/DPlus_Hosts.txt", "r");
$dextraFile = fopen("/usr/local/etc/DExtra_Hosts.txt", "r");

// echo "  <option selected>".substr($configs['reflector1'], 0, 6)."</option>\n";
echo "    <option value=\"".substr($configs['reflector1'], 0, 6)."\" selected>".substr($configs['reflector1'], 0, 6)."</option>\n";
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
            onblur="if(this.value==''){toggleField(this,this.previousSibling);}">
    <select name="confDefRefLtr">
	<?php echo "  <option value=\"".substr($configs['reflector1'], 7)."\" selected>".substr($configs['reflector1'], 7)."</option>\n"; ?>
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
    <input type="radio" name="confDefRefAuto" value="ON"<?php if ($configs['atStartup1'] == '1') {echo ' checked';} ?>>Startup
    <input type="radio" name="confDefRefAuto" value="OFF"<?php if ($configs['atStartup1'] == '0') {echo ' checked';} ?>>Manual</td>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">APRS Host:<span><b>APRS Host</b>Set your prefered APRS host here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="selectedAPRSHost">
<?php
        $testAPSRHost = $configs['aprsHostname'];
    	$aprsHostFile = fopen("/usr/local/etc/APRSHosts.txt", "r");
        while (!feof($aprsHostFile)) {
                $aprsHostFileLine = fgets($aprsHostFile);
                $aprsHost = preg_split('/:/', $aprsHostFileLine);
                if ((strpos($aprsHost[0], ';') === FALSE ) && ($aprsHost[0] != '')) {
                        if ($testAPSRHost == $aprsHost[0]) { echo "      <option value=\"$aprsHost[0]\" selected>$aprsHost[0]</option>\n"; }
                        else { echo "      <option value=\"$aprsHost[0]\">$aprsHost[0]</option>\n"; }
                }
        }
        fclose($aprsHostFile);
        ?>
    </select></td>
    </tr>
    </table>
    <input type="button" value="Apply Changes" onclick="submitform()" /><br />
<br /><?php } ?>
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['System Fusion Network']['Enable'] == 1) {
$ysfHosts = fopen("/usr/local/etc/YSFHosts.txt", "r"); ?>
    <b>Yaesu System Fusion Configuration</b>
    <table>
    <tr>
    <th width="200"><a class=tooltip href="#">Setting<span><b>Setting</b></span></a></th>
    <th colspan="2"><a class=tooltip href="#">Value<span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">YSF Startup Host:<span><b>YSF Host</b>Set your prefered<br /> YSF Host here</span></a></td>
    <td style="text-align: left;"><select name="ysfStartupHost">
<?php
        $testYSFHost = $configysfgateway['Network']['Startup'];
        while (!feof($ysfHosts)) {
                $ysfHostsLine = fgets($ysfHosts);
                $ysfHost = preg_split('/;/', $ysfHostsLine);
                if ((strpos($ysfHost[0], '#') === FALSE ) && ($ysfHost[0] != '')) {
                        if ($testYSFHost == $ysfHost[0]) { echo "      <option value=\"$ysfHost[0]\" selected>$ysfHost[0] - $ysfHost[1] - $ysfHost[2]</option>\n"; }
                        else { echo "      <option value=\"$ysfHost[0]\">$ysfHost[0] - $ysfHost[1] - $ysfHost[2]</option>\n"; }
                }
        }
        fclose($ysfHosts);
        ?>
    </select></td>
    </tr>
    </table>
    <input type="button" value="Apply Changes" onclick="submitform()" /><br />
<br /><?php } ?>
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['P25 Network']['Enable'] == 1) {
$p25Hosts = fopen("/usr/local/etc/P25Hosts.txt", "r"); ?>
    <b>P25 Configuration</b>
    <table>
    <tr>
    <th width="200"><a class=tooltip href="#">Setting<span><b>Setting</b></span></a></th>
    <th colspan="2"><a class=tooltip href="#">Value<span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">P25 Startup Host:<span><b>P25 Host</b>Set your prefered<br /> P25 Host here</span></a></td>
    <td style="text-align: left;"><select name="p25StartupHost">
<?php
        $testP25Host = $configp25gateway['Network']['Startup'];
        while (!feof($p25Hosts)) {
                $p25HostsLine = fgets($p25Hosts);
                $p25Host = preg_split('/\s+/', $p25HostsLine);
                if ((strpos($p25Host[0], '#') === FALSE ) && ($p25Host[0] != '')) {
                        if ($testP25Host == $p25Host[0]) { echo "      <option value=\"$p25Host[0]\" selected>$p25Host[0] - $p25Host[1]</option>\n"; }
                        else { echo "      <option value=\"$p25Host[0]\">$p25Host[0] - $p25Host[1]</option>\n"; }
                }
        }
        fclose($p25Hosts);
        ?>
    </select></td>
    </tr>
    </table>
<input type="button" value="Apply Changes" onclick="submitform()" /><br />
<br /><?php } ?>	
    <b>Firewall Configuration</b>
    <table>
    <tr>
    <th width="200"><a class=tooltip href="#">Setting<span><b>Setting</b></span></a></th>
    <th colspan="2"><a class=tooltip href="#">Value<span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">Dashboard Access:<span><b>Dashboard Access</b>Do you want the dashboard access<br />to be publicly available? This<br />modifies the uPNP firewall<br />Configuration.</span></a></td>
    <?php
	$testPrvPubDash = exec('sudo sed -n 32p /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubDash, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"dashAccess\" value=\"PRV\" checked>Private <input type=\"radio\" name=\"dashAccess\" value=\"PUB\">Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"dashAccess\" value=\"PRV\">Private <input type=\"radio\" name=\"dashAccess\" value=\"PUB\" checked>Public</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">ircDDBGateway Remote:<span><b>ircDDBGateway Remote Access</b>Do you want the ircDDBGateway<br />remote controll access to be<br />publicly available? This modifies<br />the uPNP firewall Configuration.</span></a></td>
    <?php
	$testPrvPubIRC = exec('sudo sed -n 33p /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubIRC, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"ircRCAccess\" value=\"PRV\" checked>Private <input type=\"radio\" name=\"ircRCAccess\" value=\"PUB\">Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"ircRCAccess\" value=\"PRV\">Private <input type=\"radio\" name=\"ircRCAccess\" value=\"PUB\" checked>Public</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class=tooltip2 href="#">SSH Access:<span><b>SSH Access</b>Do you want access to be<br />publicly available over SSH (used<br />for support issues)? This modifies<br />the uPNP firewall Configuration.</span></a></td>
    <?php
	$testPrvPubSSH = exec('sudo sed -n 31p /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubSSH, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"sshAccess\" value=\"PRV\" checked>Private <input type=\"radio\" name=\"sshAccess\" value=\"PUB\">Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"sshAccess\" value=\"PRV\">Private <input type=\"radio\" name=\"sshAccess\" value=\"PUB\" checked>Public</td>\n";
	}
    ?>
    </tr>
    </table>
    <input type="button" value="Apply Changes" onclick="submitform()" />
    </form>

<?php
	exec('ifconfig wlan0',$return);
	exec('iwconfig wlan0',$return);
	$strWlan0 = implode(" ",$return);
	$strWlan0 = preg_replace('/\s\s+/', ' ', $strWlan0);
	preg_match('/HWaddr ([0-9a-f:]+)/i',$strWlan0,$result);
	$strHWAddress = $result['1'];

	if ( isset($strHWAddress) ) {
echo '
<br />
    <b>Wireless Configuration</b>
    <table><tr><td>
    <iframe frameborder="0" scrolling="auto" name="wifi" src="wifi.php?page=wlan0_info" width="100%" onload="resizeIframe(this)">If you can see this message, your browser does not support iFrames, however if you would like to see the content please click <A HREF="wifi.php?page=wlan0_info">here</a>.</iframe>
    </td></tr></table>'; } ?>

<br />
    <b>Remote Access Password</b>
    <form name="adminPassForm" action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
    <table>
    <tr><th width="200">User</th><th colspan="3">Password</th></tr>
    <tr>
    <td align="left"><b>pi-star</b></td>
    <td align="left"><input type="password" name="adminPassword" size="30" value""></td>
    <td align="right"><input type="button" value="Set Password" onclick="submitPassform()" /></td>
    </tr>
    <tr><td colspan="3"><B>WARNING: </B>This changes the passowrd for this admin page<br />AND the "pi-star" SSH account</td></tr>
    </table>
    </form>
<?php endif; ?>
<br />
</div>
<div id="footer">
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
<div id="footer">
Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistar/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.mw0mwz.co.uk/pi-star/" target="_blank">here</a>.<br />
<br />
</div>
</div>
</body>
</html>
<?php } ?>
