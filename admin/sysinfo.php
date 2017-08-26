<?php
// Load the language support
require_once('config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('config/version.php');
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
    <meta name="Description" content="Pi-Star SysInfo" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['update'];?></title>
    <link rel="stylesheet" type="text/css" href="css/ircddb.css?version=1.3" />
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
    <script type="text/javascript" src="http://creativecouple.github.com/jquery-timing/jquery-timing.min.js"></script>
  </head>
  <body>
  <div class="container">
  <div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - SysInfo";?></h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
    <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
    <a href="/admin/power.php" style="color: #ffffff;"><?php echo $lang['power'];?></a> |
    <a href="/admin/config_backup.php" style="color: #ffffff;"><?php echo $lang['backup_restore'];?></a> |
    <a href="/admin/configure.php" style="color: #ffffff;"><?php echo $lang['configuration'];?></a>
  </p>
  </div>
  <div class="contentwide">
  <table width="100%">
<?php
echo "  <tr><td><b>Pi</b></td><td>CPU Temp</td><td>".number_format((int)@exec('cat /sys/class/thermal/thermal_zone0/temp')/1000, '2', '.', '')."&degC"</td></tr>\n";
              foreach (glob("/boot/emonSD-*") as $emonpiRelease) {
                $emonpiRelease = str_replace("/boot/", '', $emonpiRelease);
              }
              if (isset($emonpiRelease)) {
                echo "  <tr><td class=\"subinfo\"></td><td>Release</td><td>".$emonpiRelease."</td></tr>\n";
                echo "  <tr><td class=\"subinfo\"></td><td>File-system</td><td>Set root file-system temporarily to read-write, (default read-only)<button id=\"fs-rw\" class=\"btn btn-danger btn-small pull-right\">"._('Read-Write')."</button> <button id=\"fs-ro\" class=\"btn btn-info btn-small pull-right\">"._('Read-Only')."</button></td></tr>\n";
}
// Ram information
if ($system['mem_info']) {
              $sysRamUsed = $system['mem_info']['MemTotal'] - $system['mem_info']['MemFree'] - $system['mem_info']['Buffers'] - $system['mem_info']['Cached'];
              $sysRamPercent = sprintf('%.2f',($sysRamUsed / $system['mem_info']['MemTotal']) * 100);
              echo "  <tr><td><b>Memory</b></td><td>RAM</td><td><div class='progress progress-info' style='margin-bottom: 0;'><div class='bar' style='width: ".$sysRamPercent."%;'>Used&nbsp;".$sysRamPercent."%</div></div>";
              echo "  <b>Total:</b> ".formatSize($system['mem_info']['MemTotal'])."<b> Used:</b> ".formatSize($sysRamUsed)."<b> Free:</b> ".formatSize($system['mem_info']['MemTotal'] - $sysRamUsed)."</td></tr>\n";
              
              if ($system['mem_info']['SwapTotal'] > 0) {
                $sysSwapUsed = $system['mem_info']['SwapTotal'] - $system['mem_info']['SwapFree'];
                $sysSwapPercent = sprintf('%.2f',($sysSwapUsed / $system['mem_info']['SwapTotal']) * 100);
                echo "  <tr><td class='subinfo'></td><td>Swap</td><td><div class='progress progress-info' style='margin-bottom: 0;'><div class='bar' style='width: ".$sysSwapPercent."%;'>Used&nbsp;".$sysSwapPercent."%</div></div>";
                echo "  <b>Total:</b> ".formatSize($system['mem_info']['SwapTotal'])."<b> Used:</b> ".formatSize($sysSwapUsed)."<b> Free:</b> ".formatSize($system['mem_info']['SwapFree'])."</td></tr>\n";
              }
}
// Filesystem Information
                if (count($system['partitions']) > 0) {
                    echo "  <tr><td><b>Disk</b></td><td><b>Mount</b></td><td><b>Stats</b></td></tr>\n";
                    foreach($system['partitions'] as $fs) {
                      if (!$fs['Temporary']['bool'] && $fs['FileSystem']['text']!= "none" && $fs['FileSystem']['text']!= "udev") {
                        $diskFree = $fs['Free']['value'];
                        $diskTotal = $fs['Size']['value'];;
                        $diskUsed = $fs['Used']['value'];;
                        $diskPercent = sprintf('%.2f',($diskUsed / $diskTotal) * 100);
                        
                        echo "  <tr><td class='subinfo'></td><td>".$fs['Partition']['text']."</td><td><div class='progress progress-info' style='margin-bottom: 0;'><div class='bar' style='width: ".$diskPercent."%;'>Used&nbsp;".$diskPercent."%</div></div>";
                        echo "  <b>Total:</b> ".formatSize($diskTotal)."<b> Used:</b> ".formatSize($diskUsed)."<b> Free:</b> ".formatSize($diskFree)."</td></tr>\n";
                        
                      }
                    }
                }
?>
  </table>  
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
