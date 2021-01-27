<table>
<tr><th>ircDDB Network</th><th>APRS Host</th><th>CCS</th><th>DCS</th><th>DExtra</th><th>DPlus</th><th>D-Rats</th><th>Info</th><th>ircDDB</th><th>Echo</th><th>Log</th></tr>
<tr style="background: #ffffff;">
  <td><?php print $configs['ircddbHostname']; ?></td>
  <td><?php if($configs['aprsEnabled'] == 1){ print $configs['aprsHostname']; } else { print "<img src=\"images/20red.png\">";} ?></td>
  <?php
  if($configs['ccsEnabled'] == 1){print "<td style=\"background-color:#1d1;\">ON</td>"; } else { print "<td style=\"background:#606060; color:#b0b0b0;\">OFF</td>"; }
  if($configs['dcsEnabled'] == 1){print "<td style=\"background-color:#1d1;\">ON</td>"; } else { print "<td style=\"background:#606060; color:#b0b0b0;\">OFF</td>"; }
  if($configs['dextraEnabled'] == 1){print "<td style=\"background-color:#1d1;\">ON</td>"; } else { print "<td style=\"background:#606060; color:#b0b0b0;\">OFF</td>"; }
  if($configs['dplusEnabled'] == 1){print "<td style=\"background-color:#1d1;\">ON</td>"; } else { print "<td style=\"background:#606060; color:#b0b0b0;\">OFF</td>"; }
  if($configs['dratsEnabled'] == 1){print "<td style=\"background-color:#1d1;\">ON</td>"; } else { print "<td style=\"background:#606060; color:#b0b0b0;\">OFF</td>"; }
  if($configs['infoEnabled'] == 1){print "<td style=\"background-color:#1d1;\">ON</td>"; } else { print "<td style=\"background:#606060; color:#b0b0b0;\">OFF</td>"; }
  if($configs['ircddbEnabled'] == 1){print "<td style=\"background-color:#1d1;\">ON</td>"; } else { print "<td style=\"background:#606060; color:#b0b0b0;\">OFF</td>"; }
  if($configs['echoEnabled'] == 1){print "<td style=\"background-color:#1d1;\">ON</td>"; } else { print "<td style=\"background:#606060; color:#b0b0b0;\">OFF</td>"; }
  if($configs['logEnabled'] == 1){print "<td style=\"background-color:#1d1;\">ON</td>"; } else { print "<td style=\"background:#606060; color:#b0b0b0;\">OFF</td>"; }
  ?>
</tr>
</table>
