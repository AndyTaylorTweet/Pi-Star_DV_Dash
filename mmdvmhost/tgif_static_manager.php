<?php
/**
 * TGIF Static Talkgroups API manager (admin-only).
 * TGIF Development: Andy G7LRR.
 *
 * Complements the legacy tgif_manager.php link/unlink control. It does not
 * replace or call the TCP/5040 API. The bearer credential remains server-side
 * in /etc/tgifapi.key and is never emitted into HTML.
 *
 * Client contract:
 * - one GET when the admin page is rendered;
 * - POST/DELETE only on an operator action;
 * - no polling, timer or automatic retry loop.
 */

if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';

    function tgif_static_detect_dmr_id($mmdvmconfigs)
    {
        $dmrID = "";
        if (getConfigItem("DMR", "Enable", $mmdvmconfigs) != 1) {
            return $dmrID;
        }

        $dmrGatewayConfigFile = '/etc/dmrgateway';
        $configdmrgateway = array();
        if (is_readable($dmrGatewayConfigFile)) {
            $parsed = parse_ini_file($dmrGatewayConfigFile, true);
            if (is_array($parsed)) {
                $configdmrgateway = $parsed;
            }
        }

        $dmrMasterHost = getConfigItem("DMR Network", "Address", $mmdvmconfigs);
        if ($dmrMasterHost == '127.0.0.1') {
            for ($network = 1; $network <= 5; $network++) {
                $section = 'DMR Network '.$network;
                if (!isset($configdmrgateway[$section])) {
                    continue;
                }
                $cfg = $configdmrgateway[$section];
                if (($cfg['Address'] ?? '') == 'tgif.network'
                    && !empty($cfg['Enabled'])
                    && isset($cfg['Id'])) {
                    $dmrID = preg_replace('/[^0-9]/', '', (string)$cfg['Id']);
                }
            }
        } elseif ($dmrMasterHost == 'tgif.network') {
            $candidate = getConfigItem("DMR", "Id", $mmdvmconfigs);
            if (!$candidate) {
                $candidate = getConfigItem("General", "Id", $mmdvmconfigs);
            }
            $dmrID = preg_replace('/[^0-9]/', '', (string)$candidate);
        }

        if (!preg_match('/^[0-9]{6,9}$/', $dmrID)) {
            return "";
        }
        return $dmrID;
    }

    function tgif_static_read_token($path)
    {
        if (!is_readable($path)) {
            return "";
        }
        // parse_ini_file() handles the [key]/apikey shape and strips any
        // quoting itself — same read path as bm_manager.php uses for
        // /etc/bmapi.key, so there is no hand-rolled line parser here.
        $parsed = @parse_ini_file($path, true);
        if (!is_array($parsed) || !isset($parsed['key']['apikey'])) {
            return "";
        }
        $value = trim((string)$parsed['key']['apikey']);
        if (preg_match('/^TGIFSTG1\.[A-Fa-f0-9]{16}\.[A-Fa-f0-9]{16}\.[A-Za-z0-9_-]{40,64}$/', $value)) {
            return $value;
        }
        return "";
    }

    function tgif_static_write_config($path, $token)
    {
        if ($token !== ''
            && !preg_match('/^TGIFSTG1\.[A-Fa-f0-9]{16}\.[A-Fa-f0-9]{16}\.[A-Za-z0-9_-]{40,64}$/', $token)) {
            return 'Token format is not valid.';
        }

        // Same on-disk shape as /etc/bmapi.key and /etc/dapnetapi.key: an
        // ini file holding the credential and nothing else. The API URL is
        // a code constant (see tgif_static_api_request()), not operator
        // configuration, and the DMR ID is derived from /etc/mmdvmhost and
        // /etc/dmrgateway on every render by tgif_static_detect_dmr_id() —
        // storing either here would only create a stale second copy.
        $content = "[key]\napikey=" . ($token !== '' ? $token : 'None') . "\n";

        $tmp = tempnam('/tmp', 'pistar-tgif-');
        if ($tmp === false) {
            return 'Could not create a temporary configuration file.';
        }
        @chmod($tmp, 0600);
        if (file_put_contents($tmp, $content) === false) {
            @unlink($tmp);
            return 'Could not write the temporary configuration file.';
        }

        $rwRc = 0;
        exec('sudo -n mount -o remount,rw / 2>&1', $rwOut, $rwRc);
        if ($rwRc !== 0) {
            @unlink($tmp);
            return 'Could not make the Pi-Star filesystem writable.';
        }

        $installRc = 0;
        $installOut = array();
        exec('sudo -n install -m 600 -o www-data -g www-data '
             . escapeshellarg($tmp) . ' ' . escapeshellarg($path)
             . ' 2>&1', $installOut, $installRc);

        @unlink($tmp);

        $roRc = 0;
        exec('sudo -n mount -o remount,ro / 2>&1', $roOut, $roRc);

        if ($installRc !== 0) {
            return 'Could not install the TGIF API configuration.';
        }
        if ($roRc !== 0) {
            return 'Configuration saved, but the Pi-Star filesystem could not be returned to read-only mode.';
        }
        return '';
    }

    function tgif_static_api_request($token, $dmrID, $method, $body = null)
    {
        $url = 'https://api.tgif.network/v1/static-talkgroups/' . rawurlencode($dmrID);
        $headers = array(
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
            'User-Agent: Pi-Star TGIF Static TG Manager/' . $dmrID,
        );

        $http = array(
            'method' => $method,
            'timeout' => 5,
            'ignore_errors' => true,
            'follow_location' => 0,
            'max_redirects' => 0,
        );

        if ($body !== null) {
            $json = json_encode($body);
            if (!is_string($json)) {
                return array('status' => 0, 'json' => array('error' => 'json_encode_failed'));
            }
            $headers[] = 'Content-Type: application/json';
            $http['content'] = $json;
        }

        $http['header'] = implode("\r\n", $headers) . "\r\n";

        $context = stream_context_create(array(
            'http' => $http,
            'ssl' => array(
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
                'cafile' => '/etc/ssl/certs/ca-certificates.crt',
                'SNI_enabled' => true,
            ),
        ));

        $result = @file_get_contents($url, false, $context);
        $status = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $responseHeader) {
                if (preg_match('#^HTTP/\\S+\\s+([0-9]{3})\\b#', $responseHeader, $matches)) {
                    $status = (int)$matches[1];
                    break;
                }
            }
        }

        $decoded = is_string($result) ? json_decode($result, true) : null;
        return array(
            'status' => $status,
            'json' => is_array($decoded) ? $decoded : array(),
        );
    }

    function tgif_static_error_text($response)
    {
        $status = (int)($response['status'] ?? 0);
        $error = (string)($response['json']['error'] ?? '');

        $friendly = array(
            'invalid_token' => 'The Static TG API token is invalid or has been revoked.',
            'token_device_mismatch' => 'This token was created for a different hotspot ID / ESSID.',
            'device_not_connected' => 'The hotspot is not currently connected to TGIF.',
            'device_not_owned' => 'TGIF does not see this hotspot as belonging to the token account.',
            'secure_hotspot_required' => 'TGIF Hotspot Security is required for Static Talkgroups.',
            'static_tg_not_available' => 'Static Talkgroups are not available for this account.',
            'static_tg_disabled' => 'Static Talkgroups are currently disabled on TGIF.',
            'unsupported_slot' => 'That timeslot is not supported by this hotspot session.',
            'invalid_or_reserved_talkgroup' => 'That talkgroup is invalid or reserved for another TGIF function.',
            'limit_exceeded' => 'The Static Talkgroup limit for this account has been reached.',
            'membership_conflict' => 'The Static Talkgroup state changed at the same time. Refresh and try again.',
        );

        if (isset($friendly[$error])) {
            return $friendly[$error];
        }
        if ($status === 429) {
            return 'TGIF rate limited the request. Wait before trying again.';
        }
        if ($status >= 500) {
            return 'TGIF could not complete the request. Try again later.';
        }
        if ($status === 0) {
            return 'No response from the TGIF Static Talkgroups API.';
        }
        return 'TGIF API returned HTTP ' . $status . ($error !== '' ? ' (' . $error . ')' : '') . '.';
    }

    $tgifStaticConfig = '/etc/tgifapi.key';
    $tgifStaticDmrID = tgif_static_detect_dmr_id($mmdvmconfigs);
    $tgifStaticToken = tgif_static_read_token($tgifStaticConfig);

    // Minimum release that is guaranteed to carry the /etc/tgifapi.key entry
    // in /etc/sudoers.d/pistar-dashboard. Below this the Clear Token write
    // would be refused by sudo, so the panel stays hidden entirely.
    $tgifStaticRelease = isset($configPistarRelease['Pi-Star']['Version'])
        ? (string)$configPistarRelease['Pi-Star']['Version']
        : '';
    $tgifStaticSupported = ($tgifStaticRelease !== ''
        && version_compare($tgifStaticRelease, '4.3.9', '>='));

    // Three conditions, all required before this panel renders or touches the
    // network: the release carries the sudoers entry; TGIF is actually a
    // configured DMR network on this hotspot; and a valid token has been
    // stored. The token is set in Expert > TGIF API
    // (admin/expert/fulledit_tgifapikey.php), mirroring how bmapi.key is
    // owned by fulledit_bmapikey.php rather than by bm_manager.php — so an
    // operator who has not opted in pays no page-load cost at all.
    if ($tgifStaticSupported && $tgifStaticDmrID !== '' && $tgifStaticToken !== '') {
        $tgifStaticMessage = '';
        $tgifStaticError = '';
        $tgifStaticState = array();

        if (!empty($_POST) && isset($_POST['tgifStaticClearToken'])) {
            $err = tgif_static_write_config($tgifStaticConfig, '');
            if ($err === '') {
                $tgifStaticToken = '';
                $tgifStaticMessage = 'TGIF Static TG API token cleared.';
            } else {
                $tgifStaticError = $err;
            }
            unset($_POST);
        } elseif (!empty($_POST) && isset($_POST['tgifStaticModify'])) {
            if ($tgifStaticToken === '') {
                $tgifStaticError = 'Configure a TGIF Static TG API token first.';
            } else {
                $slot = (int)($_POST['tgifStaticSlot'] ?? 0);
                $talkgroup = preg_replace('/[^0-9]/', '', (string)($_POST['tgifStaticTalkgroup'] ?? ''));
                $action = (string)($_POST['tgifStaticAction'] ?? '');

                if (($slot !== 1 && $slot !== 2) || !preg_match('/^[0-9]{1,8}$/', $talkgroup) || (int)$talkgroup < 1) {
                    $tgifStaticError = 'Enter a valid talkgroup and timeslot.';
                } elseif ($action !== 'ADD' && $action !== 'DEL') {
                    $tgifStaticError = 'Choose Add or Remove.';
                } else {
                    $method = ($action === 'ADD') ? 'POST' : 'DELETE';
                    $response = tgif_static_api_request(
                        $tgifStaticToken,
                        $tgifStaticDmrID,
                        $method,
                        array('slot' => $slot, 'talkgroup' => (int)$talkgroup)
                    );
                    if (($response['status'] ?? 0) === 200 && !empty($response['json']['ok'])) {
                        $tgifStaticMessage = ($action === 'ADD')
                            ? 'Static Talkgroup added.'
                            : 'Static Talkgroup removed.';

                        // Mutation responses do not include the capability and
                        // limit fields used by this form. Perform one bounded
                        // refresh after the operator-initiated write.
                        $refresh = tgif_static_api_request(
                            $tgifStaticToken,
                            $tgifStaticDmrID,
                            'GET'
                        );
                        if (($refresh['status'] ?? 0) === 200 && !empty($refresh['json']['ok'])) {
                            $tgifStaticState = $refresh['json'];
                        } else {
                            $tgifStaticState = $response['json'];
                        }
                    } else {
                        $tgifStaticError = tgif_static_error_text($response);
                    }
                }
            }
            unset($_POST);
        }

        if ($tgifStaticToken !== '' && empty($tgifStaticState)) {
            $response = tgif_static_api_request($tgifStaticToken, $tgifStaticDmrID, 'GET');
            if (($response['status'] ?? 0) === 200 && !empty($response['json']['ok'])) {
                $tgifStaticState = $response['json'];
            } elseif ($tgifStaticError === '') {
                $tgifStaticError = tgif_static_error_text($response);
            }
        }

        // Rendered to match the BrandMeister panels rather than the stacked
        // colspan rows this started out with: a status table in the shape of
        // bm_links.php's "Active BrandMeister Connections", then a manager
        // form laid out horizontally with tooltip headers exactly like
        // bm_manager.php, and a Command Output table for action feedback.
        $memberships = isset($tgifStaticState['memberships']) && is_array($tgifStaticState['memberships'])
            ? $tgifStaticState['memberships']
            : array();
        $supportsTs1 = !empty($tgifStaticState['supports_ts1']);
        $supportsTs2 = !empty($tgifStaticState['supports_ts2']);
        $apiEnabled = !empty($tgifStaticState['enabled']);
        $limit = isset($tgifStaticState['limit']) ? (int)$tgifStaticState['limit'] : 0;

        // Build a per-slot talkgroup list in the same "None" / comma-joined
        // style bm_links.php uses for its static and dynamic TG cells.
        $ts1TGs = array();
        $ts2TGs = array();
        foreach ($memberships as $membership) {
            $membershipSlot = (int)($membership['slot'] ?? 0);
            $membershipTG = (int)($membership['talkgroup'] ?? 0);
            if ($membershipTG < 1) {
                continue;
            }
            if ($membershipSlot === 1) {
                $ts1TGs[] = $membershipTG;
            } elseif ($membershipSlot === 2) {
                $ts2TGs[] = $membershipTG;
            }
        }
        $ts1List = $supportsTs1 ? (empty($ts1TGs) ? 'None' : implode(', ', $ts1TGs)) : 'Not supported';
        $ts2List = $supportsTs2 ? (empty($ts2TGs) ? 'None' : implode(', ', $ts2TGs)) : 'Not supported';
        if ($supportsTs1 && $limit > 0) { $ts1List .= ' (' . count($ts1TGs) . '/' . $limit . ')'; }
        if ($supportsTs2 && $limit > 0) { $ts2List .= ' (' . count($ts2TGs) . '/' . $limit . ')'; }

        // Command Output — same feedback table bm_manager.php prints after a
        // submit. No setTimeout reload here: a successful Modify already
        // re-reads the state, and reloading would spend a second API call.
        if ($tgifStaticMessage !== '' || $tgifStaticError !== '') {
            echo '<b>TGIF Static TG Manager</b>'."\n";
            echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
            echo htmlspecialchars(
                $tgifStaticMessage !== '' ? $tgifStaticMessage : $tgifStaticError,
                ENT_QUOTES,
                'UTF-8'
            );
            echo "</td></tr>\n</table>\n";
            echo "<br />\n";
        }

        echo '<b>TGIF Static Talkgroups</b>
    <table>
      <tr>
        <th><a class=tooltip href="#">Repeater ID<span><b>The ID for this Repeater/Hotspot</b></span></a></th>
        <th><a class=tooltip href="#">TS1 Static TGs<span><b>Statically linked talkgroups on timeslot 1</b></span></a></th>
        <th><a class=tooltip href="#">TS2 Static TGs<span><b>Statically linked talkgroups on timeslot 2</b></span></a></th>
      </tr>'."\n";
        echo '    <tr>'."\n";
        echo '      <td>'.htmlspecialchars((string)$tgifStaticDmrID, ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td>'.htmlspecialchars($ts1List, ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td>'.htmlspecialchars($ts2List, ENT_QUOTES, 'UTF-8').'</td>';
        echo '</tr>'."\n";
        echo '  </table>'."\n";
        echo '  <br />'."\n";

        if (!$apiEnabled && !empty($tgifStaticState)) {
            echo '<b>TGIF Static TG Manager</b>'."\n";
            echo "<table>\n<tr><th>Status</th></tr>\n<tr><td>Static Talkgroups are disabled by TGIF.</td></tr>\n</table>\n";
            echo "<br />\n";
        } elseif (!$supportsTs1 && !$supportsTs2 && !empty($tgifStaticState)) {
            echo '<b>TGIF Static TG Manager</b>'."\n";
            echo "<table>\n<tr><th>Status</th></tr>\n<tr><td>No supported timeslot reported for this hotspot session.</td></tr>\n</table>\n";
            echo "<br />\n";
        } elseif ($tgifStaticError === '') {
            // Manager form — column-for-column the same layout as
            // bm_manager.php's static TG manager. Clear Token rides in the
            // same form as a second submit, the way bm_manager.php carries
            // Drop QSO / Drop All Dynamic, so there is no nested form. The
            // talkgroup field deliberately has no required attribute: it
            // would otherwise block a Clear Token submit, and the server
            // validates the value anyway.
            echo '<b>TGIF Static TG Manager</b>'."\n";
            echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post">'."\n";
            echo csrf_field_html()."\n";
            echo '<table role="presentation">'."\n";
            echo '<tr>
              <th aria-hidden="true" id="lblTgifTG" style="width:25%;"><a class=tooltip href="#">Static Talkgroup<span><b>Enter the Talkgroup number</b></span></a></th>
              <th aria-hidden="true" id="lblTgifSlot" style="width:25%;"><a class=tooltip href="#">Slot<span><b>Where to add/remove</b></span></a></th>
              <th aria-hidden="true" id="lblTgifAddRemove" style="width:25%;"><a class=tooltip href="#">Add / Remove<span><b>Add or Remove</b></span></a></th>
              <th><a class=tooltip href="#">Action<span><b>Take Action</b></span></a></th>
            </tr>'."\n";
            echo '    <tr>';
            echo '<td><input aria-labelledby="lblTgifTG" type="text" inputmode="numeric" name="tgifStaticTalkgroup" size="10" maxlength="8" /></td>';
            echo '<td role="radiogroup" aria-labelledby="lblTgifSlot">';
            if ($supportsTs1) {
                echo '<input id="rbTgifTS1" type="radio" name="tgifStaticSlot" value="1"'.(!$supportsTs2 ? ' checked="checked"' : '').' /><label for="rbTgifTS1">TS1</label> ';
            }
            if ($supportsTs2) {
                echo '<input id="rbTgifTS2" type="radio" name="tgifStaticSlot" value="2" checked="checked" /><label for="rbTgifTS2">TS2</label>';
            }
            echo '</td>';
            echo '<td role="radiogroup" aria-labelledby="lblTgifAddRemove"><input id="rbTgifAdd" type="radio" name="tgifStaticAction" value="ADD" checked="checked" /><label for="rbTgifAdd">Add</label> <input id="rbTgifDel" type="radio" name="tgifStaticAction" value="DEL" /><label for="rbTgifDel">Remove</label></td>';
            echo '<td><input type="submit" value="Modify Static" name="tgifStaticModify" /></td>';
            echo '</tr>'."\n";
            echo '    <tr>';
            echo '<td colspan="4" style="background: #ffffff;"><a class=tooltip href="https://tgif.network/api_helper.php" target="_blank" rel="noopener noreferrer">TGIF API Help<span><b>Open the TGIF API helper</b></span></a> &nbsp; <input type="submit" value="Clear Token" name="tgifStaticClearToken" onclick="return confirm(\'Clear the saved TGIF Static TG API token?\');" /></td>';
            echo '</tr>'."\n";
            echo '  </table>'."\n";
            echo '  <br />'."\n";
            echo '</form>'."\n";
        }
    }
}
