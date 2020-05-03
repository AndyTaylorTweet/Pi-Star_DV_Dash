<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

// Check if YSF is Enabled
$testMMDVModeYSF = getConfigItem("System Fusion", "Enable", $mmdvmconfigs);
if ( $testMMDVModeYSF == 1 ) {

  //Load the ysfgateway config file
  $ysfGatewayConfigFile = '/etc/ysfgateway';
  if (fopen($ysfGatewayConfigFile,'r')) { $configysfgateway = parse_ini_file($ysfGatewayConfigFile, true); }

  // Check that the remote is enabled
  if ( $configysfgateway['Remote Commands']['Enable'] == 1 ) {
    if (!empty($_POST) && isset($_POST["ysfMgrSubmit"])) {
      // Handle Posted Data
    } else {
      // Output HTML
      ?>
      <b>YSF Link Manager</b>
      <form action="http://<?php echo htmlentities($_SERVER['HTTP_HOST']).htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
      <table>
        <tr>
          <th width="150"><a class="tooltip" href="#">Reflector<span><b>Reflector</b></span></a></th>
          <th width="150"><a class="tooltip" href="#">Link / Un-Link<span><b>Link / Un-Link</b></span></a></th>
          <th width="150"><a class="tooltip" href="#">Action<span><b>Action</b></span></a></th>
        </tr>
        <tr>
          <td>
          </td>
          <td>
            <input type="radio" name="Link" value="LINK" checked="checked" />Link
            <input type="radio" name="Link" value="UNLINK" />UnLink
          </td>
          <td>
            <input type="submit" name="ysfMgrSubmit" value="Request Change" />
          </td>
        </tr>
      </table>
      </form>
      <br />
      <?php
    }
  }
}
?>
