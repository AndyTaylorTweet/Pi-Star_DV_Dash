<?php
// Most of the work here contributed by geeks4hire (Ben Horan)

include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code
?>
<b><?php echo $lang['dapnet_activity_hdr'];?></b>
<table>
  <tr>
    <th><a class="tooltip" href="#"><?php echo $lang['time'];?> (<?php echo date('T')?>)<span><b>Time in <?php echo date('T')?> time zone</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['pocsag_timeslot'];?><span><b>Message Mode</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['pocsag_ric'];?><span><b>RIC / CapCode of the receiving Pager</b></span></a></th>
    <th><a class="tooltip" href="#"><?php echo $lang['pocsag_msg'];?><span><b>Message contents</b></span></a></th>
  </tr>

<?php
  foreach ($logLinesDAPNETGateway as $dapnetMessageLine) {
      $dapnetMessageArr = explode(" ", $dapnetMessageLine);
      $dapnetMessageTxtArr = explode('"', $dapnetMessageLine);
      $utc_time = $dapnetMessageArr["0"]." ".substr($dapnetMessageArr["1"],0,-4);
      $utc_tz =  new DateTimeZone('UTC');
      $local_tz = new DateTimeZone(date_default_timezone_get ());
      $dt = new DateTime($utc_time, $utc_tz);
      $dt->setTimeZone($local_tz);
      $local_time = $dt->format('H:i:s M jS');
      $pocsag_timeslot = $dapnetMessageArr["6"];
      $pocsag_ric = str_replace(',', '', $dapnetMessageArr["8"]);
      $pocsag_msg = $dapnetMessageTxtArr["1"];
?>

  <tr>
    <td style="width: 140px; vertical-align: top; text-align: left;"><?php echo $local_time; ?></td>
    <td style="width: 70px; vertical-align: top; text-align: center;"><?php echo "TS ".$pocsag_timeslot; ?></td>
    <td style="width: 90px; vertical-align: top; text-align: center;"><?php echo $pocsag_ric; ?></td>
    <td style="width: max-content; vertical-align: top; text-align: center; word-wrap: break-word; white-space: normal !important;"><?php echo $pocsag_msg; ?></td>
  </tr>

<?php
  } // foreach
?>

</table>
