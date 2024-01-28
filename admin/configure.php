<?php
// Get the CPU temp and colour the box accordingly...
$cpuTempCRaw = exec('cat /sys/class/thermal/thermal_zone0/temp');
if ($cpuTempCRaw > 1000) { $cpuTempC = round($cpuTempCRaw / 1000, 1); } else { $cpuTempC = round($cpuTempCRaw, 1); }
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
                	list($key1,$value1) = preg_split('/=/',$line1);
                	$value1 = trim(str_replace('"','',$value1));
                	if (strlen($value1) > 0)
                	$configdstar[$key1] = $value1;
		}
        }
        fclose($configdstarfile);
}

// Load the ircDDBGateway config file
$configs = array();
if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
		if (strpos($line, '=') !== false) {
                	list($key,$value) = preg_split('/=/',$line);
                	$value = trim(str_replace('"','',$value));
                	if ($key != 'ircddbPassword' && strlen($value) > 0)
                	$configs[$key] = $value;
		}
        }
        fclose($configfile);
}

// Load the mmdvmhost config file
$mmdvmConfigFile = '/etc/mmdvmhost';
$configmmdvm = parse_ini_file($mmdvmConfigFile, true);

// Load the ysfgateway config file
$ysfgatewayConfigFile = '/etc/ysfgateway';
$configysfgateway = parse_ini_file($ysfgatewayConfigFile, true);

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

