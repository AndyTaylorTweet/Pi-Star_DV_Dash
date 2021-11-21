<?php
// THIS IS YOUR hearham.live station key:
$UPLOADKEY = 'YOURHEARHAMKEY';

$dir = dirname(__DIR__);
require_once $dir.'/config/config.php';          // MMDVMDash Config
include_once $dir.'/mmdvmhost/tools.php';        // MMDVMDash Tools
require_once $dir.'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $dir.'/config/language.php';	      // Translation Code
for($t=0; $t<60; $t+=5) {

    // Check if the config file exists
    if (file_exists('/etc/pistar-css.ini')) {
	// Use the values from the file
	$piStarCssFile = '/etc/pistar-css.ini';
	if (fopen($piStarCssFile,'r')) { $piStarCss = parse_ini_file($piStarCssFile, true); }

	// Set the Values from the config file
	if (isset($piStarCss['Lookup']['Service'])) { $callsignLookupSvc = $piStarCss['Lookup']['Service']; }		// Lookup Service "QRZ" or "RadioID"
	else { $callsignLookupSvc = "RadioID"; }										// Set the default if its missing										// Set the default if its missing
    } else {
	// Default values
	$callsignLookupSvc = "RadioID";
    }

    $nowUTC = strtotime(gmdate("Y-m-d H:i:s"));

    $i = 0;
    for ($i = 0;  ($i <= 19); $i++) { //Last 20 calls
	    if (isset($lastHeard[$i])) {
		    $listElem = $lastHeard[$i];
		    if ($listElem[5] == "RF"){
			    $date = strtotime($listElem[0]);
			    $type = $listElem[1];
			    $call = $listElem[2];
			    echo $nowUTC - $date;
			    echo 'seconds ago heard '.$call."\n";
			    if(  $nowUTC - $date < 6 ) {
				    system("curl -X POST -F 'key=$UPLOADKEY' -F 'hear=$call' https://hearham.com/api/audioDigitalLog");
			    }
		    }else{
		    //Not rf
		    }
	    }
    }

    sleep(5);
    //Re grab as in functions.php:
    $logLinesMMDVM = getMMDVMLog();
    $reverseLogLinesMMDVM = $logLinesMMDVM;
    array_multisort($reverseLogLinesMMDVM,SORT_DESC);
    $lastHeard = getLastHeard($reverseLogLinesMMDVM);

}
