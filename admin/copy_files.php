<?php
// Load the language support
require_once('config/language.php');
// Load the Pi-Star Release file
$pistarReleaseConfig = '/etc/pistar-release';
$configPistarRelease = array();
$configPistarRelease = parse_ini_file($pistarReleaseConfig, true);
// Load the Version Info
require_once('config/version.php');

//Maximum folder size that will be copied
// (in MB)
$maxsize=32;
// Destination directory
$dstdir="/usr/local/etc";

// Sanity Check that this file has been opened correctly
if ($_SERVER["PHP_SELF"] == "/admin/copy_files.php") {
  // Sanity Check Passed.
  header('Cache-Control: no-cache');
  session_start();
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
  <head>
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <meta name="language" content="English" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Author" content="Lieven De Samblanx (ON7LDS)" />
    <meta name="Description" content="Pi-Star Removable Disks" />
    <meta name="KeyWords" content="Pi-Star" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="pragma" content="no-cache" />
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <meta http-equiv="Expires" content="0" />
    <title>Pi-Star - <?php echo $lang['digital_voice']." ".$lang['dashboard']." - ".$lang['copyfiles'];?></title>
    <link rel="stylesheet" type="text/css" href="css/pistar-css.php" />
  </head>
  <body>
  <div class="container">
  <div class="header">
  <div style="font-size: 8px; text-align: right; padding-right: 8px;">Pi-Star:<?php echo $configPistarRelease['Pi-Star']['Version']?> / <?php echo $lang['dashboard'].": ".$version; ?></div>
  <h1>Pi-Star <?php echo $lang['digital_voice']." - ".$lang['copyfiles'];?></h1>
  <p style="padding-right: 5px; text-align: right; color: #ffffff;">
    <a href="/" style="color: #ffffff;"><?php echo $lang['dashboard'];?></a> |
    <a href="/admin/" style="color: #ffffff;"><?php echo $lang['admin'];?></a> |
    <a href="/admin/update.php" style="color: #ffffff;"><?php echo $lang['update'];?></a> |
    <a href="/admin/config_backup.php" style="color: #ffffff;"><?php echo $lang['backup_restore'];?></a> |
    <a href="/admin/configure.php" style="color: #ffffff;"><?php echo $lang['configuration'];?></a>
  </p>
  </div>
  <div class="contentwide">
<?php $ok1=0; $ok2=""; $out=array();
    if (!empty($_POST)) {
    $action=escapeshellcmd($_POST["action"]);
    if (strpos($action,"sd")===0)
        system("sudo umount /mnt ; sudo mount /dev/$action /mnt",$ok1);
    if (strpos($action,"umount")===0)
        system("sudo umount /mnt",$ok1);
    if ($action=="copy") {
        $size=exec("du -sm /mnt/Pi-Star",$out_size,$ok2);
        if ($ok2==0) {
            $size=preg_replace("/ .*/","",$size);
            if ($size>$maxsize) $ok2=$lang['tobig']." $maxsize MB";
            else {
                exec("sudo mount -o remount,rw / ; sleep 1; sudo cp --no-preserve=mode,owner -v /mnt/Pi-Star/* $dstdir ",$out,$ok2);
                system("sleep 1 ; sudo umount /mnt");
                if ($ok2!=0) $ok2=$lang['copyfailed']; else $ok2=$lang['copied'];
            }
        } else $ok2=$lang['nodir'];
    }
  }
?>
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
  <table width="100%">
  <tr>
    <th colspan="2"><?php echo $lang['copyfiles'];?></th>
  </tr>
  <tr>
    <td align="center">
     <?php print $lang['mount']."<br /><br />";
    $mounted=explode("\n",exec('mount | grep sd')); sort($mounted);
    $mdisks="";
    foreach ($mounted as $mount) $mdisks.=" ".preg_replace("/ on.*/","",$mount);
    $disks=explode("  ",exec('cd /dev && ls -C sd*')); sort($disks);
    $disks=array_values(array_filter($disks));
    if (count($disks)>1) for ($i=1; $i<count($disks); $i++) {
        if (strpos($disks[$i],$disks[$i-1])!==false) unset($disks[$i-1]);
    }
    foreach ($disks as $disk) {
        if (strpos($mdisks,$disk)!==false) $dot="/images/stick_red.png"; else $dot="/images/stick_green.png";
        print '<div><button style="border: none; background: none;" name="action" value="'.$disk.'">';
        print '<img src="'.$dot.'" width="50%" border="0" alt="Disk" /></button><div>'.$disk.'</div></div>';
        print '<br />';
    }
    if ($ok1!=0) print "Sorry, last action failed.";
    if (!empty($mounted[0])) {
        print $lang['unmount'];
        print '<div><button style="border: none; background: none;" name="action" value="umount">';
        print '<img src="/images/stick_eject.png" width="50%" border="0" alt="Umount Disk" /></button></div>';
        print '<br />';
    } 
    if (count($disks)==0) print $lang['nodrives'];
    ?>
    </td>
    <td align="center">
    <?php if (!empty($mounted[0])) {
      print $lang['copyfiles'];  ?> <br />
      <button style="border: none; background: none;" name="action" value="copy">
      <img src="/images/download.png" width="50%" border="0" alt="Shutdown" /></button><br />
      <?php } print $ok2."<br />"; foreach ($out as $line) print "<br>".preg_replace("/.* -> /","",$line); ?>
    </td>
  </tr>
  <tr>
  <td colspan="2" align="justify">
  <br />
  <b>WARNING:</b><br />
     Only files from the 'Pi-Star' folder (watch case !) on the disk will be copied <br />
      <ul>
      <li>Files will be written to <?php echo $dstdir; ?></li><br />
      <li>Existing files will be overwritten</li><br />
      <li>Maximum size is <?php print $maxsize; ?>MB</li><br />
      </ul>
      </td>
  </tr>

  </table>
  </form>
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
