<?php
/**
 * TGIF talkgroup link/unlink form (admin-only).
 *
 * Loaded inline by /index.php only on the admin path
 * (`$_SERVER["PHP_SELF"] == "/admin/index.php"` gate). Form fields:
 *   tgifNumber  — talkgroup number (numeric, validated)
 *   tgifSlot    — DMR slot 1 or 2
 *   tgifAction  — LINK or UNLINK (UNLINK uses default TG 4000)
 *
 * On submit, makes an HTTP GET to
 * http://tgif.network:5040/api/sessions/update/{id}/{slot}/{tg}
 * and triggers a 3 s `setTimeout` reload of the parent page.
 *
 * UPSTREAM API NOTE: TGIF retired the read-only `/api/sessions`
 * endpoint in late 2026 (now 404), and replaced active-TG visibility
 * with a Socket.IO + browser-session-token flow that doesn't fit a
 * backend-rendered hotspot dashboard. The live "Active TGIF
 * Connections" panel that used to sit above this form has been
 * removed for that reason. The link/unlink update endpoint this
 * file uses is still alive on plain HTTP — there's no HTTPS
 * variant on :5040, and tightening that side is gated on TGIF
 * shipping a documented replacement.
 *
 * Operators who want to see the current TG state should visit
 * https://tgif.network/profile.php?tab=SelfCare directly.
 */

