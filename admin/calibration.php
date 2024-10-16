<?php
// Load the language support
require_once('config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('config/version.php');

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/calibration.php") {

  if (isset($_GET['action'])) {
    if ($_GET['action'] === 'start') {
      system('sudo fuser -k 33273/udp > /dev/null 2>&1');
      system('nc -ulp 33273 | sudo -i script -qfc "/usr/local/sbin/pistar-mmdvmcal" /tmp/pi-star_mmdvmcal.log > /dev/null 2>&1 &');
    }
    else if (($_GET['action'] === 'saveoffset')) {
      if (isset($_GET['param']) && strlen($_GET['param'])) {
        system('sudo mount -o remount,rw /');
        system('sudo sed -i "/RXOffset=/c\\RXOffset='.intval($_GET['param']).'" /etc/mmdvmhost');
        system('sudo sed -i "/TXOffset=/c\\TXOffset='.intval($_GET['param']).'" /etc/mmdvmhost');
      }
    }
    exit();
  }

  if (isset($_GET['cmd']) && strlen($_GET['cmd'])) {
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_bind($sock, '127.0.0.1', 33272) || exit();
    socket_sendto($sock, $_GET['cmd'], strlen($_GET['cmd']), 0, '127.0.0.1', 33273);
    if (isset($_GET['param']) && strlen($_GET['param'])) {
      usleep(500*1000);
      socket_sendto($sock, $_GET['param']."\n", strlen($_GET['param'])+1, 0, '127.0.0.1', 33273);
    }
    if ($_GET['cmd'] === 'q') {
      sleep(1);
      socket_sendto($sock, "\n", 1, 0, '127.0.0.1', 33273); //send something to kill the pipe, also \n may be useful if something went wrong and mmdvmcal is waiting some param input
    }
    socket_close($sock);
    exit();
  }

  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();

  if (!isset($_GET['ajax'])) {
    //unset($_SESSION['mmdvmcal_offset']);
    if (file_exists('/tmp/pi-star_mmdvmcal.log')) {
      $_SESSION['mmdvmcal_offset'] = filesize('/tmp/pi-star_mmdvmcal.log');
    } else {
      $_SESSION['mmdvmcal_offset'] = 0;
    }
  }
  
  if (isset($_GET['ajax'])) {
    //session_start();
    if (!file_exists('/tmp/pi-star_mmdvmcal.log')) {
      exit();
    }
    
    $handle = fopen('/tmp/pi-star_mmdvmcal.log', 'rb');
    if (isset($_SESSION['mmdvmcal_offset'])) {
      fseek($handle, 0, SEEK_END);
      if ($_SESSION['mmdvmcal_offset'] > ftell($handle)) //log rotated/truncated
        $_SESSION['mmdvmcal_offset'] = 0; //continue at beginning of the new log
      $data = stream_get_contents($handle, -1, $_SESSION['mmdvmcal_offset']);
      $_SESSION['mmdvmcal_offset'] += strlen($data);
      echo nl2br($data);
      }
    else {
      fseek($handle, 0, SEEK_END);
      $_SESSION['mmdvmcal_offset'] = ftell($handle);
      } 
  exit();
  }

  $RXFrequency = exec('grep "RXFrequency" /etc/mmdvmhost | awk -F "=" \'{print $2}\'');
  $RXOffset = exec('grep "RXOffset" /etc/mmdvmhost | awk -F "=" \'{print $2}\'');
  
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
    <meta name="Description" content="Pi-Star Calibration" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - Calibration";?></title>
    <link rel="stylesheet" type="text/css" href="css/pistar-css.php" />
    <script type="text/javascript" src="/jquery.min.js"></script>
    <script type="text/javascript" src="/jquery-timing.min.js"></script>
    <script type="text/javascript" src="/plotly-basic.min.js"></script>
    <script type="text/javascript">

    var rxoffset = ~~'<?php echo $RXOffset; ?>';

    function sendaction(action='', param='') {
      if (action === 'start') { document.getElementById("btnStart").disabled = true; }
      if (action === 'saveoffset') { rxoffset = ~~param }
      $.ajax({
        url: 'calibration.php',
        type: 'GET',
        data: {
          'action': action,
          'param': param
        },
        cache: false,
        success: function(msg) {}
      });
      return false;
    }
    
    var sendcmd_lock=false;

    function sendcmd(cmd='', param='') {
      if (sendcmd_lock) { return false; }
      if (param !== '') { sendcmd_lock = true; } //if we have param, lock to prevent cmd overlap while waiting param
      $.ajax({
        url: 'calibration.php',
        type: 'GET',
        data: {
          'cmd': cmd,
          'param': param
        },
        cache: false,
        success: function(msg) {},
        complete: function() { sendcmd_lock = false; }
      });
      return false;
    }
    
    var cnt=0; tcnt=0;
    var cfrms=0; cbits=0, cberr=0;
    var tfrms=0; tbits=0, tberr=0;
    var eot=false;

    $(function() {
      $.repeat(1000, function() {
        $.get('/admin/calibration.php?ajax', function(data) {
         if (data.length > 0) {
<?php if (isset($_GET['debug'])) { ?>
          var objDiv = document.getElementById("tail");
          var isScrolledToBottom = objDiv.scrollHeight - objDiv.clientHeight <= objDiv.scrollTop + 1;
          $('#tail').append(data);
          if (isScrolledToBottom)
            objDiv.scrollTop = objDiv.scrollHeight;
<?php } ?>
          
          if (("\n"+data).includes("Version:")) {
            setTimeout(function(){ sendcmd('e', (~~'<?php echo $RXFrequency; ?>'+rxoffset).toString() ); }, 1000);
          }
          if (("\n"+data).includes("Finnished...")) {
            $('#ledStart').attr("src", 'images/20red.png');
            $('#ledDStar').attr("src", 'images/20red.png');
            $('#ledDMR').attr("src", 'images/20red.png');
            $('#ledYSF').attr("src", 'images/20red.png');
            $('#ledP25').attr("src", 'images/20red.png');
            $('#ledNXDN').attr("src", 'images/20red.png');
            document.getElementById("btnStart").disabled = false;
          }

          if (("\n"+data).includes("\nBER Test Mode (FEC) for D-Star")) {
            $('#ledStart').attr("src", 'images/20green.png');
            $('#ledDStar').attr("src", 'images/20green.png');
            $('#ledDMR').attr("src", 'images/20red.png');
            $('#ledYSF').attr("src", 'images/20red.png');
            $('#ledP25').attr("src", 'images/20red.png');
            $('#ledNXDN').attr("src", 'images/20red.png');
          }
          if (("\n"+data).includes("\nBER Test Mode (FEC) for DMR Simplex")) {
            $('#ledStart').attr("src", 'images/20green.png');
            $('#ledDStar').attr("src", 'images/20red.png');
            $('#ledDMR').attr("src", 'images/20green.png');
            $('#ledYSF').attr("src", 'images/20red.png');
            $('#ledP25').attr("src", 'images/20red.png');
            $('#ledNXDN').attr("src", 'images/20red.png');
          }
          if (("\n"+data).includes("\nBER Test Mode (FEC) for YSF")) {
            $('#ledStart').attr("src", 'images/20green.png');
            $('#ledDStar').attr("src", 'images/20red.png');
            $('#ledDMR').attr("src", 'images/20red.png');
            $('#ledYSF').attr("src", 'images/20green.png');
            $('#ledP25').attr("src", 'images/20red.png');
            $('#ledNXDN').attr("src", 'images/20red.png');
          }
          if (("\n"+data).includes("\nBER Test Mode (FEC) for P25")) {
            $('#ledStart').attr("src", 'images/20green.png');
            $('#ledDStar').attr("src", 'images/20red.png');
            $('#ledDMR').attr("src", 'images/20red.png');
            $('#ledYSF').attr("src", 'images/20red.png');
            $('#ledP25').attr("src", 'images/20green.png');
            $('#ledNXDN').attr("src", 'images/20red.png');
          }
          if (("\n"+data).includes("\nBER Test Mode (FEC) for NXDN")) {
            $('#ledStart').attr("src", 'images/20green.png');
            $('#ledDStar').attr("src", 'images/20red.png');
            $('#ledDMR').attr("src", 'images/20red.png');
            $('#ledYSF').attr("src", 'images/20red.png');
            $('#ledP25').attr("src", 'images/20red.png');
            $('#ledNXDN').attr("src", 'images/20green.png');
          }
          
          if (data.includes("voice end received,")) {
            eot=true;
          }

          var regex = / frequency: (\d+)/g
          while (match = regex.exec(data)) {
            $('#ledStart').attr("src", 'images/20green.png');
            $("#lblFrequency").text(match[1] + ' Hz');
            $("#lblOffset").text(~~match[1] - ~~'<?php echo $RXFrequency; ?>');
          }

          var regex = /\% \((\d+)\/(\d+)\)/g
          while (match = regex.exec(data)) {
            cfrms += 1;
            cberr += ~~match[1];
            cbits += ~~match[2];
            tfrms += 1;
            tberr += ~~match[1];
            tbits += ~~match[2];
          }
         }

          if (cbits > 0) {
            cnt++; tcnt++;
            var updfrq = $('#sltUpdFrq').val();
            if ((tcnt % updfrq == 0) || eot) {
                //$('#tail').append(cfrms +' , '+ cberr +' / '+ cbits +' , '+ (cberr/cbits*100).toFixed(2) + '%<br>');
                $("#lblFrames").text(cfrms);
                $("#lblBits").text(cbits);
                $("#lblErrors").text(cberr);
                $("#lblBER").text((cberr/cbits*100).toFixed(2)+'%');
                Plotly.extendTraces('chart', { x:[[cnt]], y:[[cberr/cbits*100]] }, [0]);
                if(cnt > 60*3) {
                    Plotly.relayout('chart', {
                        xaxis: {range: [cnt-60*3,cnt]}
                    });
                }
                cfrms=0; cbits=0; cberr=0;

                //$('#tail').append('total: ' + tfrms +' , '+ tberr +' / '+ tbits +' , '+ (tberr/tbits*100).toFixed(2) + '%<br>');
                $("#lblTFrames").text(tfrms);
                $("#lblTBits").text(tbits);
                $("#lblTErrors").text(tberr);
                $("#lblTBER").text((tberr/tbits*100).toFixed(2)+'%');
                $("#lblTSec").text(tcnt);
                if (eot) {
                  eot=false;
                  tfrms=0; tbits=0; tberr=0; tcnt=0;
                }
            }
          }

        });
      });
    });
    </script>
  </head>
  <body>
  <div class="container">
  <div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / Dashboard:<?php echo $version; ?></div>
  <h1>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - Calibration";?></h1>
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
  <tr><th>Calibration Tool</th></tr>
  <tr><td align="left">
  
<table width="800" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td align="center" valign="middle"><table border="0" cellspacing="0" cellpadding="5">
      <tr>
        <td><input name="btnStart" type="button" id="btnStart" onclick="sendaction('start');" value="Start" /></td>
        <td><img src="images/20red.png" name="ledStart" width="20" height="20" id="ledStart" /></td>
      </tr>
      <tr>
        <td><input name="btnStop" type="button" id="btnStop" onclick="sendcmd('q');" value="Stop" /></td>
        <td>&nbsp;</td>
      </tr>
    </table></td>

    <td align="center" valign="middle"><table border="0" cellspacing="0" cellpadding="4" height="160">
      <tr>
        <td><input name="btnDStar" type="button" id="btnDStar" onclick="sendcmd('k');" value="D-Star" /></td>
        <td><img src="images/20red.png" name="ledDStar" width="20" height="20" id="ledDStar" /></td>
        </tr>
      <tr>
        <td><input name="btnDMR" type="button" id="btnDMR" onclick="sendcmd('b');" value="DMR" /></td>
        <td><img src="images/20red.png" name="ledDMR" width="20" height="20" id="ledDMR" /></td>
        </tr>
      <tr>
        <td><input name="btnYSF" type="button" id="btnYSF" onclick="sendcmd('J');" value="YSF" /></td>
        <td><img src="images/20red.png" name="ledYSF" width="20" height="20" id="ledYSF" /></td>
        </tr>
      <tr>
        <td><input name="btnP25" type="button" id="btnP25" onclick="sendcmd('j');" value="P25" /></td>
        <td><img src="images/20red.png" name="ledP25" width="20" height="20" id="ledP25" /></td>
        </tr>
      <tr>
        <td><input name="btnNXDN" type="button" id="btnNXDN" onclick="sendcmd('n');" value="NXDN" /></td>
        <td><img src="images/20red.png" name="ledNXDN" width="20" height="20" id="ledNXDN" /></td>
        </tr>
    </table></td>

    <td align="center" valign="middle"><table border="0" cellspacing="0" cellpadding="5" height="160">
      <tr>
        <td align="left">Base Freq.:</td>
        <td colspan="3" id="lblBaseFreq"><?php echo $RXFrequency; ?> Hz</td>
      </tr>
      <tr>
        <td align="left">Frequency:</td>
        <td colspan="3" id="lblFrequency"><?php echo $RXFrequency + $RXOffset; ?> Hz</td>
      </tr>
      <tr>
        <td align="left">Offset:</td>
        <td><input name="btnFreqM" type="button" id="btnFreqM" onclick="sendcmd('f');" value="-" /></td>
        <td id="lblOffset" style="width:5ch"><?php echo $RXOffset; ?></td>
        <td><input name="btnFreqP" type="button" id="btnFreqP" onclick="sendcmd('F');" value="+" /></td>
      </tr>
      <tr>
        <td align="left">Step:</td>
        <td colspan="3"><input type="button" onclick="sendcmd('z','25');" value="25" /> <input type="button" onclick="sendcmd('z','50');" value="50" /> <input type="button" onclick="sendcmd('z','100');" value="100" /></td>
      </tr>
      <tr>
        <td align="left">&nbsp;</td>
        <td colspan="3"><input name="button8" type="button" onclick="sendaction('saveoffset',$('#lblOffset').text());" value="Save Offset" /></td>
      </tr>
    </table></td>

    <td align="center" valign="middle"><table border="0" cellspacing="0" cellpadding="5" height="160">
      <tr>
        <th style="width:8ch">&nbsp;</th>
        <th style="width:9ch">Current</th>
        <th style="width:9ch">Total</th>
      </tr>
      <tr>
        <td align="left">Frames:</td>
        <td id="lblFrames">&nbsp;</td>
        <td id="lblTFrames">&nbsp;</td>
      </tr>
      <tr>
        <td align="left">Bits:</td>
        <td id="lblBits">&nbsp;</td>
        <td id="lblTBits">&nbsp;</td>
      </tr>
      <tr>
        <td align="left">Errors:</td>
        <td id="lblErrors">&nbsp;</td>
        <td id="lblTErrors">&nbsp;</td>
      </tr>
      <tr>
        <td align="left">BER:</td>
        <td id="lblBER">&nbsp;</td>
        <td id="lblTBER">&nbsp;</td>
      </tr>
      <tr>
        <td align="left">Seconds:</td>
        <td id="lblSec" style="padding:0;"><select name="sltUpdFrq" id="sltUpdFrq" style="margin:0;">
                          <option value="1">1</option>
                          <option value="2">2</option>
                          <option value="3">3</option>
                          <option value="5" selected="selected">5</option>
                          <option value="10">10</option>
                          <option value="30">30</option>
                        </select>
        </td>
        <td id="lblTSec">&nbsp;</td>
      </tr>
    </table></td>
  </tr>
</table>  

  </td></tr>
  <tr><td align="left">
        <div id="chart"></div>
        <script type="text/javascript">
            Plotly.newPlot('chart', [{
                x: [0],
                y: [0],
                type: 'scatter',
                mode: 'lines',
                fill: 'tozeroy',
                line: {color: '#dd4b39'}
            }], {title:'Bit Error Rate (BER)', xaxis:{title:'Seconds',rangemode:'tozero'}, yaxis:{title:'%',rangemode:'tozero',range:[0,5]} }, {staticPlot: true});
        </script>
      </td></tr>
<?php if (isset($_GET['debug'])) { ?>
  <tr><td align="left"><div id="tail"></div></td></tr>
<?php } ?>
  </table>
  </div>
  <div class="footer">
  Pi-Star web config, &copy; Andy Taylor (MW0MWZ) 2014-<?php echo date("Y"); ?>.<br />
  Need help? Click <a style="color: #ffffff;" href="https://www.facebook.com/groups/pistarusergroup/" target="_new">here for the Support Group</a><br />
  Get your copy of Pi-Star from <a style="color: #ffffff;" href="http://www.pistar.uk/downloads/" target="_blank">here</a>.<br />
  <br />
  </div>
  </div>
  </body>
  </html>

<?php
}
?>
