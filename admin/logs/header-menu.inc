<div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star Digital Voice - Live Logs</h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
    <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
    <a href="/admin/logs/" style="color: #ffffff;"><?php echo $lang['live_logs'];?></a> |
    <a href="/admin/power.php" style="color: #ffffff;"><?php echo $lang['power'];?></a> |
    <a href="/admin/update.php" style="color: #ffffff;"><?php echo $lang['update'];?></a> |
    <a href="/admin/configure.php" style="color: #ffffff;"><?php echo $lang['configuration'];?></a>
  </p>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <b>Host:</b>
    <a href="log.php?log=MMDVMHost" style="color: #ffffff;">MMDVMHost</a> |
    <a href="log.php?log=DStarRepeater" style="color: #ffffff;">DStarRepeater</a><br />
    <b>Gateways:</b>
    <a href="log.php?log=DMRGateway" style="color: #ffffff;">DMRGateway</a> |
    <a href="log.php?log=ircDDBGateway" style="color: #ffffff;">ircDDBGateway</a> |
    <a href="log.php?log=YSFGateway" style="color: #ffffff;">YSFGateway</a> |
    <a href="log.php?log=P25Gateway" style="color: #ffffff;">P25Gateway</a> |
    <a href="log.php?log=NXDNGateway" style="color: #ffffff;">NXDNGateway</a> |
    <a href="log.php?log=DAPNETGateway" style="color: #ffffff;">DAPNETGateway</a>
  </p>
  </div>
