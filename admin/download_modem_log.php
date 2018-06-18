<?php
if (file_exists('/etc/dstar-radio.mmdvmhost')) {
$logfile = "/var/log/pi-star/MMDVM-".gmdate('Y-m-d').".log";
}
elseif (file_exists('/etc/dstar-radio.dstarrepeater')) {
$logfile = "/var/log/pi-star/DStarRepeater-".gmdate('Y-m-d').".log";
}

$unixfile = file_get_contents($logfile);
$dosfile = str_replace("\n", "\r\n", $unixfile);

header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private', false);
header('Content-Type: text/plain');

header('Content-Disposition: attachment; filename="'. basename($logfile) . '";');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($dosfile));

readfile($unixfile);

exit;
?>
