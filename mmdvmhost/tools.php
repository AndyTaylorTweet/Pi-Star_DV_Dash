<?php
function format_time($seconds) {
	$secs = intval($seconds % 60);
	$mins = intval($seconds / 60 % 60);
	$hours = intval($seconds / 3600 % 24);
	$days = intval($seconds / 86400);
	$uptimeString = "";

	if ($days > 0) {
		$uptimeString .= $days;
		$uptimeString .= (($days == 1) ? "&nbsp;day" : "&nbsp;days");
	}
	if ($hours > 0) {
		$uptimeString .= (($days > 0) ? ", " : "") . $hours;
		$uptimeString .= (($hours == 1) ? "&nbsp;hr" : "&nbsp;hrs");
	}
	if ($mins > 0) {
		$uptimeString .= (($days > 0 || $hours > 0) ? ", " : "") . $mins;
		$uptimeString .= (($mins == 1) ? "&nbsp;min" : "&nbsp;mins");
	}
	if ($secs > 0) {
		$uptimeString .= (($days > 0 || $hours > 0 || $mins > 0) ? ", " : "") . $secs;
		$uptimeString .= (($secs == 1) ? "&nbsp;s" : "&nbsp;s");
	}
	return $uptimeString;
}

function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function getMHZ($freq) {
	return substr($freq,0,3) . "." . substr($freq,3,6) . " MHz";
}

function isProcessRunning($processName, $full = false, $refresh = false) {
  if ($full) {
    static $processes_full = array();
    if ($refresh) $processes_full = array();
    if (empty($processes_full))
      exec('ps -eo args', $processes_full);
  } else {
    static $processes = array();
    if ($refresh) $processes = array();
    if (empty($processes))
      exec('ps -eo comm', $processes);
  }
  foreach (($full ? $processes_full : $processes) as $processString) {
    if (strpos($processString, $processName) !== false)
      return true;
  }
  return false;
}

function createConfigLines() { 
	$out ="";
	foreach($_GET as $key=>$val) { 
		if($key != "cmd") {
			$out .= "define(\"$key\", \"$val\");"."\n";
		}
	}
	return $out;
} 

function getSize($filesize, $precision = 2) {
	$units = array('', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y');
	foreach ($units as $idUnit => $unit) {
		if ($filesize > 1024)
			$filesize /= 1024;
		else
			break;
	}
	return round($filesize, $precision).' '.$units[$idUnit].'B';
}

function checkSetup() {
	$el = error_reporting();
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	if (defined(DISTRIBUTION)) {
?>
<div class="alert alert-danger" role="alert">You are using an old config.php. Please configure your Dashboard by calling <a href="setup.php">setup.php</a>!</div>
<?php
		
		} else {
		if (file_exists ("setup.php")) {
	?>
	<div class="alert alert-danger" role="alert">You forgot to remove setup.php in root-directory of your dashboard or you forgot to configure it! Please delete the file or configure your Dashboard by calling <a href="setup.php">setup.php</a>!</div>
	<?php
		}
	}
	error_reporting($el);
}
?>
