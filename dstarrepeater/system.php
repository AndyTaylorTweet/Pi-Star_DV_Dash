<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';
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
$rev="20141101";
$MYCALL=strtoupper($callsign);
?>
<?php
$cpuLoad = sys_getloadavg();
$cpuTempCRaw = exec('cat /sys/class/thermal/thermal_zone0/temp');
if ($cpuTempCRaw > 1000) { $cpuTempC = round($cpuTempCRaw / 1000, 1); } else { $cpuTempC = round($cpuTempCRaw, 1); }
$cpuTempF = round(+$cpuTempC * 9 / 5 + 32, 1);
if ($cpuTempC < 50) { $cpuTempHTML = "<td style=\"background: #1d1\">".$cpuTempC."&deg;C / ".$cpuTempF."&deg;F</td>\n"; }
if ($cpuTempC >= 50) { $cpuTempHTML = "<td style=\"background: #fa0\">".$cpuTempC."&deg;C / ".$cpuTempF."&deg;F</td>\n"; }
if ($cpuTempC >= 69) { $cpuTempHTML = "<td style=\"background: #f00\">".$cpuTempC."&deg;C / ".$cpuTempF."&deg;F</td>\n"; }
?>
<b><?php echo $lang['hardware_info'];?></b>
<table style="table-layout: fixed;">
  <tr>
    <th><a class="tooltip" href="#"><?php echo $lang['hostname'];?><br /><span><b>System IP Address:<br /><?php echo str_replace(',', ',<br />', exec('hostname -I'));?></b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['kernel'];?><span><b>Release</b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['platform'];?><span><b>Uptime:<br /><?php echo str_replace(',', ',<br />', exec('uptime -p'));?></b></span></a></th>
    <th colspan="2"><a class="tooltip" href="#"><?php echo $lang['cpu_load'];?><span><b>CPU Load</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['cpu_temp'];?><span><b>CPU Temp</b></span></a></th>
  </tr>
  <tr>
    <td><?php echo php_uname('n');?></td>
    <td><?php echo php_uname('r');?></td>
    <td colspan="2"><?php echo exec('platformDetect.sh');?></td>
    <td colspan="2">1m:<?php echo $cpuLoad[0];?> / 5m:<?php echo $cpuLoad[1];?> / 15m:<?php echo $cpuLoad[2];?></td>
    <?php echo $cpuTempHTML; ?>
  </tr>
  <tr>
    <th colspan="7"><?php echo $lang['service_status'];?></th>
  </tr>
  <tr>
    <td style="background: #<?php if (isProcessRunning('MMDVMHost')) { echo "1d1"; } else { echo "b55"; } ?>">MMDVMHost</td>
    <td style="background: #<?php if (isProcessRunning('DMRGateway')) { echo "1d1"; } else { echo "b55"; } ?>">DMRGateway</td>
    <td style="background: #<?php if (isProcessRunning('YSFGateway')) { echo "1d1"; } else { echo "b55"; } ?>">YSFGateway</td>
    <td style="background: #<?php if (isProcessRunning('YSFParrot')) { echo "1d1"; } else { echo "b55"; } ?>">YSFParrot</td>
    <td style="background: #<?php if (isProcessRunning('P25Gateway')) { echo "1d1"; } else { echo "b55"; } ?>">P25Gateway</td>
    <td style="background: #<?php if (isProcessRunning('P25Parrot')) { echo "1d1"; } else { echo "b55"; } ?>">P25Parrot</td>
    <td style="background: #<?php if (isProcessRunning('DAPNETGateway')) { echo "1d1"; } else { echo "b55"; } ?>">DAPNETGateway</td>
  </tr>
  <tr>
    <td style="background: #<?php if (isProcessRunning('dstarrepeaterd')) { echo "1d1"; } else { echo "b55"; } ?>">DStarRepeater</td>
    <td style="background: #<?php if (isProcessRunning('ircddbgatewayd')) { echo "1d1"; } else { echo "b55"; } ?>">ircDDBGateway</td>
    <td style="background: #<?php if (isProcessRunning('timeserverd')) { echo "1d1"; } else { echo "b55"; } ?>">TimeServer</td>
    <td style="background: #<?php if (isProcessRunning('/usr/local/sbin/pistar-watchdog',true)) { echo "1d1"; } else { echo "b55"; } ?>">PiStar-Watchdog</td>
    <td style="background: #<?php if (isProcessRunning('/usr/local/sbin/pistar-remote',true)) { echo "1d1"; } else { echo "b55"; } ?>">PiStar-Remote</td>
    <td style="background: #<?php if (isProcessRunning('/usr/local/sbin/pistar-keeper',true)) { echo "1d1"; } else { echo "b55"; } ?>">PiStar-Keeper</td>
    <td style="background: #606060;"></td>
  </tr>
</table>
<br />
