<?php
require_once('config/version.php');
require_once('config/ircddblocal.php');
$configs = array();
if ($configfile = fopen($gatewayConfigPath,'r')) {
        while ($line = fgets($configfile)) {
                list($key,$value) = split("=",$line);
                $value = trim(str_replace('"','',$value));
                if ($key != 'ircddbPassword' && strlen($value) > 0)
                $configs[$key] = $value;
        }

}
$progname = basename($_SERVER['SCRIPT_FILENAME'],".php");
$rev=$version;
$MYCALL=strtoupper($callsign);

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
    <meta name="Author" content="Hans-J. Barthen (DL5DI), Kim Huebel (DV9VH) and Andy Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Dashboard" />
    <meta name="KeyWords" content="MW0MWZ,MMDVMHost,ircDDBGateway,D-Star,ircDDB,Pi-Star,Blackwood,Wales,DL5DI,DG9VH" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="pragma" content="no-cache" />
    <title><?php echo "$MYCALL" ?> - Digital Voice Dashboard</title>
<?php include_once "config/browserdetect.php"; ?>
    <script type="text/javascript" src="/jquery.min.js"></script>
    <script type="text/javascript" src="/functions.js"></script>
    <script type="text/javascript">
      $.ajaxSetup({ cache: false });
    </script>
</head>
<body>
<div class="container">
<div class="header">
<div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
<h1>Pi-Star Digital Voice Dashboard for <?php echo $MYCALL; ?></h1>
<p style="padding-right: 5px; text-align: right; color: #ffffff;">
 <a href="/" style="color: #ffffff;">Dashboard</a> |
 <a href="/admin/" style="color: #ffffff;">Admin</a> |
<?php if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
  echo ' <a href="/admin/live_modem_log.php" style="color: #ffffff;">Live Logs</a> |'."\n";
  echo ' <a href="/admin/power.php" style="color: #ffffff;">Power</a> |'."\n";
  echo ' <a href="/admin/update.php" style="color: #ffffff;">Update</a> |'."\n";
  } ?>
 <a href="/admin/configure.php" style="color: #ffffff;">Config</a>
</p>
</div>

