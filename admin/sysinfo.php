


echo "<tr><td><b>Pi</b></td><td>CPU Temp</td><td>".number_format((int)@exec('cat /sys/class/thermal/thermal_zone0/temp')/1000, '2', '.', '')."&degC".chkRebootBtn()."</td></tr>\n";
              foreach (glob("/boot/emonSD-*") as $emonpiRelease) {
                $emonpiRelease = str_replace("/boot/", '', $emonpiRelease);
              }
              if (isset($emonpiRelease)) {
                echo "<tr><td class=\"subinfo\"></td><td>Release</td><td>".$emonpiRelease."</td></tr>\n";
                echo "<tr><td class=\"subinfo\"></td><td>File-system</td><td>Set root file-system temporarily to read-write, (default read-only)<button id=\"fs-rw\" class=\"btn btn-danger btn-small pull-right\">"._('Read-Write')."</button> <button id=\"fs-ro\" class=\"btn btn-info btn-small pull-right\">"._('Read-Only')."</button></td></tr>\n";
}
// Ram information
if ($system['mem_info']) {
              $sysRamUsed = $system['mem_info']['MemTotal'] - $system['mem_info']['MemFree'] - $system['mem_info']['Buffers'] - $system['mem_info']['Cached'];
              $sysRamPercent = sprintf('%.2f',($sysRamUsed / $system['mem_info']['MemTotal']) * 100);
              echo "<tr><td><b>Memory</b></td><td>RAM</td><td><div class='progress progress-info' style='margin-bottom: 0;'><div class='bar' style='width: ".$sysRamPercent."%;'>Used&nbsp;".$sysRamPercent."%</div></div>";
              echo "<b>Total:</b> ".formatSize($system['mem_info']['MemTotal'])."<b> Used:</b> ".formatSize($sysRamUsed)."<b> Free:</b> ".formatSize($system['mem_info']['MemTotal'] - $sysRamUsed)."</td></tr>\n";
              
              if ($system['mem_info']['SwapTotal'] > 0) {
                $sysSwapUsed = $system['mem_info']['SwapTotal'] - $system['mem_info']['SwapFree'];
                $sysSwapPercent = sprintf('%.2f',($sysSwapUsed / $system['mem_info']['SwapTotal']) * 100);
                echo "<tr><td class='subinfo'></td><td>Swap</td><td><div class='progress progress-info' style='margin-bottom: 0;'><div class='bar' style='width: ".$sysSwapPercent."%;'>Used&nbsp;".$sysSwapPercent."%</div></div>";
                echo "<b>Total:</b> ".formatSize($system['mem_info']['SwapTotal'])."<b> Used:</b> ".formatSize($sysSwapUsed)."<b> Free:</b> ".formatSize($system['mem_info']['SwapFree'])."</td></tr>\n";
              }
}
// Filesystem Information
                if (count($system['partitions']) > 0) {
                    echo "<tr><td><b>Disk</b></td><td><b>Mount</b></td><td><b>Stats</b></td></tr>\n";
                    foreach($system['partitions'] as $fs) {
                      if (!$fs['Temporary']['bool'] && $fs['FileSystem']['text']!= "none" && $fs['FileSystem']['text']!= "udev") {
                        $diskFree = $fs['Free']['value'];
                        $diskTotal = $fs['Size']['value'];;
                        $diskUsed = $fs['Used']['value'];;
                        $diskPercent = sprintf('%.2f',($diskUsed / $diskTotal) * 100);
                        
                        echo "<tr><td class='subinfo'></td><td>".$fs['Partition']['text']."</td><td><div class='progress progress-info' style='margin-bottom: 0;'><div class='bar' style='width: ".$diskPercent."%;'>Used&nbsp;".$diskPercent."%</div></div>";
                        echo "<b>Total:</b> ".formatSize($diskTotal)."<b> Used:</b> ".formatSize($diskUsed)."<b> Free:</b> ".formatSize($diskFree)."</td></tr>\n";
                        
                      }
                    }
                }
