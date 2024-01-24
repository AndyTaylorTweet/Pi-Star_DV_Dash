<?php
require_once('config/version.php');
require_once('config/ircddblocal.php');
require_once('config/language.php');
$configs = array();
if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
                list($key,$value) = preg_split('/=/',$line);
                $value = trim(str_replace('"','',$value));
                if ($key != 'ircddbPassword' && strlen($value) > 0)
                $configs[$key] = $value;
        }

}
$progname = basename($_SERVER['SCRIPT_FILENAME'],".php");
$rev=$version;
//$MYCALL=strtoupper($callsign);
$MYCALL=strtoupper($configs['gatewayCallsign']);

// Check if the config file exists
if (file_exists('/etc/pistar-css.ini')) {
	$piStarCssFile = '/etc/pistar-css.ini';
	if (fopen($piStarCssFile,'r')) { $piStarCss = parse_ini_file($piStarCssFile, true); }
	if ($piStarCss['BannerH1']['Enabled']) {
		$piStarCssBannerH1 = $piStarCss['BannerH1']['Text'];
	}
	if ($piStarCss['BannerExtText']['Enabled']) {
		$piStarCssBannerExtTxt = $piStarCss['BannerExtText']['Text'];
	}
}

//Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
<head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php echo "<meta name=\"generator\" content=\"$progname $rev\" />\n"; ?>
    <meta name="Author" content="Hans-J. Barthen (DL5DI), Kim Huebel (DG9VH) and Andy Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Dashboard" />
    <meta name="KeyWords" content="MW0MWZ,MMDVMHost,ircDDBGateway,D-Star,ircDDB,Pi-Star,Blackwood,Wales,DL5DI,DG9VH" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="css/nice-select.min.css?ver=<?php echo $configPistarRelease['Pi-Star']['Version']; ?>" />
    <title><?php echo "$MYCALL"." - ".$lang['digital_voice']." ".$lang['dashboard'];?></title>
<?php include_once "config/browserdetect.php"; ?>
    <script type="text/javascript" src="/jquery.min.js"></script>
    <script type="text/javascript" src="/functions.js"></script>
    <script type="text/javascript">
      $.ajaxSetup({ cache: false });
    </script>
</head>
<body>
<?php
if ( ($_SERVER["PHP_SELF"] == "/admin/index.php") && ($configPistarRelease['Pi-Star']['Version'] < 4.1) && ($configPistarRelease['Pi-Star']['Hardware'] == "RPi") ) {
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
if ( ($_SERVER["PHP_SELF"] == "/admin/index.php") && ($configPistarRelease['Pi-Star']['Version'] >= "4.1") && ($configPistarRelease['Pi-Star']['Version'] < "4.1.6") ) {
?>
<div>
  <table align="center" width="760px" style="margin: 0px 0px 10px 0px; width: 100%;">
    <tr>
    <td align="center" valign="top" style="background-color: #ffff90; color: #906000;">Alert: An upgrade to Pi-Star has been released, click here to upgrade now: <a href="/admin/expert/upgrade.php" alt="Upgrade Pi-Star">Upgrade Pi-Star</a>.</td>
    </tr>
  </table>
</div>
<?php } ?>
<div class="container">
<div class="header">
<div style="font-size: 8px; text-align: left; padding-left: 8px; float: left;">Hostname: <?php echo exec('cat /etc/hostname'); ?></div><div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / <?php echo $lang['dashboard'].": ".$version; ?></div>
<h1>Pi-Star <?php echo $lang['digital_voice']." ".$lang['dashboard_for']." ".$MYCALL; ?></h1>
<?php if (isset($piStarCssBannerH1)) { echo "<h1>".$piStarCssBannerH1."</h1>\n"; } ?>
<?php if (isset($piStarCssBannerExtTxt)) { echo "<p style=\"text-align: center; color: #ffffff;\">".$piStarCssBannerExtTxt."</p>\n"; }?>
<p style="padding-right: 5px; text-align: right; color: #ffffff;">
 <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
 <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
<?php if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
  echo ' <a href="/admin/live_modem_log.php" style="color: #ffffff;">'.$lang['live_logs'].'</a> |'."\n";
  echo ' <a href="/admin/power.php" style="color: #ffffff;">'.$lang['power'].'</a> |'."\n";
  echo ' <a href="/admin/update.php" style="color: #ffffff;">'.$lang['update'].'</a> |'."\n";
  } ?>
 <a href="/admin/configure.php" style="color: #ffffff;"><?php echo $lang['configuration'];?></a>
</p>
</div>