<?php
// Output some default features
if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
	echo '<div class="contentwide">'."\n";
	echo '<script type="text/javascript">'."\n";
	echo 'function reloadSysInfo(){'."\n";
	echo '  $("#sysInfo").load("/dstarrepeater/system.php");'."\n";
	echo '}'."\n";
	echo 'setInterval(function(){reloadSysInfo()}, 15000);'."\n";
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
	include 'mmdvmhost/tools.php';					// MMDVMDash Tools
	include 'mmdvmhost/functions.php';				// MMDVMDash Functions

	echo '<div class="nav">'."\n";					// Start the Side Menu
	echo '<script type="text/javascript">'."\n";
	echo 'function reloadRepeaterInfo(){'."\n";
	echo '  $("#repeaterInfo").load("/mmdvmhost/repeaterinfo.php");'."\n";
	echo '}'."\n";
	echo 'setInterval(function(){reloadRepeaterInfo()}, 1000);'."\n";
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
		echo '  $("#refLinks").load("/dstarrepeater/active_reflector_links.php");'."\n";
		echo '}'."\n";
		echo 'setInterval(function(){reloadrefLinks()}, 2500);'."\n";
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
        echo '  $("#cssConnects").load("/dstarrepeater/css_connections.php");'."\n";
        echo '}'."\n";
        echo 'setInterval(function(){reloadcssConnections()}, 15000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
        echo '</script>'."\n";
        echo '<div id="cssConnects">'."\n";
	include 'dstarrepeater/css_connections.php';			// dstarrepeater gateway config
	echo '</div>'."\n";
	}

	echo '<script type="text/javascript">'."\n";
	echo 'function reloadLastHerd(){'."\n";
	echo '  $("#lastHerd").load("/mmdvmhost/lh.php");'."\n";
	echo '}'."\n";
	echo 'setInterval(function(){reloadLastHerd()}, 1500);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
	echo '</script>'."\n";
	echo '<div id="lastHerd">'."\n";
	include 'mmdvmhost/lh.php';					// MMDVMDash Last Herd
	echo '</div>'."\n";
	echo "<br />\n";

	echo '<script type="text/javascript">'."\n";
	echo 'function reloadLocalTx(){'."\n";
	echo '  $("#localTxs").load("/mmdvmhost/localtx.php");'."\n";
	echo '}'."\n";
	echo 'setInterval(function(){reloadLocalTx()}, 1500);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
	echo '</script>'."\n";
	echo '<div id="localTxs">'."\n";
	include 'mmdvmhost/localtx.php';				// MMDVMDash Local Trasmissions
	echo '</div>'."\n";


} elseif (file_exists('/etc/dstar-radio.dstarrepeater')) {
        echo '<div class="contentwide">'."\n";
	include 'dstarrepeater/gateway_software_config.php';		// dstarrepeater gateway config
	echo '<script type="text/javascript">'."\n";
	echo 'function reloadrefLinks(){'."\n";
	echo '  $("#refLinks").load("/dstarrepeater/active_reflector_links.php");'."\n";
	echo '}'."\n";
	echo 'setInterval(function(){reloadrefLinks()}, 2500);'."\n";
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
        echo '  $("#cssConnects").load("/dstarrepeater/css_connections.php");'."\n";
        echo '}'."\n";
        echo 'setInterval(function(){reloadcssConnections()}, 15000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
        echo '</script>'."\n";
        echo '<div id="cssConnects">'."\n";
	include 'dstarrepeater/css_connections.php';			// dstarrepeater gateway config
	echo '</div>'."\n";

	echo '<script type="text/javascript">'."\n";
	echo 'function reloadLh(){'."\n";
	echo '  $("#lh").load("/dstarrepeater/last_herd.php");'."\n";
	echo '}'."\n";
	echo 'setInterval(function(){reloadLh()}, 2000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
	echo '</script>'."\n";
	echo '<div id="lh">'."\n";
	include 'dstarrepeater/last_herd.php';				//dstarrepeater Last Herd
        echo '</div>'."\n";
	echo "<br />\n";

	echo '<script type="text/javascript">'."\n";
	echo 'function reloadLocalTx(){'."\n";
	echo '  $("#localTx").load("/dstarrepeater/local_tx.php");'."\n";
	echo '}'."\n";
	echo 'setInterval(function(){reloadLocalTx()}, 3000);'."\n";
	echo '$(window).trigger(\'resize\');'."\n";
	echo '</script>'."\n";
	echo '<div id="localTx">'."\n";
	include 'dstarrepeater/local_tx.php';				//dstarrepeater Local Transmissions
        echo '</div>'."\n";
        echo '<br />'."\n";

} else {
	echo '<div class="contentwide">'."\n";
	//We dont know what mode we are in - fail...
	echo "<H1>No Mode Defined...</H1>\n";
	echo "<p>I don't know what mode I am in, you probaly just need to configure me.</p>\n";
	echo "<p>You will be re-directed to the configuration portal in 10 secs</p>\n";
	echo "<p>In the mean time, you might want to register on the support<br />\n";
	echo "page here: <a href=\"https://www.facebook.com/groups/pistar/\" target=\"_new\">https://www.facebook.com/groups/pistar/</a></p>\n";
	echo '<script type="text/javascript">setTimeout(function() { window.location="/admin/configure.php";},10000);</script>'."\n";
}
?>
</div>

<div class="footer">
Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
ircDDBGateway Dashboard by Hans-J. Barthen (DL5DI),<br />
MMDVMDash developed by Kim Huebel (DG9VH), <br />
Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistar/" target="_new">here for the Support Group</a><br />
Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.mw0mwz.co.uk/pi-star/" target="_new">here</a>.<br />
</div>

</div>
</body>
</html>