// Load the dgidgateway config file
if (file_exists('/etc/dgidgateway')) {
	$dgidgatewayConfigFile = '/etc/dgidgateway';
	if (fopen($dgidgatewayConfigFile,'r')) { $configdgidgateway = parse_ini_file($dgidgatewayConfigFile, true); }
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

// Load the p25gateway config file
if (file_exists('/etc/p25gateway')) {
	$p25gatewayConfigFile = '/etc/p25gateway';
	if (fopen($p25gatewayConfigFile,'r')) { $configp25gateway = parse_ini_file($p25gatewayConfigFile, true); }
}

// Load the nxdngateway config file
if (file_exists('/etc/nxdngateway')) {
	$nxdngatewayConfigFile = '/etc/nxdngateway';
	if (fopen($nxdngatewayConfigFile,'r')) { $confignxdngateway = parse_ini_file($nxdngatewayConfigFile, true); }
}

// Load the m17gateway config file
if (file_exists('/etc/m17gateway')) {
	$m17gatewayConfigFile = '/etc/m17gateway';
	if (fopen($m17gatewayConfigFile,'r')) { $configm17gateway = parse_ini_file($m17gatewayConfigFile, true); }
}

// Load the nxdn2dmr config file
if (file_exists('/etc/nxdn2dmr')) {
	$nxdn2dmrConfigFile = '/etc/nxdn2dmr';
	if (fopen($nxdn2dmrConfigFile,'r')) { $confignxdn2dmr = parse_ini_file($nxdn2dmrConfigFile, true); }
}

// DAPNet Gateway config
if (file_exists('/etc/dapnetgateway')) {
	$configDAPNetConfigFile = '/etc/dapnetgateway';
	if (fopen($configDAPNetConfigFile,'r')) { $configdapnetgw = parse_ini_file($configDAPNetConfigFile, true); }
}

// APRS Gateway config
if (file_exists('/etc/aprsgateway')) {
	$configAPRSconfigFile = '/etc/aprsgateway';
	if (fopen($configAPRSconfigFile,'r')) { $configaprsgw = parse_ini_file($configAPRSconfigFile, true); }
}

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
$MYCALL=strtoupper($configs['gatewayCallsign']);
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
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/nice-select.min.css?ver=<?php echo $configPistarRelease['Pi-Star']['Version']; ?>" />
    <meta http-equiv="Expires" content="0" />
    <title><?php echo "$MYCALL"." - ".$lang['digital_voice']." ".$lang['dashboard']." - ".$lang['configuration'];?></title>
    <link rel="stylesheet" type="text/css" href="css/pistar-css.php?version=0.95" />
    <script type="text/javascript">
	function disablesubmitbuttons() {
		var inputs = document.getElementsByTagName('input');
		for (var i = 0; i < inputs.length; i++) {
			if (inputs[i].type === 'button') {
				inputs[i].disabled = true;
			}
		}
	}
	function submitform() {
		disablesubmitbuttons();
		document.getElementById("config").submit();
	}
	function submitPassform() {
		disablesubmitbuttons();
		document.getElementById("adminPassForm").submit();
	}
	function submitPskform() {
		disablesubmitbuttons();
		document.getElementById("autoApPassForm").submit();
	}
	function factoryReset() {
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
    <script type="text/javascript" src="/functions.js?version=1.715"></script>
</head>
<body onload="checkFrequency(); return false;">
<?php
if ( ($configPistarRelease['Pi-Star']['Version'] < 4.1) && ($configPistarRelease['Pi-Star']['Hardware'] == "RPi") ) {
?>
<div>
  <table align="center" width="760px" style="margin: 0px 0px 10px 0px; width: 100%;">
    <tr>
    <td align="center" valign="top" style="background-color: #ffff90; color: #906000;">Alert: You are running an outdated version of Pi-Star, please upgrade.<br />
    New versions are available from the here: <a href="http://www.pistar.uk/downloads/" alt="Pi-Star Downloads">http://www.pistar.uk/downloads/</a>.</td>
    </tr>
  </table>
</div>
<?php }
if ( (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['DMR']['Enable'] == 1 && $configmmdvm['DMR']['SelfOnly'] == 0 && strlen($configmmdvm['General']['Id']) >= 7 ) && (!isset($configmmdvm['DMR']['WhiteList'])) ) {
?>
<div>
  <table align="center" width="760px" style="margin: 0px 0px 10px 0px; width: 100%;">
    <tr>
    <td align="center" valign="top" style="background-color: #ffff90; color: #906000;">Alert: You are running a hotspot in public mode without an access list for DMR, this setup *could* participate in network loops!</td>
    </tr>
  </table>
</div>
<?php } 
if ( ($configPistarRelease['Pi-Star']['Version'] >= "4.1") && ($configPistarRelease['Pi-Star']['Version'] < "4.1.7") ) {
?>
<div>
  <table align="center" width="760px" style="margin: 0px 0px 10px 0px; width: 100%;">
    <tr>
    <td align="center" valign="top" style="background-color: #ffff90; color: #906000;">Alert: An upgrade to Pi-Star has been released, click here to upgrade now: <a href="/admin/expert/upgrade.php" alt="Upgrade Pi-Star">Upgrade Pi-Star</a>.</td>
    </tr>
  </table>
</div>
<?php } ?>
<?php
$bmAPIkeyFile = '/etc/bmapi.key';
if (file_exists($bmAPIkeyFile) && fopen($bmAPIkeyFile,'r')) {
  $configBMapi = parse_ini_file($bmAPIkeyFile, true);
  $bmAPIkey = $configBMapi['key']['apikey'];
  // Check the BM API Key
  if ( strlen($bmAPIkey) <= 200 ) {
?>
<div>
  <table align="center" width="760px" style="margin: 0px 0px 10px 0px; width: 100%;">
    <tr>
    <td align="center" valign="top" style="background-color: #ff9090; color: #f01010;">Alert: You have a BM API v1 Key, click here for the announcement: <a href="https://news.brandmeister.network/introducing-user-api-keys/" alt="BM API Keys">BM API Keys - Announcement</a>.</td>
    </tr>
  </table>
</div>
<?php } } ?>
<div class="container">
<div class="header">
<div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / <?php echo $lang['dashboard'].": ".$version; ?></div>
<h1>Pi-Star <?php echo $lang['digital_voice']." - ".$lang['configuration'];?></h1>
<p style="padding-right: 5px; text-align: right; color: #ffffff;">
 <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
 <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
 <a href="/admin/expert/" style="color: #ffffff;">Expert</a> |
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
    <h2><?php echo $lang['hardware_info'];?></h2>
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
    <td colspan="2"><?php echo exec('/usr/local/bin/platformDetect.sh');?></td>
    <td><?php echo $cpuLoad[0];?> / <?php echo $cpuLoad[1];?> / <?php echo $cpuLoad[2];?></td>
    <?php echo $cpuTempHTML; ?>
    </tr>
    </table>
<br />
<?php if (!empty($_POST)):
	// Make the root filesystem writable
	system('sudo mount -o remount,rw /');

	// Admin Password Change
	if (empty($_POST['adminPassword']) != TRUE ) {
	  $rollAdminPass0 = 'htpasswd -b /var/www/.htpasswd pi-star \''.stripslashes(trim($_POST['adminPassword'])).'\'';
	  system($rollAdminPass0);
	  $rollAdminPass2 = 'sudo echo -e \''.stripslashes(trim($_POST['adminPassword'])).'\n'.stripslashes(trim($_POST['adminPassword'])).'\' | sudo passwd pi-star';
	  system($rollAdminPass2);
	  unset($_POST);
	  echo "<table>\n";
	  echo "<tr><th>Working...</th></tr>\n";
	  echo "<tr><td>Applying your configuration changes...</td></tr>\n";
	  echo "</table>\n";
	  echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
	  echo "<br />\n</div>\n";
          echo "<div class=\"footer\">\nPi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-".date("Y").".<br />\n";
          echo "Need help? Click <a style=\"color: #ffffff;\" href=\"https://www.facebook.com/groups/pistarusergroup/\" target=\"_new\">here for the Support Group</a><br />\n";
          echo "Get your copy of Pi-Star from <a style=\"color: #ffffff;\" href=\"http://www.pistar.uk/downloads/\" target=\"_blank\">here</a>.<br />\n";
          echo "<br />\n</div>\n</div>\n</body>\n</html>\n";
	  die();
	  }

	// AutoAP PSK Change
	if (empty($_POST['autoapPsk']) != TRUE ) {
	  $rollAutoApPsk = 'sudo sed -i "/wpa_passphrase=/c\\wpa_passphrase='.$_POST['autoapPsk'].'" /etc/hostapd/hostapd.conf';
	  system($rollAutoApPsk);
	  $rollAutoApWPA = 'sudo sed -i "/wpa=/c\\wpa=2" /etc/hostapd/hostapd.conf';
	  system($rollAutoApWPA);
	  unset($_POST);
	  echo "<table>\n";
	  echo "<tr><th>Working...</th></tr>\n";
	  echo "<tr><td>Applying your configuration changes...</td></tr>\n";
	  echo "</table>\n";
	  echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
	  echo "<br />\n</div>\n";
          echo "<div class=\"footer\">\nPi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-".date("Y").".<br />\n";
          echo "Need help? Click <a style=\"color: #ffffff;\" href=\"https://www.facebook.com/groups/pistarusergroup/\" target=\"_new\">here for the Support Group</a><br />\n";
          echo "Get your copy of Pi-Star from <a style=\"color: #ffffff;\" href=\"http://www.pistar.uk/downloads/\" target=\"_blank\">here</a>.<br />\n";
          echo "<br />\n</div>\n</div>\n</body>\n</html>\n";
	  die();
	  }

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
	system('sudo systemctl stop ysf2dmr.service > /dev/null 2>/dev/null &');		// YSF2DMR
	system('sudo systemctl stop ysf2nxdn.service > /dev/null 2>/dev/null &');		// YSF2NXDN
	system('sudo systemctl stop ysf2p25.service > /dev/null 2>/dev/null &');		// YSF2P25
	system('sudo systemctl stop nxdn2dmr.service > /dev/null 2>/dev/null &');		// NXDN2DMR
	system('sudo systemctl stop ysfparrot.service > /dev/null 2>/dev/null &');		// YSFParrot
	system('sudo systemctl stop p25gateway.service > /dev/null 2>/dev/null &');		// P25Gateway
	system('sudo systemctl stop p25parrot.service > /dev/null 2>/dev/null &');		// P25Parrot
	system('sudo systemctl stop nxdngateway.service > /dev/null 2>/dev/null &');		// NXDNGateway
	system('sudo systemctl stop nxdnparrot.service > /dev/null 2>/dev/null &');		// NXDNParrot
	system('sudo systemctl stop m17gateway.service > /dev/null 2>/dev/null &');		// M17Gateway
	system('sudo systemctl stop dmr2ysf.service > /dev/null 2>/dev/null &');		// DMR2YSF
	system('sudo systemctl stop dmr2nxdn.service > /dev/null 2>/dev/null &');		// DMR2YSF
	system('sudo systemctl stop dmrgateway.service > /dev/null 2>/dev/null &');		// DMRGateway
	system('sudo systemctl stop dapnetgateway.service > /dev/null 2>/dev/null &');		// DAPNetGateway
	system('sudo systemctl stop aprsgateway.service > /dev/null 2>/dev/null &');		// APRSGateway

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
	  exec('sudo rm -rf /etc/pistar-css.ini');
	  exec('sudo git --work-tree=/usr/local/sbin --git-dir=/usr/local/sbin/.git update-index --assume-unchanged pistar-upnp.service');
	  exec('sudo git --work-tree=/usr/local/sbin --git-dir=/usr/local/sbin/.git reset --hard origin/master');
	  exec('sudo git --work-tree=/usr/local/bin --git-dir=/usr/local/bin/.git reset --hard origin/master');
	  exec('sudo git --work-tree=/var/www/dashboard --git-dir=/var/www/dashboard/.git reset --hard origin/master');
          echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},5000);</script>';
	  // Make the root filesystem read-only
          system('sudo sync && sudo sync && sudo sync && sudo mount -o remount,ro /');
	  echo "<br />\n</div>\n";
          echo "<div class=\"footer\">\nPi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-".date("Y").".<br />\n";
          echo "Need help? Click <a style=\"color: #ffffff;\" href=\"https://www.facebook.com/groups/pistarusergroup/\" target=\"_new\">here for the Support Group</a><br />\n";
          echo "Get your copy of Pi-Star from <a style=\"color: #ffffff;\" href=\"http://www.pistar.uk/downloads/\" target=\"_blank\">here</a>.<br />\n";
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
	  echo "<br />\n</div>\n";
          echo "<div class=\"footer\">\nPi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-".date("Y").".<br />\n";
          echo "Need help? Click <a style=\"color: #ffffff;\" href=\"https://www.facebook.com/groups/pistarusergroup/\" target=\"_new\">here for the Support Group</a><br />\n";
          echo "Get your copy of Pi-Star from <a style=\"color: #ffffff;\" href=\"http://www.pistar.uk/downloads/\" target=\"_blank\">here</a>.<br />\n";
          echo "<br />\n</div>\n</div>\n</body>\n</html>\n";
	  die();
	  }

	// Change Radio Control Software
	if (empty($_POST['controllerSoft']) != TRUE ) {
	  system('sudo rm -rf /etc/dstar-radio.*');
	  if (escapeshellcmd($_POST['controllerSoft']) == 'DSTAR') { system('sudo touch /etc/dstar-radio.dstarrepeater'); }
	  if (escapeshellcmd($_POST['controllerSoft']) == 'MMDVM') { system('sudo touch /etc/dstar-radio.mmdvmhost'); }
	  }

	// HostAP
	if (empty($_POST['autoAP']) != TRUE ) {
	  if (escapeshellcmd($_POST['autoAP']) == 'OFF') { system('sudo touch /etc/hostap.off'); }
	  if (escapeshellcmd($_POST['autoAP']) == 'ON') { system('sudo rm -rf /etc/hostap.off'); }
	  }

	// Change Dashboard Language
	if (empty($_POST['dashboardLanguage']) != TRUE ) {
	  $rollDashLang = 'sudo sed -i "/pistarLanguage=/c\\$pistarLanguage=\''.escapeshellcmd($_POST['dashboardLanguage']).'\';" /var/www/dashboard/config/language.php';
	  system($rollDashLang);
	  }

	// Set the ircDDBGAteway Remote Password and Port
	if (empty($_POST['confPassword']) != TRUE ) {
	  $rollConfPassword0 = 'sudo sed -i "/remotePassword=/c\\remotePassword='.escapeshellcmd($_POST['confPassword']).'" /etc/ircddbgateway';
	  $rollConfPassword1 = 'sudo sed -i "/password=/c\\password='.escapeshellcmd($_POST['confPassword']).'" /root/.Remote\ Control';
	  $rollConfRemotePort = 'sudo sed -i "/port=/c\\port='.$configs['remotePort'].'" /root/.Remote\ Control';
	  system($rollConfPassword0);
	  system($rollConfPassword1);
	  system($rollConfRemotePort);
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

	// Set random (working) CCS host.
	if ($configs['ccsEnabled'] == "1") {
	  $activeCCS = array("CCS701"=>"CCS701","CCS702"=>"CCS702","CCS704"=>"CCS704");
	  shuffle($activeCCS);
	  $rollCCS = 'sudo sed -i "/ccsHost=/c\\ccsHost='.$activeCCS[0].'" /etc/ircddbgateway';
	  system($rollCCS);
	}

	// Set the Latitude
	if (empty($_POST['confLatitude']) != TRUE ) {
	  $newConfLatitude = preg_replace('/[^0-9\.\-]/', '', $_POST['confLatitude']);
	  $rollConfLat0 = 'sudo sed -i "/latitude=/c\\latitude='.$newConfLatitude.'" /etc/ircddbgateway';
	  $rollConfLat1 = 'sudo sed -i "/latitude1=/c\\latitude1='.$newConfLatitude.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Latitude'] = $newConfLatitude;
	  $configysfgateway['Info']['Latitude'] = $newConfLatitude;
	  $configysf2dmr['Info']['Latitude'] = $newConfLatitude;
	  $configysf2nxdn['Info']['Latitude'] = $newConfLatitude;
	  $configysf2p25['Info']['Latitude'] = $newConfLatitude;
	  if (isset($configdgidgateway)) { $configdgidgateway['Info']['Latitude'] = $newConfLatitude; }
	  $configdmrgateway['Info']['Latitude'] = $newConfLatitude;
	  $confignxdngateway['Info']['Latitude'] = $newConfLatitude;
	  if (isset($configm17gateway['Info']['Latitude'])) { $configm17gateway['Info']['Latitude'] = $newConfLatitude; }
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
	  $configysf2dmr['Info']['Longitude'] = $newConfLongitude;
	  $configysf2nxdn['Info']['Longitude'] = $newConfLongitude;
	  $configysf2p25['Info']['Longitude'] = $newConfLongitude;
	  if (isset($configdgidgateway)) { $configdgidgateway['Info']['Longitude'] = $newConfLongitude; }
	  $configdmrgateway['Info']['Longitude'] = $newConfLongitude;
	  $confignxdngateway['Info']['Longitude'] = $newConfLongitude;
	  if (isset($configm17gateway['Info']['Longitude'])) { $configm17gateway['Info']['Longitude'] = $newConfLongitude; }
	  system($rollConfLon0);
	  system($rollConfLon1);
	  }

	// Set the Town
	if (empty($_POST['confDesc1']) != TRUE ) {
	  $newConfDesc1 = preg_replace('/[^A-Za-z0-9\.\s\,\-]/', '', $_POST['confDesc1']);
	  $rollDesc1 = 'sudo sed -i "/description1=/c\\description1='.$newConfDesc1.'" /etc/ircddbgateway';
	  $rollDesc11 = 'sudo sed -i "/description1_1=/c\\description1_1='.$newConfDesc1.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['Location'] = '"'.$newConfDesc1.'"';
	  $configdmrgateway['Info']['Location'] = '"'.$newConfDesc1.'"';
	  $configysf2dmr['Info']['Location'] = '"'.$newConfDesc1.'"';
	  $configysf2nxdn['Info']['Location'] = '"'.$newConfDesc1.'"';
	  $configysf2p25['Info']['Location'] = '"'.$newConfDesc1.'"';
	  $confignxdngateway['Info']['Name'] = '"'.$newConfDesc1.'"';
	  if (isset($configm17gateway['Info']['Name'])) { $configm17gateway['Info']['Name'] = '"'.$newConfDesc1.'"'; }
	  system($rollDesc1);
	  system($rollDesc11);
	  }

	// Set the Country
	if (empty($_POST['confDesc2']) != TRUE ) {
	  $newConfDesc2 = preg_replace('/[^A-Za-z0-9\.\s\,\-]/', '', $_POST['confDesc2']);
	  $rollDesc2 = 'sudo sed -i "/description2=/c\\description2='.$newConfDesc2.'" /etc/ircddbgateway';
	  $rollDesc22 = 'sudo sed -i "/description1_2=/c\\description1_2='.$newConfDesc2.'" /etc/ircddbgateway';
          $configmmdvm['Info']['Description'] = '"'.$newConfDesc2.'"';
	  $configdmrgateway['Info']['Description'] = '"'.$newConfDesc2.'"';
          $configysfgateway['Info']['Description'] = '"'.$newConfDesc2.'"';
	  $confignxdngateway['Info']['Description'] = '"'.$newConfDesc2.'"';
	  if (isset($configm17gateway['Info']['Description'])) { $configm17gateway['Info']['Description'] = '"'.$newConfDesc2.'"'; }
	  if (isset($configdgidgateway)) { $configdgidgateway['Info']['Description'] = '"'.$newConfDesc2.'"'; }
	  system($rollDesc2);
	  system($rollDesc22);
	  }

	// Set the URL
	if (empty($_POST['confURL']) != TRUE ) {
	  $newConfURL = strtolower(preg_replace('/[^A-Za-z0-9\.\s\,\-\/\:]/', '', $_POST['confURL']));
	  if (escapeshellcmd($_POST['urlAuto']) == 'auto') { $txtURL = "https://www.qrz.com/db/".strtoupper(escapeshellcmd($_POST['confCallsign'])); }
	  if (escapeshellcmd($_POST['urlAuto']) == 'man')  { $txtURL = $newConfURL; }
	  if (escapeshellcmd($_POST['urlAuto']) == 'auto') { $rollURL0 = 'sudo sed -i "/url=/c\\url=https://www.qrz.com/db/'.strtoupper(escapeshellcmd($_POST['confCallsign'])).'" /etc/ircddbgateway';  }
	  if (escapeshellcmd($_POST['urlAuto']) == 'man') { $rollURL0 = 'sudo sed -i "/url=/c\\url='.$newConfURL.'" /etc/ircddbgateway'; }
          $configmmdvm['Info']['URL'] = $txtURL;
	  $configysf2dmr['Info']['URL'] = $txtURL;
	  $configysf2nxdn['Info']['URL'] = $txtURL;
	  $configysf2p25['Info']['URL'] = $txtURL;
	  $configdmrgateway['Info']['URL'] = $txtURL;
	  system($rollURL0);
	  }

	// Set the APRS Host for ircDDBGateway
	if (empty($_POST['selectedAPRSHost']) != TRUE ) {
	  $rollAPRSHost = 'sudo sed -i "/aprsHostname=/c\\aprsHostname='.escapeshellcmd($_POST['selectedAPRSHost']).'" /etc/ircddbgateway';
	  system($rollAPRSHost);
	  $configysfgateway['aprs.fi']['Server'] = escapeshellcmd($_POST['selectedAPRSHost']);
	  $configysf2dmr['aprs.fi']['Server'] = escapeshellcmd($_POST['selectedAPRSHost']);
	  $configysf2nxdn['aprs.fi']['Server'] = escapeshellcmd($_POST['selectedAPRSHost']);
	  $configysf2p25['aprs.fi']['Server'] = escapeshellcmd($_POST['selectedAPRSHost']);
	  $configysf2dmr['aprs.fi']['Enable'] = "0";
	  $configysf2nxdn['aprs.fi']['Enable'] = "0";
	  $configysf2p25['aprs.fi']['Enable'] = "0";
	  if ($configPistarRelease['Pi-Star']['Version'] >= "4.1.4") {
	    $rollAPRSGatewayHost = 'sudo sed -i "/Server=/c\\Server='.escapeshellcmd($_POST['selectedAPRSHost']).'" /etc/aprsgateway';
	    system($rollAPRSGatewayHost);
	  }
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

	// Set the POCSAG Frequency
	if (empty($_POST['pocsagFrequency']) != TRUE ) {
	  $newPocsagFREQ = preg_replace('/[^0-9\.]/', '', $_POST['pocsagFrequency']);
	  $newPocsagFREQ = str_pad(str_replace(".", "", $newPocsagFREQ), 9, "0");
	  $newPocsagFREQ = mb_strimwidth($newPocsagFREQ, 0, 9);
	  $configmmdvm['POCSAG']['Frequency'] = $newPocsagFREQ;
	}

	// Set the POCSAG AuthKey
	if (empty($_POST['pocsagAuthKey']) != TRUE ) {
	  $configdapnetgw['DAPNET']['AuthKey'] = escapeshellcmd($_POST['pocsagAuthKey']);
	}

	// Set the POCSAG Callsign
	if (empty($_POST['pocsagCallsign']) != TRUE ) {
	  $configdapnetgw['General']['Callsign'] = strtoupper(escapeshellcmd($_POST['pocsagCallsign']));
	}

	// Set the POCSAG Whitelist
	if (isset($configdapnetgw['General']['WhiteList'])) { unset($configdapnetgw['General']['WhiteList']); }
	if (empty($_POST['pocsagWhitelist']) != TRUE ) {
	  $configdapnetgw['General']['WhiteList'] = preg_replace('/[^0-9\,]/', '', escapeshellcmd($_POST['pocsagWhitelist']));
	}

	// Set the POCSAG Blacklist
	if (isset($configdapnetgw['General']['BlackList'])) { unset($configdapnetgw['General']['BlackList']); }
	if (empty($_POST['pocsagBlacklist']) != TRUE ) {
	  $configdapnetgw['General']['BlackList'] = preg_replace('/[^0-9\,]/', '', escapeshellcmd($_POST['pocsagBlacklist']));
	}

	// Set the POCSAG Server
	if (empty($_POST['pocsagServer']) != TRUE ) {
	  $configdapnetgw['DAPNET']['Address'] = escapeshellcmd($_POST['pocsagServer']);
	}

	// Set the Frequency for Duplex
	if (empty($_POST['confFREQtx']) != TRUE && empty($_POST['confFREQrx']) != TRUE ) {
	  if (empty($_POST['confHardware']) != TRUE ) { $confHardware = escapeshellcmd($_POST['confHardware']); }
	  $newConfFREQtx = preg_replace('/[^0-9\.]/', '', $_POST['confFREQtx']);
	  $newConfFREQrx = preg_replace('/[^0-9\.]/', '', $_POST['confFREQrx']);
	  $newFREQtx = number_format($newConfFREQtx, 6, ".", "");
	  $newFREQrx = number_format($newConfFREQrx, 6, ".", "");
	  $newFREQtx = str_pad(str_replace(".", "", $newFREQtx), 9, "0");
	  //$newFREQtx = mb_strimwidth($newFREQtx, 0, 9);
	  $newFREQrx = str_pad(str_replace(".", "", $newFREQrx), 9, "0");
	  //$newFREQrx = mb_strimwidth($newFREQrx, 0, 9);
	  //$newFREQirc = substr_replace($newFREQtx, '.', '3', 0);
	  //$newFREQirc = mb_strimwidth($newFREQirc, 0, 9);
	  $newFREQirc = number_format($newConfFREQtx, 5, ".", "");
	  $newFREQOffset = ($newFREQrx - $newFREQtx)/1000000;
	  $newFREQOffset = number_format($newFREQOffset, 4, '.', '');
	  $rollFREQirc = 'sudo sed -i "/frequency1=/c\\frequency1='.$newFREQirc.'" /etc/ircddbgateway';
	  $rollFREQdvap = 'sudo sed -i "/dvapFrequency=/c\\dvapFrequency='.$newFREQrx.'" /etc/dstarrepeater';
	  $rollFREQdvmegaRx = 'sudo sed -i "/dvmegaRXFrequency=/c\\dvmegaRXFrequency='.$newFREQrx.'" /etc/dstarrepeater';
	  $rollFREQdvmegaTx = 'sudo sed -i "/dvmegaTXFrequency=/c\\dvmegaTXFrequency='.$newFREQtx.'" /etc/dstarrepeater';
	  $rollModeDuplex = 'sudo sed -i "/mode=/c\\mode=0" /etc/dstarrepeater';
	  $rollGatewayType = 'sudo sed -i "/gatewayType=/c\\gatewayType=0" /etc/ircddbgateway';
	  $rollFREQOffset = 'sudo sed -i "/offset1=/c\\offset1='.$newFREQOffset.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['RXFrequency'] = $newFREQrx;
	  $configmmdvm['Info']['TXFrequency'] = $newFREQtx;
	  $configdmrgateway['Info']['RXFrequency'] = $newFREQrx;
	  $configdmrgateway['Info']['TXFrequency'] = $newFREQtx;
	  $configysfgateway['Info']['RXFrequency'] = $newFREQrx;
	  $configysfgateway['Info']['TXFrequency'] = $newFREQtx;
	  $configysfgateway['General']['Suffix'] = "RPT";
	  $configysf2dmr['Info']['RXFrequency'] = $newFREQrx;
	  $configysf2dmr['Info']['TXFrequency'] = $newFREQrx;
	  $configysf2dmr['YSF Network']['Suffix'] = "RPT";
	  $configysf2nxdn['Info']['RXFrequency'] = $newFREQrx;
	  $configysf2nxdn['Info']['TXFrequency'] = $newFREQtx;
	  $configysf2nxdn['YSF Network']['Suffix'] = "RPT";
	  $configysf2p25['Info']['RXFrequency'] = $newFREQrx;
	  $configysf2p25['Info']['TXFrequency'] = $newFREQtx;
	  $configysf2p25['YSF Network']['Suffix'] = "RPT";
	  if (isset($configdgidgateway)) { $configdgidgateway['Info']['RXFrequency'] = $newFREQrx; }
	  if (isset($configdgidgateway)) { $configdgidgateway['Info']['TXFrequency'] = $newFREQtx; }
	  if (isset($configdgidgateway)) { $configdgidgateway['General']['Suffix'] = "RPT"; }
	  $configdmr2ysf['YSF Network']['Suffix'] = "RPT";
	  $confignxdngateway['Info']['RXFrequency'] = $newFREQrx;
	  $confignxdngateway['Info']['TXFrequency'] = $newFREQtx;
	  $confignxdngateway['General']['Suffix'] = "RPT";
	  if (isset($configm17gateway['Info']['RXFrequency'])) { $configm17gateway['Info']['RXFrequency'] = $newFREQrx; }
	  if (isset($configm17gateway['Info']['TXFrequency'])) { $configm17gateway['Info']['TXFrequency'] = $newFREQtx; }
	  if (isset($configm17gateway['General']['Suffix'])) { $configm17gateway['General']['Suffix'] = "R"; }

	  system($rollFREQirc);
	  system($rollFREQdvap);
	  system($rollFREQdvmegaRx);
	  system($rollFREQdvmegaTx);
	  system($rollModeDuplex);
	  system($rollGatewayType);
	  system($rollFREQOffset);

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
	  $newFREQ = number_format($newConfFREQ, 6, ".", "");
	  $newFREQ = str_pad(str_replace(".", "", $newFREQ), 9, "0");
	  $newFREQirc = number_format($newConfFREQ, 5, ".", "");
	  //$newFREQ = mb_strimwidth($newFREQ, 0, 9);
	  //$newFREQirc = substr_replace($newFREQ, '.', '3', 0);
	  //$newFREQirc = mb_strimwidth($newFREQirc, 0, 9);
	  $newFREQOffset = "0.0000";
	  $rollFREQirc = 'sudo sed -i "/frequency1=/c\\frequency1='.$newFREQirc.'" /etc/ircddbgateway';
	  $rollFREQdvap = 'sudo sed -i "/dvapFrequency=/c\\dvapFrequency='.$newFREQ.'" /etc/dstarrepeater';
	  $rollFREQdvmegaRx = 'sudo sed -i "/dvmegaRXFrequency=/c\\dvmegaRXFrequency='.$newFREQ.'" /etc/dstarrepeater';
	  $rollFREQdvmegaTx = 'sudo sed -i "/dvmegaTXFrequency=/c\\dvmegaTXFrequency='.$newFREQ.'" /etc/dstarrepeater';
	  $rollModeSimplex = 'sudo sed -i "/mode=/c\\mode=1" /etc/dstarrepeater';
	  $rollGatewayType = 'sudo sed -i "/gatewayType=/c\\gatewayType=1" /etc/ircddbgateway';
	  $rollFREQOffset = 'sudo sed -i "/offset1=/c\\offset1='.$newFREQOffset.'" /etc/ircddbgateway';
	  $configmmdvm['Info']['RXFrequency'] = $newFREQ;
	  $configmmdvm['Info']['TXFrequency'] = $newFREQ;
	  $configdmrgateway['Info']['RXFrequency'] = $newFREQ;
	  $configdmrgateway['Info']['TXFrequency'] = $newFREQ;
	  $configysfgateway['Info']['RXFrequency'] = $newFREQ;
	  $configysfgateway['Info']['TXFrequency'] = $newFREQ;
	  $configysfgateway['General']['Suffix'] = "ND";
	  $configysf2dmr['Info']['RXFrequency'] = $newFREQ;
	  $configysf2dmr['Info']['TXFrequency'] = $newFREQ;
	  $configysf2dmr['YSF Network']['Suffix'] = "ND";
	  $configysf2nxdn['Info']['RXFrequency'] = $newFREQ;
	  $configysf2nxdn['Info']['TXFrequency'] = $newFREQ;
	  $configysf2nxdn['YSF Network']['Suffix'] = "ND";
	  $configysf2p25['Info']['RXFrequency'] = $newFREQ;
	  $configysf2p25['Info']['TXFrequency'] = $newFREQ;
	  $configysf2p25['YSF Network']['Suffix'] = "ND";
	  if (isset($configdgidgateway)) { $configdgidgateway['Info']['RXFrequency'] = $newFREQ; }
	  if (isset($configdgidgateway)) { $configdgidgateway['Info']['TXFrequency'] = $newFREQ; }
	  if (isset($configdgidgateway)) { $configdgidgateway['General']['Suffix'] = "ND"; }
	  $configdmr2ysf['YSF Network']['Suffix'] = "ND";
	  $confignxdngateway['Info']['RXFrequency'] = $newFREQ;
	  $confignxdngateway['Info']['TXFrequency'] = $newFREQ;
	  $confignxdngateway['General']['Suffix'] = "ND";
	  if (isset($configm17gateway['Info']['RXFrequency'])) { $configm17gateway['Info']['RXFrequency'] = $newFREQ; }
	  if (isset($configm17gateway['Info']['TXFrequency'])) { $configm17gateway['Info']['TXFrequency'] = $newFREQ; }
	  if (isset($configm17gateway['General']['Suffix'])) { $configm17gateway['General']['Suffix'] = "H"; }

	  system($rollFREQirc);
	  system($rollFREQdvap);
	  system($rollFREQdvmegaRx);
	  system($rollFREQdvmegaTx);
	  system($rollModeSimplex);
	  system($rollGatewayType);
	  system($rollFREQOffset);

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
	  // Removed the need for the r prefix - OpenQuad have fixed up the servers not to require it.
	  // if (preg_match("/^[0-9]/", $newCallsignUpper)) { $newCallsignUpperIRC = 'r'.$newCallsignUpper; } else { $newCallsignUpperIRC = $newCallsignUpper; }
	  $newCallsignUpperIRC = $newCallsignUpper;

	  $rollGATECALL = 'sudo sed -i "/gatewayCallsign=/c\\gatewayCallsign='.$newCallsignUpper.'" /etc/ircddbgateway';
	  $rollDPLUSLOGIN = 'sudo sed -i "/dplusLogin=/c\\dplusLogin='.str_pad($newCallsignUpper, 8, " ").'" /etc/ircddbgateway';
	  $rollDASHBOARDcall = 'sudo sed -i "/callsign=/c\\$callsign=\''.$newCallsignUpper.'\';" /var/www/dashboard/config/ircddblocal.php';
	  $rollTIMESERVERcall = 'sudo sed -i "/callsign=/c\\callsign='.$newCallsignUpper.'" /etc/timeserver';
	  $rollSTARNETSERVERcall = 'sudo sed -i "/callsign=/c\\callsign='.$newCallsignUpper.'" /etc/starnetserver';
	  $rollSTARNETSERVERirc = 'sudo sed -i "/ircddbUsername=/c\\ircddbUsername='.$newCallsignUpperIRC.'" /etc/starnetserver';

	  // Only roll ircDDBGateway Username if using OpenQuad
	  if (strpos($configs['ircddbHostname'], 'openquad.net') !== false) {
		  $rollIRCUSER = 'sudo sed -i "/ircddbUsername=/c\\ircddbUsername='.$newCallsignUpperIRC.'" /etc/ircddbgateway';
		  system($rollIRCUSER);
	  }

	  //if ( strlen($newCallsignUpper) < 6 ) { $configysfgateway['General']['Callsign'] = $newCallsignUpper."-1"; }
	  //else { $configysfgateway['General']['Callsign'] = $newCallsignUpper; }
	  $configysfgateway['General']['Callsign'] = $newCallsignUpper;
	  $configmmdvm['General']['Callsign'] = $newCallsignUpper;
	  $configysfgateway['aprs.fi']['Password'] = aprspass($newCallsignUpper);
	  $configysfgateway['aprs.fi']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2dmr['aprs.fi']['Password'] = aprspass($newCallsignUpper);
	  $configysf2dmr['aprs.fi']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2dmr['YSF Network']['Callsign'] = $newCallsignUpper;
	  $configysf2nxdn['aprs.fi']['Password'] = aprspass($newCallsignUpper);
	  $configysf2nxdn['aprs.fi']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2nxdn['YSF Network']['Callsign'] = $newCallsignUpper;
	  $configysf2p25['aprs.fi']['Password'] = aprspass($newCallsignUpper);
	  $configysf2p25['aprs.fi']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2p25['YSF Network']['Callsign'] = $newCallsignUpper;
	  $configdmr2ysf['YSF Network']['Callsign'] = $newCallsignUpper;
	  $configp25gateway['General']['Callsign'] = $newCallsignUpper;
	  $confignxdngateway['aprs.fi']['Description'] = $newCallsignUpper."_Pi-Star";
	  $confignxdngateway['aprs.fi']['Password'] = aprspass($newCallsignUpper);
	  $confignxdngateway['General']['Callsign'] = $newCallsignUpper;
	  if (isset($configm17gateway['General']['Callsign'])) { $configm17gateway['General']['Callsign'] = $newCallsignUpper; }
	  $configysfgateway['Info']['Name'] = $newCallsignUpper."_Pi-Star";
	  $configysf2dmr['Info']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2nxdn['Info']['Description'] = $newCallsignUpper."_Pi-Star";
	  $configysf2p25['Info']['Description'] = $newCallsignUpper."_Pi-Star";
	  if (isset($configdgidgateway)) { $configdgidgateway['General']['Callsign'] = $newCallsignUpper; }
	  if (isset($configdgidgateway)) { $configdgidgateway['Info']['Description'] = $newCallsignUpper."_Pi-Star"; }
	  if ($configPistarRelease['Pi-Star']['Version'] >= "4.1.4") {
	    $rollAPRSGatewayCallsign = 'sudo sed -i "/Callsign=/c\\Callsign='.$newCallsignUpper.'" /etc/aprsgateway';
	    system($rollAPRSGatewayCallsign);
	    $rollAPRSGatewayPassword = 'sudo sed -i "/Password=/c\\Password='.aprspass($newCallsignUpper).'" /etc/aprsgateway';
	    system($rollAPRSGatewayPassword);
	    $rollircDDBGatewayAprsPort = 'sudo sed -i "/aprsPort=/c\\aprsPort=8673" /etc/ircddbgateway';
	    system($rollircDDBGatewayAprsPort);
	    unset($configs['aprsPassword']);
	    $rollircDDBGatewayAprsPass = 'sudo sed -i "/aprsPassword/d" /etc/ircddbgateway';
	    system($rollircDDBGatewayAprsPass);
	    if (empty($_POST['APRSGatewayEnable']) != TRUE ) {
	    	if (escapeshellcmd($_POST['APRSGatewayEnable']) == 'ON' )  { $rollAPRSGatewayEnable = 'sudo sed -i "/Enabled=/c\\Enabled=1" /etc/aprsgateway'; }
	    	if (escapeshellcmd($_POST['APRSGatewayEnable']) == 'OFF' ) { $rollAPRSGatewayEnable = 'sudo sed -i "/Enabled=/c\\Enabled=0" /etc/aprsgateway'; }
	    }
	    system($rollAPRSGatewayEnable);
	  }

	  // If ircDDBGateway config supports APRS Password
	  if (isset($configs['aprsPassword'])) {
		  $rollircDDBGatewayAprsPassword = 'sudo sed -i "/aprsPassword=/c\\aprsPassword='.aprspass($newCallsignUpper).'" /etc/ircddbgateway';
		  system($rollircDDBGatewayAprsPassword);
	  }

	  system($rollGATECALL);
	  system($rollDPLUSLOGIN);
	  system($rollDASHBOARDcall);
	  system($rollTIMESERVERcall);
	  system($rollSTARNETSERVERcall);
	  system($rollSTARNETSERVERirc);
	}

	// Set the ircDDB Callsign routing option
	if (empty($_POST['confircddbEnabled']) != TRUE ) {
		if (escapeshellcmd($_POST['confircddbEnabled']) == 'ON' ) {
			$rollconfircddbEnabled = 'sudo sed -i "/rcddbEnabled=/c\\ircddbEnabled=1" /etc/ircddbgateway';
		}
		if (escapeshellcmd($_POST['confircddbEnabled']) == 'OFF' ) {
			$rollconfircddbEnabled = 'sudo sed -i "/rcddbEnabled=/c\\ircddbEnabled=0" /etc/ircddbgateway';
		}
		if (isset($configs['ircddbHostname']) && $configs['ircddbHostname'] == "rr.openquad.net") {
			$rollconfircddbEnabled = 'sudo sed -i "/rcddbEnabled=/c\\ircddbEnabled=0" /etc/ircddbgateway';
			$rollconfircddbHostname = 'sudo sed -i "/rcddbHostname=/c\\ircddbHostname=ircv4.openquad.net" /etc/ircddbgateway';
			system($rollconfircddbHostname);
		}
		system($rollconfircddbEnabled);
	}

	// Set the P25 Startup Host
	if (empty($_POST['p25StartupHost']) != TRUE ) {
          $newP25StartupHost = strtoupper(escapeshellcmd($_POST['p25StartupHost']));
          if ($newP25StartupHost === "NONE") {
		  unset($configp25gateway['Network']['Startup']);
		  unset($configysf2p25['P25 Network']['StartupDstId']);
		  unset($configp25gateway['Network']['Static']);
	  } else {
		  $configp25gateway['Network']['Startup'] = $newP25StartupHost;
		  $configysf2p25['P25 Network']['StartupDstId'] = $newP25StartupHost;
	  }
	}

	// Set P25 NAC
	if (empty($_POST['p25nac']) != TRUE ) {
	  $p25nacNew = strtolower(escapeshellcmd($_POST['p25nac']));
	  if (preg_match('/[a-f0-9]{3}/', $p25nacNew)) {
	    $configmmdvm['P25']['NAC'] = $p25nacNew;
	  }
	}

	// Set the NXDN Startup Host
	if (empty($_POST['nxdnStartupHost']) != TRUE ) {
	  $newNXDNStartupHost = strtoupper(escapeshellcmd($_POST['nxdnStartupHost']));
	  if (file_exists('/etc/nxdngateway')) {
		if ($newNXDNStartupHost === "NONE") {
			if (isset($confignxdngateway['Network']['Startup'])) { unset($confignxdngateway['Network']['Startup']); }
			if (isset($confignxdngateway['Network']['Static']))  { unset($confignxdngateway['Network']['Static']); }
		} else {
			$confignxdngateway['Network']['Startup'] = $newNXDNStartupHost;
	  	}
	  } else {
		$configmmdvm['NXDN Network']['GatewayAddress'] = $newNXDNStartupHost;
		$configmmdvm['NXDN Network']['GatewayPort'] = "41007";
	  }
	  $configysf2nxdn['NXDN Network']['StartupDstId'] = $newNXDNStartupHost;
	}

	// Set NXDN RAN
	if (empty($_POST['nxdnran']) != TRUE ) {
	  $nxdnranNew = strtolower(escapeshellcmd($_POST['nxdnran']));
	  $nxdnranNew = preg_replace('/[^0-9]/', '', $nxdnranNew);
	  if (($nxdnranNew >= 1) && ($nxdnranNew <= 64)) {
	      $configmmdvm['NXDN']['RAN'] = $nxdnranNew;
	  }
	}

	// Set the M17 Startup Host
	if (empty($_POST['m17StartupHost']) != TRUE || empty($_POST['m17StartupRoom']) != TRUE) {
	  $newM17StartupHost = strtoupper(escapeshellcmd($_POST['m17StartupHost']))."_".strtoupper(escapeshellcmd($_POST['m17StartupRoom']));
	  if (file_exists('/etc/m17gateway')) {
		if (strtoupper(escapeshellcmd($_POST['m17StartupHost'])) === "NONE") {
			if (isset($configm17gateway['Network']['Startup'])) { unset($configm17gateway['Network']['Startup']); }
		} else {
			$configm17gateway['Network']['Startup'] = $newM17StartupHost;
	  	}
	  }
	}

	// Set M17 CAN
	if (isset($_POST['m17can']) && $_POST['m17can'] !== '') {
	  $m17canNew = strtolower(escapeshellcmd($_POST['m17can']));
	  $m17canNew = preg_replace('/[^0-9]/', '', $m17canNew);
	  $configmmdvm['M17']['CAN'] = $m17canNew;
	}

	// Set the YSF Startup Host
	if (empty($_POST['ysfStartupHost']) != TRUE ) {
	  $newYSFStartupHostArr = explode(',', escapeshellcmd($_POST['ysfStartupHost']));
	  //$newYSFStartupHost = strtoupper(escapeshellcmd($_POST['ysfStartupHost']));
	  //if ($newYSFStartupHost == "NONE") { unset($configysfgateway['Network']['Startup']); }
	  //else { $configysfgateway['Network']['Startup'] = $newYSFStartupHost; }
	  if (isset($configysfgateway['FCS Network'])) {
		if ($newYSFStartupHostArr[0] == "none") {
			unset($configysfgateway['Network']['Startup']);
			$configdmr2ysf['DMR Network']['DefaultDstTG'] = "9";
		}
	  	else {
			$configysfgateway['Network']['Startup'] = $newYSFStartupHostArr[1];
			if (substr( $newYSFStartupHostArr[0], 0, 3 ) !== "FCS") {
				$configdmr2ysf['DMR Network']['DefaultDstTG'] = $newYSFStartupHostArr[0];
			} else {
				$configdmr2ysf['DMR Network']['DefaultDstTG'] = "9";
			}
			//$configdmr2ysf['DMR Network']['DefaultDstTG'] = str_replace("FCS", "1", $newYSFStartupHostArr[0]);
		}
	  } else {
	  	if ($newYSFStartupHostArr[0] == "none") {
			unset($configysfgateway['Network']['Startup']);
			$configdmr2ysf['DMR Network']['DefaultDstTG'] = "9";
		}
	  	else {
			$configysfgateway['Network']['Startup'] = $newYSFStartupHostArr[0];
			if (substr( $newYSFStartupHostArr[0], 0, 3 ) !== "FCS") {
				$configdmr2ysf['DMR Network']['DefaultDstTG'] = $newYSFStartupHostArr[0];
			} else {
				$configdmr2ysf['DMR Network']['DefaultDstTG'] = "9";
			}
			//$configdmr2ysf['DMR Network']['DefaultDstTG'] = str_replace("FCS", "1", $newYSFStartupHostArr[0]);
		}
	  }
	}

	// Set YSFGateway to automatically pass through WiresX
	if (empty($_POST['wiresXCommandPassthrough']) != TRUE ) {
	  if (escapeshellcmd($_POST['wiresXCommandPassthrough']) == 'ON' )  { $configysfgateway['General']['WiresXCommandPassthrough'] = "1"; }
	  if (escapeshellcmd($_POST['wiresXCommandPassthrough']) == 'OFF' ) { $configysfgateway['General']['WiresXCommandPassthrough'] = "0"; }
	}

	// Remove hostfiles.ysfupper and use the new YSFGateway Feature
	if (empty($_POST['confHostFilesYSFUpper']) != TRUE ) {
		if (escapeshellcmd($_POST['confHostFilesYSFUpper']) == 'ON' )   { $configysfgateway['General']['WiresXMakeUpper'] = "1"; }
		if (escapeshellcmd($_POST['confHostFilesYSFUpper']) == 'OFF' )  { $configysfgateway['General']['WiresXMakeUpper'] = "0"; }
		if (file_exists('/etc/hostfiles.ysfupper')) { system('sudo rm -rf /etc/hostfiles.ysfupper'); }
	}

	// Set the YSF2DMR Master
	if (empty($_POST['ysf2dmrMasterHost']) != TRUE ) {
	  $ysf2dmrMasterHostArr = explode(',', escapeshellcmd($_POST['ysf2dmrMasterHost']));
	  $configysf2dmr['DMR Network']['Address'] = $ysf2dmrMasterHostArr[0];
	  $configysf2dmr['DMR Network']['Password'] = '"'.$ysf2dmrMasterHostArr[1].'"';
	  $configysf2dmr['DMR Network']['Port'] = $ysf2dmrMasterHostArr[2];
	  if (isset($_POST['bmHSSecurity'])) {
		  if (empty($_POST['bmHSSecurity']) != TRUE ) {
			  $configysf2dmr['DMR Network']['Password'] = '"'.$_POST['bmHSSecurity'].'"';
			  $configModem['BrandMeister']['Password'] = '"'.$_POST['bmHSSecurity'].'"';
		  } else {
			  unset ($configModem['BrandMeister']['Password']);
		  }
	  }
	}

	// Set the YSF2DMR Starting TG
	if (empty($_POST['ysf2dmrTg']) != TRUE ) {
	  $ysf2dmrStartupDstId = preg_replace('/[^0-9]/', '', $_POST['ysf2dmrTg']);
	  $configysf2dmr['DMR Network']['StartupDstId'] = $ysf2dmrStartupDstId;
	}

	// Set the YSF2NXDN Master
	if (empty($_POST['ysf2nxdnStartupDstId']) != TRUE ) {
	  $configysf2nxdn['NXDN Network']['StartupDstId'] = escapeshellcmd($_POST['ysf2nxdnStartupDstId']);
	  if (file_exists('/etc/nxdngateway')) {
	    if (escapeshellcmd($_POST['ysf2nxdnStartupDstId']) === "none") {
	      unset($confignxdngateway['Network']['Startup']);
	      unset($confignxdngateway['Network']['Static']);
	    } else {
	      $confignxdngateway['Network']['Startup'] = escapeshellcmd($_POST['ysf2nxdnStartupDstId']);
	    }		  
	  }
	}

	// Set the YSF2NXDN Id
	if (empty($_POST['ysf2nxdnId']) != TRUE ) {
	  $configysf2nxdn['NXDN Network']['Id'] = preg_replace('/[^0-9]/', '', $_POST['ysf2nxdnId']);
	}

	// Set the YSF2P25 Master
	if (empty($_POST['ysf2p25StartupDstId']) != TRUE ) {
	  $newYSF2P25StartupHost = strtoupper(escapeshellcmd($_POST['ysf2p25StartupDstId']));

	  if ($newYSF2P25StartupHost === "NONE") {
		  unset($configp25gateway['Network']['Startup']);
		  unset($configp25gateway['Network']['Static']);
		  unset($configysf2p25['P25 Network']['StartupDstId']);
	  } else {
		  $configp25gateway['Network']['Startup'] = $newYSF2P25StartupHost;
		  $configysf2p25['P25 Network']['StartupDstId'] = $newYSF2P25StartupHost;
	  }
	}

	// Set the YSF2P25 P25Id
	if (empty($_POST['ysf2p25Id']) != TRUE ) {
	  $configysf2p25['P25 Network']['Id'] = preg_replace('/[^0-9]/', '', $_POST['ysf2p25Id']);
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
	  $newPostDmrId = substr($newPostDmrId, 0, 7);
	  $configmmdvm['General']['Id'] = $newPostDmrId;
	  $configmmdvm['DMR']['Id'] = $newPostDmrId;
	  $configysfgateway['General']['Id'] = $newPostDmrId;
	  $configdmrgateway['XLX Network']['Id'] = $newPostDmrId;
	  $configdmr2ysf['DMR Network']['Id'] = $newPostDmrId;
	  $configdmr2nxdn['DMR Network']['Id'] = $newPostDmrId;
	  if (isset($configdgidgateway)) { $configdgidgateway['General']['Id'] = $newPostDmrId; }
	}

	// Set DMR Extended ID
	if (empty($_POST['dmrExtendedId']) != TRUE ) {
	  $newPostdmrExtendedId = preg_replace('/[^0-9]/', '', $_POST['dmrExtendedId']);
	  $configmmdvm['DMR']['Id'] = $configmmdvm['General']['Id'].$newPostdmrExtendedId;
	}

	// Set BrandMeister Extended ID
	if (empty($_POST['bmExtendedId']) != TRUE ) {
	  $newPostbmExtendedId = preg_replace('/[^0-9]/', '', $_POST['bmExtendedId']);
	  $configdmrgateway['DMR Network 1']['Id'] = $configmmdvm['General']['Id'].$newPostbmExtendedId;
	}

	// Set DMR Plus Extended ID
	if (empty($_POST['dmrPlusExtendedId']) != TRUE ) {
	  $newPostdmrPlusExtendedId = preg_replace('/[^0-9]/', '', $_POST['dmrPlusExtendedId']);
	  $configdmrgateway['DMR Network 2']['Id'] = $configmmdvm['General']['Id'].$newPostdmrPlusExtendedId;
	}

	// Set YSF2DMR ID
	if (empty($_POST['ysf2dmrId']) != TRUE ) {
	  $newPostYsf2DmrId = preg_replace('/[^0-9]/', '', $_POST['ysf2dmrId']);
	  $configysf2dmr['DMR Network']['Id'] = $newPostYsf2DmrId;
	}

	// Set NXDN ID
	if (empty($_POST['nxdnId']) != TRUE ) {
	  $newPostNxdnId = preg_replace('/[^0-9]/', '', $_POST['nxdnId']);
	  $configmmdvm['NXDN']['Id'] = $newPostNxdnId;
	  if ($configmmdvm['NXDN']['Id'] > 65535) { unset($configmmdvm['NXDN']['Id']); }
	}

	// Set DMR Master Server
	if (empty($_POST['dmrMasterHost']) != TRUE ) {
	  $dmrMasterHostArr = explode(',', escapeshellcmd($_POST['dmrMasterHost']));
	  $configmmdvm['DMR Network']['Address'] = $dmrMasterHostArr[0];
	  $configmmdvm['DMR Network']['RemoteAddress'] = $dmrMasterHostArr[0];
	  $configmmdvm['DMR Network']['Password'] = '"'.$dmrMasterHostArr[1].'"';
	  $configmmdvm['DMR Network']['Port'] = $dmrMasterHostArr[2];
	  $configmmdvm['DMR Network']['RemotePort'] = $dmrMasterHostArr[2];
	  if ($dmrMasterHostArr[0] == '127.0.0.1' && $dmrMasterHostArr[2] == '62031') {
		  // DMR Gateway
		  $configmmdvm['DMR Network']['Type'] = "Gateway";
	  } else {
		  // Everything Else
		  $configmmdvm['DMR Network']['Type'] = "Direct";
	  }
 	  if ((isset($_POST['bmHSSecurity'])) && substr($dmrMasterHostArr[3], 0, 2) == "BM") {
		  if (empty($_POST['bmHSSecurity']) != TRUE ) {
			  $configModem['BrandMeister']['Password'] = '"'.$_POST['bmHSSecurity'].'"';
			  if ($dmrMasterHostArr[0] != '127.0.0.1') { $configmmdvm['DMR Network']['Password'] = '"'.$_POST['bmHSSecurity'].'"'; }
		  } else {
			  unset ($configModem['BrandMeister']['Password']);
		  }
	  }
	  if ((isset($_POST['tgifHSSecurity'])) && substr($dmrMasterHostArr[3], 0, 4) == "TGIF") {
		  if (empty($_POST['tgifHSSecurity']) != TRUE ) {
			  $configModem['TGIF']['Password'] = '"'.$_POST['tgifHSSecurity'].'"';
			  if ($dmrMasterHostArr[0] != '127.0.0.1') { $configmmdvm['DMR Network']['Password'] = '"'.$_POST['tgifHSSecurity'].'"'; }
		  } else {
			  unset ($configModem['TGIF']['Password']);
		  }
	  }

		if (substr($dmrMasterHostArr[3], 0, 2) == "BM") {
			unset ($configmmdvm['DMR Network']['Options']);
			unset ($configdmrgateway['DMR Network 2']['Options']);
			unset ($configmmdvm['DMR Network']['Local']);
			unset ($configmmdvm['DMR Network']['LocalPort']);
			unset ($configysf2dmr['DMR Network']['Options']);
			unset ($configysf2dmr['DMR Network']['Local']);
			if (isset($configModem['BrandMeister']['Password'])) {
				$configmmdvm['DMR Network']['Password'] = '"'.str_replace('"', "", $configModem['BrandMeister']['Password']).'"';
			}
		}

		// DMR Gateway
		if ($dmrMasterHostArr[0] == '127.0.0.1' && $dmrMasterHostArr[2] == '62031') {
			unset ($configmmdvm['DMR Network']['Options']);
			unset ($configdmrgateway['DMR Network 2']['Options']);
			$configmmdvm['DMR Network']['Local'] = "62032";
			$configmmdvm['DMR Network']['LocalPort'] = "62032";
			unset ($configysf2dmr['DMR Network']['Options']);
			$configysf2dmr['DMR Network']['Local'] = "62032";
			if (isset($configdmr2ysf['DMR Network']['LocalAddress'])) {
				$configdmr2ysf['DMR Network']['LocalAddress'] = "127.0.0.1";
			}
			if (isset($configdmr2nxdn['DMR Network']['LocalAddress'])) {
				$configdmr2nxdn['DMR Network']['LocalAddress'] = "127.0.0.1";
			}
		}

		// DMR2YSF
		if ($dmrMasterHostArr[0] == '127.0.0.2' && $dmrMasterHostArr[2] == '62033') {
			unset ($configmmdvm['DMR Network']['Options']);
			$configmmdvm['DMR Network']['Local'] = "62034";
			$configmmdvm['DMR Network']['LocalPort'] = "62034";
			if (isset($configdmr2ysf['DMR Network']['LocalAddress'])) {
				$configdmr2ysf['DMR Network']['LocalAddress'] = "127.0.0.2";
			}
		}

		// DMR2NXDN
		if ($dmrMasterHostArr[0] == '127.0.0.3' && $dmrMasterHostArr[2] == '62035') {
			unset ($configmmdvm['DMR Network']['Options']);
			$configmmdvm['DMR Network']['Local'] = "62036";
			$configmmdvm['DMR Network']['LocalPort'] = "62036";
			if (isset($configdmr2nxdn['DMR Network']['LocalAddress'])) {
				$configdmr2nxdn['DMR Network']['LocalAddress'] = "127.0.0.3";
			}
		}

		// TGIF
		if (substr($dmrMasterHostArr[3], 0, 4) == "TGIF") {
			unset ($configmmdvm['DMR Network']['Options']);
		}

		// Set the DMR+ / HBLink Options= line
		if ((substr($dmrMasterHostArr[3], 0, 4) == "DMR+") || (substr($dmrMasterHostArr[3], 0, 3) == "HB_") || (substr($dmrMasterHostArr[3], 0, 3) == "FD_") || (substr($dmrMasterHostArr[3], 0, 8) == "FreeDMR_") || (substr($dmrMasterHostArr[3], 0, 9) == "FreeSTAR_")) {
			unset ($configmmdvm['DMR Network']['Local']);
			unset ($configmmdvm['DMR Network']['LocalPort']);
			unset ($configysf2dmr['DMR Network']['Local']);
			if (empty($_POST['dmrNetworkOptions']) != TRUE ) {
				$dmrOptionsLineStripped = str_replace('"', "", $_POST['dmrNetworkOptions']);
				$configmmdvm['DMR Network']['Options'] = '"'.$dmrOptionsLineStripped.'"';
				$configdmrgateway['DMR Network 2']['Options'] = '"'.$dmrOptionsLineStripped.'"';
			}
			else {
				unset ($configmmdvm['DMR Network']['Options']);
				unset ($configdmrgateway['DMR Network 2']['Options']);
				unset ($configysf2dmr['DMR Network']['Options']);
			}
		}
	}
	if (empty($_POST['dmrMasterHost']) == TRUE ) {
		unset ($configmmdvm['DMR Network']['Options']);
		unset ($configdmrgateway['DMR Network 2']['Options']);
	}
	if (empty($_POST['dmrMasterHost1']) != TRUE ) {
	  $dmrMasterHostArr1 = explode(',', escapeshellcmd($_POST['dmrMasterHost1']));
	  $configdmrgateway['DMR Network 1']['Address'] = $dmrMasterHostArr1[0];
	  $configdmrgateway['DMR Network 1']['Password'] = '"'.$dmrMasterHostArr1[1].'"';
	  if (empty($_POST['bmHSSecurity']) != TRUE ) {
	    $configdmrgateway['DMR Network 1']['Password'] = '"'.$_POST['bmHSSecurity'].'"';
	  }
	  $configdmrgateway['DMR Network 1']['Port'] = $dmrMasterHostArr1[2];
	  $configdmrgateway['DMR Network 1']['Name'] = $dmrMasterHostArr1[3];
	}
	if (empty($_POST['dmrMasterHost2']) != TRUE ) {
	  $dmrMasterHostArr2 = explode(',', escapeshellcmd($_POST['dmrMasterHost2']));
	  $configdmrgateway['DMR Network 2']['Address'] = $dmrMasterHostArr2[0];
	  $configdmrgateway['DMR Network 2']['Password'] = '"'.$dmrMasterHostArr2[1].'"';
	  $configdmrgateway['DMR Network 2']['Port'] = $dmrMasterHostArr2[2];
	  $configdmrgateway['DMR Network 2']['Name'] = $dmrMasterHostArr2[3];
	  if (empty($_POST['dmrNetworkOptions']) != TRUE ) {
	    $dmrOptionsLineStripped = str_replace('"', "", $_POST['dmrNetworkOptions']);
	    unset ($configmmdvm['DMR Network']['Options']);
	    $configdmrgateway['DMR Network 2']['Options'] = '"'.$dmrOptionsLineStripped.'"';
	  }
	  else {
		unset ($configdmrgateway['DMR Network 2']['Options']);
	       }
	}
	if (empty($_POST['dmrMasterHost3']) != TRUE ) {
	  $dmrMasterHostArr3 = explode(',', escapeshellcmd($_POST['dmrMasterHost3']));
	  $configdmrgateway['XLX Network 1']['Address'] = $dmrMasterHostArr3[0];
	  $configdmrgateway['XLX Network 1']['Password'] = '"'.$dmrMasterHostArr3[1].'"';
	  $configdmrgateway['XLX Network 1']['Port'] = $dmrMasterHostArr3[2];
	  $configdmrgateway['XLX Network 1']['Name'] = $dmrMasterHostArr3[3];
	  $configdmrgateway['XLX Network']['Startup'] = substr($dmrMasterHostArr3[3], 4);
	}

	// XLX StartUp TG
	if (empty($_POST['dmrMasterHost3Startup']) != TRUE ) {
	  $dmrMasterHost3Startup = escapeshellcmd($_POST['dmrMasterHost3Startup']);
	  if ($dmrMasterHost3Startup != "None") {
	    $configdmrgateway['XLX Network 1']['Startup'] = $dmrMasterHost3Startup;
	  }
	  else { unset($configdmrgateway['XLX Network 1']['Startup']); }
	}

	// XLX Module Override
	if (empty($_POST['dmrMasterHost3StartupModule']) != TRUE ) {
	  $dmrMasterHost3StartupModule = escapeshellcmd($_POST['dmrMasterHost3StartupModule']);
	  if ($dmrMasterHost3StartupModule == "Default") {
	    unset($configdmrgateway['XLX Network']['Module']);
	  } else {
	    $configdmrgateway['XLX Network']['Module'] = $dmrMasterHost3StartupModule;
	  }
	}

	// Set JutterBuffer Option
	//if (empty($_POST['dmrDMRnetJitterBufer']) != TRUE ) {
	//  if (escapeshellcmd($_POST['dmrDMRnetJitterBufer']) == 'ON' ) { $configmmdvm['DMR Network']['JitterEnabled'] = "1"; $configysf2dmr['DMR Network']['JitterEnabled'] = "1"; }
	//  if (escapeshellcmd($_POST['dmrDMRnetJitterBufer']) == 'OFF' ) { $configmmdvm['DMR Network']['JitterEnabled'] = "0"; $configysf2dmr['DMR Network']['JitterEnabled'] = "0"; }
	//}
	unset($configmmdvm['DMR Network']['JitterEnabled']);

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
	  if (escapeshellcmd($_POST['dmrGatewayXlxEn']) == 'ON' ) { $configdmrgateway['XLX Network 1']['Enabled'] = "1"; $configdmrgateway['XLX Network']['Enabled'] = "1"; }
	  if (escapeshellcmd($_POST['dmrGatewayXlxEn']) == 'OFF' ) { $configdmrgateway['XLX Network 1']['Enabled'] = "0"; $configdmrgateway['XLX Network']['Enabled'] = "0"; }
	}

	// Set the DMRGateway Network 2 On or Off
	if (empty($_POST['dmrGatewayNet2En']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrGatewayNet2En']) == 'ON' ) { $configdmrgateway['DMR Network 2']['Enabled'] = "1"; }
	  if (escapeshellcmd($_POST['dmrGatewayNet2En']) == 'OFF' ) { $configdmrgateway['DMR Network 2']['Enabled'] = "0"; }
	}

	// Set the DMRGateway Network 1 On or Off
	if (empty($_POST['dmrGatewayNet1En']) != TRUE ) {
	  if (escapeshellcmd($_POST['dmrGatewayNet1En']) == 'ON' ) { $configdmrgateway['DMR Network 1']['Enabled'] = "1"; }
	  if (escapeshellcmd($_POST['dmrGatewayNet1En']) == 'OFF' ) { $configdmrgateway['DMR Network 1']['Enabled'] = "0"; }
	}

	// Remove old settings
	if (isset($configmmdvm['General']['ModeHang'])) { unset($configmmdvm['General']['ModeHang']); }
	if (isset($configdmrgateway['General']['Timeout'])) { unset($configdmrgateway['General']['Timeout']); }
	if (isset($configmmdvm['General']['RFModeHang'])) { $configmmdvm['General']['RFModeHang'] = 300; }
	if (isset($configmmdvm['General']['NetModeHang'])) { $configmmdvm['General']['NetModeHang'] = 300; }

	// Set DMR Hang Timers
	if (empty($_POST['dmrRfHangTime']) != TRUE ) {
	  $configmmdvm['DMR']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dmrRfHangTime']);
	  $configdmrgateway['General']['RFTimeout'] = preg_replace('/[^0-9]/', '', $_POST['dmrRfHangTime']);
	}
	if (empty($_POST['dmrNetHangTime']) != TRUE ) {
	  $configmmdvm['DMR Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['dmrNetHangTime']);
	  $configdmrgateway['General']['NetTimeout'] = preg_replace('/[^0-9]/', '', $_POST['dmrNetHangTime']);
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
	  if (isset($configdgidgateway)) { $configdgidgateway['General']['RFHangTime'] = preg_replace('/[^0-9]/', '', $_POST['ysfRfHangTime']); }
	  if (isset($configdgidgateway)) { $configdgidgateway['YSF Network']['RFHangTime'] = preg_replace('/[^0-9]/', '', $_POST['ysfRfHangTime']); }
	  if (isset($configdgidgateway)) { $configdgidgateway['FCS Network']['RFHangTime'] = preg_replace('/[^0-9]/', '', $_POST['ysfRfHangTime']); }
	  if (isset($configdgidgateway)) { $configdgidgateway['IMRS Network']['RFHangTime'] = preg_replace('/[^0-9]/', '', $_POST['ysfRfHangTime']); }
	}
	if (empty($_POST['ysfNetHangTime']) != TRUE ) {
	  $configmmdvm['System Fusion Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['ysfNetHangTime']);
	  if (isset($configdgidgateway)) { $configdgidgateway['General']['NetHangTime'] = preg_replace('/[^0-9]/', '', $_POST['ysfNetHangTime']); }
	  if (isset($configdgidgateway)) { $configdgidgateway['YSF Network']['NetHangTime'] = preg_replace('/[^0-9]/', '', $_POST['ysfNetHangTime']); }
	  if (isset($configdgidgateway)) { $configdgidgateway['FCS Network']['NetHangTime'] = preg_replace('/[^0-9]/', '', $_POST['ysfNetHangTime']); }
	  if (isset($configdgidgateway)) { $configdgidgateway['IMRS Network']['NetHangTime'] = preg_replace('/[^0-9]/', '', $_POST['ysfNetHangTime']); }
	}
	// Set P25 Hang Timers
	if (empty($_POST['p25RfHangTime']) != TRUE ) {
	  $configmmdvm['P25']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['p25RfHangTime']);
	  $configp25gateway['Network']['RFHangTime'] = "0";
	}
	if (empty($_POST['p25NetHangTime']) != TRUE ) {
	  $configmmdvm['P25 Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['p25NetHangTime']);
	  $configp25gateway['Network']['NetHangTime'] = "0";
	}
	// Set NXDN Hang Timers
	if (empty($_POST['nxdnRfHangTime']) != TRUE ) {
	  $configmmdvm['NXDN']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['nxdnRfHangTime']);
	  $confignxdngateway['Network']['RFHangTime'] = "0";
	}
	if (empty($_POST['nxdnNetHangTime']) != TRUE ) {
	  $configmmdvm['NXDN Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['nxdnNetHangTime']);
	  $confignxdngateway['Network']['NetHangTime'] = "0";
	}
	// Set M17 Hang Timers
	if (empty($_POST['m17RfHangTime']) != TRUE ) {
	  $configmmdvm['M17']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['m17RfHangTime']);
	}
	if (empty($_POST['m17NetHangTime']) != TRUE ) {
	  $configmmdvm['M17 Network']['ModeHang'] = preg_replace('/[^0-9]/', '', $_POST['m17NetHangTime']);
	  if (isset($configm17gateway['Network']['HangTime'])) { $configm17gateway['Network']['HangTime'] = preg_replace('/[^0-9]/', '', $_POST['m17NetHangTime']); }
	}

	// Set the hardware type
	if (empty($_POST['confHardware']) != TRUE ) {
	$confHardware = escapeshellcmd($_POST['confHardware']);
	$configModem['Modem']['Hardware'] = $confHardware;
	// Set the Start delay
	$rollDstarRepeaterStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=30" /lib/systemd/system/dstarrepeater.timer';
	$rollMMDVMHostStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=30" /lib/systemd/system/mmdvmhost.timer';
	// Turn on RPT1 Validation in DStarRepeater
	$rollRpt1Validation = 'sudo sed -i "/rpt1Validation=/c\\rpt1Validation=1" /etc/dstarrepeater';
	// Set Standard IP/Port for DStarRepeater/MMDVMHost
	$rollRepeaterAddress1 = 'sudo sed -i "/repeaterAddress1=/c\\repeaterAddress1=127.0.0.1" /etc/ircddbgateway';
	$rollRepeaterPort1 = 'sudo sed -i "/repeaterPort1=/c\\repeaterPort1=20011" /etc/ircddbgateway';

	  if ( $confHardware == 'idrp2c' ) {
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=1" /etc/ircddbgateway';
	    $rollRepeaterAddress1 = 'sudo sed -i "/repeaterAddress1=/c\\repeaterAddress1=172.16.0.1" /etc/ircddbgateway';
	    $rollRepeaterPort1 = 'sudo sed -i "/repeaterPort1=/c\\repeaterPort1=20000" /etc/ircddbgateway';
	    system($rollRepeaterType1);
	    $testNeworkConfig = exec('grep "eth0:1" /etc/network/interfaces | wc -l');
	    if (substr($testNeworkConfig, 0, 1) === '0') {
	      system('sudo sed -i "$ a\ \\nauto eth0:1\\nallow-hotplug eth0:1\\niface eth0:1 inet static\\n    address 172.16.0.20\\n    netmask 255.255.255.0" /etc/network/interfaces');
	    }
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'icomTerminalAuto' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=Icom Access Point\/Terminal Mode" /etc/dstarrepeater';
	    $rollIcomPort = 'sudo sed -i "/icomPort=/c\\icomPort=/dev/icom_ta" /etc/dstarrepeater';
	    $rollRpt1Validation = 'sudo sed -i "/rpt1Validation=/c\\rpt1Validation=0" /etc/dstarrepeater';
	    system($rollModemType);
	    system($rollIcomPort);
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'dvmpis' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=2" /etc/dstarrepeater';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvmpid' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvmuadu' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvmuada' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyACM0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvmbss' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=2" /etc/dstarrepeater';
	    $rollDstarRepeaterStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=60" /lib/systemd/system/dstarrepeater.timer';
	    $rollMMDVMHostStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=60" /lib/systemd/system/mmdvmhost.timer';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvmbsd' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
	    $rollDstarRepeaterStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=60" /lib/systemd/system/dstarrepeater.timer';
	    $rollMMDVMHostStartDelay = 'sudo sed -i "/OnStartupSec=/c\\OnStartupSec=60" /lib/systemd/system/mmdvmhost.timer';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvmuagmsku' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvmuagmska' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyACM0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvrptr1' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V1" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVRPTRPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvrptr2' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V2" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVRPTRPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvrptr3' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DV-RPTR V3" /etc/dstarrepeater';
	    $rollDVRPTRPort = 'sudo sed -i "/dvrptr1Port=/c\\dvrptr1Port=/dev/ttyACM0" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollDVRPTRPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'gmsk_modem' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=GMSK Modem" /etc/dstarrepeater';
	    system($rollModemType);
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvap' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVAP" /etc/dstarrepeater';
            $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'zumspotlibre' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'zumspotusb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'lsusb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'zumspotgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'zumspotdualgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'zumspotduplexgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

          if ( $confHardware == 'zumradiopiusb' ) {
            $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
            $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
            system($rollRepeaterType1);
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
            $configmmdvm['General']['Duplex'] = 0;
            $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
          }

	  if ( $confHardware == 'zumradiopigpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'zum' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyACM0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
            $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'stm32dvm' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'stm32usb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'f4mgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'f4mf7m' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'mmdvmhshat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'lshshatgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'mmdvmhshatambe' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttySC0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'mmdvmhsdualbandgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'sbhsdualbandgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'genesyshat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'genesysdualhat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'mmdvmhsdualhatgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'lshsdualhatgpio' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'mmdvmhsdualhatusb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'mmdvmrpthat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'mmdvmmdohat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'mmdvmvyehat' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'mmdvmvyehatdual' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 1;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'mnnano-spot' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'mnnano-teensy' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollMMDVMPort = 'sudo sed -i "/mmdvmPort=/c\\mmdvmPort=/dev/ttyUSB0" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollMMDVMPort);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyUSB0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'nanodv' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'nanodvusb' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	  }

	  if ( $confHardware == 'dvmpicast' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyAMA0" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=2" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyAMA0";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvmpicasths' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyS2" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyS2";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  if ( $confHardware == 'dvmpicasthd' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=DVMEGA" /etc/dstarrepeater';
	    $rollDVMegaPort = 'sudo sed -i "/dvmegaPort=/c\\dvmegaPort=/dev/ttyS2" /etc/dstarrepeater';
	    $rollDVMegaVariant = 'sudo sed -i "/dvmegaVariant=/c\\dvmegaVariant=3" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    $configmmdvm['Modem']['Port'] = "/dev/ttyS2";
	    system($rollModemType);
	    system($rollDVMegaPort);
	    system($rollDVMegaVariant);
	    system($rollRepeaterType1);
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }
	  
	  if ( $confHardware == 'opengd77' ) {
	    $rollModemType = 'sudo sed -i "/modemType=/c\\modemType=MMDVM" /etc/dstarrepeater';
	    $rollRepeaterType1 = 'sudo sed -i "/repeaterType1=/c\\repeaterType1=0" /etc/ircddbgateway';
	    system($rollModemType);
	    system($rollRepeaterType1);
	    $configmmdvm['Modem']['Port'] = "/dev/ttyACM0";
	    $configmmdvm['General']['Duplex'] = 0;
	    $configmmdvm['DMR Network']['Slot1'] = 0;
	    $configmmdvm['Modem']['Protocol'] = "uart";
	    $configmmdvm['Modem']['UARTPort'] = $configmmdvm['Modem']['Port'];
	    if (isset($configmmdvm['FM']['Enable'])) { $configmmdvm['FM']['Enable'] = "0"; }
	  }

	  // Set the Service start delay
	  system($rollDstarRepeaterStartDelay);
	  system($rollMMDVMHostStartDelay);
	  // Turn on RPT1 validation on DStarRepeater
	  system($rollRpt1Validation);
	  // Set Standard IP/Port for ircDDBGateway
	  system($rollRepeaterAddress1);
	  system($rollRepeaterPort1);
	}

	// Set the Dashboard Public
	if (empty($_POST['dashAccess']) != TRUE ) {
	  $publicDashboard = 'sudo sed -i \'/$ipVar 80 80/c\\\t\t$DAEMON -u ${igdURL} -e ${hostVar}_Dash -a $ipVar 80 80 TCP > /dev/null 2>&1\' /usr/local/sbin/pistar-upnp.service';
	  $privateDashboard = 'sudo sed -i \'/$ipVar 80 80/ s/^#*/#/\' /usr/local/sbin/pistar-upnp.service';

	  if (escapeshellcmd($_POST['dashAccess']) == 'PUB' ) { system($publicDashboard); }
	  if (escapeshellcmd($_POST['dashAccess']) == 'PRV' ) { system($privateDashboard); }
	}

	// Set the ircDDBGateway Remote Public
	if (empty($_POST['ircRCAccess']) != TRUE ) {
	  $publicRCirc = 'sudo sed -i \'/$ipVar 10022 10022/c\\\t\t\t$DAEMON -u ${igdURL} -e ${hostVar}_Remote -a $ipVar 10022 10022 UDP > /dev/null 2>&1\' /usr/local/sbin/pistar-upnp.service';
	  $privateRCirc = 'sudo sed -i \'/$ipVar 10022 10022/ s/^#*/#/\' /usr/local/sbin/pistar-upnp.service';

	  if (escapeshellcmd($_POST['ircRCAccess']) == 'PUB' ) { system($publicRCirc); }
	  if (escapeshellcmd($_POST['ircRCAccess']) == 'PRV' ) { system($privateRCirc); }
	}

	// Set SSH Access Public
	if (empty($_POST['sshAccess']) != TRUE ) {
	  $publicSSH = 'sudo sed -i \'/$ipVar 22 22/c\\\t\t$DAEMON -u ${igdURL} -e ${hostVar}_SSH -a $ipVar 22 22 TCP > /dev/null 2>&1\' /usr/local/sbin/pistar-upnp.service';
	  $privateSSH = 'sudo sed -i \'/$ipVar 22 22/ s/^#*/#/\' /usr/local/sbin/pistar-upnp.service';

	  if (escapeshellcmd($_POST['sshAccess']) == 'PUB' ) { system($publicSSH); }
	  if (escapeshellcmd($_POST['sshAccess']) == 'PRV' ) { system($privateSSH); }
	}

	// Set uPNP On or Off
	if (empty($_POST['uPNP']) != TRUE ) {
	  $uPNPon = 'sudo sed -i \'/pistar-upnp.service/c\\*/5 *\t* * *\troot\t/usr/local/sbin/pistar-upnp.service start > /dev/null 2>&1 &\' /etc/crontab';
	  $uPNPoff = 'sudo sed -i \'/pistar-upnp.service/ s/^#*/#/\' /etc/crontab';
	  $uPNPsvcOn = 'sudo systemctl enable pistar-upnp.timer';
	  $uPNPsvcOff = 'sudo systemctl disable pistar-upnp.timer';
	  $uPNPsvcStart = '(sudo systemctl stop pistar-upnp.service && sudo systemctl start pistar-upnp.service) > /dev/null 2>&1 &';
	  $uPNPsvcStop = '(sudo systemctl stop pistar-upnp.service) > /dev/null 2>&1 &';

	  if (escapeshellcmd($_POST['uPNP']) == 'ON' )  { system($uPNPon); system($uPNPsvcOn); system($uPNPsvcStart); }
	  if (escapeshellcmd($_POST['uPNP']) == 'OFF' ) { system($uPNPoff); system($uPNPsvcStop); system($uPNPsvcOff); }
	}

	// D-Star Time Announce
	if (empty($_POST['confTimeAnnounce']) != TRUE ) {
	  if (escapeshellcmd($_POST['confTimeAnnounce']) == 'ON' )  { system('sudo rm -rf /etc/timeserver.dissable'); }
	  if (escapeshellcmd($_POST['confTimeAnnounce']) == 'OFF' )  { system('sudo touch /etc/timeserver.dissable'); }
	}

	// Set MMDVMHost DMR Mode
	if (empty($_POST['MMDVMModeDMR']) != TRUE ) {
	  if (escapeshellcmd($_POST['MMDVMModeDMR']) == 'ON' )  { $configmmdvm['DMR']['Enable'] = "1"; $configmmdvm['DMR Network']['Enable'] = "1"; $configysf2dmr['Enabled']['Enabled'] = "0";}
	  if (escapeshellcmd($_POST['MMDVMModeDMR']) == 'OFF' ) { $configmmdvm['DMR']['Enable'] = "0"; $configmmdvm['DMR Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost D-Star Mode
	if (empty($_POST['MMDVMModeDSTAR']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDSTAR']) == 'ON' )  { $configmmdvm['D-Star']['Enable'] = "1"; $configmmdvm['D-Star Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeDSTAR']) == 'OFF' ) { $configmmdvm['D-Star']['Enable'] = "0"; $configmmdvm['D-Star Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost Fusion Mode
	if (empty($_POST['MMDVMModeFUSION']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeFUSION']) == 'ON' )  { $configmmdvm['System Fusion']['Enable'] = "1"; $configmmdvm['System Fusion Network']['Enable'] = "1"; $configdmr2ysf['Enabled']['Enabled'] = "0"; }
          if (escapeshellcmd($_POST['MMDVMModeFUSION']) == 'OFF' ) { $configmmdvm['System Fusion']['Enable'] = "0"; $configmmdvm['System Fusion Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost P25 Mode
	if (empty($_POST['MMDVMModeP25']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeP25']) == 'ON' )  { $configmmdvm['P25']['Enable'] = "1"; $configmmdvm['P25 Network']['Enable'] = "1"; $configysf2p25['Enabled']['Enabled'] = "0"; }
          if (escapeshellcmd($_POST['MMDVMModeP25']) == 'OFF' ) { $configmmdvm['P25']['Enable'] = "0"; $configmmdvm['P25 Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost NXDN Mode
	if (empty($_POST['MMDVMModeNXDN']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeNXDN']) == 'ON' )  { $configmmdvm['NXDN']['Enable'] = "1"; $configmmdvm['NXDN Network']['Enable'] = "1"; $configysf2nxdn['Enabled']['Enabled'] = "0"; }
          if (escapeshellcmd($_POST['MMDVMModeNXDN']) == 'OFF' ) { $configmmdvm['NXDN']['Enable'] = "0"; $configmmdvm['NXDN Network']['Enable'] = "0"; }
	}

	// Set MMDVMHost M17 Mode
	if (empty($_POST['MMDVMModeM17']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeM17']) == 'ON' )  { $configmmdvm['M17']['Enable'] = "1"; $configmmdvm['M17 Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeM17']) == 'OFF' ) { $configmmdvm['M17']['Enable'] = "0"; $configmmdvm['M17 Network']['Enable'] = "0"; }
	}

	// Set YSF2DMR Mode
	if (empty($_POST['MMDVMModeYSF2DMR']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeYSF2DMR']) == 'ON' )  { $configysf2dmr['Enabled']['Enabled'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModeYSF2DMR']) == 'OFF' ) { $configysf2dmr['Enabled']['Enabled'] = "0"; }
	}

	// Set YSF2NXDN Mode
	if (empty($_POST['MMDVMModeYSF2NXDN']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeYSF2NXDN']) == 'ON' )  { $configysf2nxdn['Enabled']['Enabled'] = "1"; $configmmdvm['NXDN']['Enable'] = "0"; $configmmdvm['NXDN Network']['Enable'] = "0";}
          if (escapeshellcmd($_POST['MMDVMModeYSF2NXDN']) == 'OFF' ) { $configysf2nxdn['Enabled']['Enabled'] = "0"; }
	}

	// Set YSF2P25 Mode
	if (empty($_POST['MMDVMModeYSF2P25']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeYSF2P25']) == 'ON' )  { $configysf2p25['Enabled']['Enabled'] = "1"; $configmmdvm['P25']['Enable'] = "0"; $configmmdvm['P25 Network']['Enable'] = "0"; }
          if (escapeshellcmd($_POST['MMDVMModeYSF2P25']) == 'OFF' ) { $configysf2p25['Enabled']['Enabled'] = "0"; }
	  if (escapeshellcmd($_POST['MMDVMModeFUSION']) == 'OFF' ) { $configysf2p25['Enabled']['Enabled'] = "0"; }
	}

	// Set DMR2YSF Mode
	if (empty($_POST['MMDVMModeDMR2YSF']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDMR2YSF']) == 'ON' )  {
		  $configdmr2ysf['Enabled']['Enabled'] = "1";
		  unset($configdmrgateway['DMR Network 3']);
		  $configdmrgateway['DMR Network 3']['Enabled'] = "0";
		  $configdmrgateway['DMR Network 3']['Name'] = "DMR2YSF_Cross-over";
		  $configdmrgateway['DMR Network 3']['Id'] = $configdmrgateway['DMR Network 2']['Id'];
		  $configdmrgateway['DMR Network 3']['Address'] = "127.0.0.1";
		  $configdmrgateway['DMR Network 3']['Port'] = "62033";
		  $configdmrgateway['DMR Network 3']['Local'] = "62034";
		  $configdmrgateway['DMR Network 3']['TGRewrite0'] = "2,7000001,2,1,999998";
		  $configdmrgateway['DMR Network 3']['SrcRewrite0'] = "2,1,2,7000001,999998";
		  $configdmrgateway['DMR Network 3']['PCRewrite0'] = "2,7000001,2,1,999998";
		  $configdmrgateway['DMR Network 3']['Password'] = '"'."PASSWORD".'"';
		  $configdmrgateway['DMR Network 3']['Location'] = "0";
		  $configdmrgateway['DMR Network 3']['Debug'] = "0";
		  $configmmdvm['System Fusion']['Enable'] = "0";
		  $configmmdvm['System Fusion Network']['Enable'] = "0";
	  }
          if (escapeshellcmd($_POST['MMDVMModeDMR2YSF']) == 'OFF' ) {
		  $configdmr2ysf['Enabled']['Enabled'] = "0";
		  $configdmrgateway['DMR Network 3']['Enabled'] = "0";
	  }
	}

	// Set DMR2NXDN Mode
	if (empty($_POST['MMDVMModeDMR2NXDN']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModeDMR2NXDN']) == 'ON' )  {
		  if (empty($_POST['MMDVMModeDMR2YSF']) != TRUE ) {
		  	if (escapeshellcmd($_POST['MMDVMModeDMR2YSF']) == 'ON' )  {
				$configdmr2ysf['Enabled']['Enabled'] = "0";
		  	}
	  	  }
		  if (empty($_POST['MMDVMModeYSF2NXDN']) != TRUE ) {
		  	if (escapeshellcmd($_POST['MMDVMModeYSF2NXDN']) == 'ON' )  {
				$configysf2nxdn['Enabled']['Enabled'] = "0";
		  	}
	  	  }
		  $configdmr2nxdn['Enabled']['Enabled'] = "1";
		  unset($configdmrgateway['DMR Network 3']);
		  $configdmrgateway['DMR Network 3']['Enabled'] = "0";
		  $configdmrgateway['DMR Network 3']['Name'] = "DMR2NXDN_Cross-over";
		  $configdmrgateway['DMR Network 3']['Id'] = $configdmrgateway['DMR Network 2']['Id'];
		  $configdmrgateway['DMR Network 3']['Address'] = "127.0.0.1";
		  $configdmrgateway['DMR Network 3']['Port'] = "62035";
		  $configdmrgateway['DMR Network 3']['Local'] = "62036";
		  $configdmrgateway['DMR Network 3']['TGRewrite0'] = "2,7000001,2,1,999998";
		  $configdmrgateway['DMR Network 3']['SrcRewrite0'] = "2,1,2,7000001,999998";
		  $configdmrgateway['DMR Network 3']['PCRewrite0'] = "2,7000001,2,1,999998";
		  $configdmrgateway['DMR Network 3']['Password'] = '"'."PASSWORD".'"';
		  $configdmrgateway['DMR Network 3']['Location'] = "0";
		  $configdmrgateway['DMR Network 3']['Debug'] = "0";
		  $configmmdvm['NXDN']['Enable'] = "0";
		  $configmmdvm['NXDN Network']['Enable'] = "0";
	  }
          if (escapeshellcmd($_POST['MMDVMModeDMR2NXDN']) == 'OFF' ) {
		  $configdmr2nxdn['Enabled']['Enabled'] = "0";
		  $configdmrgateway['DMR Network 3']['Enabled'] = "0";
	  }
	}

	// Work out if DMR Network 3 should be ON or not
	if (empty($_POST['MMDVMModeDMR2YSF']) != TRUE || empty($_POST['MMDVMModeDMR2NXDN']) != TRUE) {
		if (escapeshellcmd($_POST['MMDVMModeDMR2YSF']) == 'ON' || escapeshellcmd($_POST['MMDVMModeDMR2NXDN']) == 'ON') {
			$configdmrgateway['DMR Network 3']['Enabled'] = "1";
		} else {
			$configdmrgateway['DMR Network 3']['Enabled'] = "0";
		}
	}

	// Set POCSAG Mode
	if (empty($_POST['MMDVMModePOCSAG']) != TRUE ) {
          if (escapeshellcmd($_POST['MMDVMModePOCSAG']) == 'ON' )  { $configmmdvm['POCSAG']['Enable'] = "1"; $configmmdvm['POCSAG Network']['Enable'] = "1"; }
          if (escapeshellcmd($_POST['MMDVMModePOCSAG']) == 'OFF' ) { $configmmdvm['POCSAG']['Enable'] = "0"; $configmmdvm['POCSAG Network']['Enable'] = "0"; }
	}

	// Set the MMDVMHost Display Type
	if  (empty($_POST['mmdvmDisplayType']) != TRUE ) {
	  if (substr($_POST['mmdvmDisplayType'] , 0, 4 ) === "OLED") {
		  $configmmdvm['General']['Display'] = "OLED";
		  $configmmdvm['OLED']['Type'] = substr($_POST['mmdvmDisplayType'] , 4, 1 );
		  if ($configmmdvm['OLED']['Type'] == "6") { $configmmdvm['OLED']['Scroll'] = "0"; }
	  }
	  else {
		  $configmmdvm['General']['Display'] = escapeshellcmd($_POST['mmdvmDisplayType']);
	  }
	}

	// Set the MMDVMHost Display Type
	if  (empty($_POST['mmdvmDisplayPort']) != TRUE ) {
	  if (($_POST['mmdvmDisplayPort'] == "None") || ($_POST['mmdvmDisplayPort'] == "modem")) {
		  $configmmdvm['TFT Serial']['Port'] = $_POST['mmdvmDisplayPort'];
		  $configmmdvm['Nextion']['Port'] = $_POST['mmdvmDisplayPort'];
	  } else {
		  $configmmdvm['TFT Serial']['Port'] = "/dev/".$_POST['mmdvmDisplayPort'];
		  $configmmdvm['Nextion']['Port'] = "/dev/".$_POST['mmdvmDisplayPort'];
	  }
	}

	// Set the Nextion Display Layout
	if (empty($_POST['mmdvmNextionDisplayType']) != TRUE ) {
	  if (escapeshellcmd($_POST['mmdvmNextionDisplayType']) == "G4KLX") { $configmmdvm['Nextion']['ScreenLayout'] = "0"; }
	  if (escapeshellcmd($_POST['mmdvmNextionDisplayType']) == "ON7LDSL2") { $configmmdvm['Nextion']['ScreenLayout'] = "2"; }
	  if (escapeshellcmd($_POST['mmdvmNextionDisplayType']) == "ON7LDSL3") { $configmmdvm['Nextion']['ScreenLayout'] = "3"; }
	  if (escapeshellcmd($_POST['mmdvmNextionDisplayType']) == "ON7LDSL3HS") { $configmmdvm['Nextion']['ScreenLayout'] = "4"; }
	}

	// Set MMDVMHost DMR Colour Code
	if (empty($_POST['dmrColorCode']) != TRUE ) {
          $configmmdvm['DMR']['ColorCode'] = escapeshellcmd($_POST['dmrColorCode']);
	}

	// Set MMDVMHost DMR Access List
	if (isset($configmmdvm['DMR']['WhiteList'])) { unset($configmmdvm['DMR']['WhiteList']); }
	if (empty($_POST['confDMRWhiteList']) != TRUE ) {
	  $configmmdvm['DMR']['WhiteList'] = escapeshellcmd(preg_replace('/[^0-9\,]/', '', $_POST['confDMRWhiteList']));
	}

	// Set Node Lock Status
	if (empty($_POST['nodeMode']) != TRUE ) {
	  if (escapeshellcmd($_POST['nodeMode']) == 'prv' ) {
            $configmmdvm['DMR']['SelfOnly'] = 1;
            $configmmdvm['D-Star']['SelfOnly'] = 1;
	    $configmmdvm['System Fusion']['SelfOnly'] = 1;
	    $configmmdvm['P25']['SelfOnly'] = 1;
	    $configmmdvm['NXDN']['SelfOnly'] = 1;
            system('sudo sed -i "/restriction=/c\\restriction=1" /etc/dstarrepeater');
          }
	  if (escapeshellcmd($_POST['nodeMode']) == 'pub' ) {
            $configmmdvm['DMR']['SelfOnly'] = 0;
            $configmmdvm['D-Star']['SelfOnly'] = 0;
	    $configmmdvm['System Fusion']['SelfOnly'] = 0;
	    $configmmdvm['P25']['SelfOnly'] = 0;
	    $configmmdvm['NXDN']['SelfOnly'] = 0;
            system('sudo sed -i "/restriction=/c\\restriction=0" /etc/dstarrepeater');
          }
	}

	// Set the Hostname
	if (empty($_POST['confHostame']) != TRUE ) {
	  $newHostnameLower = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $_POST['confHostame']));
	  $currHostname = exec('cat /etc/hostname');
	  $rollHostname = 'sudo sed -i "s/'.$currHostname.'/'.$newHostnameLower.'/" /etc/hostname';
	  $rollHosts = 'sudo sed -i "s/'.$currHostname.'/'.$newHostnameLower.'/" /etc/hosts';
	  $rollMotd = 'sudo sed -i "s/'.$currHostname.'/'.$newHostnameLower.'/" /etc/motd';
	  system($rollHostname);
	  system($rollHosts);
	  system($rollMotd);
	  if (file_exists('/etc/hostapd/hostapd.conf')) {
		  // Update the Hotspot name to the Hostname
		  $rollApSsid = 'sudo sed -i "/^ssid=/c\\ssid='.$newHostnameLower.'" /etc/hostapd/hostapd.conf';
		  system($rollApSsid);
	  }
	}

	// Add missing values to DMRGateway
	if (!isset($configdmrgateway['Info']['Enabled'])) { $configdmrgateway['Info']['Enabled'] = "0"; }
	if (!isset($configdmrgateway['Info']['Power'])) { $configdmrgateway['Info']['Power'] = $configmmdvm['Info']['Power']; }
	if (!isset($configdmrgateway['Info']['Height'])) { $configdmrgateway['Info']['Height'] = $configmmdvm['Info']['Height']; }
	if (!isset($configdmrgateway['XLX Network']['Enabled'])) { $configdmrgateway['XLX Network']['Enabled'] = "0"; }
	if (!isset($configdmrgateway['XLX Network']['File'])) { $configdmrgateway['XLX Network']['File'] = "/usr/local/etc/XLXHosts.txt"; }
	if (!isset($configdmrgateway['XLX Network']['Port'])) { $configdmrgateway['XLX Network']['Port'] = "62030"; }
	if (!isset($configdmrgateway['XLX Network']['Password'])) { $configdmrgateway['XLX Network']['Password'] = "passw0rd"; }
	if (!isset($configdmrgateway['XLX Network']['ReloadTime'])) { $configdmrgateway['XLX Network']['ReloadTime'] = "60"; }
	if (!isset($configdmrgateway['XLX Network']['Slot'])) { $configdmrgateway['XLX Network']['Slot'] = "2"; }
	if (!isset($configdmrgateway['XLX Network']['TG'])) { $configdmrgateway['XLX Network']['TG'] = "6"; }
	if (!isset($configdmrgateway['XLX Network']['Base'])) { $configdmrgateway['XLX Network']['Base'] = "64000"; }
	if (!isset($configdmrgateway['XLX Network']['Startup'])) { $configdmrgateway['XLX Network']['Startup'] = "950"; }
	if (!isset($configdmrgateway['XLX Network']['Relink'])) { $configdmrgateway['XLX Network']['Relink'] = "60"; }
	if (!isset($configdmrgateway['XLX Network']['Debug'])) { $configdmrgateway['XLX Network']['Debug'] = "0"; }
	if (!isset($configdmrgateway['DMR Network 3']['Enabled'])) { $configdmrgateway['DMR Network 3']['Enabled'] = "0"; }
	if (!isset($configdmrgateway['DMR Network 3']['Name'])) { $configdmrgateway['DMR Network 3']['Name'] = "HBLink"; }
	if (!isset($configdmrgateway['DMR Network 3']['Address'])) { $configdmrgateway['DMR Network 3']['Address'] = "1.2.3.4"; }
	if (!isset($configdmrgateway['DMR Network 3']['Port'])) { $configdmrgateway['DMR Network 3']['Port'] = "5555"; }
	if (!isset($configdmrgateway['DMR Network 3']['TGRewrite0'])) { $configdmrgateway['DMR Network 3']['TGRewrite0'] = "2,11,2,11,1"; }
	if (!isset($configdmrgateway['DMR Network 3']['Password'])) { $configdmrgateway['DMR Network 3']['Password'] = "PASSWORD"; }
	if (!isset($configdmrgateway['DMR Network 3']['Location'])) { $configdmrgateway['DMR Network 3']['Location'] = "0"; }
	if (!isset($configdmrgateway['DMR Network 3']['Debug'])) { $configdmrgateway['DMR Network 3']['Debug'] = "0"; }
	if (!isset($configdmrgateway['XLX Network']['UserControl'])) { $configdmrgateway['XLX Network']['UserControl'] = "1"; }
	$dmrGatewayVer = exec("DMRGateway -v | awk {'print $3'} | cut -c 1-8");
	if ($dmrGatewayVer > 20210130) {
		if (!isset($configdmrgateway['DMR Network 1']['Location'])) { $configdmrgateway['DMR Network 1']['Location'] = "1"; }
		if (!isset($configdmrgateway['DMR Network 2']['Location'])) { $configdmrgateway['DMR Network 2']['Location'] = "0"; }
		if (!isset($configdmrgateway['DMR Network 3']['Location'])) { $configdmrgateway['DMR Network 3']['Location'] = "0"; }
		if (isset($configdmrgateway['DMR Network 4'])) {
			if (!isset($configdmrgateway['DMR Network 4']['Location'])) { $configdmrgateway['DMR Network 4']['Location'] = "0"; }
		}
		if (isset($configdmrgateway['DMR Network 5'])) {
			if (!isset($configdmrgateway['DMR Network 5']['Location'])) { $configdmrgateway['DMR Network 5']['Location'] = "0"; }
		}
		if (isset($configdmrgateway['DMR Network 6'])) {
			if (!isset($configdmrgateway['DMR Network 6']['Location'])) { $configdmrgateway['DMR Network 6']['Location'] = "0"; }
		}
		if (!isset($configdmrgateway['GPSD'])) {
			$configdmrgateway['GPSD']['Enable'] = "0";
			$configdmrgateway['GPSD']['Address'] = "127.0.0.1";
			$configdmrgateway['GPSD']['Port'] = "2947";
		}
		if (!isset($configdmrgateway['APRS'])) {
			$configdmrgateway['APRS']['Enable'] = "1";
			$configdmrgateway['APRS']['Address'] = "127.0.0.1";
			$configdmrgateway['APRS']['Port'] = "8673";
			$configdmrgateway['APRS']['Description'] = "APRS for DMRGateway";
			$configdmrgateway['APRS']['Suffix'] = "DMR";
		}
		if (!isset($configdmrgateway['Dynamic TG Control'])) {
			$configdmrgateway['Dynamic TG Control']['Enabled'] = "1";
			$configdmrgateway['Dynamic TG Control']['Port'] = "3769";
		}
	}
	// DMRGateway can break the lines with quotes in, when DMRGateway is off...
	if ( isset($configdmrgateway['Info']['Location']) && substr($configdmrgateway['Info']['Location'], 0, 1) !== '"' ) 			{ $configdmrgateway['Info']['Location'] = '"'.$configdmrgateway['Info']['Location'].'"'; }
	if ( isset($configdmrgateway['Info']['Description']) && substr($configdmrgateway['Info']['Description'], 0, 1) !== '"' ) 		{ $configdmrgateway['Info']['Description'] = '"'.$configdmrgateway['Info']['Description'].'"'; }
	if ( isset($configdmrgateway['DMR Network 1']['Password']) && substr($configdmrgateway['DMR Network 1']['Password'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 1']['Password'] = '"'.$configdmrgateway['DMR Network 1']['Password'].'"'; }
	if ( isset($configdmrgateway['DMR Network 1']['Options']) &&  substr($configdmrgateway['DMR Network 1']['Options'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 1']['Options'] = '"'.$configdmrgateway['DMR Network 1']['Options'].'"'; }
	if ( isset($configdmrgateway['DMR Network 2']['Password']) && substr($configdmrgateway['DMR Network 2']['Password'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 2']['Password'] = '"'.$configdmrgateway['DMR Network 2']['Password'].'"'; }
	if ( isset($configdmrgateway['DMR Network 2']['Options']) &&  substr($configdmrgateway['DMR Network 2']['Options'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 2']['Options'] = '"'.$configdmrgateway['DMR Network 2']['Options'].'"'; }
	if ( isset($configdmrgateway['DMR Network 3']['Password']) && substr($configdmrgateway['DMR Network 3']['Password'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 3']['Password'] = '"'.$configdmrgateway['DMR Network 3']['Password'].'"'; }
	if ( isset($configdmrgateway['DMR Network 3']['Options']) &&  substr($configdmrgateway['DMR Network 3']['Options'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 3']['Options'] = '"'.$configdmrgateway['DMR Network 3']['Options'].'"'; }
	if ( isset($configdmrgateway['DMR Network 4']['Password']) && substr($configdmrgateway['DMR Network 4']['Password'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 4']['Password'] = '"'.$configdmrgateway['DMR Network 4']['Password'].'"'; }
	if ( isset($configdmrgateway['DMR Network 4']['Options']) &&  substr($configdmrgateway['DMR Network 4']['Options'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 4']['Options'] = '"'.$configdmrgateway['DMR Network 4']['Options'].'"'; }
	if ( isset($configdmrgateway['DMR Network 5']['Password']) && substr($configdmrgateway['DMR Network 5']['Password'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 5']['Password'] = '"'.$configdmrgateway['DMR Network 5']['Password'].'"'; }
	if ( isset($configdmrgateway['DMR Network 5']['Options']) &&  substr($configdmrgateway['DMR Network 5']['Options'], 0, 1) !== '"' )	{ $configdmrgateway['DMR Network 5']['Options'] = '"'.$configdmrgateway['DMR Network 5']['Options'].'"'; }

	// Add missing options to MMDVMHost
	if (!isset($configmmdvm['Modem']['RFLevel'])) { $configmmdvm['Modem']['RFLevel'] = "100"; }
	if (!isset($configmmdvm['Modem']['RXDCOffset'])) { $configmmdvm['Modem']['RXDCOffset'] = "0"; }
	if (!isset($configmmdvm['Modem']['TXDCOffset'])) { $configmmdvm['Modem']['TXDCOffset'] = "0"; }
	if (!isset($configmmdvm['Modem']['CWIdTXLevel'])) { $configmmdvm['Modem']['CWIdTXLevel'] = "50"; }
	if (!isset($configmmdvm['Modem']['NXDNTXLevel'])) { $configmmdvm['Modem']['NXDNTXLevel'] = "50"; }
	if (!isset($configmmdvm['Modem']['M17TXLevel'])) { $configmmdvm['Modem']['M17TXLevel'] = "50"; }
	if (!isset($configmmdvm['Modem']['POCSAGTXLevel'])) { $configmmdvm['Modem']['POCSAGTXLevel'] = "50"; }
	if (!isset($configmmdvm['Modem']['FMTXLevel'])) { $configmmdvm['Modem']['FMTXLevel'] = "50"; }
	if (!isset($configmmdvm['Modem']['AX25TXLevel'])) { $configmmdvm['Modem']['AX25TXLevel'] = "50"; }
	if (!isset($configmmdvm['Modem']['UseCOSAsLockout'])) { $configmmdvm['Modem']['UseCOSAsLockout'] = "0"; }
	if (!isset($configmmdvm['Modem']['UARTSpeed'])) { $configmmdvm['Modem']['UARTSpeed'] = "115200"; }
	if (!isset($configmmdvm['D-Star']['AckReply'])) { $configmmdvm['D-Star']['AckReply'] = "1"; }
	if (!isset($configmmdvm['D-Star']['AckTime'])) { $configmmdvm['D-Star']['AckTime'] = "750"; }
	if (!isset($configmmdvm['D-Star']['AckMessage'])) { $configmmdvm['D-Star']['AckMessage'] = "0"; }
	if (!isset($configmmdvm['D-Star']['RemoteGateway'])) { $configmmdvm['D-Star']['RemoteGateway'] = "0"; }
	if (!isset($configmmdvm['DMR']['BeaconInterval'])) { $configmmdvm['DMR']['BeaconInterval'] = "60"; }
	if (!isset($configmmdvm['DMR']['BeaconDuration'])) { $configmmdvm['DMR']['BeaconDuration'] = "3"; }
	if (!isset($configmmdvm['DMR']['OVCM'])) { $configmmdvm['DMR']['OVCM'] = "0"; }
	if (!isset($configmmdvm['DMR Network']['Type'])) { $configmmdvm['DMR Network']['Type'] = "Direct"; }
	if (!isset($configmmdvm['P25']['RemoteGateway'])) { $configmmdvm['P25']['RemoteGateway'] = "0"; }
	if (!isset($configmmdvm['P25']['TXHang'])) { $configmmdvm['P25']['TXHang'] = "5"; }
	if (!isset($configmmdvm['OLED']['Scroll'])) { $configmmdvm['OLED']['Scroll'] = "0"; }
	if (!isset($configmmdvm['NXDN']['Enable'])) { $configmmdvm['NXDN']['Enable'] = "0"; }
	if (!isset($configmmdvm['NXDN']['RAN'])) { $configmmdvm['NXDN']['RAN'] = "1"; }
	if (!isset($configmmdvm['NXDN']['SelfOnly'])) { $configmmdvm['NXDN']['SelfOnly'] = "1"; }
	if (!isset($configmmdvm['NXDN']['RemoteGateway'])) { $configmmdvm['NXDN']['RemoteGateway'] = "0"; }
	if (!isset($configmmdvm['NXDN']['TXHang'])) { $configmmdvm['NXDN']['TXHang'] = "5"; }
	if (!isset($configmmdvm['M17']['Enable'])) { $configmmdvm['M17']['Enable'] = "0"; }
	if (!isset($configmmdvm['M17']['CAN'])) { $configmmdvm['M17']['CAN'] = "0"; }
	if (!isset($configmmdvm['M17']['SelfOnly'])) { $configmmdvm['M17']['SelfOnly'] = "1"; }
	if (!isset($configmmdvm['M17']['TXHang'])) { $configmmdvm['M17']['TXHang'] = "5"; }
	if (!isset($configmmdvm['M17']['Enable'])) { $configmmdvm['M17']['Enable'] = "0"; }
	if (!isset($configmmdvm['M17']['ModeHang'])) { $configmmdvm['M17']['ModeHang'] = "20"; }
	if (!isset($configmmdvm['AX.25']['Enable'])) { $configmmdvm['AX.25']['Enable'] = "0"; }
	if (!isset($configmmdvm['AX.25']['TXDelay'])) { $configmmdvm['AX.25']['TXDelay'] = "300"; }
	if (!isset($configmmdvm['AX.25']['RXTwist'])) { $configmmdvm['AX.25']['RXTwist'] = "6"; }
	if (!isset($configmmdvm['AX.25']['SlotTime'])) { $configmmdvm['AX.25']['SlotTime'] = "30"; }
	if (!isset($configmmdvm['AX.25']['PPersist'])) { $configmmdvm['AX.25']['PPersist'] = "128"; }
	if (!isset($configmmdvm['AX.25']['Trace'])) { $configmmdvm['AX.25']['Trace'] = "0"; }
	if (!isset($configmmdvm['NXDN Network']['Enable'])) { $configmmdvm['NXDN Network']['Enable'] = "0"; }
	if (!isset($configmmdvm['NXDN Network']['LocalPort'])) { $configmmdvm['NXDN Network']['LocalPort'] = "3300"; }
	if (!isset($configmmdvm['NXDN Network']['GatewayAddress'])) { $configmmdvm['NXDN Network']['GatewayAddress'] = "127.0.0.1"; }
	if (!isset($configmmdvm['NXDN Network']['GatewayPort'])) { $configmmdvm['NXDN Network']['GatewayPort'] = "4300"; }
	if (!isset($configmmdvm['NXDN Network']['Protocol'])) { $configmmdvm['NXDN Network']['Protocol'] = "Icom"; }
	if (!isset($configmmdvm['NXDN Network']['Debug'])) { $configmmdvm['NXDN Network']['Debug'] = "0"; }
	if (!isset($configmmdvm['M17 Network']['Enable'])) { $configmmdvm['M17 Network']['Enable'] = "0"; }
	if (!isset($configmmdvm['M17 Network']['LocalAddress'])) { $configmmdvm['M17 Network']['LocalAddress'] = "127.0.0.1"; }
	if (!isset($configmmdvm['M17 Network']['LocalPort'])) { $configmmdvm['M17 Network']['LocalPort'] = "17011"; }
	if (!isset($configmmdvm['M17 Network']['GatewayAddress'])) { $configmmdvm['M17 Network']['GatewayAddress'] = "127.0.0.1"; }
	if (!isset($configmmdvm['M17 Network']['GatewayPort'])) { $configmmdvm['M17 Network']['GatewayPort'] = "17010"; }
	if (!isset($configmmdvm['M17 Network']['ModeHang'])) { $configmmdvm['M17 Network']['ModeHang'] = "20"; }
	if (!isset($configmmdvm['M17 Network']['Debug'])) { $configmmdvm['M17 Network']['Debug'] = "0"; }
	if (!isset($configmmdvm['AX.25 Network']['Enable'])) { $configmmdvm['AX.25 Network']['Enable'] = "0"; }
	if (!isset($configmmdvm['AX.25 Network']['Port'])) { $configmmdvm['AX.25 Network']['Port'] = "/dev/ttyp7"; }
	if (!isset($configmmdvm['AX.25 Network']['Speed'])) { $configmmdvm['AX.25 Network']['Speed'] = "9600"; }
	if (!isset($configmmdvm['AX.25 Network']['Debug'])) { $configmmdvm['AX.25 Network']['Debug'] = "0"; }
	if (!isset($configmmdvm['NXDN Id Lookup']['File'])) { $configmmdvm['NXDN Id Lookup']['File'] = "/usr/local/etc/NXDN.csv"; }
	if (!isset($configmmdvm['NXDN Id Lookup']['Time'])) { $configmmdvm['NXDN Id Lookup']['Time'] = "24"; }
	if (!isset($configmmdvm['System Fusion']['TXHang'])) { $configmmdvm['System Fusion']['TXHang'] = "3"; }
	if (!isset($configmmdvm['Lock File']['Enable'])) { $configmmdvm['Lock File']['Enable'] = "0"; }
	if (!isset($configmmdvm['Lock File']['File'])) { $configmmdvm['Lock File']['File'] = "/tmp/MMDVMHost.lock"; }
	if (!isset($configmmdvm['Mobile GPS']['Enable'])) { $configmmdvm['Mobile GPS']['Enable'] = "0"; }
 	if (!isset($configmmdvm['Mobile GPS']['Address'])) { $configmmdvm['Mobile GPS']['Address'] = "127.0.0.1"; }
	if (!isset($configmmdvm['Mobile GPS']['Port'])) { $configmmdvm['Mobile GPS']['Port'] = "7834"; }
	if (!isset($configmmdvm['OLED']['Rotate'])) { $configmmdvm['OLED']['Rotate'] = "0"; }
	if (!isset($configmmdvm['OLED']['Cast'])) { $configmmdvm['OLED']['Cast'] = "0"; }
	if (!isset($configmmdvm['OLED']['LogoScreensaver'])) { $configmmdvm['OLED']['LogoScreensaver'] = "0"; }
	if (!isset($configmmdvm['Remote Control']['Enable'])) { $configmmdvm['Remote Control']['Enable'] = "0"; }
	if (!isset($configmmdvm['Remote Control']['Port'])) { $configmmdvm['Remote Control']['Port'] = "7642"; }
	if (!isset($configmmdvm['Remote Control']['Address'])) { $configmmdvm['Remote Control']['Address'] = "127.0.0.1"; }
	if (isset($configmmdvm['TFT Serial']['Port'])) {
		if ( $configmmdvm['TFT Serial']['Port'] == "/dev/modem" ) { $configmmdvm['TFT Serial']['Port'] = "modem"; }
	}
	if (isset($configmmdvm['Nextion']['Port'])) {
		if ( $configmmdvm['Nextion']['Port'] == "/dev/modem" ) { $configmmdvm['Nextion']['Port'] = "modem"; }
	}
	if (!isset($configmmdvm['FM'])) {
		$configmmdvm['FM']['Enable'] = "0";
		$configmmdvm['FM']['Callsign'] = $newCallsignUpper;
		$configmmdvm['FM']['CallsignSpeed'] = "20";
		$configmmdvm['FM']['CallsignFrequency'] = "1000";
		$configmmdvm['FM']['CallsignTime'] = "10";
		$configmmdvm['FM']['CallsignHoldoff'] = "0";
		$configmmdvm['FM']['CallsignHighLevel'] = "50";
		$configmmdvm['FM']['CallsignLowLevel'] = "20";
		$configmmdvm['FM']['CallsignAtStart'] = "1";
		$configmmdvm['FM']['CallsignAtEnd'] = "1";
		$configmmdvm['FM']['CallsignAtLatch'] = "0";
		$configmmdvm['FM']['RFAck'] = "K";
		$configmmdvm['FM']['ExtAck'] = "N";
		$configmmdvm['FM']['AckSpeed'] = "20";
		$configmmdvm['FM']['AckFrequency'] = "1750";
		$configmmdvm['FM']['AckMinTime'] = "4";
		$configmmdvm['FM']['AckDelay'] = "1000";
		$configmmdvm['FM']['AckLevel'] = "50";
		$configmmdvm['FM']['Timeout'] = "180";
		$configmmdvm['FM']['TimeoutLevel'] = "80";
		$configmmdvm['FM']['CTCSSFrequency'] = "94.8";
		$configmmdvm['FM']['CTCSSThreshold'] = "30";
		$configmmdvm['FM']['CTCSSHighThreshold'] = "30";
		$configmmdvm['FM']['CTCSSLowThreshold'] = "20";
		$configmmdvm['FM']['CTCSSLevel'] = "20";
		$configmmdvm['FM']['KerchunkTime'] = "0";
		$configmmdvm['FM']['HangTime'] = "7";
		$configmmdvm['FM']['AccessMode'] = "1";
		$configmmdvm['FM']['COSInvert'] = "0";
		$configmmdvm['FM']['RFAudioBoost'] = "1";
		$configmmdvm['FM']['MaxDevLevel'] = "90";
		$configmmdvm['FM']['ExtAudioBoost'] = "1";
	}
	

	// Add missing options to DMR2YSF
	if (!isset($configdmr2ysf['YSF Network']['FCSRooms'])) { $configdmr2ysf['YSF Network']['FCSRooms'] = "/usr/local/etc/FCSHosts.txt"; }
	if (!isset($configdmr2ysf['DMR Network']['DefaultDstTG'])) { $configdmr2ysf['DMR Network']['DefaultDstTG'] = "9"; }
	if (!isset($configdmr2ysf['DMR Network']['TGUnlink'])) { $configdmr2ysf['DMR Network']['TGUnlink'] = "4000"; }
	if (!isset($configdmr2ysf['DMR Network']['TGListFile'])) { $configdmr2ysf['DMR Network']['TGListFile'] = "/usr/local/etc/TGList_YSF.txt"; }
	$configdmr2ysf['Log']['DisplayLevel'] = "0";
	$configdmr2ysf['Log']['FileLevel'] = "2";
	if (!isset($configdmr2ysf['YSF Network']['DT1']))   { $configdmr2ysf['YSF Network']['DT1'] = "1,34,97,95,43,3,17,0,0,0"; }
	if (!isset($configdmr2ysf['YSF Network']['DT2']))   { $configdmr2ysf['YSF Network']['DT2'] = "0,0,0,0,108,32,28,32,3,8"; }
	if (!isset($configdmr2ysf['YSF Network']['Debug'])) { $configdmr2ysf['YSF Network']['Debug'] = "0"; }

	// Add missing options to YSFGateway
	if (!isset($configysfgateway['General']['WiresXMakeUpper'])) { $configysfgateway['General']['WiresXMakeUpper'] = "1"; }
	if (!isset($configysfgateway['Network']['Revert'])) { $configysfgateway['Network']['Revert'] = "0"; }
	if (!isset($configysfgateway['Network']['Port'])) { $configysfgateway['Network']['Port'] = "42000"; }
	if (isset($configysfgateway['Network']['YSF2DMRAddress'])) { unset($configysfgateway['Network']['YSF2DMRAddress']); }
	if (isset($configysfgateway['Network']['YSF2DMRPort'])) { unset($configysfgateway['Network']['YSF2DMRPort']); }
	unset($configysfgateway['Network']['DataPort']);
	unset($configysfgateway['Network']['StatusPort']);
	if (!isset($configysfgateway['Mobile GPS']['Enable'])) { $configysfgateway['Mobile GPS']['Enable'] = "0"; }
 	if (!isset($configysfgateway['Mobile GPS']['Address'])) { $configysfgateway['Mobile GPS']['Address'] = "127.0.0.1"; }
	if (!isset($configysfgateway['Mobile GPS']['Port'])) { $configysfgateway['Mobile GPS']['Port'] = "7834"; }

	// Add missing options to YSF2DMR
	if (!isset($configysf2dmr['Info']['Power'])) { $configysf2dmr['Info']['Power'] = "1"; }
	if (!isset($configysf2dmr['Info']['Height'])) { $configysf2dmr['Info']['Height'] = "0"; }
	if (!isset($configysf2dmr['YSF Network']['DstAddress'])) { $configysf2dmr['YSF Network']['DstAddress'] = "127.0.0.1"; }
	if (!isset($configysf2dmr['YSF Network']['DstPort'])) { $configysf2dmr['YSF Network']['DstPort'] = "42000"; }
	if (!isset($configysf2dmr['YSF Network']['LocalAddress'])) { $configysf2dmr['YSF Network']['LocalAddress'] = "127.0.0.1"; }
	if (!isset($configysf2dmr['YSF Network']['LocalPort'])) { $configysf2dmr['YSF Network']['LocalPort'] = "42013"; }
	if (!isset($configysf2dmr['YSF Network']['Daemon'])) { $configysf2dmr['YSF Network']['Daemon'] = "1"; }
	if (!isset($configysf2dmr['YSF Network']['EnableWiresX'])) { $configysf2dmr['YSF Network']['EnableWiresX'] = "1"; }
	if (!isset($configysf2dmr['DMR Network']['StartupDstId'])) { $configysf2dmr['DMR Network']['StartupDstId'] = "31672"; }
	if (!isset($configysf2dmr['DMR Network']['StartupPC'])) { $configysf2dmr['DMR Network']['StartupPC'] = "0"; }
	if (!isset($configysf2dmr['DMR Network']['Jitter'])) { $configysf2dmr['DMR Network']['Jitter'] = "500"; }
	if (!isset($configysf2dmr['DMR Network']['EnableUnlink'])) { $configysf2dmr['DMR Network']['EnableUnlink'] = "1"; }
	if (!isset($configysf2dmr['DMR Network']['TGUnlink'])) { $configysf2dmr['DMR Network']['TGUnlink'] = "4000"; }
	if (!isset($configysf2dmr['DMR Network']['PCUnlink'])) { $configysf2dmr['DMR Network']['PCUnlink'] = "0"; }
	if (!isset($configysf2dmr['DMR Network']['Debug'])) { $configysf2dmr['DMR Network']['Debug'] = "0"; }
	if ( (!isset($configysf2dmr['DMR Network']['TGListFile'])) && (file_exists('/usr/local/etc/TGList_BM.txt')) ) { $configysf2dmr['DMR Network']['TGListFile'] = "/usr/local/etc/TGList_BM.txt"; }
	if (!isset($configysf2dmr['DMR Id Lookup']['File'])) { $configysf2dmr['DMR Id Lookup']['File'] = "/usr/local/etc/DMRIds.dat"; }
	if (!isset($configysf2dmr['DMR Id Lookup']['Time'])) { $configysf2dmr['DMR Id Lookup']['Time'] = "24"; }
	if (!isset($configysf2dmr['DMR Id Lookup']['DropUnknown'])) { $configysf2dmr['DMR Id Lookup']['DropUnknown'] = "0"; }
	if (!isset($configysf2dmr['Log']['DisplayLevel'])) { $configysf2dmr['Log']['DisplayLevel'] = "1"; }
	if (!isset($configysf2dmr['Log']['FileLevel'])) { $configysf2dmr['Log']['FileLevel'] = "2"; }
	if (!isset($configysf2dmr['Log']['FilePath'])) { $configysf2dmr['Log']['FilePath'] = "/var/log/pi-star"; }
	if (!isset($configysf2dmr['Log']['FileRoot'])) { $configysf2dmr['Log']['FileRoot'] = "YSF2DMR"; }
	if (!isset($configysf2dmr['aprs.fi']['Enable'])) { $configysf2dmr['aprs.fi']['Enable'] = "0"; }
	if (!isset($configysf2dmr['aprs.fi']['Port'])) { $configysf2dmr['aprs.fi']['Port'] = "14580"; }
	if (!isset($configysf2dmr['aprs.fi']['Refresh'])) { $configysf2dmr['aprs.fi']['Refresh'] = "240"; }
	if (!isset($configysf2dmr['Enabled']['Enabled'])) { $configysf2dmr['Enabled']['Enabled'] = "0"; }
	unset($configysf2dmr['Info']['Enabled']);
	unset($configysf2dmr['DMR Network']['JitterEnabled']);
	$configysf2dmr['Log']['DisplayLevel'] = "0";
	$configysf2dmr['Log']['FileLevel'] = "0";
	if (!isset($configysf2dmr['aprs.fi']['Enable'])) { $configysf2dmr['aprs.fi']['Enable'] = "0"; }
	if (!isset($configysf2dmr['YSF Network']['WiresXMakeUpper'])) { $configysf2dmr['YSF Network']['WiresXMakeUpper'] = "1"; }
	if (!isset($configysf2dmr['YSF Network']['DT1'])) { $configysf2dmr['YSF Network']['DT1'] = "1,34,97,95,43,3,17,0,0,0"; }
	if (!isset($configysf2dmr['YSF Network']['DT2'])) { $configysf2dmr['YSF Network']['DT2'] = "0,0,0,0,108,32,28,32,3,8"; }

	// Add missing options to YSF2NXDN
	$configysf2nxdn['YSF Network']['LocalPort'] = $configysfgateway['YSF Network']['YSF2NXDNPort'];
	$configysf2nxdn['YSF Network']['DstPort'] = $configysfgateway['YSF Network']['Port'];
	$configysf2nxdn['YSF Network']['Daemon'] = "1";
	$configysf2nxdn['YSF Network']['EnableWiresX'] = "1";
	if (!isset($configysf2nxdn['Enabled']['Enabled'])) { $configysf2nxdn['Enabled']['Enabled'] = "0"; }
	$configysf2nxdn['NXDN Id Lookup']['File'] = "/usr/local/etc/NXDN.csv";
	$configysf2nxdn['NXDN Network']['TGListFile'] = "/usr/local/etc/TGList_NXDN.txt";
	$configysf2nxdn['Log']['DisplayLevel'] = "0";
	$configysf2nxdn['Log']['FileLevel'] = "0";
	$configysf2nxdn['Log']['FilePath'] = "/var/log/pi-star";
	$configysf2nxdn['Log']['FileRoot'] = "YSF2NXDN";
	if (!isset($configysf2nxdn['aprs.fi']['Enable'])) { $configysf2nxdn['aprs.fi']['Enable'] = "0"; }
	if (!isset($configysf2nxdn['YSF Network']['WiresXMakeUpper'])) { $configysf2nxdn['YSF Network']['WiresXMakeUpper'] = "1"; }
	if (!isset($configysf2nxdn['YSF Network']['DT1'])) { $configysf2nxdn['YSF Network']['DT1'] = "1,34,97,95,43,3,17,0,0,0"; }
	if (!isset($configysf2nxdn['YSF Network']['DT2'])) { $configysf2nxdn['YSF Network']['DT2'] = "0,0,0,0,108,32,28,32,3,8"; }

	// Add missing options to YSF2P25
	$configysf2p25['YSF Network']['LocalPort'] = $configysfgateway['YSF Network']['YSF2P25Port'];
	$configysf2p25['YSF Network']['DstPort'] = $configysfgateway['YSF Network']['Port'];
	$configysf2p25['YSF Network']['Daemon'] = "1";
	$configysf2p25['YSF Network']['EnableWiresX'] = "1";
	if (!isset($configysf2p25['Enabled']['Enabled'])) { $configysf2p25['Enabled']['Enabled'] = "0"; }
	$configysf2p25['DMR Id Lookup']['File'] = "/usr/local/etc/DMRIds.dat";
	$configysf2p25['P25 Network']['TGListFile'] = "/usr/local/etc/TGList_P25.txt";
	$configysf2p25['Log']['DisplayLevel'] = "0";
	$configysf2p25['Log']['FileLevel'] = "0";
	$configysf2p25['Log']['FilePath'] = "/var/log/pi-star";
	$configysf2p25['Log']['FileRoot'] = "YSF2P25";
	if (isset($configysf2p25['aprs.fi'])) { unset($configysf2p25['aprs.fi']); }
	if (!isset($configysf2p25['YSF Network']['WiresXMakeUpper'])) { $configysf2p25['YSF Network']['WiresXMakeUpper'] = "1"; }
	if (!isset($configysf2p25['YSF Network']['DT1'])) { $configysf2p25['YSF Network']['DT1'] = "1,34,97,95,43,3,17,0,0,0"; }
	if (!isset($configysf2p25['YSF Network']['DT2'])) { $configysf2p25['YSF Network']['DT2'] = "0,0,0,0,108,32,28,32,3,8"; }

	// Defaults for DGIdGateway
	if (isset($configdgidgateway)) {
		$configdgidgateway['General']['LocalPort'] = $configmmdvm['System Fusion Network']['GatewayPort'];
		$configdgidgateway['General']['RptPort'] =  $configmmdvm['System Fusion Network']['LocalPort'];
		$configdgidgateway['Log']['DisplayLevel'] = 1; 
		$configdgidgateway['Log']['FileLevel'] = 1;
		$configdgidgateway['Log']['FilePath'] = "/var/log/pi-star";
		$configdgidgateway['Log']['FileRoot'] = "DGIdGateway";
		$configdgidgateway['Log']['FileRotate'] = 0;
		$configdgidgateway['YSF Network']['Hosts'] = "/usr/local/etc/YSFHosts.txt";
	}

	// Clean up for NXDN Gateway
	if (file_exists('/etc/nxdngateway')) {
		if (isset($confignxdngateway['Network']['HostsFile'])) {
			$confignxdngateway['Network']['HostsFile1'] = $confignxdngateway['Network']['HostsFile'];
			$confignxdngateway['Network']['HostsFile2'] = "/usr/local/etc/NXDNHostsLocal.txt";
			unset($confignxdngateway['Network']['HostsFile']);
			if (!file_exists('/usr/local/etc/NXDNHostsLocal.txt')) { exec('sudo touch /usr/local/etc/NXDNHostsLocal.txt'); }
		}
		$configmmdvm['NXDN Network']['LocalAddress'] = "127.0.0.1";
		$configmmdvm['NXDN Network']['LocalPort'] = "14021";
		$configmmdvm['NXDN Network']['GatewayAddress'] = "127.0.0.1";
		$configmmdvm['NXDN Network']['GatewayPort'] = "14020";
		if(isset($configmmdvm['NXDN']['SelfOnly'])) {
			$nxdnSelfOnlyTmp = $configmmdvm['NXDN']['SelfOnly'];
			unset($configmmdvm['NXDN']['SelfOnly']);
			$configmmdvm['NXDN']['SelfOnly'] = $nxdnSelfOnlyTmp;
		}
		if(isset($configmmdvm['NXDN']['ModeHang'])) {
			$nxdnRfModeHangTmp = $configmmdvm['NXDN']['ModeHang'];
			unset($configmmdvm['NXDN']['ModeHang']);
			$configmmdvm['NXDN']['ModeHang'] = $nxdnRfModeHangTmp;
		}
		if(isset($configmmdvm['NXDN Network']['ModeHang'])) {
			$nxdnNetModeHangTmp = $configmmdvm['NXDN Network']['ModeHang'];
			unset($configmmdvm['NXDN Network']['ModeHang']);
			$configmmdvm['NXDN Network']['ModeHang'] = $nxdnNetModeHangTmp;
		}
		// Add in all the APRS stuff
		if(!isset($confignxdngateway['Info']['Power'])) { $confignxdngateway['Info']['Power'] = "1"; }
		if(!isset($confignxdngateway['Info']['Height'])) { $confignxdngateway['Info']['Height'] = "0"; }
		if(!isset($confignxdngateway['aprs.fi']['Enable'])) { $confignxdngateway['aprs.fi']['Enable'] = "0"; }
		if(!isset($confignxdngateway['aprs.fi']['Server'])) { $confignxdngateway['aprs.fi']['Server'] = "euro.aprs2.net"; }
		if(!isset($confignxdngateway['aprs.fi']['Port'])) { $confignxdngateway['aprs.fi']['Port'] = "14580"; }
		if(!isset($confignxdngateway['aprs.fi']['Password'])) { $confignxdngateway['aprs.fi']['Password'] = "9999"; }
		if(!isset($confignxdngateway['aprs.fi']['Description'])) { $confignxdngateway['aprs.fi']['Description'] = "APRS for NXDN Gateway"; }
		if(!isset($confignxdngateway['aprs.fi']['Suffix'])) { $confignxdngateway['aprs.fi']['Suffix'] = "N"; }
	}

	// Clean up legacy options
	$dmrGatewayVer = exec("DMRGateway -v | awk {'print $3'} | cut -c 1-8");
	if ($dmrGatewayVer > 20170924) {
		unset($configdmrgateway['XLX Network 1']);
		unset($configdmrgateway['XLX Network 2']);
	}

	// Add P25Gateway Options
	$p25GatewayVer = exec("P25Gateway -v | awk {'print $3'} | cut -c 1-8");
	if ($p25GatewayVer > 20200502) {
		if (!isset($configp25gateway['Remote Commands']['Enable'])) { $configp25gateway['Remote Commands']['Enable'] = "1"; }
		if (!isset($configp25gateway['Remote Commands']['Port'])) { $configp25gateway['Remote Commands']['Port'] = "6074"; }
	}
	if ($p25GatewayVer > 20210201) {
		if (isset($configp25gateway['General']['Announcements'])) { unset($configp25gateway['General']['Announcements']); }
		if (!isset($configp25gateway['Log']['DisplayLevel'])) { $configp25gateway['Log']['DisplayLevel'] = "1"; }
		if (!isset($configp25gateway['Log']['FileLevel'])) { $configp25gateway['Log']['FileLevel'] = "1"; }
		if (!isset($configp25gateway['Network']['P252DMRAddress'])) { $configp25gateway['Network']['P252DMRAddress'] = "127.0.0.1"; }
		if (!isset($configp25gateway['Network']['P252DMRPort'])) { $configp25gateway['Network']['P252DMRPort'] = "42012"; }
		if (isset($configp25gateway['Network']['Startup'])) {
			$configp25gateway['Network']['Static'] = $configp25gateway['Network']['Startup'];
			unset($configp25gateway['Network']['Startup']);
		}
	}

	// Add NXDNGateway Options
	$nxdnGatewayVer = exec("NXDNGateway -v | awk {'print $3'} | cut -c 1-8");
	if ($nxdnGatewayVer > 20200502) {
		if (!isset($confignxdngateway['Remote Commands']['Enable'])) { $confignxdngateway['Remote Commands']['Enable'] = "1"; }
		if (!isset($confignxdngateway['Remote Commands']['Port'])) { $confignxdngateway['Remote Commands']['Port'] = "6075"; }
	}
	if ($nxdnGatewayVer > 20210131) {
		if (isset($confignxdngateway['aprs.fi'])) { unset($confignxdngateway['aprs.fi']); }
		if (isset($confignxdngateway['Mobile GPS'])) { unset($confignxdngateway['Mobile GPS']); }
		if (!isset($confignxdngateway['General']['RptProtocol'])) { $confignxdngateway['General']['RptProtocol'] = "Icom"; }
		if (!isset($confignxdngateway['Log']['DisplayLevel'])) { $confignxdngateway['Log']['DisplayLevel'] = "1"; }
		if (!isset($confignxdngateway['Log']['FileLevel'])) { $confignxdngateway['Log']['FileLevel'] = "1"; }
		if (!isset($confignxdngateway['APRS']['Enable'])) { $confignxdngateway['APRS']['Enable'] = "1"; }
		if (!isset($confignxdngateway['APRS']['Address'])) { $confignxdngateway['APRS']['Address'] = "127.0.0.1"; }
		if (!isset($confignxdngateway['APRS']['Port'])) { $confignxdngateway['APRS']['Port'] = "8673"; }
		if (!isset($confignxdngateway['APRS']['Suffix'])) { $confignxdngateway['APRS']['Suffix'] = "N"; }
		if (!isset($confignxdngateway['APRS']['Description'])) { $confignxdngateway['APRS']['Description'] = $configysfgateway['Info']['Name']."_".$configysfgateway['General']['Suffix']; }
		if (isset($confignxdngateway['APRS']['Description'])) { $confignxdngateway['APRS']['Description'] = $configysfgateway['Info']['Name']."_".$configysfgateway['General']['Suffix']; }
		if (!isset($confignxdngateway['GPSD']['Enable'])) { $confignxdngateway['GPSD']['Enable'] = "0"; }
		if (!isset($confignxdngateway['GPSD']['Address'])) { $confignxdngateway['GPSD']['Address'] = "127.0.0.1"; }
		if (!isset($confignxdngateway['GPSD']['Port'])) { $confignxdngateway['GPSD']['Port'] = "2947"; }
		if (isset($confignxdngateway['Network']['Startup'])) {
			$confignxdngateway['Network']['Static'] = $confignxdngateway['Network']['Startup'];
			unset($confignxdngateway['Network']['Startup']);
		}
	}

	// Migrate YSFGateway Config
	$ysfGatewayVer = exec("YSFGateway -v | awk {'print $3'} | cut -c 1-8");
	if ($ysfGatewayVer > 20180303) {
		if (isset($configysfgateway['Network']['Startup'])) { $ysfTmpStartup = $configysfgateway['Network']['Startup']; }
		if (!isset($configysfgateway['aprs.fi']['Enable'])) { $configysfgateway['aprs.fi']['Enable'] = "1"; }
		//unset($configysfgateway['Network']);
		if (isset($ysfTmpStartup)) { $configysfgateway['Network']['Startup'] = $ysfTmpStartup; }
		if (!isset($configysfgateway['Network']['InactivityTimeout'])) { $configysfgateway['Network']['InactivityTimeout'] = "0"; }
		if (!isset($configysfgateway['Network']['Revert'])) { $configysfgateway['Network']['Revert'] = "0"; }
		$configysfgateway['Network']['Debug'] = "0";
		$configysfgateway['YSF Network']['Enable'] = "1";
		$configysfgateway['YSF Network']['Port'] = "42000";
		$configysfgateway['YSF Network']['Hosts'] = "/usr/local/etc/YSFHosts.txt";
		$configysfgateway['YSF Network']['ReloadTime'] = "60";
		$configysfgateway['YSF Network']['ParrotAddress'] = "127.0.0.1";
		$configysfgateway['YSF Network']['ParrotPort'] = "42012";
		$configysfgateway['YSF Network']['YSF2DMRAddress'] = "127.0.0.1";
		$configysfgateway['YSF Network']['YSF2DMRPort'] = "42013";
		$configysfgateway['YSF Network']['YSF2NXDNAddress'] = "127.0.0.1";
		$configysfgateway['YSF Network']['YSF2NXDNPort'] = "42014";
		$configysfgateway['YSF Network']['YSF2P25Address'] = "127.0.0.1";
		$configysfgateway['YSF Network']['YSF2P25Port'] = "42015";
		$configysfgateway['FCS Network']['Enable'] = "1";
		$configysfgateway['FCS Network']['Port'] = "42001";
		$configysfgateway['FCS Network']['Rooms'] = "/usr/local/etc/FCSHosts.txt";
	}
	if ($ysfGatewayVer > 20200502) {
		if (!isset($configysfgateway['Remote Commands']['Enable'])) { $configysfgateway['Remote Commands']['Enable'] = "1"; }
		if (!isset($configysfgateway['Remote Commands']['Port'])) { $configysfgateway['Remote Commands']['Port'] = "6073"; }
	}
	if ($ysfGatewayVer > 20200907) {
		if (!isset($configysfgateway['General']['Debug'])) { $configysfgateway['General']['Debug'] = "0"; }
		if (!isset($configysfgateway['GPSD']['Enable'])) { $configysfgateway['GPSD']['Enable'] = "0"; }
		if (!isset($configysfgateway['GPSD']['Address'])) { $configysfgateway['GPSD']['Address'] = "127.0.0.1"; }
		if (!isset($configysfgateway['GPSD']['Port'])) { $configysfgateway['GPSD']['Port'] = "2947"; }
		if (!isset($configysfgateway['APRS']['Enable'])) { $configysfgateway['APRS']['Enable'] = "0"; }
		if (!isset($configysfgateway['APRS']['Address'])) { $configysfgateway['APRS']['Address'] = "127.0.0.1"; }
		if (!isset($configysfgateway['APRS']['Port'])) { $configysfgateway['APRS']['Port'] = "8673"; }
		if (!isset($configysfgateway['APRS']['Description'])) { $configysfgateway['APRS']['Description'] = $configysfgateway['Info']['Name']."_".$configysfgateway['General']['Suffix']; }
		if (isset($configysfgateway['APRS']['Description'])) { $configysfgateway['APRS']['Description'] = $configysfgateway['Info']['Name']."_".$configysfgateway['General']['Suffix']; }
		if (!isset($configysfgateway['APRS']['Suffix'])) { $configysfgateway['APRS']['Suffix'] = "Y"; }
		if (isset($configysfgateway['Mobile GPS'])) { unset($configysfgateway['Mobile GPS']); }
		if (isset($configysfgateway['aprs.fi'])) { unset($configysfgateway['aprs.fi']); }
	}
	if ($ysfGatewayVer > 20210130) {
		if (isset($configysfgateway['APRS']['Enable'])) { $configysfgateway['APRS']['Enable'] = "1"; }
	}

	// Add the DAPNet Config
	if (!isset($configdapnetgw['General']['Callsign'])) { $configdapnetgw['General']['Callsign'] = "M1ABC"; }
	if (!isset($configdapnetgw['General']['RptAddress'])) { $configdapnetgw['General']['RptAddress'] = "127.0.0.1"; }
	if (!isset($configdapnetgw['General']['RptPort'])) { $configdapnetgw['General']['RptPort'] = "3800"; }
	if (!isset($configdapnetgw['General']['LocalAddress'])) { $configdapnetgw['General']['LocalAddress'] = "127.0.0.1"; }
	if (!isset($configdapnetgw['General']['LocalPort'])) { $configdapnetgw['General']['LocalPort'] = "4800"; }
	if (!isset($configdapnetgw['General']['Daemon'])) { $configdapnetgw['General']['Daemon'] = "0"; }
	if (!isset($configdapnetgw['Log']['DisplayLevel'])) { $configdapnetgw['Log']['DisplayLevel'] = "0"; }
	if (!isset($configdapnetgw['Log']['FileLevel'])) { $configdapnetgw['Log']['FileLevel'] = "2"; }
	if (!isset($configdapnetgw['Log']['FilePath'])) { $configdapnetgw['Log']['FilePath'] = "/var/log/pi-star"; }
	if (!isset($configdapnetgw['Log']['FileRoot'])) { $configdapnetgw['Log']['FileRoot'] = "DAPNETGateway"; }
	if (!isset($configdapnetgw['DAPNET']['Address'])) { $configdapnetgw['DAPNET']['Address'] = "dapnet.afu.rwth-aachen.de"; }
	if (!isset($configdapnetgw['DAPNET']['Port'])) { $configdapnetgw['DAPNET']['Port'] = "43434"; }
	if (!isset($configdapnetgw['DAPNET']['AuthKey'])) { $configdapnetgw['DAPNET']['AuthKey'] = "TOPSECRET"; }
	if (!isset($configdapnetgw['DAPNET']['SuppressTimeWhenBusy'])) { $configdapnetgw['DAPNET']['SuppressTimeWhenBusy'] = "1"; }
	if (!isset($configdapnetgw['DAPNET']['Debug'])) { $configdapnetgw['DAPNET']['Debug'] = "0"; }
	if (!isset($configmmdvm['POCSAG']['Enable'])) { $configmmdvm['POCSAG']['Enable'] = "0"; }
	if (!isset($configmmdvm['POCSAG']['Frequency'])) { $configmmdvm['POCSAG']['Frequency'] = "439987500"; }
	if (!isset($configmmdvm['POCSAG Network']['Enable'])) { $configmmdvm['POCSAG Network']['Enable'] = "0"; }
	if (!isset($configmmdvm['POCSAG Network']['LocalAddress'])) { $configmmdvm['POCSAG Network']['LocalAddress'] = "127.0.0.1"; }
	if (!isset($configmmdvm['POCSAG Network']['LocalPort'])) { $configmmdvm['POCSAG Network']['LocalPort'] = "3800"; }
	if (!isset($configmmdvm['POCSAG Network']['GatewayAddress'])) { $configmmdvm['POCSAG Network']['GatewayAddress'] = "127.0.0.1"; }
	if (!isset($configmmdvm['POCSAG Network']['GatewayPort'])) { $configmmdvm['POCSAG Network']['GatewayPort'] = "4800"; }
	if (!isset($configmmdvm['POCSAG Network']['ModeHang'])) { $configmmdvm['POCSAG Network']['ModeHang'] = "5"; }
	if (!isset($configmmdvm['POCSAG Network']['Debug'])) { $configmmdvm['POCSAG Network']['Debug'] = "0"; }
	if (isset($configmmdvm['POCSAG Network']['ModeHang'])) { $configmmdvm['POCSAG Network']['ModeHang'] = "5"; }

	// Fix Demon mode on M17Gateway
	if (isset($configm17gateway['General']['Daemon'])) { $configm17gateway['General']['Daemon'] = "1"; }

	// MobileGPS Setup
	if (file_exists('/etc/mobilegps')) {
		if (empty($_POST['mobilegps_enable']) != TRUE ) {
			// Add missing lines to MMDVMHost Config
			if (!isset($configmmdvm['Mobile GPS']['Enable'])) { $configmmdvm['Mobile GPS']['Enable'] = "0"; }
			if (!isset($configmmdvm['Mobile GPS']['Address'])) { $configmmdvm['Mobile GPS']['Address'] = "127.0.0.1"; }
			if (!isset($configmmdvm['Mobile GPS']['Port'])) { $configmmdvm['Mobile GPS']['Port'] = "7834"; }
			// Add missing lines to YSFGateway Config
			if (!isset($configysfgateway['GPSD'])) {
				if (!isset($configysfgateway['Mobile GPS']['Enable'])) { $configysfgateway['Mobile GPS']['Enable'] = "0"; }
				if (!isset($configysfgateway['Mobile GPS']['Address'])) { $configysfgateway['Mobile GPS']['Address'] = "127.0.0.1"; }
				if (!isset($configysfgateway['Mobile GPS']['Port'])) { $configysfgateway['Mobile GPS']['Port'] = "7834"; }
			}
			// Add missing lines to NXDNGateway Config
			//if (!isset($confignxdngateway['Mobile GPS']['Enable'])) { $confignxdngateway['Mobile GPS']['Enable'] = "0"; }
			//if (!isset($confignxdngateway['Mobile GPS']['Address'])) { $confignxdngateway['Mobile GPS']['Address'] = "127.0.0.1"; }
			//if (!isset($confignxdngateway['Mobile GPS']['Port'])) { $confignxdngateway['Mobile GPS']['Port'] = "7834"; }

			// Clean up MobilGPS config
			system('sudo sed -i "/Daemon=/c\\Daemon=0" /etc/mobilegps');
			system('sudo sed -i "/Debug=/c\\Debug=0" /etc/mobilegps');
			system('sudo sed -i "/DisplayLevel=/c\\DisplayLevel=0" /etc/mobilegps');
			system('sudo sed -i "/FileLevel=/c\\FileLevel=1" /etc/mobilegps');
			system('sudo sed -i "/FilePath=/c\\FilePath=/var/log/pi-star" /etc/mobilegps');

			// Enable or Disable MobileGPS
			if (escapeshellcmd($_POST['mobilegps_enable']) == 'ON' )  {
				$configmmdvm['Mobile GPS']['Enable'] = "1";
				if (isset($configysfgateway['Mobile GPS']['Enable'])) { $configysfgateway['Mobile GPS']['Enable'] = "1"; }
				if (isset($confignxdngateway['Mobile GPS']['Enable'])) { $confignxdngateway['Mobile GPS']['Enable'] = "1"; }
				system('sudo sed -i "/Enabled=/c\\Enabled=1" /etc/mobilegps');
			} else {
				$configmmdvm['Mobile GPS']['Enable'] = "0";
				if (isset($configysfgateway['Mobile GPS']['Enable'])) { $configysfgateway['Mobile GPS']['Enable'] = "0"; }
				if (isset($confignxdngateway['Mobile GPS']['Enable'])) { $confignxdngateway['Mobile GPS']['Enable'] = "0"; }
				system('sudo sed -i "/Enabled=/c\\Enabled=0" /etc/mobilegps');
			}
		}
		if (empty($_POST['mobilegps_port']) != TRUE ) {
			$newMobileGPSport = preg_replace('/[^a-z0-9]/i', '', $_POST['mobilegps_port']);
			system('sudo sed -i "/Port=\/dev/c\\Port=/dev/'.$newMobileGPSport.'" /etc/mobilegps');
		}
		if (empty($_POST['mobilegps_speed']) != TRUE ) {
			$newMobileGPSspeed = preg_replace('/[^0-9]/', '', $_POST['mobilegps_speed']);
			system('sudo sed -i "/Speed=/c\\Speed='.$newMobileGPSspeed.'" /etc/mobilegps');
		}
	}

	// Create the hostfiles.nodextra file if required
	if (empty($_POST['confHostFilesNoDExtra']) != TRUE ) {
		if (escapeshellcmd($_POST['confHostFilesNoDExtra']) == 'ON' )  {
			if (!file_exists('/etc/hostfiles.nodextra')) { system('sudo touch /etc/hostfiles.nodextra'); }
		}
		if (escapeshellcmd($_POST['confHostFilesNoDExtra']) == 'OFF' )  {
			if (file_exists('/etc/hostfiles.nodextra')) { system('sudo rm -rf /etc/hostfiles.nodextra'); }
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

	// NXDNGateway config file wrangling
	$nxdngwContent = "";
        foreach($confignxdngateway as $nxdngwSection=>$nxdngwValues) {
                // UnBreak special cases
                $nxdngwSection = str_replace("_", " ", $nxdngwSection);
                $nxdngwContent .= "[".$nxdngwSection."]\n";
                // append the values
                foreach($nxdngwValues as $nxdngwKey=>$nxdngwValue) {
                        $nxdngwContent .= $nxdngwKey."=".$nxdngwValue."\n";
                        }
                        $nxdngwContent .= "\n";
                }

        if (!$handleNXDNGWconfig = fopen('/tmp/kXKwkDKy793HF5.tmp', 'w')) {
                return false;
        }

	if (!is_writable('/tmp/kXKwkDKy793HF5.tmp')) {
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
	        $success = fwrite($handleNXDNGWconfig, $nxdngwContent);
	        fclose($handleNXDNGWconfig);
		if ( (intval(exec('cat /tmp/kXKwkDKy793HF5.tmp | wc -l')) > 30 ) && (file_exists('/etc/nxdngateway')) ) {
			exec('sudo mv /tmp/kXKwkDKy793HF5.tmp /etc/nxdngateway');		// Move the file back
			exec('sudo chmod 644 /etc/nxdngateway');				// Set the correct runtime permissions
			exec('sudo chown root:root /etc/nxdngateway');				// Set the owner
		}
	}

	// P25Gateway config file wrangling
	$p25gwContent = "";
        foreach($configp25gateway as $p25gwSection=>$p25gwValues) {
                // UnBreak special cases
                $p25gwSection = str_replace("_", " ", $p25gwSection);
                $p25gwContent .= "[".$p25gwSection."]\n";
                // append the values
                foreach($p25gwValues as $p25gwKey=>$p25gwValue) {
                        $p25gwContent .= $p25gwKey."=".$p25gwValue."\n";
                        }
                        $p25gwContent .= "\n";
                }

        if (!$handleP25GWconfig = fopen('/tmp/sJSySkheSgrelJX.tmp', 'w')) {
                return false;
        }

	if (!is_writable('/tmp/sJSySkheSgrelJX.tmp')) {
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
	        $success = fwrite($handleP25GWconfig, $p25gwContent);
	        fclose($handleP25GWconfig);
		if ( (intval(exec('cat /tmp/sJSySkheSgrelJX.tmp | wc -l')) > 30 ) && (file_exists('/etc/p25gateway')) ) {
			exec('sudo mv /tmp/sJSySkheSgrelJX.tmp /etc/p25gateway');		// Move the file back
			exec('sudo chmod 644 /etc/p25gateway');					// Set the correct runtime permissions
			exec('sudo chown root:root /etc/p25gateway');				// Set the owner
		}
	}

	// M17Gateway config file wrangling
	$m17gwContent = "";
        foreach($configm17gateway as $m17gwSection=>$m17gwValues) {
                // UnBreak special cases
                $m17gwSection = str_replace("_", " ", $m17gwSection);
                $m17gwContent .= "[".$m17gwSection."]\n";
                // append the values
                foreach($m17gwValues as $m17gwKey=>$m17gwValue) {
                        $m17gwContent .= $m17gwKey."=".$m17gwValue."\n";
                        }
                        $m17gwContent .= "\n";
                }

        if (!$handleM17GWconfig = fopen('/tmp/spNcSdRUEmySTo9.tmp', 'w')) {
                return false;
        }

	if (!is_writable('/tmp/spNcSdRUEmySTo9.tmp')) {
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
	        $success = fwrite($handleM17GWconfig, $m17gwContent);
	        fclose($handleM17GWconfig);
		if ( (intval(exec('cat /tmp/spNcSdRUEmySTo9.tmp | wc -l')) > 30 ) && (file_exists('/etc/m17gateway')) ) {
			exec('sudo mv /tmp/spNcSdRUEmySTo9.tmp /etc/m17gateway');		// Move the file back
			exec('sudo chmod 644 /etc/m17gateway');					// Set the correct runtime permissions
			exec('sudo chown root:root /etc/m17gateway');				// Set the owner
		}
	}

        // ysf2dmr config file wrangling
        $ysf2dmrContent = "";
        foreach($configysf2dmr as $ysf2dmrSection=>$ysf2dmrValues) {
                // UnBreak special cases
                $ysf2dmrSection = str_replace("_", " ", $ysf2dmrSection);
                $ysf2dmrContent .= "[".$ysf2dmrSection."]\n";
                // append the values
                foreach($ysf2dmrValues as $ysf2dmrKey=>$ysf2dmrValue) {
                        $ysf2dmrContent .= $ysf2dmrKey."=".$ysf2dmrValue."\n";
                        }
                        $ysf2dmrContent .= "\n";
                }

        if (!$handleYSF2DMRconfig = fopen('/tmp/dsWGR34tHRrSFFGA.tmp', 'w')) {
                return false;
        }

        if (!is_writable('/tmp/dsWGR34tHRrSFFGA.tmp')) {
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
                $success = fwrite($handleYSF2DMRconfig, $ysf2dmrContent);
                fclose($handleYSF2DMRconfig);
                if (intval(exec('cat /tmp/dsWGR34tHRrSFFGA.tmp | wc -l')) > 35 ) {
                        exec('sudo mv /tmp/dsWGR34tHRrSFFGA.tmp /etc/ysf2dmr');                 // Move the file back
                        exec('sudo chmod 644 /etc/ysf2dmr');                                    // Set the correct runtime permissions
                        exec('sudo chown root:root /etc/ysf2dmr');                              // Set the owner
                }
        }

	// ysf2nxdn config file wrangling
        $ysf2nxdnContent = "";
        foreach($configysf2nxdn as $ysf2nxdnSection=>$ysf2nxdnValues) {
                // UnBreak special cases
                $ysf2nxdnSection = str_replace("_", " ", $ysf2nxdnSection);
                $ysf2nxdnContent .= "[".$ysf2nxdnSection."]\n";
                // append the values
                foreach($ysf2nxdnValues as $ysf2nxdnKey=>$ysf2nxdnValue) {
                        $ysf2nxdnContent .= $ysf2nxdnKey."=".$ysf2nxdnValue."\n";
                        }
                        $ysf2nxdnContent .= "\n";
                }
        if (!$handleYSF2NXDNconfig = fopen('/tmp/dsWGR34tHRrSFFGb.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/dsWGR34tHRrSFFGb.tmp')) {
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
                $success = fwrite($handleYSF2NXDNconfig, $ysf2nxdnContent);
                fclose($handleYSF2NXDNconfig);
                if (intval(exec('cat /tmp/dsWGR34tHRrSFFGb.tmp | wc -l')) > 35 ) {
                        exec('sudo mv /tmp/dsWGR34tHRrSFFGb.tmp /etc/ysf2nxdn');                 // Move the file back
                        exec('sudo chmod 644 /etc/ysf2nxdn');                                    // Set the correct runtime permissions
                        exec('sudo chown root:root /etc/ysf2nxdn');                              // Set the owner
                }
        }

	// ysf2p25 config file wrangling
        $ysf2p25Content = "";
        foreach($configysf2p25 as $ysf2p25Section=>$ysf2p25Values) {
                // UnBreak special cases
                $ysf2p25Section = str_replace("_", " ", $ysf2p25Section);
                $ysf2p25Content .= "[".$ysf2p25Section."]\n";
                // append the values
                foreach($ysf2p25Values as $ysf2p25Key=>$ysf2p25Value) {
                        $ysf2p25Content .= $ysf2p25Key."=".$ysf2p25Value."\n";
                        }
                        $ysf2p25Content .= "\n";
                }
        if (!$handleYSF2P25config = fopen('/tmp/dsWGR34tHRrSFFGc.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/dsWGR34tHRrSFFGc.tmp')) {
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
                $success = fwrite($handleYSF2P25config, $ysf2p25Content);
                fclose($handleYSF2P25config);
                if (intval(exec('cat /tmp/dsWGR34tHRrSFFGc.tmp | wc -l')) > 25 ) {
                        exec('sudo mv /tmp/dsWGR34tHRrSFFGc.tmp /etc/ysf2p25');                 // Move the file back
                        exec('sudo chmod 644 /etc/ysf2p25');                                    // Set the correct runtime permissions
                        exec('sudo chown root:root /etc/ysf2p25');                              // Set the owner
                }
        }

	// dgidgateway config file wrangling
	if (isset($configdgidgateway)) {
		$dgidgatewayContent = "";
		foreach($configdgidgateway as $dgidgatewaySection=>$dgidgatewayValues) {
			// UnBreak special cases
			$dgidgatewaySection = str_replace("_", " ", $dgidgatewaySection);
			$dgidgatewayContent .= "[".$dgidgatewaySection."]\n";
			// append the values
			foreach($dgidgatewayValues as $dgidgatewayKey=>$dgidgatewayValue) {
				$dgidgatewayContent .= $dgidgatewayKey."=".$dgidgatewayValue."\n";
				}
				$dgidgatewayContent .= "\n";
			}
		if (!$handleDGIdGatewayConfig = fopen('/tmp/cu0G4tG3CA45Z9B.tmp', 'w')) {
			return false;
		}
		if (!is_writable('/tmp/cu0G4tG3CA45Z9B.tmp')) {
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
			$success = fwrite($handleDGIdGatewayConfig, $dgidgatewayContent);
			fclose($handleDGIdGatewayConfig);
			if (intval(exec('cat /tmp/cu0G4tG3CA45Z9B.tmp | wc -l')) > 25 ) {
				exec('sudo mv /tmp/cu0G4tG3CA45Z9B.tmp /etc/dgidgateway');		// Move the file back
				exec('sudo chmod 644 /etc/dgidgateway');				// Set the correct runtime permissions
				exec('sudo chown root:root /etc/dgidgateway');				// Set the owner
			}
		}
	}

	// dmr2ysf config file wrangling
        $dmr2ysfContent = "";
        foreach($configdmr2ysf as $dmr2ysfSection=>$dmr2ysfValues) {
                // UnBreak special cases
                $dmr2ysfSection = str_replace("_", " ", $dmr2ysfSection);
                $dmr2ysfContent .= "[".$dmr2ysfSection."]\n";
                // append the values
                foreach($dmr2ysfValues as $dmr2ysfKey=>$dmr2ysfValue) {
                        $dmr2ysfContent .= $dmr2ysfKey."=".$dmr2ysfValue."\n";
                        }
                        $dmr2ysfContent .= "\n";
                }
        if (!$handleDMR2YSFconfig = fopen('/tmp/dhJSgdy7755HGc.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/dhJSgdy7755HGc.tmp')) {
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
                $success = fwrite($handleDMR2YSFconfig, $dmr2ysfContent);
                fclose($handleDMR2YSFconfig);
                if (intval(exec('cat /tmp/dhJSgdy7755HGc.tmp | wc -l')) > 25 ) {
                        exec('sudo mv /tmp/dhJSgdy7755HGc.tmp /etc/dmr2ysf');		// Move the file back
                        exec('sudo chmod 644 /etc/dmr2ysf');				// Set the correct runtime permissions
                        exec('sudo chown root:root /etc/dmr2ysf');			// Set the owner
                }
        }

	// dmr2nxdn config file wrangling
        $dmr2nxdnContent = "";
        foreach($configdmr2nxdn as $dmr2nxdnSection=>$dmr2nxdnValues) {
                // UnBreak special cases
                $dmr2nxdnSection = str_replace("_", " ", $dmr2nxdnSection);
                $dmr2nxdnContent .= "[".$dmr2nxdnSection."]\n";
                // append the values
                foreach($dmr2nxdnValues as $dmr2nxdnKey=>$dmr2nxdnValue) {
                        $dmr2nxdnContent .= $dmr2nxdnKey."=".$dmr2nxdnValue."\n";
                        }
                        $dmr2nxdnContent .= "\n";
                }
        if (!$handleDMR2NXDNconfig = fopen('/tmp/nthfheS55HGc.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/nthfheS55HGc.tmp')) {
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
                $success = fwrite($handleDMR2NXDNconfig, $dmr2nxdnContent);
                fclose($handleDMR2NXDNconfig);
                if (intval(exec('cat /tmp/nthfheS55HGc.tmp | wc -l')) > 25 ) {
                        exec('sudo mv /tmp/nthfheS55HGc.tmp /etc/dmr2nxdn');		// Move the file back
                        exec('sudo chmod 644 /etc/dmr2nxdn');				// Set the correct runtime permissions
                        exec('sudo chown root:root /etc/dmr2nxdn');			// Set the owner
                }
        }

	// DAPNet Gateway Config file wragling
	$dapnetContent = "";
        foreach($configdapnetgw as $dapnetSection=>$dapnetValues) {
                // UnBreak special cases
                $dapnetSection = str_replace("_", " ", $dapnetSection);
                $dapnetContent .= "[".$dapnetSection."]\n";
                // append the values
                foreach($dapnetValues as $dapnetKey=>$dapnetValue) {
                        $dapnetContent .= $dapnetKey."=".$dapnetValue."\n";
                        }
                        $dapnetContent .= "\n";
                }
        if (!$handledapnetconfig = fopen('/tmp/lsHWie734HS.tmp', 'w')) {
                return false;
        }
        if (!is_writable('/tmp/lsHWie734HS.tmp')) {
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
                $success = fwrite($handledapnetconfig, $dapnetContent);
                fclose($handledapnetconfig);
                if (intval(exec('cat /tmp/lsHWie734HS.tmp | wc -l')) > 19 ) {
                        exec('sudo mv /tmp/lsHWie734HS.tmp /etc/dapnetgateway');		// Move the file back
                        exec('sudo chmod 644 /etc/dapnetgateway');				// Set the correct runtime permissions
                        exec('sudo chown root:root /etc/dapnetgateway');			// Set the owner
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
			if ($modemKey == "Password") { $configModemContent .= $modemKey."=".'"'.str_replace('"', "", $modemValue).'"'."\n"; }
			else { $configModemContent .= $modemKey."=".$modemValue."\n"; }
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
	system('sudo systemctl start ysf2dmr.service > /dev/null 2>/dev/null &');		// YSF2DMR
	system('sudo systemctl start ysf2nxdn.service > /dev/null 2>/dev/null &');		// YSF2NXDN
	system('sudo systemctl start ysf2p25.service > /dev/null 2>/dev/null &');		// YSF2P25
	system('sudo systemctl start nxdn2dmr.service > /dev/null 2>/dev/null &');		// NXDN2DMR
	system('sudo systemctl start ysfgateway.service > /dev/null 2>/dev/null &');		// YSFGateway
	system('sudo systemctl start ysfparrot.service > /dev/null 2>/dev/null &');		// YSFParrot
	system('sudo systemctl start p25gateway.service > /dev/null 2>/dev/null &');		// P25Gateway
	system('sudo systemctl start p25parrot.service > /dev/null 2>/dev/null &');		// P25Parrot
	system('sudo systemctl start nxdngateway.service > /dev/null 2>/dev/null &');		// NXDNGateway
	system('sudo systemctl start nxdnparrot.service > /dev/null 2>/dev/null &');		// NXDNParrot
	system('sudo systemctl start m17gateway.service > /dev/null 2>/dev/null &');		// M17Gateway
	system('sudo systemctl start dmr2ysf.service > /dev/null 2>/dev/null &');		// DMR2YSF
	system('sudo systemctl start dmr2nxdn.service > /dev/null 2>/dev/null &');		// DMR2NXDN
	system('sudo systemctl start dmrgateway.service > /dev/null 2>/dev/null &');		// DMRGateway
	system('sudo systemctl start dapnetgateway.service > /dev/null 2>/dev/null &');		// DAPNetGateway
	system('sudo systemctl start aprsgateway.service > /dev/null 2>/dev/null &');		// APRSGateway

	// Set the system timezone
	if (empty($_POST['systemTimezone']) != TRUE ) {
		$rollTimeZone = 'sudo timedatectl set-timezone '.escapeshellcmd($_POST['systemTimezone']);
		system($rollTimeZone);
		$rollTimeZoneConfig = 'sudo sed -i "/date_default_timezone_set/c\\date_default_timezone_set(\''.escapeshellcmd($_POST['systemTimezone']).'\')\;" /var/www/dashboard/config/config.php';
		system($rollTimeZoneConfig);
	}

	// Start Cron (occasionally remounts root as RO - would be bad if it did this at the wrong time....)
	system('sudo systemctl start cron.service > /dev/null 2>/dev/null &');			//Cron

	unset($_POST);
	echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},7500);</script>';

	// Make the root filesystem read-only
	system('sudo sync && sudo sync && sudo sync && sudo mount -o remount,ro /');

else:
	// Output the HTML Form here
	if ((file_exists('/etc/dstar-radio.mmdvmhost') || file_exists('/etc/dstar-radio.dstarrepeater')) && !$configModem['Modem']['Hardware']) { echo "<script type\"text/javascript\">\n\talert(\"WARNING:\\nThe Modem selection section has been updated,\\nPlease re-select your modem from the list.\")\n</script>\n"; }
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false) {
		$toggleDMRCheckboxCr			= 'onclick="toggleDMRCheckbox()"';
		$toggleDSTARCheckboxCr			= 'onclick="toggleDSTARCheckbox()"';
		$toggleYSFCheckboxCr			= 'onclick="toggleYSFCheckbox()"';
		$toggleP25CheckboxCr			= 'onclick="toggleP25Checkbox()"';
		$toggleNXDNCheckboxCr			= 'onclick="toggleNXDNCheckbox()"';
		$toggleM17CheckboxCr			= 'onclick="toggleM17Checkbox()"';
		$toggleYSF2DMRCheckboxCr		= 'onclick="toggleYSF2DMRCheckbox()"';
		$toggleYSF2NXDNCheckboxCr		= 'onclick="toggleYSF2NXDNCheckbox()"';
		$toggleYSF2P25CheckboxCr		= 'onclick="toggleYSF2P25Checkbox()"';
		$toggleDMR2YSFCheckboxCr		= 'onclick="toggleDMR2YSFCheckbox()"';
		$toggleDMR2NXDNCheckboxCr		= 'onclick="toggleDMR2NXDNCheckbox()"';
		$togglePOCSAGCheckboxCr			= 'onclick="togglePOCSAGCheckbox()"';
		$toggleAPRSGatewayCheckboxCr		= 'onclick="toggleAPRSGatewayCheckbox()"';
		$toggleDmrGatewayNet1EnCheckboxCr	= 'onclick="toggleDmrGatewayNet1EnCheckbox()"';
		$toggleDmrGatewayNet2EnCheckboxCr	= 'onclick="toggleDmrGatewayNet2EnCheckbox()"';
		$toggleDmrGatewayXlxEnCheckboxCr	= 'onclick="toggleDmrGatewayXlxEnCheckbox()"';
		$toggleDmrEmbeddedLCOnlyCr		= 'onclick="toggleDmrEmbeddedLCOnly()"';
		$toggleDmrDumpTADataCr			= 'onclick="toggleDmrDumpTAData()"';
		$toggleHostFilesYSFUpperCr		= 'onclick="toggleHostFilesYSFUpper()"';
		$toggleWiresXCommandPassthroughCr	= 'onclick="toggleWiresXCommandPassthrough()"';
		$toggleDstarTimeAnnounceCr		= 'onclick="toggleDstarTimeAnnounce()"';
		$toggleDstarDplusHostfilesCr		= 'onclick="toggleDstarDplusHostfiles()"';
		$toggleMobilegps_enableCr		= 'onclick="toggleMobilegps_enable()"';
		$toggleircddbEnabledCr			= 'onclick="toggleircddbEnabled()"';
	} else {
		$toggleDMRCheckboxCr			= "";
		$toggleDSTARCheckboxCr			= "";
		$toggleYSFCheckboxCr			= "";
		$toggleP25CheckboxCr			= "";
		$toggleNXDNCheckboxCr			= "";
		$toggleM17CheckboxCr			= "";
		$toggleYSF2DMRCheckboxCr		= "";
		$toggleYSF2NXDNCheckboxCr		= "";
		$toggleYSF2P25CheckboxCr		= "";
		$toggleDMR2YSFCheckboxCr		= "";
		$toggleDMR2NXDNCheckboxCr		= "";
		$togglePOCSAGCheckboxCr			= "";
		$toggleAPRSGatewayCheckboxCr		= "";
		$toggleDmrGatewayNet1EnCheckboxCr	= "";
		$toggleDmrGatewayNet2EnCheckboxCr	= "";
		$toggleDmrGatewayXlxEnCheckboxCr	= "";
		$toggleDmrEmbeddedLCOnlyCr		= "";
		$toggleDmrDumpTADataCr			= "";
		$toggleHostFilesYSFUpperCr		= "";
		$toggleWiresXCommandPassthroughCr	= "";
		$toggleDstarTimeAnnounceCr		= "";
		$toggleDstarDplusHostfilesCr		= "";
		$toggleMobilegps_enableCr		= "";
		$toggleircddbEnabledCr			= "";
	}
?>
<form id="factoryReset" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<div><input type="hidden" name="factoryReset" value="1" /></div>
</form>

<form id="config" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<h2><?php echo $lang['control_software'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from<br />the configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['controller_software'];?>:<span><b>Radio Control Software</b>Choose the software used to control the DV Radio Module. Please note that DV Mega hardware will require a firmware upgrade.</span></a></td>
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
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['controller_mode'];?>:<span><b>TRX Mode</b>Choose the mode type Simplex node or Duplex repeater.</span></a></td>
    <?php
	if ($configmmdvm['Info']['RXFrequency'] === $configmmdvm['Info']['TXFrequency']) {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"trxMode\" value=\"SIMPLEX\" checked=\"checked\" />Simplex Node <input type=\"radio\" name=\"trxMode\" value=\"DUPLEX\" />Duplex Repeater (or Half-Duplex on Hotspots)</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"trxMode\" value=\"SIMPLEX\" />Simplex Node <input type=\"radio\" name=\"trxMode\" value=\"DUPLEX\" checked=\"checked\" />Duplex Repeater (or Half-Duplex on Hotspots)</td>\n";
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
    <input type="hidden" name="MMDVMModeNXDN" value="OFF" />
    <input type="hidden" name="MMDVMModeM17" value="OFF" />
    <input type="hidden" name="MMDVMModeYSF2DMR" value="OFF" />
    <input type="hidden" name="MMDVMModeYSF2NXDN" value="OFF" />
    <input type="hidden" name="MMDVMModeYSF2P25" value="OFF" />
    <input type="hidden" name="MMDVMModeDMR2YSF" value="OFF" />
    <input type="hidden" name="MMDVMModeDMR2NXDN" value="OFF" />
    <input type="hidden" name="MMDVMModePOCSAG" value="OFF" />
	<h2><?php echo $lang['mmdvmhost_config'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from<br />the configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_mode'];?>:<span><b>DMR Mode</b>Turn on DMR Features</span></a></td>
    <?php
	if ( $configmmdvm['DMR']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDMRCheckboxCr." /><label id=\"aria-toggle-dmr\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DMR Mode\" aria-checked=\"true\" onKeyPress=\"toggleDMRCheckbox()\" onclick=\"toggleDMRCheckbox()\" for=\"toggle-dmr\"><font style=\"font-size:0px\">DMR Mode</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDMRCheckboxCr." /><label id=\"aria-toggle-dmr\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DMR Mode\" aria-checked=\"false\" onKeyPress=\"toggleDMRCheckbox()\" onclick=\"toggleDMRCheckbox()\" for=\"toggle-dmr\"><font style=\"font-size:0px\">DMR Mode</font></label></div></td>\n";
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
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dstar\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDSTAR\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDSTARCheckboxCr." /><label id=\"aria-toggle-dstar\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DStar Mode\" aria-checked=\"true\" onKeyPress=\"toggleDSTARCheckbox()\" onclick=\"toggleDSTARCheckbox()\" for=\"toggle-dstar\"><font style=\"font-size:0px\">DStar Mode</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dstar\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDSTAR\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDSTARCheckboxCr." /><label id=\"aria-toggle-dstar\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DStar Mode\" aria-checked=\"false\" onKeyPress=\"toggleDSTARCheckbox()\" onclick=\"toggleDSTARCheckbox()\" for=\"toggle-dstar\"><font style=\"font-size:0px\">DStar Mode</font></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="dstarRfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['D-Star']['ModeHang'])) { echo $configmmdvm['D-Star']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="dstarNetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['D-Star Network']['ModeHang'])) { echo $configmmdvm['D-Star Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['m17_mode'];?>:<span><b>M17 Mode</b>Turn on M17 Features</span></a></td>
    <?php
	if ( $configmmdvm['M17']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-m17\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeM17\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleM17CheckboxCr." /><label id=\"aria-toggle-m17\" role=\"checkbox\" tabindex=\"0\" aria-label=\"M 17 Mode\" aria-checked=\"true\" onKeyPress=\"toggleM17Checkbox()\" onclick=\"toggleM17Checkbox()\" for=\"toggle-m17\"><font style=\"font-size:0px\">M 17 Mode</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-m17\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeM17\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleM17CheckboxCr." /><label id=\"aria-toggle-m17\" role=\"checkbox\" tabindex=\"0\" aria-label=\"M 17 Mode\" aria-checked=\"false\" onKeyPress=\"toggleM17Checkbox()\" onclick=\"toggleM17Checkbox()\" for=\"toggle-m17\"><font style=\"font-size:0px\">M 17 Mode</font></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="m17RfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['M17']['ModeHang'])) { echo $configmmdvm['M17']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="m17NetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['M17 Network']['ModeHang'])) { echo $configmmdvm['M17 Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['ysf_mode'];?>:<span><b>YSF Mode</b>Turn on YSF Features</span></a></td>
    <?php
	if ( $configmmdvm['System Fusion']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeFUSION\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleYSFCheckboxCr." /><label id=\"aria-toggle-ysf\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Y S F Mode\" aria-checked=\"true\" onKeyPress=\"toggleYSFCheckbox()\" onclick=\"toggleYSFCheckbox()\" for=\"toggle-ysf\"><font style=\"font-size:0px\">Y S F Mode</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeFUSION\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleYSFCheckboxCr." /><label id=\"aria-toggle-ysf\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Y S F Mode\" aria-checked=\"false\" onKeyPress=\"toggleYSFCheckbox()\" onclick=\"toggleYSFCheckbox()\" for=\"toggle-ysf\"><font style=\"font-size:0px\">Y S F Mode</font></label></div></td>\n";
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
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeP25\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleP25CheckboxCr." /><label id=\"aria-toggle-p25\" role=\"checkbox\" tabindex=\"0\" aria-label=\"P 25 Mode\" aria-checked=\"true\" onKeyPress=\"toggleP25Checkbox()\" onclick=\"toggleP25Checkbox()\" for=\"toggle-p25\"><font style=\"font-size:0px\">P 25 Mode</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeP25\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleP25CheckboxCr." /><label id=\"aria-toggle-p25\" role=\"checkbox\" tabindex=\"0\" aria-label=\"P 25 Mode\" aria-checked=\"false\" onKeyPress=\"toggleP25Checkbox()\" onclick=\"toggleP25Checkbox()\" for=\"toggle-p25\"><font style=\"font-size:0px\">P 25 Mode</font></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="p25RfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['P25']['ModeHang'])) { echo $configmmdvm['P25']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="p25NetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['P25 Network']['ModeHang'])) { echo $configmmdvm['P25 Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['nxdn_mode'];?>:<span><b>NXDN Mode</b>Turn on NXDN Features</span></a></td>
    <?php
	if ( $configmmdvm['NXDN']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeNXDN\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleNXDNCheckboxCr." /><label id=\"aria-toggle-nxdn\" role=\"checkbox\" tabindex=\"0\" aria-label=\"NXDN Mode\" aria-checked=\"true\" onKeyPress=\"toggleNXDNCheckbox()\" onclick=\"toggleNXDNCheckbox()\" for=\"toggle-nxdn\"><font style=\"font-size:0px\">NXDN Mode</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeNXDN\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleNXDNCheckboxCr." /><label id=\"aria-toggle-nxdn\" role=\"checkbox\" tabindex=\"0\" aria-label=\"NXDN Mode\" aria-checked=\"false\" onKeyPress=\"toggleNXDNCheckbox()\" onclick=\"toggleNXDNCheckbox()\" for=\"toggle-nxdn\"><font style=\"font-size:0px\">NXDN Mode</font></label></div></td>\n";
	}
    ?>
    <td>RF Hangtime: <input type="text" name="nxdnRfHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['NXDN']['ModeHang'])) { echo $configmmdvm['NXDN']['ModeHang']; } else { echo "20"; } ?>" />
    Net Hangtime: <input type="text" name="nxdnNetHangTime" size="7" maxlength="3" value="<?php if (isset($configmmdvm['NXDN Network']['ModeHang'])) { echo $configmmdvm['NXDN Network']['ModeHang']; } else { echo "20"; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">YSF2DMR:<span><b>YSF2DMR Mode</b>Turn on YSF2DMR Features</span></a></td>
    <?php
	if ( $configysf2dmr['Enabled']['Enabled'] == 1 ) {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2dmr\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2DMR\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleYSF2DMRCheckboxCr." /><label id=\"aria-toggle-ysf2dmr\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Y S F 2 DMR Mode\" aria-checked=\"true\" onKeyPress=\"toggleYSF2DMRCheckbox()\" onclick=\"toggleYSF2DMRCheckbox()\" for=\"toggle-ysf2dmr\"><font style=\"font-size:0px\">Y S F 2 DMR Mode</font></label></div></td>\n";
		}
	else {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2dmr\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2DMR\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleYSF2DMRCheckboxCr." /><label id=\"aria-toggle-ysf2dmr\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Y S F 2 DMR Mode\" aria-checked=\"false\" onKeyPress=\"toggleYSF2DMRCheckbox()\" onclick=\"toggleYSF2DMRCheckbox()\" for=\"toggle-ysf2dmr\"><font style=\"font-size:0px\">Y S F 2 DMR Mode</font></label></div></td>\n";
	}
    ?>
    </tr>
    <?php if (file_exists('/etc/ysf2nxdn')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">YSF2NXDN:<span><b>YSF2NXDN Mode</b>Turn on YSF2NXDN Features</span></a></td>
    <?php
	if ( $configysf2nxdn['Enabled']['Enabled'] == 1 ) {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2NXDN\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleYSF2NXDNCheckboxCr." /><label id=\"aria-toggle-ysf2nxdn\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Y S F 2 NXDN Mode\" aria-checked=\"true\" onKeyPress=\"toggleYSF2NXDNCheckbox()\" onclick=\"toggleYSF2NXDNCheckbox()\" for=\"toggle-ysf2nxdn\"><font style=\"font-size:0px\">Y S F 2 NXDN Mode</font></label></div></td>\n";
		}
	else {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2NXDN\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleYSF2NXDNCheckboxCr." /><label id=\"aria-toggle-ysf2nxdn\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Y S F 2 NXDN Mode\" aria-checked=\"false\" onKeyPress=\"toggleYSF2NXDNCheckbox()\" onclick=\"toggleYSF2NXDNCheckbox()\" for=\"toggle-ysf2nxdn\"><font style=\"font-size:0px\">Y S F 2 NXDN Mode</font></label></div></td>\n";
	}
    ?>
    </tr>
    <?php } ?>
    <?php if (file_exists('/etc/ysf2p25')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">YSF2P25:<span><b>YSF2P25 Mode</b>Turn on YSF2P25 Features</span></a></td>
    <?php
	if ( $configysf2p25['Enabled']['Enabled'] == 1 ) {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2P25\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleYSF2P25CheckboxCr." /><label id=\"aria-toggle-ysf2p25\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Y S F 2 P 25 Mode\" aria-checked=\"true\" onKeyPress=\"toggleYSF2P25Checkbox()\" onclick=\"toggleYSF2P25Checkbox()\" for=\"toggle-ysf2p25\"><font style=\"font-size:0px\">Y S F 2 P 25 Mode</font></label></div></td>\n";
		}
	else {
		echo "<td colspan=\"2\" align=\"left\"><div class=\"switch\"><input id=\"toggle-ysf2p25\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeYSF2P25\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleYSF2P25CheckboxCr." /><label id=\"aria-toggle-ysf2p25\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Y S F 2 P 25 Mode\" aria-checked=\"false\" onKeyPress=\"toggleYSF2P25Checkbox()\" onclick=\"toggleYSF2P25Checkbox()\" for=\"toggle-ysf2p25\"><font style=\"font-size:0px\">Y S F 2 P 25 Mode</font></label></div></td>\n";
	}
    ?>
    </tr>
    <?php } ?>
    <?php if (file_exists('/etc/dmr2ysf')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">DMR2YSF:<span><b>DMR2YSF Mode</b>Turn on DMR2YSF Features</span></a></td>
    <?php
	if ( $configdmr2ysf['Enabled']['Enabled'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr2ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR2YSF\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDMR2YSFCheckboxCr." /><label id=\"aria-toggle-dmr2ysf\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DMR 2 Y S F Mode\" aria-checked=\"true\" onKeyPress=\"toggleDMR2YSFCheckbox()\" onclick=\"toggleDMR2YSFCheckbox()\" for=\"toggle-dmr2ysf\"><font style=\"font-size:0px\">DMR 2 Y S F Mode</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr2ysf\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR2YSF\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDMR2YSFCheckboxCr." /><label id=\"aria-toggle-dmr2ysf\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DMR 2 Y S F Mode\" aria-checked=\"false\" onKeyPress=\"toggleDMR2YSFCheckbox()\" onclick=\"toggleDMR2YSFCheckbox()\" for=\"toggle-dmr2ysf\"><font style=\"font-size:0px\">DMR 2 Y S F Mode</font></label></div></td>\n";
	}
    ?>
    <td>Uses 7 prefix on DMRGateway</td>
    </tr>
    <?php } ?>
    <?php if (file_exists('/etc/dmr2nxdn')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">DMR2NXDN:<span><b>DMR2NXDN Mode</b>Turn on DMR2NXDN Features</span></a></td>
    <?php
	if ( $configdmr2nxdn['Enabled']['Enabled'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr2nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR2NXDN\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDMR2NXDNCheckboxCr." /><label id=\"aria-toggle-dmr2nxdn\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DMR 2 NXDN Mode\" aria-checked=\"true\" onKeyPress=\"toggleDMR2NXDNCheckbox()\" onclick=\"toggleDMR2NXDNCheckbox()\" for=\"toggle-dmr2nxdn\"><font style=\"font-size:0px\">DMR 2 NXDN Mode</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dmr2nxdn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModeDMR2NXDN\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDMR2NXDNCheckboxCr." /><label id=\"aria-toggle-dmr2nxdn\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DMR 2 NXDN Mode\" aria-checked=\"false\" onKeyPress=\"toggleDMR2NXDNCheckbox()\" onclick=\"toggleDMR2NXDNCheckbox()\" for=\"toggle-dmr2nxdn\"><font style=\"font-size:0px\">DMR 2 NXDN Mode</font></label></div></td>\n";
	}
    ?>
    <td>Uses 7 prefix on DMRGateway</td>
    </tr>
    <?php } ?>
    <?php if (file_exists('/etc/dapnetgateway')) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">POCSAG:<span><b>POCSAG Mode</b>Turn on POCSAG Features</span></a></td>
    <?php
	if ( $configmmdvm['POCSAG']['Enable'] == 1 ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-pocsag\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModePOCSAG\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$togglePOCSAGCheckboxCr." /><label id=\"aria-toggle-pocsag\" role=\"checkbox\" tabindex=\"0\" aria-label=\"POCSAG Mode\" aria-checked=\"true\" onKeyPress=\"togglePOCSAGCheckbox()\" onclick=\"togglePOCSAGCheckbox()\" for=\"toggle-pocsag\"><font style=\"font-size:0px\">POCSAG Mode</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-pocsag\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"MMDVMModePOCSAG\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$togglePOCSAGCheckboxCr." /><label id=\"aria-toggle-pocsag\" role=\"checkbox\" tabindex=\"0\" aria-label=\"POCSAG Mode\" aria-checked=\"false\" onKeyPress=\"togglePOCSAGCheckbox()\" onclick=\"togglePOCSAGCheckbox()\" for=\"toggle-pocsag\"><font style=\"font-size:0px\">POCSAG Mode</font></label></div></td>\n";
	}
    ?>
    <td>POCSAG Paging Features</td>
    </tr>
    <?php } ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['mmdvm_display'];?>:<span><b>Display Type</b>Choose your display type, if you have one.</span></a></td>
    <td align="left" colspan="2"><select name="mmdvmDisplayType">
	    <option <?php if (($configmmdvm['General']['Display'] == "None") || ($configmmdvm['General']['Display'] == "") ) {echo 'selected="selected" ';}; ?>value="None">None</option>
	    <option <?php if (($configmmdvm['General']['Display'] == "OLED") && ($configmmdvm['OLED']['Type'] == "3")) {echo 'selected="selected" ';}; ?>value="OLED3">OLED Type 3</option>
	    <option <?php if (($configmmdvm['General']['Display'] == "OLED") && ($configmmdvm['OLED']['Type'] == "6")) {echo 'selected="selected" ';}; ?>value="OLED6">OLED Type 6</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "Nextion") {echo 'selected="selected" ';}; ?>value="Nextion">Nextion</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "HD44780") {echo 'selected="selected" ';}; ?>value="HD44780">HD44780</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "TFT Serial") {echo 'selected="selected" ';}; ?>value="TFT Serial">TFT Serial</option>
	    <option <?php if ($configmmdvm['General']['Display'] == "LCDproc") {echo 'selected="selected" ';}; ?>value="LCDproc">LCDproc</option>
	    </select>
	    Port: <select name="mmdvmDisplayPort">
	    <?php
            if (($configmmdvm['General']['Display'] == "None") || ($configmmdvm['General']['Display'] == "")) {
                echo '      <option selected="selected" value="None">None</option>'."\n";
            } else {
                echo '      <option value="None">None</option>'."\n";
            }
            if (isset($configmmdvm['Nextion']['Port'])) {
                if ($configmmdvm['Nextion']['Port'] == "modem") {
                        echo '      <option selected="selected" value="modem">modem</option>'."\n";
                } else {
                        echo '      <option value="modem">modem</option>'."\n";
                }
                if ( ($configmmdvm['Nextion']['Port'] == "None") || ($configmmdvm['Nextion']['Port'] == "" )) { } else {
			$currentPort = str_replace($configmmdvm['Nextion']['Port'], "/dev/", "");
                        echo '      <option selected="selected" value="'.$currentPort.'">'.$configmmdvm['Nextion']['Port'].'</option>'."\n";
                }
            }
            exec('ls /dev/ | egrep -h "ttyA|ttyUSB"', $availablePorts);
            foreach($availablePorts as $port) {
                 echo "     <option value=\"$port\">/dev/$port</option>\n";
            }
	    ?>
	    <?php if (file_exists('/dev/ttyS2')) { ?>
	    <option <?php if ($configmmdvm['Nextion']['Port'] == "/dev/ttyS2") {echo 'selected="selected" ';}; ?>value="ttyS2">/dev/ttyS2</option>
    	    <?php } ?>
	    <?php if (file_exists('/dev/ttyNextionDriver')) { ?>
	    <option <?php if ($configmmdvm['Nextion']['Port'] == "/dev/ttyNextionDriver") {echo 'selected="selected" ';}; ?>value="ttyNextionDriver">/dev/ttyNextionDriver</option>
    	    <?php } ?>
	    </select>
	    Nextion Layout: <select name="mmdvmNextionDisplayType">
	    <option <?php if ($configmmdvm['Nextion']['ScreenLayout'] == "0") {echo 'selected="selected" ';}; ?>value="G4KLX">G4KLX</option>
	    <option <?php if ($configmmdvm['Nextion']['ScreenLayout'] == "2") {echo 'selected="selected" ';}; ?>value="ON7LDSL2">ON7LDS L2</option>
	    <option <?php if ($configmmdvm['Nextion']['ScreenLayout'] == "3") {echo 'selected="selected" ';}; ?>value="ON7LDSL3">ON7LDS L3</option>
	    <option <?php if ($configmmdvm['Nextion']['ScreenLayout'] == "4") {echo 'selected="selected" ';}; ?>value="ON7LDSL3HS">ON7LDS L3 HS</option>
	    </select>
    </td></tr>
    <!--<tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['mode_hangtime'];?>:<span><b>Net Hang Time</b>Stay in the last mode for this many seconds</span></a></td>
    <td align="left" colspan="2"><input type="text" name="hangTime" size="13" maxlength="3" value="<?php echo $configmmdvm['General']['RFModeHang']; ?>" /> in seconds (90 secs works well for Multi-Mode)</td>
    </tr>-->
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
    <?php } ?>
	<h2><?php echo $lang['general_config'];?></h2>
    <input type="hidden" name="APRSGatewayEnable" value="OFF" />
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">Hostname:<span><b>System Hostname</b>This is the system hostname, used for access to the dashboard etc.</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confHostame" size="13" maxlength="15" value="<?php echo exec('cat /etc/hostname'); ?>" />Do not add suffixes such as .local</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['node_call'];?>:<span><b>Gateway Callsign</b>This is your licenced callsign for use on this gateway, do not append the "G"</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confCallsign" size="13" maxlength="7" value="<?php echo $configs['gatewayCallsign'] ?>" /></td>
    </tr>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && (($configmmdvm['DMR']['Enable'] == 1) || ($configmmdvm['P25']['Enable'] == 1 ) || ($configmmdvm['System Fusion']['Enable'] == 1 ))) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_id'];?>:<span><b>CCS7/DMR ID</b>Enter your CCS7 / DMR ID here</span></a></td>
    <td align="left" colspan="2"><input type="text" name="dmrId" size="13" maxlength="9" value="<?php if (isset($configmmdvm['General']['Id'])) { echo $configmmdvm['General']['Id']; } else { echo $configmmdvm['DMR']['Id']; } ?>" /></td>
    </tr><?php } ?>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && ($configmmdvm['NXDN']['Enable'] == 1)) { ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">NXDN ID:<span><b>NXDN ID</b>Enter your NXDN ID here</span></a></td>
      <td align="left" colspan="2"><input type="text" name="nxdnId" size="13" maxlength="5" value="<?php if (isset($configmmdvm['NXDN']['Id'])) { echo $configmmdvm['NXDN']['Id']; } ?>" /></td>
    </tr><?php } ?>
<?php if ($configmmdvm['Info']['TXFrequency'] === $configmmdvm['Info']['RXFrequency']) {
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['radio_freq'].":<span><b>Radio Frequency</b>This is the Frequency your<br />Pi-Star is on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" id=\"confFREQ\" onkeyup=\"checkFrequency(); return false;\" name=\"confFREQ\" size=\"13\" maxlength=\"12\" value=\"".number_format(round(($configmmdvm['Info']['RXFrequency']/1000000),6), 6, '.', '')."\" />MHz</td>\n";
	echo "    </tr>\n";
	}
	else {
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['radio_freq']." RX:<span><b>Radio Frequency</b>This is the Frequency your<br />repeater will listen on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" id=\"confFREQrx\" onkeyup=\"checkFrequency(); return false;\" name=\"confFREQrx\" size=\"13\" maxlength=\"12\" value=\"".number_format(round(($configmmdvm['Info']['RXFrequency']/1000000),6), 6, '.', '')."\" />MHz</td>\n";
	echo "    </tr>\n";
	echo "    <tr>\n";
	echo "    <td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['radio_freq']." TX:<span><b>Radio Frequency</b>This is the Frequency your<br />repeater will transmit on</span></a></td>\n";
	echo "    <td align=\"left\" colspan=\"2\"><input type=\"text\" id=\"confFREQtx\" onkeyup=\"checkFrequency(); return false;\" name=\"confFREQtx\" size=\"13\" maxlength=\"12\" value=\"".number_format(round(($configmmdvm['Info']['TXFrequency']/1000000),6), 6, '.', '')."\" />MHz</td>\n";
	echo "    </tr>\n";
	}
?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['lattitude'];?>:<span><b>Gateway Latitude</b>This is the latitude where the gateway is located (positive number for North, negative number for South) - Set to 0 to hide your hotspot location</span></a></td>
    <td align="left" colspan="2"><input type="text" id="confLatitude" name="confLatitude" size="13" maxlength="9" value="<?php echo $configs['latitude'] ?>" />degrees (positive value for North, negative for South)</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['longitude'];?>:<span><b>Gateway Longitude</b>This is the longitude where the gateway is located (positive number for East, negative number for West) - Set to 0 to hide your hotspot location</span></a></td>
    <td align="left" colspan="2"><input type="text" id="confLongitude" name="confLongitude" size="13" maxlength="9" value="<?php echo $configs['longitude'] ?>" />degrees (positive value for East, negative for West)</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['town'];?>:<span><b>Gateway Town</b>The town where the gateway is located</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confDesc1" size="30" maxlength="30" value="<?php echo $configs['description1'] ?>" /></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['country'];?>:<span><b>Gateway Country</b>The country where the gateway is located</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confDesc2" size="30" maxlength="30" value="<?php echo $configs['description2'] ?>" /></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['url'];?>:<span><b>Gateway URL</b>The URL used to access this dashboard</span></a></td>
    <td align="left"><input type="text" name="confURL" size="30" maxlength="30" value="<?php echo $configs['url'] ?>" /></td>
    <td width="300">
    <input type="radio" name="urlAuto" value="auto"<?php if (strpos($configs['url'], 'www.qrz.com/db/'.$configmmdvm['General']['Callsign']) !== FALSE) {echo ' checked="checked"';} ?> />Auto
    <input type="radio" name="urlAuto" value="man"<?php if (strpos($configs['url'], 'www.qrz.com/db/'.$configmmdvm['General']['Callsign']) == FALSE) {echo ' checked="checked"';} ?> />Manual</td>
    </tr>
<?php if ( (file_exists('/etc/dstar-radio.dstarrepeater')) || (file_exists('/etc/dstar-radio.mmdvmhost')) ) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['radio_type'];?>:<span><b>Radio/Modem</b>What kind of radio or modem hardware do you have?</span></a></td>
    <td align="left" colspan="2"><select name="confHardware">
		<option<?php if (!$configModem['Modem']['Hardware']) { echo ' selected="selected"';}?> value="">--</option>
	        <?php if (file_exists('/dev/icom_ta')) { ?>
	    		<option<?php if ($configModem['Modem']['Hardware'] === 'icomTerminalAuto') {		echo ' selected="selected"';}?> value="icomTerminalAuto">Icom Radio in Terminal Mode (DStarRepeater Only)</option>
	        <?php } ?>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'idrp2c') {		echo ' selected="selected"';}?> value="idrp2c">Icom Repeater Controller ID-RP2C (DStarRepeater Only)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpis') {		echo ' selected="selected"';}?> value="dvmpis">DV-Mega Raspberry Pi Hat (GPIO) - Single Band (70cm)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpid') {		echo ' selected="selected"';}?> value="dvmpid">DV-Mega Raspberry Pi Hat (GPIO) - Dual Band</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmuadu') {		echo ' selected="selected"';}?> value="dvmuadu">DV-Mega on Arduino (USB - /dev/ttyUSB0) - Dual Band</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'dvmuada') {		echo ' selected="selected"';}?> value="dvmuada">DV-Mega on Arduino (USB - /dev/ttyACM0) - Dual Band</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmuagmsku') {		echo ' selected="selected"';}?> value="dvmuagmsku">DV-Mega on Arduino (USB - /dev/ttyUSB0) - GMSK Modem</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmuagmska') {		echo ' selected="selected"';}?> value="dvmuagmska">DV-Mega on Arduino (USB - /dev/ttyACM0) - GMSK Modem</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmbss') {		echo ' selected="selected"';}?> value="dvmbss">DV-Mega on Bluestack (USB) - Single Band (70cm)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmbsd') {		echo ' selected="selected"';}?> value="dvmbsd">DV-Mega on Bluestack (USB) - Dual Band</option>
	    	<?php if (file_exists('/dev/ttyS2')) { ?>
	    		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpicast') {	echo ' selected="selected"';}?> value="dvmpicast">DV-Mega Cast Base Radio (Main Unit)</option>
	    		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpicasths') {	echo ' selected="selected"';}?> value="dvmpicasths">DV-Mega Cast Hotspot - Single Band (70cm)</option>
	    		<option<?php if ($configModem['Modem']['Hardware'] === 'dvmpicasthd') {	echo ' selected="selected"';}?> value="dvmpicasthd">DV-Mega Cast Hotspot - Dual Band (2m/70cm)</option>
	    	<?php } ?>
		<option<?php if ($configModem['Modem']['Hardware'] === 'gmsk_modem') {		echo ' selected="selected"';}?> value="gmsk_modem">GMSK Modem (USB DStarRepeater Only)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'dvrptr1') {		echo ' selected="selected"';}?> value="dvrptr1">DV-RPTR V1 (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvrptr2') {		echo ' selected="selected"';}?> value="dvrptr2">DV-RPTR V2 (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvrptr3') {		echo ' selected="selected"';}?> value="dvrptr3">DV-RPTR V3 (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'dvap') {		echo ' selected="selected"';}?> value="dvap">DVAP (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zum') {			echo ' selected="selected"';}?> value="zum">MMDVM / MMDVM_HS / Teensy / ZUM (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'stm32dvm') {		echo ' selected="selected"';}?> value="stm32dvm">STM32-DVM / MMDVM_HS - Raspberry Pi Hat (GPIO)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'stm32usb') {		echo ' selected="selected"';}?> value="stm32usb">STM32-DVM (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumspotlibre') {	echo ' selected="selected"';}?> value="zumspotlibre">ZUMspot - Libre (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zumspotusb') {		echo ' selected="selected"';}?> value="zumspotusb">ZUMspot - USB Stick</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zumspotgpio') {		echo ' selected="selected"';}?> value="zumspotgpio">ZUMspot - Single Band Raspberry Pi Hat (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumspotdualgpio') {	echo ' selected="selected"';}?> value="zumspotdualgpio">ZUMspot - Dual Band Raspberry Pi Hat (GPIO)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'zumspotduplexgpio') {	echo ' selected="selected"';}?> value="zumspotduplexgpio">ZUMspot - Duplex Raspberry Pi Hat (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumradiopigpio') {	echo ' selected="selected"';}?> value="zumradiopigpio">ZUM Radio-MMDVM for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'zumradiopiusb') {	echo ' selected="selected"';}?> value="zumradiopiusb">ZUM Radio-MMDVM-Nucleo (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mnnano-spot') {		echo ' selected="selected"';}?> value="mnnano-spot">MicroNode Nano-Spot (Built In)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mnnano-teensy') {	echo ' selected="selected"';}?> value="mnnano-teensy">MicroNode Teensy (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'f4mgpio') {		echo ' selected="selected"';}?> value="f4mgpio">MMDVM F4M-GPIO (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'f4mf7m') {		echo ' selected="selected"';}?> value="f4mf7m">MMDVM F4M/F7M (F0DEI) for USB</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmhsdualbandgpio') {	echo ' selected="selected"';}?> value="mmdvmhsdualbandgpio">MMDVM_HS_Dual_Band for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmhshat') {		echo ' selected="selected"';}?> value="mmdvmhshat">MMDVM_HS_Hat (DB9MAT &amp; DF2ET) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmhsdualhatgpio') {	echo ' selected="selected"';}?> value="mmdvmhsdualhatgpio">MMDVM_HS_Dual_Hat (DB9MAT, DF2ET &amp; DO7EN) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmhsdualhatusb') {	echo ' selected="selected"';}?> value="mmdvmhsdualhatusb">MMDVM_HS_Dual_Hat (DB9MAT, DF2ET &amp; DO7EN) for Pi (USB)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmhshatambe') {	echo ' selected="selected"';}?> value="mmdvmhshatambe">MMDVM_HS_AMBE (D2RG HS_AMBE) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmrpthat') {		echo ' selected="selected"';}?> value="mmdvmrpthat">MMDVM_RPT_Hat (DB9MAT, DF2ET &amp; F0DEI) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmmdohat') {		echo ' selected="selected"';}?> value="mmdvmmdohat">MMDVM_HS_MDO Hat (BG3MDO) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmvyehat') {		echo ' selected="selected"';}?> value="mmdvmvyehat">MMDVM_HS_NPi Hat (VR2VYE) for Nano Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'mmdvmvyehatdual') {	echo ' selected="selected"';}?> value="mmdvmvyehatdual">MMDVM_HS_Hat_Dual Hat (VR2VYE) for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'genesyshat') {		echo ' selected="selected"';}?> value="genesyshat">Genesis - HHDVM_HS_Hat for Pi (GPIO)</option>
	        <option<?php if ($configModem['Modem']['Hardware'] === 'genesysdualhat') {	echo ' selected="selected"';}?> value="genesysdualhat">Genesis Dual - HHDVM_HS_Hat_Dual for Pi (GPIO)</option>
	    	<option<?php if ($configModem['Modem']['Hardware'] === 'lshshatgpio') {		echo ' selected="selected"';}?> value="lshshatgpio">LoneStar - MMDVM_HS_Hat for Pi (GPIO)</option>
	    	<option<?php if ($configModem['Modem']['Hardware'] === 'lshsdualhatgpio') {	echo ' selected="selected"';}?> value="lshsdualhatgpio">LoneStar - MMDVM_HS_Dual_Hat for Pi (GPIO)</option>
	    	<option<?php if ($configModem['Modem']['Hardware'] === 'lsusb') {		echo ' selected="selected"';}?> value="lsusb">LoneStar - USB Stick</option>
	    	<option<?php if ($configModem['Modem']['Hardware'] === 'sbhsdualbandgpio') {	echo ' selected="selected"';}?> value="sbhsdualbandgpio">SkyBridge - MMDVM_HS_Dual_Band for Pi (GPIO)</option>
	    	<option<?php if ($configModem['Modem']['Hardware'] === 'nanodv') {		echo ' selected="selected"';}?> value="nanodv">MMDVM_NANO_DV (BG4TGO) for NanoPi AIR (GPIO)</option>
	    	<option<?php if ($configModem['Modem']['Hardware'] === 'nanodvusb') {		echo ' selected="selected"';}?> value="nanodvusb">MMDVM_NANO_DV (BG4TGO) for NanoPi AIR (USB)</option>
		<option<?php if ($configModem['Modem']['Hardware'] === 'opengd77') {		echo ' selected="selected"';}?> value="opengd77">OpenGD77 DMR hotspot (USB)</option>
    </select></td>
    </tr>
<?php } ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['node_type'];?>:<span><b>Node Lock</b>Set the public/private node type. &quot;Private&quot; limits access to your system to your ID/Callsign only, this may be a licence requirement for your country and helps prevent network loops.</span></a></td>
    <td align="left" colspan="2">
    <input type="radio" name="nodeMode" value="prv"<?php if ($configmmdvm['DMR']['SelfOnly'] == 1) {echo ' checked="checked"';} ?> />Private
    <input type="radio" name="nodeMode" value="pub"<?php if ($configmmdvm['DMR']['SelfOnly'] == 0) {echo ' checked="checked"';} ?> />Public</td>
    </tr>
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['DMR']['Enable'] == 1 && $configmmdvm['DMR']['SelfOnly'] == 0) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">DMR Access List:<span><b>DMR IDs</b>Set the DMR IDs here that should have access to your hotspot, expected comma seperated list.</span></a></td>
    <td align="left" colspan="2"><input type="text" name="confDMRWhiteList" size="30" maxlength="50" value="<?php if (isset($configmmdvm['DMR']['WhiteList'])) { echo $configmmdvm['DMR']['WhiteList']; } ?>" /></td>
    </tr>
<?php } ?>	    
<?php if (file_exists('/etc/aprsgateway')) {
    echo "<tr>\n";
    echo "<td align=\"left\"><a class=\"tooltip2\" href=\"#\">".$lang['aprs_host']." Enable:<span><b>APRS Host Enable</b>Enabling this feature will make your location public.</span></a></td>\n";
    if ( $configaprsgw['Enabled']['Enabled'] == 1 ) {
        echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-aprsgateway\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"APRSGatewayEnable\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleAPRSGatewayCheckboxCr." /><label id=\"aria-toggle-aprsgateway\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable APRS Position Reporting\" aria-checked=\"true\" onKeyPress=\"toggleAPRSGatewayCheckbox()\" onclick=\"toggleAPRSGatewayCheckbox()\" for=\"toggle-aprsgateway\"><font style=\"font-size:0px\">Enable APRS Position Reporting</font></label></div></td>\n";
    } else {
        echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-aprsgateway\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"APRSGatewayEnable\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleAPRSGatewayCheckboxCr." /><label id=\"aria-toggle-aprsgateway\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable APRS Position Reporting\" aria-checked=\"false\" onKeyPress=\"toggleAPRSGatewayCheckbox()\" onclick=\"toggleAPRSGatewayCheckbox()\" for=\"toggle-aprsgateway\"><font style=\"font-size:0px\">Enable APRS Position Reporting</font></label></div></td>\n";
    }
} ?>
<tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['aprs_host'];?>:<span><b>APRS Host</b>Set your prefered APRS host here.</span></a></td>
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
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['timezone'];?>:<span><b>System TimeZone</b>Set the system timezone</span></a></td>
    <td style="text-align: left;" colspan="2"><select name="systemTimezone">
<?php
  exec('timedatectl list-timezones', $tzList);
  if (!in_array("UTC", $tzList)) { array_push($tzList, "UTC"); }
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
	echo '    <td align="left"><a class="tooltip2" href="#">'.$lang['dash_lang'].':<span><b>Dashboard Language</b>Set the language for the dashboard.</span></a></td>'."\n";
	echo '    <td align="left" colspan="2"><select name="dashboardLanguage">'."\n";

	if ($dh = opendir($lang_dir)) {
	while ($files[] = readdir($dh))
		sort($files); // Add sorting for the Language(s)
		foreach ($files as $file){
			if (($file != 'index.php') && ($file != '.') && ($file != '..') && ($file != '')) {
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
	<h2><?php echo $lang['dmr_config'];?></h2>
    <input type="hidden" name="dmrEmbeddedLCOnly" value="OFF" />
    <input type="hidden" name="dmrDumpTAData" value="OFF" />
    <input type="hidden" name="dmrGatewayXlxEn" value="OFF" />
    <input type="hidden" name="dmrGatewayNet1En" value="OFF" />
    <input type="hidden" name="dmrGatewayNet2En" value="OFF" />
    <input type="hidden" name="dmrDMRnetJitterBufer" value="OFF" />
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_master'];?>:<span><b>DMR Master (MMDVMHost)</b>Set your prefered DMR master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost">
<?php
        $testMMDVMdmrMaster = $configmmdvm['DMR Network']['Address'];
	$testMMDVMdmrMasterPort = $configmmdvm['DMR Network']['Port'];
        while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHost = preg_split('/\s+/', $dmrMasterLine);
                if ((strpos($dmrMasterHost[0], '#') === FALSE ) && (substr($dmrMasterHost[0], 0, 3) != "XLX") && ($dmrMasterHost[0] != '')) {
                        if (($testMMDVMdmrMaster == $dmrMasterHost[2]) && ($testMMDVMdmrMasterPort == $dmrMasterHost[4])) { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\" selected=\"selected\">$dmrMasterHost[0]</option>\n"; $dmrMasterNow = $dmrMasterHost[0]; }
                        else { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\">$dmrMasterHost[0]</option>\n"; }
                }
        }
        fclose($dmrMasterFile);
        ?>
    </select></td>
    </tr>
<?php if ($dmrMasterNow == "DMRGateway") { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_master'];?>:<span><b>BrandMeister Master</b>Set your prefered DMR master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost1">
<?php
	$dmrMasterFile1 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	$testMMDVMdmrMaster1 = $configdmrgateway['DMR Network 1']['Address'];
	$testMMDVMdmrMaster1Port = $configdmrgateway['DMR Network 1']['Port'];
	while (!feof($dmrMasterFile1)) {
		$dmrMasterLine1 = fgets($dmrMasterFile1);
                $dmrMasterHost1 = preg_split('/\s+/', $dmrMasterLine1);
                if ((strpos($dmrMasterHost1[0], '#') === FALSE ) && (substr($dmrMasterHost1[0], 0, 2) == "BM") && ($dmrMasterHost1[0] != '')) {
                        if (($testMMDVMdmrMaster1 == $dmrMasterHost1[2]) && ($testMMDVMdmrMaster1Port == $dmrMasterHost1[4])) { echo "      <option value=\"$dmrMasterHost1[2],$dmrMasterHost1[3],$dmrMasterHost1[4],$dmrMasterHost1[0]\" selected=\"selected\">$dmrMasterHost1[0]</option>\n"; }
                        else { echo "      <option value=\"$dmrMasterHost1[2],$dmrMasterHost1[3],$dmrMasterHost1[4],$dmrMasterHost1[0]\">$dmrMasterHost1[0]</option>\n"; }
                }
	}
	fclose($dmrMasterFile1);
?>
    </select></td></tr>
    <tr>
      <td align="left"><a class="tooltip2" href="#">BM Hotspot Security:<span><b>BrandMeister Password</b>Override the Password for BrandMeister with your own custom password, make sure you already configured this using BM Self Care. Empty the field to use the default.</span></a></td>
      <td align="left">
        <input type="password" name="bmHSSecurity" size="30" maxlength="30" value="<?php if (isset($configModem['BrandMeister']['Password'])) {echo $configModem['BrandMeister']['Password'];} ?>"></input>
      </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_network'];?> ESSID:<span><b>BrandMeister Extended ID</b>This is the extended ID, to make your DMR ID 9 digits long</span></a></td>
    <td align="left">
<?php
	if (isset($configdmrgateway['DMR Network 1']['Id'])) {
		if (strlen($configdmrgateway['DMR Network 1']['Id']) > strlen($configmmdvm['General']['Id'])) {
			$brandMeisterESSID = substr($configdmrgateway['DMR Network 1']['Id'], -2);
		} else {
			$brandMeisterESSID = "None";
		}
	} else {
		if (isset($configmmdvm['General']['Id'])) {
			if (strlen($configmmdvm['General']['Id']) == 9) {
				$brandMeisterESSID = substr($configmmdvm['General']['Id'], -2);
			} else {
				$brandMeisterESSID = "None";
			}
		} else {
			$brandMeisterESSID = "None";
		}
	}

	if (isset($configmmdvm['General']['Id'])) { if ($configmmdvm['General']['Id'] !== "1234567") { echo substr($configmmdvm['General']['Id'], 0, 7); } }
	echo "<select name=\"bmExtendedId\">\n";
	if ($brandMeisterESSID == "None") { echo "      <option value=\"None\" selected=\"selected\">None</option>\n"; } else { echo "      <option value=\"None\">None</option>\n"; }
	for ($brandMeisterESSIDInput = 1; $brandMeisterESSIDInput <= 99; $brandMeisterESSIDInput++) {
		$brandMeisterESSIDInput = str_pad($brandMeisterESSIDInput, 2, "0", STR_PAD_LEFT);
		if ($brandMeisterESSID === $brandMeisterESSIDInput) {
			echo "      <option value=\"$brandMeisterESSIDInput\" selected=\"selected\">$brandMeisterESSIDInput</option>\n";
		} else {
			echo "      <option value=\"$brandMeisterESSIDInput\">$brandMeisterESSIDInput</option>\n";
		}
	}
	echo "</select>\n";
?>
    </td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_network'];?> Enable:<span><b>BrandMeister Network Enable</b>Enable or disable BrandMeister Network</span></a></td>
    <td align="left">
    <?php if ($configdmrgateway['DMR Network 1']['Enabled'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayNet1En\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayNet1En\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrGatewayNet1EnCheckboxCr." /><label id=\"aria-toggle-dmrGatewayNet1En\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable BrandMeister DMR\" aria-checked=\"true\" onKeyPress=\"toggleDmrGatewayNet1EnCheckbox()\" onclick=\"toggleDmrGatewayNet1EnCheckbox()\" for=\"toggle-dmrGatewayNet1En\"><font style=\"font-size:0px\">Enable Brandmeister DMR</font></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayNet1En\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayNet1En\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrGatewayNet1EnCheckboxCr." /><label id=\"aria-toggle-dmrGatewayNet1En\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable BrandMeister DMR\" aria-checked=\"false\" onKeyPress=\"toggleDmrGatewayNet1EnCheckbox()\" onclick=\"toggleDmrGatewayNet1EnCheckbox()\" for=\"toggle-dmrGatewayNet1En\"><font style=\"font-size:0px\">Enable Brandmeister DMR</font></label></div>\n"; } ?>
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['bm_network'];?>:<span><b>BrandMeister Dashboards</b>Direct links to your BrandMeister Dashboards</span></a></td>
    <td>
      <a href="https://brandmeister.network/?page=device&amp;id=<?php if (isset($configdmrgateway['DMR Network 1']['Id'])) { echo $configdmrgateway['DMR Network 1']['Id']; } else { echo $configmmdvm['General']['Id']; } ?>" target="_new" style="color: #000;">Device Information</a> |
      <a href="https://brandmeister.network/?page=device-edit&amp;id=<?php if (isset($configdmrgateway['DMR Network 1']['Id'])) { echo $configdmrgateway['DMR Network 1']['Id']; } else { echo $configmmdvm['General']['Id']; } ?>" target="_new" style="color: #000;">Edit Device (BrandMeister Selfcare)</a>
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_master'];?>:<span><b>DMR+ Master</b>Set your prefered DMR master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost2">
<?php
	$dmrMasterFile2 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	$testMMDVMdmrMaster2= $configdmrgateway['DMR Network 2']['Address'];
	$testMMDVMdmrMaster2Port = $configdmrgateway['DMR Network 2']['Port'];
	while (!feof($dmrMasterFile2)) {
		$dmrMasterLine2 = fgets($dmrMasterFile2);
                $dmrMasterHost2 = preg_split('/\s+/', $dmrMasterLine2);
                if ((strpos($dmrMasterHost2[0], '#') === FALSE ) && (substr($dmrMasterHost2[0], 0, 4) == "DMR+") && ($dmrMasterHost2[0] != '')) {
                        if (($testMMDVMdmrMaster2 == $dmrMasterHost2[2]) && ($testMMDVMdmrMaster2Port == $dmrMasterHost2[4])) { echo "      <option value=\"$dmrMasterHost2[2],$dmrMasterHost2[3],$dmrMasterHost2[4],$dmrMasterHost2[0]\" selected=\"selected\">$dmrMasterHost2[0]</option>\n"; }
                        else { echo "      <option value=\"$dmrMasterHost2[2],$dmrMasterHost2[3],$dmrMasterHost2[4],$dmrMasterHost2[0]\">$dmrMasterHost2[0]</option>\n"; }
                }
	}
	fclose($dmrMasterFile2);
?>
    </select></td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_network'];?>:<span><b>DMR+ Network</b>Set your options= for DMR+ here</span></a></td>
    <td align="left">
    Options=<input type="text" name="dmrNetworkOptions" size="65" maxlength="250" value="<?php if (isset($configdmrgateway['DMR Network 2']['Options'])) { echo $configdmrgateway['DMR Network 2']['Options']; } ?>" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_network'];?> ESSID:<span><b>DMR Plus Extended ID</b>This is the extended ID, to make your DMR ID 8 digits long</span></a></td>
    <td align="left">
<?php
	if (isset($configdmrgateway['DMR Network 2']['Id'])) {
		if (strlen($configdmrgateway['DMR Network 2']['Id']) > strlen($configmmdvm['General']['Id'])) {
			$dmrPlusESSID = substr($configdmrgateway['DMR Network 2']['Id'], -2);
		} else {
			$dmrPlusESSID = "None";
		}
	} else {
		if (isset($configmmdvm['General']['Id'])) {
			if (strlen($configmmdvm['General']['Id']) == 9) {
				$dmrPlusESSID = substr($configmmdvm['General']['Id'], -2);
			} else {
				$dmrPlusESSID = "None";
			}
		} else {
			$dmrPlusESSID = "None";
		}
	}

	if (isset($configmmdvm['General']['Id'])) { if ($configmmdvm['General']['Id'] !== "1234567") { echo substr($configmmdvm['General']['Id'], 0, 7); } }
	echo "<select name=\"dmrPlusExtendedId\">\n";
	if ($dmrPlusESSID == "None") { echo "      <option value=\"None\" selected=\"selected\">None</option>\n"; } else { echo "      <option value=\"None\">None</option>\n"; }
	for ($dmrPlusESSIDInput = 1; $dmrPlusESSIDInput <= 99; $dmrPlusESSIDInput++) {
		$dmrPlusESSIDInput = str_pad($dmrPlusESSIDInput, 2, "0", STR_PAD_LEFT);
		if ($dmrPlusESSID === $dmrPlusESSIDInput) {
			echo "      <option value=\"$dmrPlusESSIDInput\" selected=\"selected\">$dmrPlusESSIDInput</option>\n";
		} else {
			echo "      <option value=\"$dmrPlusESSIDInput\">$dmrPlusESSIDInput</option>\n";
		}
	}
	echo "</select>\n";
?>
    </td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_plus_network'];?> Enable:<span><b>DMR+ Network Enable</b></span></a></td>
    <td align="left">
    <?php if ($configdmrgateway['DMR Network 2']['Enabled'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayNet2En\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayNet2En\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrGatewayNet2EnCheckboxCr." /><label id=\"aria-toggle-dmrGatewayNet2En\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable DMR+\" aria-checked=\"true\" onKeyPress=\"toggleDmrGatewayNet2EnCheckbox()\" onclick=\"toggleDmrGatewayNet2EnCheckbox()\" for=\"toggle-dmrGatewayNet2En\"><font style=\"font-size:0px\">Enable DMR+</font></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayNet2En\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayNet2En\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrGatewayNet2EnCheckboxCr." /><label id=\"aria-toggle-dmrGatewayNet2En\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable DMR+\" aria-checked=\"false\" onKeyPress=\"toggleDmrGatewayNet2EnCheckbox()\" onclick=\"toggleDmrGatewayNet2EnCheckbox()\" for=\"toggle-dmrGatewayNet2En\"><font style=\"font-size:0px\">Enable DMR+</font></label></div>\n"; } ?>
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['xlx_master'];?>:<span><b>XLX Master</b>Set your prefered XLX master here</span></a></td>
    <td style="text-align: left;"><select name="dmrMasterHost3">
<?php
	$dmrMasterFile3 = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	if (isset($configdmrgateway['XLX Network 1']['Address'])) { $testMMDVMdmrMaster3= $configdmrgateway['XLX Network 1']['Address']; }
	if (isset($configdmrgateway['XLX Network']['Startup'])) { $testMMDVMdmrMaster3= $configdmrgateway['XLX Network']['Startup']; }
	while (!feof($dmrMasterFile3)) {
		$dmrMasterLine3 = fgets($dmrMasterFile3);
                $dmrMasterHost3 = preg_split('/\s+/', $dmrMasterLine3);
                if ((strpos($dmrMasterHost3[0], '#') === FALSE ) && (substr($dmrMasterHost3[0], 0, 3) == "XLX") && ($dmrMasterHost3[0] != '')) {
                        if ($testMMDVMdmrMaster3 == $dmrMasterHost3[2]) { echo "      <option value=\"$dmrMasterHost3[2],$dmrMasterHost3[3],$dmrMasterHost3[4],$dmrMasterHost3[0]\" selected=\"selected\">$dmrMasterHost3[0]</option>\n"; }
			if ('XLX_'.$testMMDVMdmrMaster3 == $dmrMasterHost3[0]) { echo "      <option value=\"$dmrMasterHost3[2],$dmrMasterHost3[3],$dmrMasterHost3[4],$dmrMasterHost3[0]\" selected=\"selected\">$dmrMasterHost3[0]</option>\n"; }
                        else { echo "      <option value=\"$dmrMasterHost3[2],$dmrMasterHost3[3],$dmrMasterHost3[4],$dmrMasterHost3[0]\">$dmrMasterHost3[0]</option>\n"; }
                }
	}
	fclose($dmrMasterFile3);
?>
    </select></td></tr>
    <?php if (isset($configdmrgateway['XLX Network 1']['Startup'])) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">XLX Startup TG:<span><b>XLX Startup TG</b></span></a></td>
    <td align="left"><select name="dmrMasterHost3Startup">
<?php
	if (isset($configdmrgateway['XLX Network 1']['Startup'])) {
		echo '      <option value="None">None</option>'."\n";
	}
	else {
		echo '      <option value="None" selected="selected">None</option>'."\n";
	}
	for ($xlxSu = 1; $xlxSu <= 26; $xlxSu++) {
		$xlxSuVal = '40'.sprintf('%02d', $xlxSu);
		if ((isset($configdmrgateway['XLX Network 1']['Startup'])) && ($configdmrgateway['XLX Network 1']['Startup'] == $xlxSuVal)) {
			echo '      <option value="'.$xlxSuVal.'" selected="selected">'.$xlxSuVal.'</option>'."\n";
		}
		else {
			echo '      <option value="'.$xlxSuVal.'">'.$xlxSuVal.'</option>'."\n";
		}
	}
?>
    </select></td></tr>
    <?php } ?>
    <?php if (isset($configdmrgateway['XLX Network']['TG'])) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">XLX Startup Module:<span><b>XLX Startup Module override</b>Default will use the host file option, or override it here.</span></a></td>
    <td align="left"><select name="dmrMasterHost3StartupModule">
<?php
	if ((isset($configdmrgateway['XLX Network']['Module'])) && ($configdmrgateway['XLX Network']['Module'] != "@")) {
		echo '        <option value="'.$configdmrgateway['XLX Network']['Module'].'" selected="selected">'.$configdmrgateway['XLX Network']['Module'].'</option>'."\n";
		echo '        <option value="Default">Default</option>'."\n";
		echo '        <option value="@">None</option>'."\n";
	} elseif ((isset($configdmrgateway['XLX Network']['Module'])) && ($configdmrgateway['XLX Network']['Module'] == "@")) {
		echo '        <option value="Default">Default</option>'."\n";
		echo '        <option value="@" selected="selected">None</option>'."\n";
	} else {
		echo '        <option value="Default" selected="selected">Default</option>'."\n";
		echo '        <option value=" ">None</option>'."\n";
	}
?>
	<option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
        <option value="E">E</option>
        <option value="F">F</option>
        <option value="G">G</option>
        <option value="H">H</option>
        <option value="I">I</option>
        <option value="J">J</option>
        <option value="K">K</option>
        <option value="L">L</option>
        <option value="M">M</option>
        <option value="N">N</option>
        <option value="O">O</option>
        <option value="P">P</option>
        <option value="Q">Q</option>
        <option value="R">R</option>
        <option value="S">S</option>
        <option value="T">T</option>
        <option value="U">U</option>
        <option value="V">V</option>
        <option value="W">W</option>
        <option value="X">X</option>
        <option value="Y">Y</option>
        <option value="Z">Z</option>
    </select></td>
    </tr>
    <?php } ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['xlx_enable'];?>:<span><b>XLX Master Enable</b>Turn your XLX connection on or off.</span></a></td>
    <td align="left">
    <?php
    if ((isset($configdmrgateway['XLX Network 1']['Enabled'])) && ($configdmrgateway['XLX Network 1']['Enabled'] == 1)) { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayXlxEn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayXlxEn\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrGatewayXlxEnCheckboxCr." /><label id=\"aria-toggle-dmrGatewayXlxEn\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable XLX Network\" aria-checked=\"true\" onKeyPress=\"toggleDmrGatewayXlxEnCheckbox()\" onclick=\"toggleDmrGatewayXlxEnCheckbox()\" for=\"toggle-dmrGatewayXlxEn\"><font style=\"font-size:0px\">Enable XLX via DMR</font></label></div>\n"; }
    else if ((isset($configdmrgateway['XLX Network']['Enabled'])) && ($configdmrgateway['XLX Network']['Enabled'] == 1)) { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayXlxEn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayXlxEn\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrGatewayXlxEnCheckboxCr." /><label id=\"aria-toggle-dmrGatewayXlxEn\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable XLX Network\" aria-checked=\"true\" onKeyPress=\"toggleDmrGatewayXlxEnCheckbox()\" onclick=\"toggleDmrGatewayXlxEnCheckbox()\" for=\"toggle-dmrGatewayXlxEn\"><font style=\"font-size:0px\">Enable XLX via DMR</font></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrGatewayXlxEn\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrGatewayXlxEn\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrGatewayXlxEnCheckboxCr." /><label id=\"aria-toggle-dmrGatewayXlxEn\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable XLX Network\" aria-checked=\"false\" onKeyPress=\"toggleDmrGatewayXlxEnCheckbox()\" onclick=\"toggleDmrGatewayXlxEnCheckbox()\" for=\"toggle-dmrGatewayXlxEn\"><font style=\"font-size:0px\">Enable XLX via DMR</font></label></div>\n"; } ?>
    </td></tr>
<?php }
    if (substr($dmrMasterNow, 0, 2) == "BM") { echo '    <tr>
      <td align="left"><a class="tooltip2" href="#">Hotspot Security:<span><b>Custom Password</b>Override the Password for your DMR Host with your own custom password, make sure you already configured this with your chosen DMR Host too. Empty the field to use the default.</span></a></td>
      <td align="left">
        <input type="password" name="bmHSSecurity" size="30" maxlength="30" value="'; if (isset($configModem['BrandMeister']['Password'])) {echo $configModem['BrandMeister']['Password'];}; echo '"></input>
      </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">'.$lang['bm_network'].':<span><b>BrandMeister Dashboards</b>Direct links to your BrandMeister Dashboards</span></a></td>
    <td>
      <a href="https://brandmeister.network/?page=device&amp;id='; if (isset($configmmdvm['DMR']['Id'])) { echo $configmmdvm['DMR']['Id']; } else { echo $configmmdvm['General']['Id']; }; echo '" target="_new" style="color: #000;">Device Information</a> |
      <a href="https://brandmeister.network/?page=device-edit&amp;id='; if (isset($configmmdvm['DMR']['Id'])) { echo $configmmdvm['DMR']['Id']; } else { echo $configmmdvm['General']['Id']; }; echo '" target="_new" style="color: #000;">Edit Device (BrandMeister Selfcare)</a>
    </td>
    </tr>'."\n";}
    if (substr($dmrMasterNow, 0, 8) == "FreeDMR_") {
      echo '    <tr>
    <td align="left"><a class="tooltip2" href="#">DMR Options:<span><b>DMR Network</b>Set your options= for DMR here</span></a></td>
    <td align="left">
    Options=<input type="text" name="dmrNetworkOptions" size="65" maxlength="250" value="';
	if (isset($configmmdvm['DMR Network']['Options'])) { echo $configmmdvm['DMR Network']['Options']; }
        echo '" />
    </td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">FreeDMR Help:<span><b>FreeDMR Help</b>FreeDMR Options Help</span></a></td>
    <td>
      <a href="http://www.freedmr.uk/index.php/static-talk-groups-pi-star/" target="_new" style="color: #000;">FreeDMR Options Guide</a> |
      <a href="http://www.freedmr.uk/index.php/dashboard/options-calculator/" target="_new" style="color: #000;">FreeDMR Options Calculator</a>
    </td>
    </tr>'."\n";}
    if ((substr($dmrMasterNow, 0, 4) == "DMR+") || (substr($dmrMasterNow, 0, 3) == "HB_") || (substr($dmrMasterNow, 0, 3) == "FD_") || (substr($dmrMasterNow, 0, 9) == "FreeSTAR_")) {
      echo '    <tr>
    <td align="left"><a class="tooltip2" href="#">DMR Options:<span><b>DMR Network</b>Set your options= for DMR here</span></a></td>
    <td align="left">
    Options=<input type="text" name="dmrNetworkOptions" size="65" maxlength="250" value="';
	if (isset($configmmdvm['DMR Network']['Options'])) { echo $configmmdvm['DMR Network']['Options']; }
        echo '" />
    </td>
    </tr>'."\n";}
    if (substr($dmrMasterNow, 0, 4) == "TGIF") {
      echo '    <tr>
      <td align="left"><a class="tooltip2" href="#">Hotspot Security:<span><b>Custom Password</b>Override the Password for your DMR Host with your own custom password, make sure you already configured this with your chosen DMR Host too. Empty the field to use the default.</span></a></td>
      <td align="left">
        <input type="password" name="tgifHSSecurity" size="30" maxlength="30" value="'; if (isset($configModem['TGIF']['Password'])) {echo $configModem['TGIF']['Password'];}; echo '"></input>
      </td>
    </tr>
    <tr>'."\n";}
?>

<?php if ($dmrMasterNow !== "DMRGateway") { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">DMR ESSID:<span><b>DMR Extended ID</b>This is the extended ID, to make your DMR ID 8 or 9 digits long</span></a></td>
    <td align="left"><?php
	if (isset($configmmdvm['DMR']['Id'])) {
		if (strlen($configmmdvm['DMR']['Id']) > strlen($configmmdvm['General']['Id'])) {
			$essidLen = strlen($configmmdvm['DMR']['Id']) - strlen($configmmdvm['General']['Id']);
			$dmrESSID = substr($configmmdvm['DMR']['Id'], - $essidLen);
		} else {
			$dmrESSID = "None";
		}
	} else {
		$dmrESSID = "None";
	}

	if (isset($configmmdvm['General']['Id'])) { if ($configmmdvm['General']['Id'] !== "1234567") { echo substr($configmmdvm['General']['Id'], 0, 7); } }
	echo "<select name=\"dmrExtendedId\">\n";
	if ($dmrESSID == "None") { echo "      <option value=\"None\" selected=\"selected\">None</option>\n"; } else { echo "      <option value=\"None\">None</option>\n"; }
	for ($dmrESSIDInput = 1; $dmrESSIDInput <= 99; $dmrESSIDInput++) {
		$dmrESSIDInput = str_pad($dmrESSIDInput, 2, "0", STR_PAD_LEFT);
		if ($dmrESSID === $dmrESSIDInput) {
			echo "      <option value=\"$dmrESSIDInput\" selected=\"selected\">$dmrESSIDInput</option>\n";
		} else {
			echo "      <option value=\"$dmrESSIDInput\">$dmrESSIDInput</option>\n";
		}
	}
	echo "</select>\n";
?>
    </td></tr>
    <tr>
<?php } ?>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_cc'];?>:<span><b>DMR Color Code</b>Set your DMR Color Code here</span></a></td>
    <td style="text-align: left;"><select name="dmrColorCode">
	<?php for ($dmrColorCodeInput = 0; $dmrColorCodeInput <= 15; $dmrColorCodeInput++) {
		if ($configmmdvm['DMR']['ColorCode'] == $dmrColorCodeInput) { echo "<option selected=\"selected\" value=\"$dmrColorCodeInput\">$dmrColorCodeInput</option>\n"; }
		else {echo "      <option value=\"$dmrColorCodeInput\">$dmrColorCodeInput</option>\n"; }
	} ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_embeddedlconly'];?>:<span><b>DMR EmbeddedLCOnly</b>Turn ON to disable extended message support, including GPS and Talker Alias data. This can help reduce problems with some DMR Radios that do not support such features.</span></a></td>
    <td align="left">
    <?php if ($configmmdvm['DMR']['EmbeddedLCOnly'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrEmbeddedLCOnly\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrEmbeddedLCOnly\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrEmbeddedLCOnlyCr." /><label id=\"aria-toggle-dmrEmbeddedLCOnly\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable DMR Embedded LC Only\" aria-checked=\"true\" onKeyPress=\"toggleDmrEmbeddedLCOnly()\" onclick=\"toggleDmrEmbeddedLCOnly()\" for=\"toggle-dmrEmbeddedLCOnly\"><font style=\"font-size:0px\">Enable DMR Embedded LC only</font></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrEmbeddedLCOnly\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrEmbeddedLCOnly\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrEmbeddedLCOnlyCr." /><label id=\"aria-toggle-dmrEmbeddedLCOnly\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Enable DMR Embedded LC Only\" aria-checked=\"false\" onKeyPress=\"toggleDmrEmbeddedLCOnly()\" onclick=\"toggleDmrEmbeddedLCOnly()\" for=\"toggle-dmrEmbeddedLCOnly\"><font style=\"font-size:0px\">Enable DMR Embedded LC Only</font></label></div>\n"; } ?>
    </td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_dumptadata'];?>:<span><b>DMR DumpTAData</b>Turn ON to dump GPS and Talker Alias data to MMDVMHost log file.</span></a></td>
    <td align="left">
    <?php if ($configmmdvm['DMR']['DumpTAData'] == 1) { echo "<div class=\"switch\"><input id=\"toggle-dmrDumpTAData\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDumpTAData\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrDumpTADataCr." /><label id=\"aria-toggle-dmrDumpTAData\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DMR Dump TA Data\" aria-checked=\"true\" onKeyPress=\"toggleDmrDumpTAData()\" onclick=\"toggleDmrDumpTAData()\" for=\"toggle-dmrDumpTAData\"><font style=\"font-size:0px\">DMR Dump T-A Data</font></label></div>\n"; }
    else { echo "<div class=\"switch\"><input id=\"toggle-dmrDumpTAData\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDumpTAData\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDmrDumpTADataCr." /><label id=\"aria-toggle-dmrDumpTAData\" role=\"checkbox\" tabindex=\"0\" aria-label=\"DMR Dump TA Data\" aria-checked=\"false\" onKeyPress=\"toggleDmrDumpTAData()\" onclick=\"toggleDmrDumpTAData()\" for=\"toggle-dmrDumpTAData\"><font style=\"font-size:0px\">DMR Dump T-A Data</font></label></div>\n"; } ?>
    </td></tr>
    <!-- <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo "DMR JitterBuffer";?>:<span><b>DMR JitterBuffer</b>Turn on for improved network resiliancy, in high Latency networks.</span></a></td>
    <td align="left">
    <?php // if ((isset($configmmdvm['DMR Network']['JitterEnabled'])) && ($configmmdvm['DMR Network']['JitterEnabled'] == 0)) { echo "<div class=\"switch\"><input id=\"toggle-dmrJitterBufer\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDMRnetJitterBufer\" value=\"ON\"/><label for=\"toggle-dmrJitterBufer\"></label></div>\n"; }
    // else { echo "<div class=\"switch\"><input id=\"toggle-dmrJitterBufer\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"dmrDMRnetJitterBufer\" value=\"ON\" checked=\"checked\" /><label for=\"toggle-dmrJitterBufer\"></label></div>\n"; }
    ?>
    </td></tr>. -->
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>

<?php if (file_exists('/etc/dstar-radio.dstarrepeater') || $configmmdvm['D-Star']['Enable'] == 1) { ?>
	<h2><?php echo $lang['dstar_config'];?></h2>
	<input type="hidden" name="confTimeAnnounce" value="OFF" />
	<input type="hidden" name="confircddbEnabled" value="OFF" />
	<input type="hidden" name="confHostFilesNoDExtra" value="OFF" />
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
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_irc_password'];?>:<span><b>Remote Password</b>Used for ircDDBGateway remote control access</span></a></td>
    <td align="left" colspan="2"><input type="password" name="confPassword" size="30" maxlength="30" value="<?php echo $configs['remotePassword'] ?>" /></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_default_ref'];?>:<span><b>Default Reflector</b>Used for setting the default reflector.</span></a></td>
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
	if (strpos($dcsLine, 'DCS') !== FALSE && strpos($dcsLine, '#') === FALSE) {
		echo "	<option value=\"".substr($dcsLine, 0, 6)."\">".substr($dcsLine, 0, 6)."</option>\n";
	}
	if (strpos($dcsLine, 'XLX') !== FALSE && strpos($dcsLine, '#') === FALSE) {
		echo "	<option value=\"".substr($dcsLine, 0, 6)."\">".substr($dcsLine, 0, 6)."</option>\n";
	}
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
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_irc_lang'];?>:<span><b>ircDDBGateway Language</b>Set your prefered language here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="ircDDBGatewayAnnounceLanguage">
<?php
        $testIrcLanguage = $configs['language'];
	if (is_readable("/var/www/dashboard/config/ircddbgateway_languages.inc")) {
	  $ircLanguageFile = fopen("/var/www/dashboard/config/ircddbgateway_languages.inc", "r");
        while (!feof($ircLanguageFile)) {
                $ircLanguageFileLine = fgets($ircLanguageFile);
                $ircLanguage = preg_split('/;/', $ircLanguageFileLine);
                if ((strpos($ircLanguage[0], '#') === FALSE ) && ($ircLanguage[0] != '')) {
			$ircLanguage[2] = rtrim($ircLanguage[2]);
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
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dstar_irc_time'];?>:<span><b>Time Announce</b>Announce time hourly</span></a></td>
    <?php
	if ( !file_exists('/etc/timeserver.dissable') ) {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-timeAnnounce\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confTimeAnnounce\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDstarTimeAnnounceCr." /><label id=\"aria-toggle-timeAnnounce\" role=\"checkbox\" tabindex=\"0\" aria-label=\"D-Star Time Announcements\" aria-checked=\"true\" onKeyPress=\"toggleDstarTimeAnnounce()\" onclick=\"toggleDstarTimeAnnounce()\" for=\"toggle-timeAnnounce\"><font style=\"font-size:0px\">D-Star Time Announcements</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-timeAnnounce\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confTimeAnnounce\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDstarTimeAnnounceCr." /><label id=\"aria-toggle-timeAnnounce\" role=\"checkbox\" tabindex=\"0\" aria-label=\"D-Star Time Announcements\" aria-checked=\"false\" onKeyPress=\"toggleDstarTimeAnnounce()\" onclick=\"toggleDstarTimeAnnounce()\" for=\"toggle-timeAnnounce\"><font style=\"font-size:0px\">D-Star Time Announcements</font></label></div></td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">Callsign Routing:<span><b>Callsign Routing</b>Do you want callsign routing for D-Star</span></a></td>
    <?php
	if ( isset($configs['ircddbEnabled']) && $configs['ircddbEnabled'] == "1" ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-ircddbEnabled\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confircddbEnabled\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleircddbEnabledCr." /><label id=\"aria-toggle-ircddbEnabled\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Use ircDDB callsign routing\" aria-checked=\"true\" onKeyPress=\"toggleircddbEnabled()\" onclick=\"toggleircddbEnabled()\" for=\"toggle-ircddbEnabled\"><font style=\"font-size:0px\">Enable ircDDB callsign routing</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-ircddbEnabled\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confircddbEnabled\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleircddbEnabledCr." /><label id=\"aria-toggle-ircddbEnabled\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Use ircDDB callsign routing\" aria-checked=\"false\" onKeyPress=\"toggleircddbEnabled()\" onclick=\"toggleircddbEnabled()\" for=\"toggle-ircddbEnabled\"><font style=\"font-size:0px\">Enable ircDDB callsign routing</font></label></div></td>\n";
	}
    ?>
    <td>Connect ircDDB for call routing</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">Use DPlus for XRF:<span><b>No DExtra</b>Should host files use DPlus Protocol for XRFs</span></a></td>
    <?php
	if ( file_exists('/etc/hostfiles.nodextra') ) {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dplusHostFiles\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesNoDExtra\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDstarDplusHostfilesCr." /><label id=\"aria-toggle-dplusHostFiles\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Use D-Plus for XRF Hosts\" aria-checked=\"true\" onKeyPress=\"toggleDstarDplusHostfiles()\" onclick=\"toggleDstarDplusHostfiles()\" for=\"toggle-dplusHostFiles\"><font style=\"font-size:0px\">Use D-Plus for XRF Hosts</font></label></div></td>\n";
		}
	else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-dplusHostFiles\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesNoDExtra\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleDstarDplusHostfilesCr." /><label id=\"aria-toggle-dplusHostFiles\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Use D-Plus for XRF Hosts\" aria-checked=\"false\" onKeyPress=\"toggleDstarDplusHostfiles()\" onclick=\"toggleDstarDplusHostfiles()\" for=\"toggle-dplusHostFiles\"><font style=\"font-size:0px\">Use D-Plus for XRF Hosts</font></label></div></td>\n";
	}
    ?>
    <td>Note: Update Required if changed</td>
    </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && ($configmmdvm['System Fusion Network']['Enable'] == 1 || $configdmr2ysf['Enabled']['Enabled'] == 1 )) {
$ysfHosts = fopen("/usr/local/etc/YSFHosts.txt", "r"); ?>
	<input type="hidden" name="confHostFilesYSFUpper" value="OFF" />
	<input type="hidden" name="wiresXCommandPassthrough" value="OFF" />
	<h2><?php echo $lang['ysf_config'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['ysf_startup_host'];?>:<span><b>YSF Host</b>Set your prefered YSF Host here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="ysfStartupHost">
<?php
        if (isset($configysfgateway['Network']['Startup'])) {
                $testYSFHost = $configysfgateway['Network']['Startup'];
                echo "      <option value=\"none\">None</option>\n";
        	}
        else {
                $testYSFHost = "none";
                echo "      <option value=\"none\" selected=\"selected\">None</option>\n";
    		}
	if ($testYSFHost == "ZZ Parrot")  {
		echo "      <option value=\"00001,ZZ Parrot\" selected=\"selected\">YSF00001 - Parrot</option>\n";
	} else {
		echo "      <option value=\"00001,ZZ Parrot\">YSF00001 - Parrot</option>\n";
	}
	if ($testYSFHost == "YSF2DMR")  {
		echo "      <option value=\"00002,YSF2DMR\"  selected=\"selected\">YSF00002 - Link YSF2DMR</option>\n";
	} else {
		echo "      <option value=\"00002,YSF2DMR\">YSF00002 - Link YSF2DMR</option>\n";
	}
	if ($testYSFHost == "YSF2NXDN") {
		echo "      <option value=\"00003,YSF2NXDN\" selected=\"selected\">YSF00003 - Link YSF2NXDN</option>\n";
	} else {
		echo "      <option value=\"00003,YSF2NXDN\">YSF00003 - Link YSF2NXDN</option>\n";
	}
	if ($testYSFHost == "YSF2P25")  {
		echo "      <option value=\"00004,YSF2P25\"  selected=\"selected\">YSF00004 - Link YSF2P25</option>\n";
	} else {
		echo "      <option value=\"00004,YSF2P25\">YSF00004 - Link YSF2P25</option>\n";
	}
        while (!feof($ysfHosts)) {
                $ysfHostsLine = fgets($ysfHosts);
                $ysfHost = preg_split('/;/', $ysfHostsLine);
                if ((strpos($ysfHost[0], '#') === FALSE ) && ($ysfHost[0] != '')) {
                        if ($testYSFHost == $ysfHost[1]) { echo "      <option value=\"$ysfHost[0],$ysfHost[1]\" selected=\"selected\">YSF$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n"; }
			else { echo "      <option value=\"$ysfHost[0],$ysfHost[1]\">YSF$ysfHost[0] - ".htmlspecialchars($ysfHost[1])." - ".htmlspecialchars($ysfHost[2])."</option>\n"; }
                }
        }
        fclose($ysfHosts);
	if (file_exists("/usr/local/etc/FCSHosts.txt")) {
                $fcsHosts = fopen("/usr/local/etc/FCSHosts.txt", "r");
                while (!feof($fcsHosts)) {
                        $ysfHostsLine = fgets($fcsHosts);
                        $ysfHost = preg_split('/;/', $ysfHostsLine);
			if (substr($ysfHost[0], 0, 3) == "FCS") {
                                if ($testYSFHost == $ysfHost[0]) { echo "      <option value=\"$ysfHost[0],$ysfHost[0]\" selected=\"selected\">$ysfHost[0] - ".htmlspecialchars($ysfHost[1])."</option>\n"; }
                                else { echo "      <option value=\"$ysfHost[0],$ysfHost[0]\">$ysfHost[0] - ".htmlspecialchars($ysfHost[1])."</option>\n"; }
                        }
                }
                fclose($fcsHosts);
        }
        ?>
    </select></td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">UPPERCASE Hostfiles:<span><b>UPPERCASE Hostfiles</b>Should host files use UPPERCASE only - fixes issues with FT-70D radios.</span></a></td>
    <?php
	if ( isset($configysfgateway['General']['WiresXMakeUpper']) ) {
		if ( $configysfgateway['General']['WiresXMakeUpper'] ) {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-confHostFilesYSFUpper\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesYSFUpper\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleHostFilesYSFUpperCr." /><label id=\"aria-toggle-confHostFilesYSFUpper\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Uppercase Host Files\" aria-checked=\"true\" onKeyPress=\"toggleHostFilesYSFUpper()\" onclick=\"toggleHostFilesYSFUpper()\" for=\"toggle-confHostFilesYSFUpper\"><font style=\"font-size:0px\">Uppercase Host Files</font></label></div></td>\n";
		}
		else {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-confHostFilesYSFUpper\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesYSFUpper\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleHostFilesYSFUpperCr." /><label id=\"aria-toggle-confHostFilesYSFUpper\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Uppercase Host Files\" aria-checked=\"false\" onKeyPress=\"toggleHostFilesYSFUpper()\" onclick=\"toggleHostFilesYSFUpper()\" for=\"toggle-confHostFilesYSFUpper\"><font style=\"font-size:0px\">Uppercase Host Files</font></label></div></td>\n";
		}
	} else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-confHostFilesYSFUpper\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"confHostFilesYSFUpper\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleHostFilesYSFUpperCr." /><label id=\"aria-toggle-confHostFilesYSFUpper\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Uppercase Host Files\" aria-checked=\"false\" onKeyPress=\"toggleHostFilesYSFUpper()\" onclick=\"toggleHostFilesYSFUpper()\" for=\"toggle-confHostFilesYSFUpper\"><font style=\"font-size:0px\">Uppercase Host files</font></label></div></td>\n";
	}
    ?>
    <td>Note: Update Required if changed</td>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#">WiresX Passthrough:<span><b>WiresX Auto Passthrough</b>Use this to automatically send WiresX commands through to YSF2xxx cross-over modes.</span></a></td>
    <?php
	if ( isset($configysfgateway['General']['WiresXCommandPassthrough']) ) {
		if ( $configysfgateway['General']['WiresXCommandPassthrough'] ) {
			echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-confWiresXCommandPassthrough\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"wiresXCommandPassthrough\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleWiresXCommandPassthroughCr." /><label id=\"aria-toggle-confWiresXCommandPassthrough\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Wires-X Command Passthrough\" aria-checked=\"true\" onKeyPress=\"toggleWiresXCommandPassthrough()\" onclick=\"toggleWiresXCommandPassthrough()\" for=\"toggle-confWiresXCommandPassthrough\"><font style=\"font-size:0px\">Wires-X Command Passthrough</font></label></div></td>\n";
		}
		else {
			echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-confWiresXCommandPassthrough\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"wiresXCommandPassthrough\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleWiresXCommandPassthroughCr." /><label id=\"aria-toggle-confWiresXCommandPassthrough\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Wires-X Command Passthrough\" aria-checked=\"false\" onKeyPress=\"toggleWiresXCommandPassthrough()\" onclick=\"toggleWiresXCommandPassthrough()\" for=\"toggle-confWiresXCommandPassthrough\"><font style=\"font-size:0px\">Wires-X Command Passthrough</font></label></div></td>\n";
		}
	} else {
		echo "<td align=\"left\" colspan=\"2\"><div class=\"switch\"><input id=\"toggle-confWiresXCommandPassthrough\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"wiresXCommandPassthrough\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleWiresXCommandPassthroughCr." /><label id=\"aria-toggle-confWiresXCommandPassthrough\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Wires-X Command Passthrough\" aria-checked=\"false\" onKeyPress=\"toggleWiresXCommandPassthrough()\" onclick=\"toggleWiresXCommandPassthrough()\" for=\"toggle-confWiresXCommandPassthrough\"><font style=\"font-size:0px\">Wires-X Command Passthrough</font></label></div></td>\n";
	}
    ?>
    </tr>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configysf2dmr['Enabled']['Enabled'] == 1) {
    $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r"); ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#">(YSF2DMR)<?php echo $lang['dmr_id'];?>:<span><b>CCS7/DMR ID</b>Enter your CCS7 / DMR ID here</span></a></td>
    <td align="left" colspan="2">
<?php
	if (isset($configysf2dmr['DMR Network']['Id'])) {
		if (strlen($configysf2dmr['DMR Network']['Id']) > strlen($configmmdvm['General']['Id'])) {
			$essidYSF2DMRLen = strlen($configysf2dmr['DMR Network']['Id']) - strlen($configmmdvm['General']['Id']);
			$ysf2dmrESSID = substr($configysf2dmr['DMR Network']['Id'], -$essidYSF2DMRLen);
		} else {
			$ysf2dmrESSID = "None";
		}
	} else {
		$ysf2dmrESSID = "None";
	}

	if (isset($configmmdvm['General']['Id'])) { if ($configmmdvm['General']['Id'] !== "1234567") { echo substr($configmmdvm['General']['Id'], 0, 7); } }
	if (isset($configmmdvm['General']['Id'])) { $ysf2dmrIdBase = substr($configmmdvm['General']['Id'], 0, 7); } else { $ysf2dmrIdBase = "1234567"; }
	echo "<select name=\"ysf2dmrId\">\n";
	if ($ysf2dmrESSID == "None") { echo "      <option value=\"$ysf2dmrIdBase\" selected=\"selected\">None</option>\n"; } else { echo "      <option value=\"None\">None</option>\n"; }
	for ($ysf2dmrESSIDInput = 1; $ysf2dmrESSIDInput <= 99; $ysf2dmrESSIDInput++) {
		$ysf2dmrESSIDInput = str_pad($ysf2dmrESSIDInput, 2, "0", STR_PAD_LEFT);
		if ($ysf2dmrESSID === $ysf2dmrESSIDInput) {
			echo "      <option value=\"$ysf2dmrIdBase$ysf2dmrESSIDInput\" selected=\"selected\">$ysf2dmrESSIDInput</option>\n";
		} else {
			echo "      <option value=\"$ysf2dmrIdBase$ysf2dmrESSIDInput\">$ysf2dmrESSIDInput</option>\n";
		}
	}
	echo "</select>\n";
?>
    </td></tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['dmr_master'];?>:<span><b>DMR Master (YSF2DMR)</b>Set your prefered DMR master here</span></a></td>
    <td colspan="2" style="text-align: left;"><select name="ysf2dmrMasterHost">
<?php
        $testMMDVMysf2dmrMaster = $configysf2dmr['DMR Network']['Address'];
        while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHost = preg_split('/\s+/', $dmrMasterLine);
                if ((strpos($dmrMasterHost[0], '#') === FALSE ) && (substr($dmrMasterHost[0], 0, 3) != "XLX") && (substr($dmrMasterHost[0], 0, 4) != "DMRG") && (substr($dmrMasterHost[0], 0, 4) != "DMR2") && ($dmrMasterHost[0] != '')) {
                        if ($testMMDVMysf2dmrMaster == $dmrMasterHost[2]) { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\" selected=\"selected\">$dmrMasterHost[0]</option>\n"; $dmrMasterNow = $dmrMasterHost[0]; }
                        else { echo "      <option value=\"$dmrMasterHost[2],$dmrMasterHost[3],$dmrMasterHost[4],$dmrMasterHost[0]\">$dmrMasterHost[0]</option>\n"; }
                }
        }
        fclose($dmrMasterFile);
        ?>
    </select></td>
    </tr>
    <tr>
      <td align="left"><a class="tooltip2" href="#">Hotspot Security:<span><b>DMR Master Password</b>Override the Password for DMR with your own custom password, make sure you already configured this on your chosed DMR Master. Empty the field to use the default.</span></a></td>
      <td align="left" colspan="2">
        <input type="password" name="bmHSSecurity" size="30" maxlength="30" value="<?php if (isset($configModem['BrandMeister']['Password'])) {echo $configModem['BrandMeister']['Password'];} ?>"></input>
      </td>
    </tr>
    <tr>
      <td align="left"><a class="tooltip2" href="#">DMR TG:<span><b>YSF2DMR TG</b>Enter your DMR TG here</span></a></td>
      <td align="left" colspan="2"><input type="text" name="ysf2dmrTg" size="13" maxlength="7" value="<?php if (isset($configysf2dmr['DMR Network']['StartupDstId'])) { echo $configysf2dmr['DMR Network']['StartupDstId']; } ?>" /></td>  
    </tr>
    <?php } ?>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configysf2nxdn['Enabled']['Enabled'] == 1) { ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">(YSF2NXDN) NXDN ID:<span><b>NXDN ID</b>Enter your NXDN ID here</span></a></td>
      <td align="left" colspan="2"><input type="text" name="ysf2nxdnId" size="13" maxlength="5" value="<?php if (isset($configysf2nxdn['NXDN Network']['Id'])) { echo $configysf2nxdn['NXDN Network']['Id']; } ?>" /></td>
    </tr>
    <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['nxdn_startup_host'];?>:<span><b>NXDN Host</b>Set your prefered NXDN Host here</span></a></td>
        <td colspan="2" style="text-align: left;"><select name="ysf2nxdnStartupDstId">
<?php
	$nxdnHosts = fopen("/usr/local/etc/NXDNHosts.txt", "r");
	$testNXDNHost = $configysf2nxdn['NXDN Network']['StartupDstId'];
	if ($testNXDNHost == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
        else { echo "      <option value=\"none\">None</option>\n"; }
	if ($testNXDNHost == "10") { echo "      <option value=\"10\" selected=\"selected\">10 - Parrot</option>\n"; }
        else { echo "      <option value=\"10\">10 - Parrot</option>\n"; }
        while (!feof($nxdnHosts)) {
                $nxdnHostsLine = fgets($nxdnHosts);
                $nxdnHost = preg_split('/\s+/', $nxdnHostsLine);
                if ((strpos($nxdnHost[0], '#') === FALSE ) && ($nxdnHost[0] != '')) {
                        if ($testNXDNHost == $nxdnHost[0]) { echo "      <option value=\"$nxdnHost[0]\" selected=\"selected\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
                        else { echo "      <option value=\"$nxdnHost[0]\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
                }
        }
        fclose($nxdnHosts);
	if (file_exists('/usr/local/etc/NXDNHostsLocal.txt')) {
		$nxdnHosts2 = fopen("/usr/local/etc/NXDNHostsLocal.txt", "r");
		while (!feof($nxdnHosts2)) {
               		$nxdnHostsLine2 = fgets($nxdnHosts2);
               		$nxdnHost2 = preg_split('/\s+/', $nxdnHostsLine2);
               		if ((strpos($nxdnHost2[0], '#') === FALSE ) && ($nxdnHost2[0] != '')) {
               	        	if ($testNXDNHost == $nxdnHost2[0]) { echo "      <option value=\"$nxdnHost2[0]\" selected=\"selected\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
               	        	else { echo "      <option value=\"$nxdnHost2[0]\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
               		}
		}
	fclose($nxdnHosts2);
	}
?>
        </select></td>
      </tr>
    <?php } ?>
    <?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configysf2p25['Enabled']['Enabled'] == 1) { ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">(YSF2P25) <?php echo $lang['dmr_id'];?>:<span><b>DMR ID</b>Enter your CCS7 / DMR ID here</span></a></td>
      <td align="left" colspan="2"><input type="text" name="ysf2p25Id" size="13" maxlength="7" value="<?php if (isset($configysf2p25['P25 Network']['Id'])) { echo $configysf2p25['P25 Network']['Id']; } ?>" /></td>
    </tr>
    <tr>
      <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_startup_host'];?>:<span><b>P25 Host</b>Set your prefered P25 Host here</span></a></td>
      <td colspan="2" style="text-align: left;"><select name="ysf2p25StartupDstId">
<?php
	$p25Hosts = fopen("/usr/local/etc/P25Hosts.txt", "r");
	if (isset($configysf2p25['P25 Network']['StartupDstId'])) {
		$testP25Host = $configysf2p25['P25 Network']['StartupDstId'];
	} else {
		$testP25Host = "";
	}
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
        if (file_exists('/usr/local/etc/P25HostsLocal.txt')) {
		$p25Hosts2 = fopen("/usr/local/etc/P25HostsLocal.txt", "r");
		while (!feof($p25Hosts2)) {
                	$p25HostsLine2 = fgets($p25Hosts2);
                	$p25Host2 = preg_split('/\s+/', $p25HostsLine2);
                	if ((strpos($p25Host2[0], '#') === FALSE ) && ($p25Host2[0] != '')) {
                        	if ($testP25Host == $p25Host2[0]) { echo "      <option value=\"$p25Host2[0]\" selected=\"selected\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                        	else { echo "      <option value=\"$p25Host2[0]\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                	}
		}
		fclose($p25Hosts2);
	}
        ?>
    </select></td>
    </tr>
    <?php } ?>
	    
	    
	    
	    
	    
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && $configmmdvm['P25 Network']['Enable'] == 1) {
$p25Hosts = fopen("/usr/local/etc/P25Hosts.txt", "r");
	?>
	<h2><?php echo $lang['p25_config'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_startup_host'];?>:<span><b>P25 Host</b>Set your prefered P25 Host here</span></a></td>
    <td style="text-align: left;"><select name="p25StartupHost">
<?php
	if (isset($configp25gateway['Network']['Startup'])) { $testP25Host = $configp25gateway['Network']['Startup']; }
	elseif (isset($configp25gateway['Network']['Static'])) { $testP25Host = $configp25gateway['Network']['Static']; }
	else { $testP25Host = "none"; }
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
        if (file_exists('/usr/local/etc/P25HostsLocal.txt')) {
		$p25Hosts2 = fopen("/usr/local/etc/P25HostsLocal.txt", "r");
		while (!feof($p25Hosts2)) {
                	$p25HostsLine2 = fgets($p25Hosts2);
                	$p25Host2 = preg_split('/\s+/', $p25HostsLine2);
                	if ((strpos($p25Host2[0], '#') === FALSE ) && ($p25Host2[0] != '')) {
                        	if ($testP25Host == $p25Host2[0]) { echo "      <option value=\"$p25Host2[0]\" selected=\"selected\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                        	else { echo "      <option value=\"$p25Host2[0]\">$p25Host2[0] - $p25Host2[1]</option>\n"; }
                	}
		}
		fclose($p25Hosts2);
	}
        ?>
    </select></td>
    </tr>
<?php if ($configmmdvm['P25']['NAC']) { ?>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['p25_nac'];?>:<span><b>P25 NAC</b>Set your NAC code here</span></a></td>
    <td align="left"><input type="text" name="p25nac" size="13" maxlength="3" value="<?php echo $configmmdvm['P25']['NAC'];?>" /></td>
    </tr>
<?php } ?>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>
	
<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && ($configmmdvm['NXDN Network']['Enable'] == 1 || $configdmr2nxdn['Enabled']['Enabled'] == 1) ) { ?>
	<h2><?php echo $lang['nxdn_config'];?></h2>
    <table>
      <tr>
        <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
        <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['nxdn_startup_host'];?>:<span><b>NXDN Host</b>Set your prefered NXDN Host here</span></a></td>
        <td style="text-align: left;"><select name="nxdnStartupHost">
<?php
	if (file_exists('/etc/nxdngateway')) {
		$nxdnHosts = fopen("/usr/local/etc/NXDNHosts.txt", "r");
		if (isset($confignxdngateway['Network']['Startup'])) { $testNXDNHost = $confignxdngateway['Network']['Startup']; }
		elseif (isset($confignxdngateway['Network']['Static'])) { $testNXDNHost = $confignxdngateway['Network']['Static']; }
		else { $testNXDNHost = ""; }
		if ($testNXDNHost == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
	        else { echo "      <option value=\"none\">None</option>\n"; }
		if ($testNXDNHost == "10") { echo "      <option value=\"10\" selected=\"selected\">10 - Parrot</option>\n"; }
	        else { echo "      <option value=\"10\">10 - Parrot</option>\n"; }
	        while (!feof($nxdnHosts)) {
	                $nxdnHostsLine = fgets($nxdnHosts);
	                $nxdnHost = preg_split('/\s+/', $nxdnHostsLine);
	                if ((strpos($nxdnHost[0], '#') === FALSE ) && ($nxdnHost[0] != '')) {
	                        if ($testNXDNHost == $nxdnHost[0]) { echo "      <option value=\"$nxdnHost[0]\" selected=\"selected\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
	                        else { echo "      <option value=\"$nxdnHost[0]\">$nxdnHost[0] - $nxdnHost[1]</option>\n"; }
	                }
	        }
	        fclose($nxdnHosts);
		if (file_exists('/usr/local/etc/NXDNHostsLocal.txt')) {
			$nxdnHosts2 = fopen("/usr/local/etc/NXDNHostsLocal.txt", "r");
			while (!feof($nxdnHosts2)) {
                		$nxdnHostsLine2 = fgets($nxdnHosts2);
                		$nxdnHost2 = preg_split('/\s+/', $nxdnHostsLine2);
                		if ((strpos($nxdnHost2[0], '#') === FALSE ) && ($nxdnHost2[0] != '')) {
                	        	if ($testNXDNHost == $nxdnHost2[0]) { echo "      <option value=\"$nxdnHost2[0]\" selected=\"selected\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
                	        	else { echo "      <option value=\"$nxdnHost2[0]\">$nxdnHost2[0] - $nxdnHost2[1]</option>\n"; }
                		}
			}
		fclose($nxdnHosts2);
		}
	} else {
		echo '<option value="176.9.1.168">D2FET Test Host - 176.9.1.168</option>'."\n";
	}
?>
        </select></td>
      </tr>
    <?php if ($configmmdvm['NXDN']['RAN']) { ?>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['nxdn_ran'];?>:<span><b>NXDN RAN</b>Set your RAN code here, sane values are 1-64</span></a></td>
        <td align="left"><input type="text" name="nxdnran" size="13" maxlength="2" value="<?php echo $configmmdvm['NXDN']['RAN'];?>" /></td>
      </tr>
    <?php } ?>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>

<?php if (file_exists('/etc/dstar-radio.mmdvmhost') && ($configmmdvm['M17 Network']['Enable'] == 1) ) { ?>
	<h2><?php echo $lang['m17_config'];?></h2>
    <table>
      <tr>
        <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
        <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['m17_startup_host'];?>:<span><b>M17 Host</b>Set your prefered M17 Host here</span></a></td>
        <td style="text-align: left;"><select name="m17StartupHost">
<?php
	if (file_exists('/etc/m17gateway')) {
		$m17Hosts = fopen("/usr/local/etc/M17Hosts.txt", "r");
		if (isset($configm17gateway['Network']['Startup'])) { $testM17Host = explode("_", $configm17gateway['Network']['Startup'])[0]; }
		else { $testM17Host = ""; }
		if ($testM17Host == "") { echo "      <option value=\"none\" selected=\"selected\">None</option>\n"; }
	        else { echo "      <option value=\"none\">None</option>\n"; }
	        while (!feof($m17Hosts)) {
	                $m17HostsLine = fgets($m17Hosts);
	                $m17Host = preg_split('/\s+/', $m17HostsLine);
	                if ((strpos($m17Host[0], '#') === FALSE ) && ($m17Host[0] != '')) {
	                        if ($testM17Host == $m17Host[0]) { echo "      <option value=\"$m17Host[0]\" selected=\"selected\">$m17Host[0]</option>\n"; }
	                        else { echo "      <option value=\"$m17Host[0]\">$m17Host[0]</option>\n"; }
	                }
	        }
	        fclose($m17Hosts);
		if (file_exists('/root/M17Hosts.txt')) {
			$m17Hosts2 = fopen("/root/M17Hosts.txt", "r");
			while (!feof($m17Hosts2)) {
                		$m17HostsLine2 = fgets($m17Hosts2);
                		$m17Host2 = preg_split('/\s+/', $m17HostsLine2);
                		if ((strpos($m17Host2[0], '#') === FALSE ) && ($m17Host2[0] != '')) {
                	        	if ($testM17Host == $m17Host2[0]) { echo "      <option value=\"$m17Host2[0]\" selected=\"selected\">$m17Host2[0]</option>\n"; }
                	        	else { echo "      <option value=\"$m17Host2[0]\">$m17Host2[0]</option>\n"; }
                		}
			}
		fclose($m17Hosts2);
		}
	}
?>
        </select>
	<select name="m17StartupRoom">
	  <?php if (isset($configm17gateway['Network']['Startup'])) { echo "<option value=\"".substr($configm17gateway['Network']['Startup'], -1)."\" selected=\"selected\">".substr($configm17gateway['Network']['Startup'], -1)."</option>"; } ?>
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
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['m17_can'];?>:<span><b>M17 CAN</b>Set your CAN code here, sane values are 0-64</span></a></td>
        <td align="left"><input type="text" name="m17can" size="13" maxlength="2" value="<?php echo $configmmdvm['M17']['CAN'];?>" /></td>
      </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>

<?php if ( $configmmdvm['POCSAG']['Enable'] == 1 ) { ?>
	<h2><?php echo $lang['pocsag_config'];?></h2>
    <table>
      <tr>
        <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
        <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG Server:<span><b>POCSAG Server</b>Set the POCSAG Network here</span></a></td>
	<td style="text-align: left;"><select name="pocsagServer">
        	<option value="<?php echo $configdapnetgw['DAPNET']['Address'];?>" selected="selected"><?php echo $configdapnetgw['DAPNET']['Address'];?></option>
		<option value="dapnet.afu.rwth-aachen.de">dapnet.afu.rwth-aachen.de</option>
		<option value="db0dbn.ig-funk-siebengebirge.de">db0dbn.ig-funk-siebengebirge.de</option>
		<option value="dapnet.db0sda.ampr.org">dapnet.db0sda.ampr.org (HAMNET)</option>
		<option value="node1.dapnet-italia.it">node1.dapnet-italia.it</option>
		</select></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG <?php echo $lang['node_call'];?>:<span><b>POCSAG Callsign</b>Set your paging callsign here</span></a></td>
        <td align="left"><input type="text" name="pocsagCallsign" size="13" maxlength="12" value="<?php echo $configdapnetgw['General']['Callsign'];?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG <?php echo $lang['radio_freq'];?>:<span><b>POCSAG Frequency</b>Set your paging frequency here</span></a></td>
        <td align="left"><input type="text" id="pocsagFrequency" onkeyup="checkFrequency(); return false;" name="pocsagFrequency" size="13" maxlength="12" value="<?php echo number_format($configmmdvm['POCSAG']['Frequency'], 0, '.', '.');?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">DAPNET AuthKey:<span><b>DAPNET AuthKey</b>Set your DAPNET AuthKey here</span></a></td>
        <td align="left"><input type="password" name="pocsagAuthKey" size="30" maxlength="50" value="<?php echo $configdapnetgw['DAPNET']['AuthKey'];?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG Whitelist:<span><b>POCSAG Whitelist</b>Set your POCSAG RIC Whitelist here, if these are set ONLY these RICs will be transmitted. List is comma seperated.</span></a></td>
        <td align="left"><input type="text" name="pocsagWhitelist" size="60" maxlength="350" value="<?php if (isset($configdapnetgw['General']['WhiteList'])) { echo $configdapnetgw['General']['WhiteList']; } ?>" /></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#">POCSAG Blacklist:<span><b>POCSAG Blacklist</b>Set your POCSAG RIC Blacklist here, if these are set any other RIC will be transmitted, but not these. List is comma seperated.</span></a></td>
        <td align="left"><input type="text" name="pocsagBlacklist" size="60" maxlength="350" value="<?php if (isset($configdapnetgw['General']['BlackList'])) { echo $configdapnetgw['General']['BlackList']; } ?>" /></td>
      </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>

<?php if ( file_exists('/etc/mobilegps') && file_exists('/etc/dstar-radio.mmdvmhost') ) { ?>
    <input type="hidden" name="mobilegps_enable" value="OFF" />
    <h2><?php echo $lang['mobilegps_config'];?></h2>
    <table>
      <tr>
        <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
        <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['mobilegps_enable'];?>:<span><b>MobileGPS</b>Enable the MobileGPS service, this uses a GPS dongle to update the position information in MMDVMHost.</span></a></td>
	<?php
	if ( isset($configmmdvm['Mobile GPS']['Enable']) ) {
		if ( $configmmdvm['Mobile GPS']['Enable'] ) {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-confMobilegps_enable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"mobilegps_enable\" value=\"ON\" checked=\"checked\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleMobilegps_enableCr." /><label id=\"aria-toggle-confMobilegps_enable\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Mobile GPS\" aria-checked=\"true\" onKeyPress=\"toggleMobilegps_enable()\" onclick=\"toggleMobilegps_enable()\" for=\"toggle-confMobilegps_enable\"><font style=\"font-size:0px\">Mobile GPS</font></label></div></td>\n";
		}
		else {
			echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-confMobilegps_enable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"mobilegps_enable\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleMobilegps_enableCr." /><label id=\"aria-toggle-confMobilegps_enable\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Mobile GPS\" aria-checked=\"false\" onKeyPress=\"toggleMobilegps_enable()\" onclick=\"toggleMobilegps_enable()\" for=\"toggle-confMobilegps_enable\"><font style=\"font-size:0px\">Mobile GPS</font></label></div></td>\n";
		}
	} else {
		echo "<td align=\"left\"><div class=\"switch\"><input id=\"toggle-confMobilegps_enable\" class=\"toggle toggle-round-flat\" type=\"checkbox\" name=\"mobilegps_enable\" value=\"ON\" aria-hidden=\"true\" tabindex=\"-1\" ".$toggleMobilegps_enableCr." /><label id=\"aria-toggle-confMobilegps_enable\" role=\"checkbox\" tabindex=\"0\" aria-label=\"Mobile GPS\" aria-checked=\"false\" onKeyPress=\"toggleMobilegps_enable()\" onclick=\"toggleMobilegps_enable()\" for=\"toggle-confMobilegps_enable\"><font style=\"font-size:0px\">Mobile GPS</font></label></div></td>\n";
	}
	?>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['mobilegps_port'];?>:<span><b>GPS Port</b>The port used by the Mobile GPS service.</span></a></td>
	<td align="left"><select name="mobilegps_port">
		<option value="<?php echo exec('grep "Port=/dev/" /etc/mobilegps | awk -F "=" \'{print $2}\' | awk -F "/" \'{print $3}\''); ?>" selected="selected"><?php echo exec('grep "Port=/dev/" /etc/mobilegps | awk -F "=" \'{print $2}\''); ?></option>
		<?php
		  exec('ls /dev/ | egrep -h "ttyA|ttyUSB"', $mobileGPSPorts);
		  foreach($mobileGPSPorts as $port) {
			  echo "		<option value=\"$port\">/dev/$port</option>\n";
		  }
		?>
		</select></td>
      </tr>
      <tr>
        <td align="left"><a class="tooltip2" href="#"><?php echo $lang['mobilegps_speed'];?>:<span><b>GPS Port Speed</b>Port speed for the Mobile GPS port.</span></a></td>
	<td align="left"><input type="text" name="mobilegps_speed" size="13" maxlength="6" value="<?php echo exec('grep "Speed" /etc/mobilegps | awk -F "=" \'{print $2}\''); ?>" /></td>
      </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /><br /><br /></div>
<?php } ?>

	<h2><?php echo $lang['fw_config'];?></h2>
    <table>
    <tr>
    <th width="200"><a class="tooltip" href="#"><?php echo $lang['setting'];?><span><b>Setting</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['value'];?><span><b>Value</b>The current value from the<br />configuration files</span></a></th>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['fw_dash'];?>:<span><b>Dashboard Access</b>Do you want the dashboard access to be publicly available? This modifies the uPNP firewall configuration.</span></a></td>
    <?php
	$testPrvPubDash = exec('sudo grep "80 80" /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubDash, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"dashAccess\" value=\"PRV\" checked=\"checked\" />Private <input type=\"radio\" name=\"dashAccess\" value=\"PUB\" />Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"dashAccess\" value=\"PRV\" />Private <input type=\"radio\" name=\"dashAccess\" value=\"PUB\" checked=\"checked\" />Public</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['fw_irc'];?>:<span><b>ircDDBGateway Remote</b>Do you want the ircDDBGateway remote control access to be publicly available? This modifies the uPNP firewall Configuration.</span></a></td>
    <?php
	$testPrvPubIRC = exec('sudo grep "10022 10022" /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubIRC, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"ircRCAccess\" value=\"PRV\" checked=\"checked\" />Private <input type=\"radio\" name=\"ircRCAccess\" value=\"PUB\" />Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"ircRCAccess\" value=\"PRV\" />Private <input type=\"radio\" name=\"ircRCAccess\" value=\"PUB\" checked=\"checked\" />Public</td>\n";
	}
    ?>
    </tr>
    <tr>
    <td align="left"><a class="tooltip2" href="#"><?php echo $lang['fw_ssh'];?>:<span><b>SSH Access</b>Do you want access to be publicly available over SSH (used for support issues)? This modifies the uPNP firewall Configuration.</span></a></td>
    <?php
	$testPrvPubSSH = exec('sudo grep "22 22" /usr/local/sbin/pistar-upnp.service | cut -c 1');
	if (substr($testPrvPubSSH, 0, 1) === '#') {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"sshAccess\" value=\"PRV\" checked=\"checked\" />Private <input type=\"radio\" name=\"sshAccess\" value=\"PUB\" />Public</td>\n";
		}
	else {
		echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"sshAccess\" value=\"PRV\" />Private <input type=\"radio\" name=\"sshAccess\" value=\"PUB\" checked=\"checked\" />Public</td>\n";
	}
    ?>
    </tr>
    <?php if (file_exists('/etc/default/hostapd')) { ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">Auto AP:<span><b>Auto AP</b>Do you want your Pi to create its own AP if it cannot connect to WiFi within 120 secs of boot</span></a></td>
      <?php
        if (file_exists('/etc/hostap.off')) {
	  echo "   <td align=\"left\"><input type=\"radio\" name=\"autoAP\" value=\"ON\" />On <input type=\"radio\" name=\"autoAP\" value=\"OFF\" checked=\"checked\" />Off</td>\n";
	}
        else {
	  echo "   <td align=\"left\"><input type=\"radio\" name=\"autoAP\" value=\"ON\" checked=\"checked\" />On <input type=\"radio\" name=\"autoAP\" value=\"OFF\" />Off</td>\n";
	}
      ?>
      <td>Note: Reboot Required if changed</td>
    </tr>
    <?php } ?>
    <tr>
      <td align="left"><a class="tooltip2" href="#">uPNP:<span><b>uPNP</b>Do you want your Pi to create its own Firewall rules for use with D-Star.</span></a></td>
      <?php
        $testupnp = exec('grep "pistar-upnp.service" /etc/crontab | cut -c 1');
	if (substr($testupnp, 0, 1) === '#') {
	  echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"uPNP\" value=\"ON\" />On <input type=\"radio\" name=\"uPNP\" value=\"OFF\" checked=\"checked\" />Off</td>\n";
	}
        else {
	  echo "   <td align=\"left\" colspan=\"2\"><input type=\"radio\" name=\"uPNP\" value=\"ON\" checked=\"checked\" />On <input type=\"radio\" name=\"uPNP\" value=\"OFF\" />Off</td>\n";
	}
      ?>
    </tr>
    </table>
	<div><input type="button" value="<?php echo $lang['apply'];?>" onclick="submitform()" /></div>
    </form>

<?php
	if ( file_exists('/sys/class/net/wlan0') || file_exists('/sys/class/net/wlan1') || file_exists('/sys/class/net/wlan0_ap') ) {
echo '
<br />
    <h2>'.$lang['wifi_config'].'</h2>
    <table><tr><td>
    <iframe frameborder="0" scrolling="auto" name="wifi" src="wifi.php?page=wlan0_info" width="100%" onload="javascript:resizeIframe(this);">If you can see this message, your browser does not support iFrames, however if you would like to see the content please click <a href="wifi.php?page=wlan0_info">here</a>.</iframe>
    </td></tr></table>
    <br />
    <form id="autoApPassForm" action="'.htmlspecialchars($_SERVER["PHP_SELF"]).'" method="post">
    <table>
    <tr><th width="200">Auto AP SSID</th><th colspan="3">PSK</th></tr>
    <tr>
    <td align="left"><b>'.php_uname('n').'</b></td>
    <td align="left"><label for="psk1">PSK:</label><input type="password" name="autoapPsk" id="psk1" onkeyup="checkPsk(); return false;" size="20" />
    <label for="psk2">Confirm PSK:</label><input type="password" name="autoapPsk" id="psk2" onkeyup="checkPskMatch(); return false;" />
    <br /><span id="confirmMessage" class="confirmMessage"></span></td>
    <td align="right"><input type="button" id="submitpsk" value="Set PSK" onclick="submitPskform()" disabled="disabled" /></td>
    </tr>
    </table>
    </form>';} ?>

<br />
	<h2><?php echo $lang['remote_access_pw'];?></h2>
    <form id="adminPassForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <table>
    <tr><th width="200"><?php echo $lang['user'];?></th><th colspan="3"><?php echo $lang['password'];?></th></tr>
    <tr>
    <td align="left"><b>pi-star</b></td>
    <td align="left"><label for="pass1">Password:</label><input type="password" name="adminPassword" id="pass1" onkeyup="checkPass(); return false;" size="20" />
    <label for="pass2">Confirm Password:</label><input type="password" name="adminPassword" id="pass2" onkeyup="checkPass(); return false;" />
    <br /><span id="confirmMessage" class="confirmMessage"></span></td>
    <td align="right"><input type="button" id="submitpwd" value="<?php echo $lang['set_password'];?>" onclick="submitPassform()" disabled="disabled" /></td>
    </tr>
    <tr><td colspan="3"><b>WARNING:</b> This changes the password for this admin page<br />AND the "pi-star" SSH account</td></tr>
    </table>
    </form>
<?php endif; ?>
<br />
</div>
<div class="footer">
Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
<br />
</div>
</div>
<script type="text/javascript" src="/nice-select.min.js"></script>
<script type="text/javascript">
    var selectize = document.querySelectorAll('select')
    var options = {searchable: true};
    selectize.forEach(function(select){
        if( select.length > 30 && null === select.onchange && !select.name.includes("ExtendedId") ) {
            select.classList.add("small", "selectize");
            tabletd = select.closest('td');
            tabletd.style.cssText = 'overflow-x:unset';
            NiceSelect.bind(select, options);
        }
    });
</script>
</body>
</html>

<?php } else { ?>
<br />
<br />
</div>
<div class="footer">
Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
<br />
</div>
</div>
<script type="text/javascript" src="/nice-select.min.js"></script>
<script type="text/javascript">
    var selectize = document.querySelectorAll('select')
    var options = {searchable: true};
    selectize.forEach(function(select){
        if( select.length > 30 && null === select.onchange && !select.name.includes("ExtendedId") ) {
            select.classList.add("small", "selectize");
            tabletd = select.closest('td');
            tabletd.style.cssText = 'overflow-x:unset';
            NiceSelect.bind(select, options);
        }
    });
</script>
</body>
</html>
<?php } ?>
