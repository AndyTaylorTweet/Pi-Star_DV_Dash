<?php
/**
 * TGIF Static Talkgroups status panel.
 *
 * AJAX-loaded partial; refreshed every 180 seconds by /index.php — the same
 * slow cadence bm_links.php uses, and for the same reason: each load hits a
 * third-party HTTPS API. Renders a read-only table of the static talkgroup
 * subscriptions TGIF holds for the configured DMR ID.
 *
 * Inputs:
 *   - /etc/mmdvmhost, /etc/dmrgateway   Works out whether TGIF is a live DMR
 *                                      network and which DMR ID applies.
 *   - /etc/tgifapi.key                  Bearer token, [key]/apikey ini.
 *   - /etc/pistar-release               Release gate (4.3.9+).
 *   - https://api.tgif.network          Live API for static TG state.
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
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tgif_static_common.php';     // TGIF static TG helpers

$tgifLinksContext = tgif_static_context($mmdvmconfigs);
if ($tgifLinksContext !== false) {
    $tgifLinksState = array();
    $tgifLinksError = '';

    $response = tgif_static_api_request($tgifLinksContext['token'], $tgifLinksContext['dmrID'], 'GET');
    if ((isset($response['status']) ? (int)$response['status'] : 0) === 200
        && !empty($response['json']['ok'])) {
        $tgifLinksState = $response['json'];
    } else {
        $tgifLinksError = tgif_static_error_text($response);
    }

    $memberships = isset($tgifLinksState['memberships']) && is_array($tgifLinksState['memberships'])
        ? $tgifLinksState['memberships']
        : array();
    $supportsTs1 = !empty($tgifLinksState['supports_ts1']);
    $supportsTs2 = !empty($tgifLinksState['supports_ts2']);
    $apiEnabled = !empty($tgifLinksState['enabled']);
    $limit = isset($tgifLinksState['limit']) ? (int)$tgifLinksState['limit'] : 0;

    // Per-slot talkgroup lists in the same "None" / comma-joined style
    // bm_links.php uses for its static and dynamic TG cells.
    $ts1TGs = array();
    $ts2TGs = array();
    foreach ($memberships as $membership) {
        $membershipSlot = isset($membership['slot']) ? (int)$membership['slot'] : 0;
        $membershipTG = isset($membership['talkgroup']) ? (int)$membership['talkgroup'] : 0;
        if ($membershipTG < 1) {
            continue;
        }
        if ($membershipSlot === 1) {
            $ts1TGs[] = $membershipTG;
        } elseif ($membershipSlot === 2) {
            $ts2TGs[] = $membershipTG;
        }
    }

    if ($tgifLinksError !== '') {
        $ts1List = $ts2List = 'Unavailable';
    } elseif (!$apiEnabled) {
        $ts1List = $ts2List = 'Disabled by TGIF';
    } else {
        $ts1List = $supportsTs1 ? (empty($ts1TGs) ? 'None' : implode(', ', $ts1TGs)) : 'Not supported';
        $ts2List = $supportsTs2 ? (empty($ts2TGs) ? 'None' : implode(', ', $ts2TGs)) : 'Not supported';
        if ($supportsTs1 && $limit > 0) { $ts1List .= ' (' . count($ts1TGs) . '/' . $limit . ')'; }
        if ($supportsTs2 && $limit > 0) { $ts2List .= ' (' . count($ts2TGs) . '/' . $limit . ')'; }
    }

    echo '<b>TGIF Static Talkgroups</b>
    <table>
      <tr>
        <th><a class=tooltip href="#">Repeater ID<span><b>The ID for this Repeater/Hotspot</b></span></a></th>
        <th><a class=tooltip href="#">TS1 Static TGs<span><b>Statically linked talkgroups on timeslot 1</b></span></a></th>
        <th><a class=tooltip href="#">TS2 Static TGs<span><b>Statically linked talkgroups on timeslot 2</b></span></a></th>
      </tr>'."\n";
    echo '    <tr>'."\n";
    echo '      <td>'.htmlspecialchars((string)$tgifLinksContext['dmrID'], ENT_QUOTES, 'UTF-8').'</td>';
    echo '<td>'.htmlspecialchars($ts1List, ENT_QUOTES, 'UTF-8').'</td>';
    echo '<td>'.htmlspecialchars($ts2List, ENT_QUOTES, 'UTF-8').'</td>';
    echo '</tr>'."\n";
    echo '  </table>'."\n";
    echo '  <br />'."\n";

    if ($tgifLinksError !== '') {
        echo '<table>'."\n";
        echo '<tr><th>TGIF API</th></tr>'."\n";
        echo '<tr><td>'.htmlspecialchars($tgifLinksError, ENT_QUOTES, 'UTF-8').'</td></tr>'."\n";
        echo '</table>'."\n";
        echo '<br />'."\n";
    }
}
