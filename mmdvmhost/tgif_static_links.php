<?php
/**
 * Active TGIF Connections panel.
 *
 * AJAX-loaded partial; refreshed every 180 seconds by /index.php — the same
 * slow cadence bm_links.php uses, and for the same reason: each load hits a
 * third-party HTTPS API. Modelled on bm_links.php's "Active BrandMeister
 * Connections" table: one row for the connected master and DMR ID, with the
 * static and dynamic talkgroup subscriptions in their own cells.
 *
 * TGIF serves the two kinds from separate endpoints, so this makes two calls
 * per refresh where BrandMeister returns both in one response. They are
 * independent: if one endpoint fails its cell reads "Unavailable" and the
 * other is still shown.
 *
 * Inputs:
 *   - /etc/mmdvmhost, /etc/dmrgateway   Works out whether TGIF is a live DMR
 *                                      network and which DMR ID applies.
 *   - /etc/tgifapi.key                  Bearer token, [key]/apikey ini.
 *   - /etc/pistar-release               Release gate (4.3.9+).
 *   - https://api.tgif.network          /v1/static-talkgroups/{id} and
 *                                      /v1/dynamic-talkgroups/{id}.
 *
 * Display-only. The companion tgif_static_manager.php provides the
 * add/remove form, exactly as bm_manager.php companions bm_links.php.
 *
 * AJAX-loaded partial — embeddable variant only. Omits the
 * X-Frame-Options / frame-ancestors directives the parent already asserts;
 * they apply to iframe ancestry, not XHR responses.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/security_headers.php');
setEmbeddableSecurityHeaders();

include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';                   // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';                 // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';             // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';                  // Translation Code
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tgif_static_common.php';     // TGIF TG helpers

$tgifLinksContext = tgif_static_context($mmdvmconfigs);
if ($tgifLinksContext !== false) {
    $tgifLinksErrors = array();

    // --- Static talkgroups: a list of {slot, talkgroup} memberships --------
    $staticState = array();
    $response = tgif_api_request(
        $tgifLinksContext['token'],
        $tgifLinksContext['dmrID'],
        'static-talkgroups',
        'GET'
    );
    if ((isset($response['status']) ? (int)$response['status'] : 0) === 200
        && !empty($response['json']['ok'])) {
        $staticState = $response['json'];
    } else {
        $tgifLinksErrors[] = 'Static: ' . tgif_static_error_text($response);
    }

    // --- Dynamic talkgroups: one talkgroup per timeslot -------------------
    // Shape per the TGIF dynamic endpoint: ts1_talkgroup / ts2_talkgroup,
    // where 0 (or absent) means nothing is linked on that slot.
    $dynamicState = array();
    $dynamicDenied = false;
    $dynamicResponse = tgif_api_request(
        $tgifLinksContext['token'],
        $tgifLinksContext['dmrID'],
        'dynamic-talkgroups',
        'GET'
    );
    if ((isset($dynamicResponse['status']) ? (int)$dynamicResponse['status'] : 0) === 200
        && !empty($dynamicResponse['json']['ok'])) {
        $dynamicState = $dynamicResponse['json'];
    } else {
        // Dynamic control is a separate entitlement on the TGIF account - a
        // token that reads static fine can still be refused here. Call that
        // out in the cell rather than the generic "Unavailable", which would
        // read as an outage.
        $dynamicDenied = (isset($dynamicResponse['json']['error'])
            && $dynamicResponse['json']['error'] === 'dynamic_tg_permission_required');
        $tgifLinksErrors[] = 'Dynamic: ' . tgif_static_error_text($dynamicResponse);
    }

    // Both cells use bm_links.php's "TG<n>(<slot>)" format, wrapped at 15
    // characters so a long list stacks inside the cell rather than stretching
    // the table, and "None" when the list comes back empty.
    $tgifStaticTGList = '';
    $memberships = isset($staticState['memberships']) && is_array($staticState['memberships'])
        ? $staticState['memberships']
        : array();
    foreach ($memberships as $membership) {
        $tgSlot = isset($membership['slot']) ? (int)$membership['slot'] : 0;
        $tgNum = isset($membership['talkgroup']) ? (int)$membership['talkgroup'] : 0;
        if ($tgNum > 0 && ($tgSlot === 1 || $tgSlot === 2)) {
            $tgifStaticTGList .= 'TG'.$tgNum.'('.$tgSlot.') ';
        }
    }
    $tgifStaticTGList = wordwrap($tgifStaticTGList, 15, "<br />\n");
    if (preg_match('/TG/', $tgifStaticTGList) == false) {
        $tgifStaticTGList = empty($staticState) ? 'Unavailable' : 'None';
    }

    $tgifDynamicTGList = '';
    foreach (array(1 => 'ts1_talkgroup', 2 => 'ts2_talkgroup') as $tgSlot => $field) {
        $tgNum = isset($dynamicState[$field]) ? (int)$dynamicState[$field] : 0;
        if ($tgNum > 0) {
            $tgifDynamicTGList .= 'TG'.$tgNum.'('.$tgSlot.') ';
        }
    }
    $tgifDynamicTGList = wordwrap($tgifDynamicTGList, 15, "<br />\n");
    if (preg_match('/TG/', $tgifDynamicTGList) == false) {
        if ($dynamicDenied) {
            $tgifDynamicTGList = 'Not permitted';
        } else {
            $tgifDynamicTGList = empty($dynamicState) ? 'Unavailable' : 'None';
        }
    }

    echo '<b>Active TGIF Connections</b>
    <table>
      <tr>
        <th><a class=tooltip href="#">TGIF Master<span><b>Connected Master</b></span></a></th>
        <th><a class=tooltip href="#">Repeater ID<span><b>The ID for this Repeater/Hotspot</b></span></a></th>
        <th><a class=tooltip href="#">Static TGs<span><b>Statically linked talkgroups</b></span></a></th>
        <th><a class=tooltip href="#">Dynamic TGs<span><b>Dynamically linked talkgroups</b></span></a></th>
      </tr>'."\n";
    echo '    <tr>'."\n";
    echo '      <td>tgif.network</td>';
    echo '<td>'.htmlspecialchars((string)$tgifLinksContext['dmrID'], ENT_QUOTES, 'UTF-8').'</td>';
    // The TG lists carry wordwrap-injected <br /> tags by design, so they are
    // intentionally not escaped here; every talkgroup and slot inside them was
    // cast to (int) above, matching the same reasoning in bm_links.php.
    echo '<td>'.$tgifStaticTGList.'</td>';
    echo '<td>'.$tgifDynamicTGList.'</td>';
    echo '</tr>'."\n";
    echo '  </table>'."\n";
    echo '  <br />'."\n";

    if (!empty($tgifLinksErrors)) {
        echo '<table>'."\n";
        echo '<tr><th>TGIF API</th></tr>'."\n";
        foreach ($tgifLinksErrors as $tgifLinksError) {
            echo '<tr><td>'.htmlspecialchars($tgifLinksError, ENT_QUOTES, 'UTF-8').'</td></tr>'."\n";
        }
        echo '</table>'."\n";
        echo '<br />'."\n";
    }
}