if ($_SERVER["PHP_SELF"] == "/admin/index.php") { // Stop this working outside of the admin page
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tgif_static_common.php'; // TGIF HTTPS/token helpers

    function httpStatusText($code = 0) {
        // List of HTTP status codes.
        $statuslist = array(
            '100' => 'Continue',
            '101' => 'Switching Protocols',
            '200' => 'OK',
            '201' => 'Created',
            '202' => 'Accepted',
            '203' => 'Non-Authoritative Information',
            '204' => 'No Content',
            '205' => 'Reset Content',
            '206' => 'Partial Content',
            '300' => 'Multiple Choices',
            '302' => 'Found',
            '303' => 'See Other',
            '304' => 'Not Modified',
            '305' => 'Use Proxy',
            '400' => 'Bad Request',
            '401' => 'Unauthorized',
            '402' => 'Payment Required',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '405' => 'Method Not Allowed',
            '406' => 'Not Acceptable',
            '407' => 'Proxy Authentication Required',
            '408' => 'Request Timeout',
            '409' => 'Conflict',
            '410' => 'Gone',
            '411' => 'Length Required',
            '412' => 'Precondition Failed',
            '413' => 'Request Entity Too Large',
            '414' => 'Request-URI Too Long',
            '415' => 'Unsupported Media Type',
            '416' => 'Requested Range Not Satisfiable',
            '417' => 'Expectation Failed',
            '500' => 'Internal Server Error',
            '501' => 'Not Implemented',
            '502' => 'Bad Gateway',
            '503' => 'Service Unavailable',
            '504' => 'Gateway Timeout',
            '505' => 'HTTP Version Not Supported'
        );
        // Caste the status code to a string.
        $code = preg_replace("/[^0-9]/", "", $code);
        $code = (string)$code;
        // Determine if it exists in the array.
        if(array_key_exists($code, $statuslist) ) {
            // Return the status text
            return $statuslist[$code];
        } else {
            // If it doesn't exists, degrade by returning the code.
            return $code;
        }
    }

    // Set some Variable
    $dmrID = "";

    // Check if DMR is Enabled
    $testMMDVModeDMR = getConfigItem("DMR", "Enable", $mmdvmconfigs);

    if ( $testMMDVModeDMR == 1 ) {
      //Load the dmrgateway config file
      $dmrGatewayConfigFile = '/etc/dmrgateway';
      if (fopen($dmrGatewayConfigFile,'r')) { $configdmrgateway = parse_ini_file($dmrGatewayConfigFile, true); }

      // Get the current DMR Master from the config
      $dmrMasterHost = getConfigItem("DMR Network", "Address", $mmdvmconfigs);
      if ( $dmrMasterHost == '127.0.0.1' ) {
        // DMRGateway, need to check each config
        if (isset($configdmrgateway['DMR Network 1']['Address'])) {
          if (($configdmrgateway['DMR Network 1']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 1']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 1']['Id'];
          }
        }
        if (isset($configdmrgateway['DMR Network 2']['Address'])) {
          if (($configdmrgateway['DMR Network 2']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 2']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 2']['Id'];
          }
        }
        if (isset($configdmrgateway['DMR Network 3']['Address'])) {
          if (($configdmrgateway['DMR Network 3']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 3']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 3']['Id'];
          }
        }
        if (isset($configdmrgateway['DMR Network 4']['Address'])) {
          if (($configdmrgateway['DMR Network 4']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 4']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 4']['Id'];
          }
        }
        if (isset($configdmrgateway['DMR Network 5']['Address'])) {
          if (($configdmrgateway['DMR Network 5']['Address'] == "tgif.network") && ($configdmrgateway['DMR Network 5']['Enabled'])) {
        $dmrID = $configdmrgateway['DMR Network 5']['Id'];
          }
        }
      } else if ( $dmrMasterHost == 'tgif.network' ) {
        // MMDVMHost Connected directly to TGIF, get the ID form here
        if (getConfigItem("DMR", "Id", $mmdvmconfigs)) {
          $dmrID = getConfigItem("DMR", "Id", $mmdvmconfigs);
        } else {
          $dmrID = getConfigItem("General", "Id", $mmdvmconfigs);
        }
      }
    }

    // Prefer the authenticated TGIF context when available. This uses the
    // same device-ID detection as the Static TG feature without replacing
    // the legacy detection above.
    $tgifContext = tgif_static_context($mmdvmconfigs);
    if (is_array($tgifContext)) {
      $dmrID = $tgifContext['dmrID'];
    }

    if ( $dmrID ) {
      // Work out if the data has been posted or not
      if ( !empty($_POST) && isset($_POST["tgifSubmit"]) ): // Data has been posted for this page
        // Are we a repeater
        if ( getConfigItem("DMR Network", "Slot1", $mmdvmconfigs) == "0" ) {
        $targetSlot = "1";
        } else {
        $targetSlot = preg_replace("/[^0-9]/", "", $_POST["tgifSlot"]);
        $targetSlot--;
        }
        // Figure out what has been posted
        if ( (isset($_POST["tgifNumber"])) && (isset($_POST["tgifSubmit"])) ) {
          $targetTG = preg_replace("/[^0-9]/", "", $_POST["tgifNumber"]);
          if ($targetTG < 1) { $targetTG = "4000"; }
        } else {
          $targetTG = "4000";
        }
        if ($_POST["tgifAction"] == "UNLINK") { $targetTG = "4000"; }
        // Use the authenticated TGIF HTTPS API. The legacy :5040 endpoint
        // remains available for older Pi-Star installations.
        $result = array('status' => 0, 'json' => array('error' => 'tgif_api_key_required'));
        if (is_array($tgifContext)) {
          $result = tgif_dynamic_api_request(
              $tgifContext['token'],
              $dmrID,
              ((int)$targetSlot) + 1,
              $targetTG
          );
        }
        // Output to the browser
        echo '<b>TGIF Dynamic Manager</b>'."\n";
        echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
        //echo "Sending command to TGIF API";
        echo "TGIF API: ";
        if ((int)$result['status'] === 200 && !empty($result['json']['ok'])) {
          echo httpStatusText(200);
        } else {
          $error = isset($result['json']['error']) ? (string)$result['json']['error'] : '';
          echo httpStatusText((int)$result['status']);
          if ($error !== '') {
            echo ' (' . htmlentities($error) . ')';
          }
        }
        echo "</td></tr>\n</table>\n";
        echo "<br />\n";
        // Clean up...
        unset($_POST);
        echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>';
      else: // Do this when we are not handling post data
        $dynamicState = array('status' => 0, 'json' => array('error' => 'tgif_api_key_required'));
        if (is_array($tgifContext)) {
          $dynamicState = tgif_dynamic_api_state($tgifContext['token'], $dmrID);
        }
        echo '<b>TGIF Dynamic Talkgroups</b>'."\n";
        if ((int)$dynamicState['status'] === 200 && !empty($dynamicState['json']['ok'])) {
          $ts1 = isset($dynamicState['json']['ts1_talkgroup']) ? (int)$dynamicState['json']['ts1_talkgroup'] : 0;
          $ts2 = isset($dynamicState['json']['ts2_talkgroup']) ? (int)$dynamicState['json']['ts2_talkgroup'] : 0;
          echo '<table><tr><th>TS1 Dynamic TG</th><th>TS2 Dynamic TG</th></tr><tr>';
          echo '<td>' . ($ts1 > 0 ? 'TG ' . $ts1 : 'None') . '</td>';
          echo '<td>' . ($ts2 > 0 ? 'TG ' . $ts2 : 'None') . '</td>';
          echo '</tr></table><br />'."\n";
        } else {
          $error = isset($dynamicState['json']['error']) ? (string)$dynamicState['json']['error'] : '';
          echo '<table><tr><th>Current Dynamic TG</th></tr><tr><td>Unavailable';
          if ($error !== '') echo ' (' . htmlentities($error) . ')';
          echo '</td></tr></table><br />'."\n";
        }
        echo '<b>TGIF Dynamic Manager</b>'."\n";
        echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">'."\n";
        echo csrf_field_html()."\n";
        echo '<table>
        <tr>
          <th style="width:25%;"><a class=tooltip href="#">Talkgroup Number<span><b>Enter the Talkgroup number</b></span></a></th>
          <th style="width:25%;"><a class=tooltip href="#">Slot<span><b>Where to link/unlink</b></span></a></th>
          <th style="width:25%;"><a class=tooltip href="#">Link / Unlink<span><b>Link or unlink</b></span></a></th>
          <th><a class=tooltip href="#">Action<span><b>Take Action</b></span></a></th>
        </tr>
        <tr>
          <td><input type="text" name="tgifNumber" size="10" maxlength="7" /></td>
          <td><input type="radio" name="tgifSlot" value="1" />TS1 <input type="radio" name="tgifSlot" value="2" checked="checked" />TS2</td>
          <td><input type="radio" name="tgifAction" value="LINK" />Link <input type="radio" name="tgifAction" value="UNLINK" checked="checked" />UnLink</td>
          <td><input type="submit" value="Modify Dynamic" name="tgifSubmit" /></td>
        </tr>
        </table><br />'."\n";
      endif;
    }
}