<?php
// Output some default features
if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
	echo '<div class="contentwide">'."\n";
	echo '<script type="text/javascript">'."\n";
	echo 'function reloadSysInfo(){'."\n";
	echo '  $("#sysInfo").load("/dstarrepeater/system.php",function(){ setTimeout(reloadSysInfo,15000) });'."\n";
	echo '}'."\n";
	echo 'setTimeout(reloadSysInfo,15000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
	echo '</script>'."\n";
	echo '<div id="sysInfo">'."\n";
	include 'dstarrepeater/system.php';				// Basic System Info
	echo '</div>'."\n";
	echo '</div>'."\n";
	}
// First lets figure out if we are in MMDVMHost mode, or dstarrepeater mode;
if (file_exists('/etc/dstar-radio.mmdvmhost')) {
	include 'config/config.php';					// MMDVMDash Config
	include_once 'mmdvmhost/tools.php';				// MMDVMDash Tools

	function getMMDVMConfigFileContent() {
		// loads /etc/mmdvmhost into array for further use
		$conf = array();
		if ($configs = @fopen('/etc/mmdvmhost', 'r')) {
			while ($config = fgets($configs)) {
				array_push($conf, trim ( $config, " \t\n\r\0\x0B"));
			}
			fclose($configs);
		}
		return $conf;
	}
	$mmdvmconfigfile = getMMDVMConfigFileContent();

	echo '<div class="nav">'."\n";					// Start the Side Menu
	echo '<script type="text/javascript">'."\n";
	echo 'function reloadRepeaterInfo(){'."\n";
	echo '  $("#repeaterInfo").load("/mmdvmhost/repeaterinfo.php",function(){ setTimeout(reloadRepeaterInfo,1000) });'."\n";
	echo '}'."\n";
	echo 'setTimeout(reloadRepeaterInfo,1000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
	echo '</script>'."\n";
	echo '<div id="repeaterInfo">'."\n";
	include 'mmdvmhost/repeaterinfo.php';				// MMDVMDash Repeater Info
	echo '</div>'."\n";
	echo '</div>'."\n";

	echo '<div class="content">'."\n";

	$testMMDVModeDSTARnet = getConfigItem("D-Star Network", "Enable", $mmdvmconfigs);
        if ( $testMMDVModeDSTARnet == 1 ) {				// If D-Star network is enabled, add these extra features.

	if ($_SERVER["PHP_SELF"] == "/admin/index.php") { 		// Admin Only Option
		echo '<script type="text/javascript">'."\n";
		echo 'function reloadrefLinks(){'."\n";
		echo '  $("#refLinks").load("/dstarrepeater/active_reflector_links.php",function(){ setTimeout(reloadrefLinks,15000) });'."\n";
		echo '}'."\n";
		echo 'setTimeout(reloadrefLinks,15000);'."\n";
		echo '$(window).trigger(\'resize\');'."\n";
		echo '</script>'."\n";
		echo '<div id="refLinks">'."\n";
		include 'dstarrepeater/active_reflector_links.php';	// dstarrepeater gateway config
	        echo '</div>'."\n";
	        echo '<br />'."\n";

		include 'dstarrepeater/link_manager.php';		// D-Star Link Manager
		echo "<br />\n";
		}

        echo '<script type="text/javascript">'."\n";
        echo 'function reloadcssConnections(){'."\n";
        echo '  $("#cssConnects").load("/dstarrepeater/css_connections.php",function(){ setTimeout(reloadcssConnections,15000) });'."\n";
        echo '}'."\n";
        echo 'setTimeout(reloadcssConnections,15000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
        echo '</script>'."\n";
        echo '<div id="cssConnects">'."\n";
	include 'dstarrepeater/css_connections.php';			// dstarrepeater gateway config
	echo '</div>'."\n";
	}

	if ($_SERVER["PHP_SELF"] == "/admin/index.php") { 		// Admin Only Option
		echo '<script type="text/javascript">'."\n";
        	echo 'function reloadbmConnections(){'."\n";
        	echo '  $("#bmConnects").load("/mmdvmhost/bm_links.php",function(){ setTimeout(reloadbmConnections,180000) });'."\n";
        	echo '}'."\n";
        	echo 'setTimeout(reloadbmConnections,180000);'."\n";
		echo '$(window).trigger(\'resize\');'."\n";
        	echo '</script>'."\n";
        	echo '<div id="bmConnects">'."\n";
		include 'mmdvmhost/bm_links.php';                       // BM Links
		echo '</div>'."\n";
	}
	if ($_SERVER["PHP_SELF"] == "/admin/index.php") {               // Admin Only Options
                include 'mmdvmhost/bm_manager.php';                     // BM DMR Link Manager
        }
	if ($_SERVER["PHP_SELF"] == "/admin/index.php") { 		// Admin Only Option
		echo '<script type="text/javascript">'."\n";
        	echo 'function reloadtgifConnections(){'."\n";
        	echo '  $("#tgifConnects").load("/mmdvmhost/tgif_links.php",function(){ setTimeout(reloadtgifConnections,180000) });'."\n";
        	echo '}'."\n";
        	echo 'setTimeout(reloadtgifConnections,180000);'."\n";
		echo '$(window).trigger(\'resize\');'."\n";
        	echo '</script>'."\n";
        	echo '<div id="tgifConnects">'."\n";
		include 'mmdvmhost/tgif_links.php';			// TGIF Links
		echo '</div>'."\n";
	}
	if ($_SERVER["PHP_SELF"] == "/admin/index.php") {               // Admin Only Options
                include 'mmdvmhost/tgif_manager.php';			// TGIF DMR Link Manager
        }
	$testMMDVModeYSFnet = getConfigItem("System Fusion Network", "Enable", $mmdvmconfigs);
        if ( $testMMDVModeYSFnet == 1 ) {				// If YSF network is enabled, add these extra features.
		if ($_SERVER["PHP_SELF"] == "/admin/index.php") { 	// Admin Only Option
			include 'mmdvmhost/ysf_manager.php';		// YSF Links
		}
	}
	$testMMDVModeP25net = getConfigItem("P25 Network", "Enable", $mmdvmconfigs);
        if ( $testMMDVModeP25net == 1 ) {				// If P25 network is enabled, add these extra features.
		if ($_SERVER["PHP_SELF"] == "/admin/index.php") { 	// Admin Only Option
			include 'mmdvmhost/p25_manager.php';		// P25 Links
		}
	}
	$testMMDVModeNXDNnet = getConfigItem("NXDN Network", "Enable", $mmdvmconfigs);
        if ( $testMMDVModeNXDNnet == 1 ) {				// If NXDN network is enabled, add these extra features.
		if ($_SERVER["PHP_SELF"] == "/admin/index.php") { 	// Admin Only Option
			include 'mmdvmhost/nxdn_manager.php';		// NXDN Links
		}
	}
	$testMMDVModeM17net = getConfigItem("M17 Network", "Enable", $mmdvmconfigs);
        if ( $testMMDVModeM17net == 1 ) {				// If NXDN network is enabled, add these extra features.
		if ($_SERVER["PHP_SELF"] == "/admin/index.php") { 	// Admin Only Option
			include 'mmdvmhost/m17_manager.php';		// M17 Links
		}
	}
	echo '<script type="text/javascript">'."\n";
	echo 'function reloadLocalTx(){'."\n";
	echo '  $("#localTxs").load("/mmdvmhost/localtx.php",function(){ setTimeout(reloadLocalTx,1500) });'."\n";
	echo '}'."\n";
	echo 'setTimeout(reloadLocalTx,1500);'."\n";
	echo 'function reloadLastHerd(){'."\n";
	echo '  $("#lastHerd").load("/mmdvmhost/lh.php",function(){ setTimeout(reloadLastHerd,1500) });'."\n";
	echo '}'."\n";
	echo 'setTimeout(reloadLastHerd,1500);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
	echo '</script>'."\n";
	echo '<div id="lastHerd">'."\n";
	include 'mmdvmhost/lh.php';					// MMDVMDash Last Herd
	echo '</div>'."\n";
	echo "<br />\n";
	echo '<div id="localTxs">'."\n";
	include 'mmdvmhost/localtx.php';				// MMDVMDash Local Trasmissions
	echo '</div>'."\n";
	
	// If POCSAG is enabled, show the information pannel
	$testMMDVModePOCSAG = getConfigItem("POCSAG Network", "Enable", $mmdvmconfigfile);
	if ( $testMMDVModePOCSAG == 1 ) {
		echo '<script type="text/javascript">'."\n";
		echo 'function reloadPages(){'."\n";
		echo '  $("#Pages").load("/mmdvmhost/pages.php",function(){ setTimeout(reloadPages,5000) });'."\n";
		echo '}'."\n";
		echo 'setTimeout(reloadPages,5000);'."\n";
		echo '$(window).trigger(\'resize\');'."\n";
		echo '</script>'."\n";
		echo "<br />\n";
		echo '<div id="Pages">'."\n";
		include 'mmdvmhost/pages.php';				// POCSAG Messages
		echo '</div>'."\n";
	}

} elseif (file_exists('/etc/dstar-radio.dstarrepeater')) {
        echo '<div class="contentwide">'."\n";
	include 'dstarrepeater/gateway_software_config.php';		// dstarrepeater gateway config
	echo '<script type="text/javascript">'."\n";
	echo 'function reloadrefLinks(){'."\n";
	echo '  $("#refLinks").load("/dstarrepeater/active_reflector_links.php",function(){ setTimeout(reloadrefLinks,15000) });'."\n";
	echo '}'."\n";
	echo 'setTimeout(reloadrefLinks,15000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
	echo '</script>'."\n";
        echo '<br />'."\n";
	echo '<div id="refLinks">'."\n";
	include 'dstarrepeater/active_reflector_links.php';		// dstarrepeater gateway config
        echo '</div>'."\n";
        echo '<br />'."\n";
	if ($_SERVER["PHP_SELF"] == "/admin/index.php") {		// Admin Only Options
		include 'dstarrepeater/link_manager.php';		// D-Star Link Manager
		echo "<br />\n";
		}

	echo '<script type="text/javascript">'."\n";
        echo 'function reloadcssConnections(){'."\n";
        echo '  $("#cssConnects").load("/dstarrepeater/css_connections.php",function(){ setTimeout(reloadcssConnections,15000) });'."\n";
        echo '}'."\n";
        echo 'setTimeout(reloadcssConnections,15000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
        echo '</script>'."\n";
        echo '<div id="cssConnects">'."\n";
	include 'dstarrepeater/css_connections.php';			// dstarrepeater gateway config
	echo '</div>'."\n";

	echo '<script type="text/javascript">'."\n";
	echo 'function reloadLocalTx(){'."\n";
	echo '  $("#localTx").load("/dstarrepeater/local_tx.php",function(){ setTimeout(reloadLocalTx,3000) });'."\n";
	echo '}'."\n";
	echo 'setTimeout(reloadLocalTx,3000);'."\n";
	echo 'function reloadLastHerd(){'."\n";
	echo '  $("#lh").load("/dstarrepeater/last_herd.php",function(){ setTimeout(reloadLastHerd,3000) });'."\n";
	echo '}'."\n";
	echo 'setTimeout(reloadLastHerd,3000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
	echo '</script>'."\n";
	echo '<div id="lh">'."\n";
	include 'dstarrepeater/last_herd.php';				//dstarrepeater Last Herd
        echo '</div>'."\n";
	echo "<br />\n";
	echo '<div id="localTx">'."\n";
	include 'dstarrepeater/local_tx.php';				//dstarrepeater Local Transmissions
        echo '</div>'."\n";
        echo '<br />'."\n";

} else {
	echo '<div class="contentwide">'."\n";
	//We dont know what mode we are in - fail...
	echo "<H1>No Mode Defined...</H1>\n";
	echo "<p>I don't know what mode I am in, you probably just need to configure me.</p>\n";
	echo "<p>You will be re-directed to the configuration portal in 10 secs</p>\n";
	echo "<p>In the meantime, you might want to register on the support<br />\n";
	echo "page here: <a href=\"https://www.facebook.com/groups/pistarusergroup/\" target=\"_new\">https://www.facebook.com/groups/pistarusergroup/</a><br />\n";
	echo "or the Support forum here: <a href=\"https://forum.pistar.uk/\" target=\"_new\">https://forum.pistar.uk/</a></p>\n";
	echo '<script type="text/javascript">setTimeout(function() { window.location="/admin/configure.php";},10000);</script>'."\n";
}
?>
</div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
ircDDBGateway Dashboard by Hans-J. Barthen (DL5DI),<br />
MMDVMDash developed by Kim Huebel (DG9VH), <br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Facebook Group</a><br />
or Click <a style="color: #ffffff;" href="https://forum.pistar.uk/" target="_new">here to join the Support Forum</a><br />	
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_new">here</a>.<br />
</div>

</div>
</body>
<script type="text/javascript" src="/nice-select.min.js"></script>
<script type="text/javascript">
    var selectize = document.querySelectorAll('select')
    var options = {searchable: true};
    selectize.forEach(function(select){
        if( select.length > 30 && null === select.onchange ) {
            select.classList.add("small", "selectize");
            tabletd = select.closest('td');
            tabletd.style.cssText = 'overflow-x:unset';
            NiceSelect.bind(select, options);
        }
    });
</script>
</html>
