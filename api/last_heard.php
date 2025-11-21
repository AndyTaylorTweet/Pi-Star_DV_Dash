<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions

header('Content-type: application/json');

$json_response = array();

$trans_history_count = count($lastHeard);
// Return the n most recent transmissions based on query string parameter, up to the maximum stored in history.
$num_transmissions = isset($_GET['num_transmissions']) ? intval($_GET['num_transmissions']) : $trans_history_count;
$transmissions = array_slice($lastHeard, 0, min($num_transmissions, $trans_history_count));

foreach ($transmissions as $transmission) {
    $transmission_json = array();
    $transmission_json['time_utc'] = trim($transmission[0]);
    $transmission_json['mode'] = trim($transmission[1]);
    $transmission_json['callsign'] = trim($transmission[2]);
    $transmission_json['callsign_suffix'] = trim($transmission[3]);
    $transmission_json['target'] = trim($transmission[4]);
    $transmission_json['src'] = trim($transmission[5]);
    $transmission_json['duration'] = trim($transmission[6]);
    $transmission_json['loss'] = trim($transmission[7]);
    $transmission_json['bit_error_rate'] = trim($transmission[8]);
    $transmission_json['rssi'] = trim($transmission[9]);

    $json_response[] = $transmission_json;
}
echo json_encode($json_response);
?>
