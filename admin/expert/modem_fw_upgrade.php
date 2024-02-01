<?php
// Load the language support
require_once('../config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('../config/version.php');

// Force the Locale to the stock locale just while we run the update
setlocale(LC_ALL, "LC_CTYPE=en_GB.UTF-8;LC_NUMERIC=C;LC_TIME=C;LC_COLLATE=C;LC_MONETARY=C;LC_MESSAGES=C;LC_PAPER=C;LC_NAME=C;LC_ADDRESS=C;LC_TELEPHONE=C;LC_MEASUREMENT=C;LC_IDENTIFICATION=C");

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/expert/modem_fw_upgrade.php") {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['modem'])) {
	    $selectedOption = $_POST['modem'];
	}
    }

    if (!isset($_GET['ajax'])) {
	system('sudo touch /var/log/pi-star/pi-star_modemflash.log > /dev/null 2>&1 &');
	system('sudo echo "" > /var/log/pi-star/pi-star_modemflash.log > /dev/null 2>&1 &');
	if (isset($selectedOption)) { system('sudo NP=1 /usr/local/sbin/pistar-modemupgrade ' . escapeshellarg($selectedOption) . ' > /dev/null 2>&1 &'); }
    }

    // passed sanity chk.
    header('Cache-Control: no-cache');
    session_start();

    if (!isset($_GET['ajax'])) {
	if (file_exists('/var/log/pi-star/pi-star_modemflash.log')) {
	    $_SESSION['update_offset'] = filesize('/var/log/pi-star/pi-star_modemflash.log');
	} else {
	    $_SESSION['update_offset'] = 0;
        }
    }
  
    if (isset($_GET['ajax'])) {
        if (!file_exists('/var/log/pi-star/pi-star_modemflash.log')) {
            exit();
        }
        $handle = fopen('/var/log/pi-star/pi-star_modemflash.log', 'rb');
        if (isset($_SESSION['update_offset'])) {
            fseek($handle, 0, SEEK_END);
            if ($_SESSION['update_offset'] > ftell($handle)) //log rotated/truncated
                $_SESSION['update_offset'] = 0; //continue at beginning of the new log
                $data = stream_get_contents($handle, -1, $_SESSION['update_offset']);
                $_SESSION['update_offset'] += strlen($data);
                echo nl2br($data);
            }
        else {
            fseek($handle, 0, SEEK_END);
            $_SESSION['update_offset'] = ftell($handle);
        } 
        exit();
    }
 
   // Get the firmware version
   if (file_exists('/usr/local/bin/firmware/version.txt')) {
       $versionData = parse_ini_file('/usr/local/bin/firmware/version.txt', true);
   }
   if (isset($versionData['Firmware']['mmdvm_hs_version'])) {
       $mmdvm_hs_version = $versionData['Firmware']['mmdvm_hs_version'];
       $dvmega_fw_version = $versionData['Firmware']['dvmega_version'];
       $rpt_version = $versionData['Firmware']['rpt_version'];
       $fw_ver_msg = "Latest firmware version(s): <b> Hotspot:". $mmdvm_hs_version. " DV-Mega:".$dvmega_fw_version." Repeater:".$rpt_version."</b>.";
   } else {
       $fw_ver_msg = "Unkown (failed to retrieve firmware version).";
   }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
  <head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Author" content="Andrew Taylor (MW0MWZ)" />
    <meta name="Description" content="Pi-Star Update" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - Modem FW ".$lang['update'];?></title>
    <link rel="stylesheet" type="text/css" href="../css/pistar-css.php" />
    <script type="text/javascript" src="/jquery.min.js"></script>
    <script type="text/javascript" src="/jquery-timing.min.js"></script>
    <script type="text/javascript">
    function disableSubmitButtons() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                    if (inputs[i].type === 'button') {
                            inputs[i].disabled = true;
		            inputs[i].value = 'Please Wait...';
                    }
            }
    }

    function submitform() {
	disableSubmitButtons();
	document.getElementById("up_fw").submit();
    }

    $(function() {
      $.repeat(1000, function() {
        $.get('/admin/expert/modem_fw_upgrade.php?ajax', function(data) {
          if (data.length < 1) return;
          var objDiv = document.getElementById("tail");
          var isScrolledToBottom = objDiv.scrollHeight - objDiv.clientHeight <= objDiv.scrollTop + 1;
          $('#tail').append(data);
          if (isScrolledToBottom)
            objDiv.scrollTop = objDiv.scrollHeight;
        });
      });
    });
    </script>
  </head>
  <body>
  <div class="container">
  <?php include './header-menu.inc'; ?>
  <div class="contentwide">
  <table width="100%">
  <?php if (empty($_POST['modem'])) { ?>
  <tr><th>Modem Firmware Upgrade Utility</th></tr>
  <tr><td>
	<br />
	<h2>Modem Firmware Upgrade Utility</h2>
	<p>This tool will attempt to upgrade your selected modem to the latest version available firmware version:<br />
	<?php echo $fw_ver_msg; ?></p>
	<p>When ready, select your modem type below and click, "Upgrade Modem". Do not interrupt the process or<br />
	navigate away from the page while the process is running.</p>
	<p><strong>Please understand what you are doing, as well as the risks associated with flashing your modem.</strong></p>
	<p><em>(IMPORTANT: Please note, we are not firmware developers, and we offer no support for firmware.<br />
	We provide utilities to update the firmware. For firmware support, you will need to utilise other<br />
	support resources from the firmware developers/maintainers or the web.)</em></p>
  </td></tr>
  <tr><td>
<?php

    $friendlyNames = [
      'hs_hat' => 'MMDVM_HS_Hat (14.7456MHz TCXO) GPIO',
      'hs_hat-12mhz' => 'MMDVM_HS_Hat (12.2880MHz TCXO) GPIO',
      'hs_dual_hat' => 'MMDVM_HS_Dual_Hat (14.7456MHz TCXO) GPIO',
      'hs_dual_hat-12mhz' => 'MMDVM_HS_Dual_Hat (12.2880MHz TCXO) GPIO',
      'zum_rpi' => 'ZUMSpot RPi boards/hotspots GPIO',
      'zum_rpi-duplex' => 'ZUMSpot RPi duplex GPIO board/hotspots',
      'zum_usb' => 'ZUMspot USB stick',
      'zum_libre' => 'ZUMspot Libre Kit or generic MMDVM_HS board',
      'skybridge' => 'SkyBridge board/hotspots (14.7456MHz TCXO) GPIO',
      'euronode' => 'DVMega-EuroNode hotspots (14.7456MHz TCXO) GPIO',
      'nanodv_npi' => 'NANO_DV NPi GPIO by BG4TGO',
      'nanodv_usb' => 'NANO_DV USB by BG4TG',
      'hs_hat_ambe' => 'HS_HAT_AMBE (14.7456MHz TCXO) GPIO',
      'hs_hat_lonestar-usb' => 'LoneStar LS MMDVM USB (14.7456MHz TCXO) USB',
      'hs_hat_generic' => 'MMDVM_HS_GENERIC (14.7456MHz TCXO) GPIO',
      'hs_hat_generic_duplex' => 'MMDVM_HS_GENERIC_DUPLEX (14.7456MHz TCXO) GPIO',
      'hs_hat_generic_duplex-usb' => 'MMDVM_HS_GENERIC_DUPLEX (14.7456MHz TCXO) USB',
      'hs_hat_nano_hotspot' => 'Nano_hotSPOT by BI7JTA (14.7456MHz TCXO) GPIO',
      'dvmega_gpio' => 'DV-Mega Raspberry Pi Hat (Single or Dual Band) GPIO',
      'dvmega_usb_uno' => 'DV-Mega Arduino Uno Shield USB (ttyUSB0)',
      'dvmega_usb_mega' => 'DV-Mega Arduino Mega Shield USB (ttyUSB0)',
      'rpt_nucleo-64' => 'Repeater - Nucleo64 F446RE GPIO',
      'rpt_nucleo-144' => 'Repeater - Nucleo144 F767ZI GPIO',
      'rpt_mmdvm_hat-0.2' => 'Repeater - MMDVM_RPT_Hat 0.2 GPIO',
      'rpt_mmdvm_hat-0.3' => 'Repeater - MMDVM_RPT_Hat 0.3 GPIO',
      'rpt_zum-0.9' => 'Repeater - ZUM Radio MMDVM for Pi v0.9 GPIO',
      'rpt_zum-1.0' => 'Repeater - ZUM Radio MMDVM for Pi v1.0 GPIO',
      'rpt_builder_v3' => 'Repeater - Repeater Builder STM32_DVM_PiHat V3 GPIO',
      'rpt_builder_v4' => 'Repeater - Repeater Builder STM32_DVM_PiHat V4 GPIO',
      'rpt_builder_v5' => 'Repeater - Repeater Builder STM32_DVM_PiHat V5 GPIO',
      //'mmdvm_pi-f7' => 'MMDVM Pi F7 Board 460800 baud (12.000MHz TCXO) GPIO',
      //'mmdvm_pi-f4' => 'MMDVM Pi F4 Board 460800 baud (12.000MHz TCXO) GPIO',
    ];

    $output = shell_exec('sudo /usr/local/sbin/pistar-modemupgrade list');

    if ($output !== null) {
        // Split the output into an array of options
        $options = explode("\n", trim($output));

        // Create the select element
        echo '<p><form method="post" id="up_fw">';
        echo '<label for="modem">Select Modem:</label>';
        echo '<select id="modem" name="modem">';
	echo '<option value="" disabled selected>Please choose device type...</option>';
	// Output each option with user-friendly names
	foreach ($options as $option) {
	    $friendlyName = isset($friendlyNames[$option]) ? $friendlyNames[$option] : $option;
	    echo '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($friendlyName) . '</option>';
 	}
        echo '</select>';
        echo '<input type="button" value="Upgrade Modem" onclick="submitform()">';
        echo '</form></p>';
    } else {
        echo '<p>Error executing the command.</p>';
    }
?>
  </form>
  </td></tr>
  </table>
  </div>
  <div class="footer">
  Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Modem Flashing Tool &copy; Chip Cuccio (W0CHP) 2020-<?php echo date("Y");?><br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  </div>
  </div>
  </body>
  </html>
  <?php } else { ?>

  <tr><th>Modem Flash/Upgrade Output</th></tr>
  <tr><td align="left"><div id="tail">Starting FW Upgrade, please wait...<br /></div></td></tr>
  </table>
  </div>
  <div class="footer">
  Pi-Star / Pi-Star Dashboard, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Modem Flashing Tool &copy; Chip Cuccio (W0CHP) 2020-<?php echo date("Y");?><br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  </div>
  </div>
  </body>
  </html>
<?php } 
}
?>

