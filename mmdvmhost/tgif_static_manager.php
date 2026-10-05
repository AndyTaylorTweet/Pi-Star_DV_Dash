<?php
/**
 * TGIF Static Talkgroups API manager (admin-only).
 * TGIF Development: Andy G7LRR.
 *
 * Complements the legacy tgif_manager.php link/unlink control. It does not
 * replace or call the TCP/5040 API. The bearer credential remains server-side
 * in /etc/tgif-static-api.conf and is never emitted into HTML.
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
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) {
            return "";
        }
        foreach ($lines as $line) {
            if (strpos($line, 'TGIF_STATIC_API_TOKEN=') !== 0) {
                continue;
            }
            $value = trim(substr($line, strlen('TGIF_STATIC_API_TOKEN=')));
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === "'" && $last === "'") || ($first === '"' && $last === '"')) {
                    $value = substr($value, 1, -1);
                }
            }
            if (preg_match('/^TGIFSTG1\.[A-Fa-f0-9]{16}\.[A-Fa-f0-9]{16}\.[A-Za-z0-9_-]{40,64}$/', $value)) {
                return $value;
            }
            return "";
        }
        return "";
    }

    function tgif_static_write_config($path, $token, $dmrID)
    {
        if ($token !== ''
            && !preg_match('/^TGIFSTG1\.[A-Fa-f0-9]{16}\.[A-Fa-f0-9]{16}\.[A-Za-z0-9_-]{40,64}$/', $token)) {
            return 'Token format is not valid.';
        }

        $content = "# TGIF Static Talkgroups API client configuration\n"
                 . "# Managed by the Pi-Star dashboard. Do not share this file.\n"
                 . "TGIF_STATIC_API_URL='https://api.tgif.network/v1/static-talkgroups'\n"
                 . "TGIF_STATIC_API_TOKEN='" . $token . "'\n"
                 . "TGIF_DMR_ID='" . $dmrID . "'\n";

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

    $tgifStaticConfig = '/etc/tgif-static-api.conf';
    $tgifStaticDmrID = tgif_static_detect_dmr_id($mmdvmconfigs);

    if ($tgifStaticDmrID !== '') {
        $tgifStaticMessage = '';
        $tgifStaticError = '';
        $tgifStaticToken = tgif_static_read_token($tgifStaticConfig);
        $tgifStaticState = array();

        if (!empty($_POST) && isset($_POST['tgifStaticSaveToken'])) {
            $candidate = trim((string)($_POST['tgifStaticToken'] ?? ''));
            $err = tgif_static_write_config($tgifStaticConfig, $candidate, $tgifStaticDmrID);
            if ($err === '') {
                $tgifStaticToken = $candidate;
                $tgifStaticMessage = 'TGIF Static TG API token saved.';
            } else {
                $tgifStaticError = $err;
            }
            unset($_POST);
        } elseif (!empty($_POST) && isset($_POST['tgifStaticClearToken'])) {
            $err = tgif_static_write_config($tgifStaticConfig, '', $tgifStaticDmrID);
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

        echo '<b>TGIF Static Talkgroups</b>'."\n";
        echo '<table role="presentation" style="width:100%;max-width:100%;table-layout:auto;box-sizing:border-box">'."\n";
        echo '<tr><th colspan="3">TGIF Static Talkgroups</th></tr>'."\n";

        if ($tgifStaticMessage !== '') {
            echo '<tr><td colspan="3" role="status"><b>' . htmlspecialchars($tgifStaticMessage, ENT_QUOTES, 'UTF-8') . '</b></td></tr>'."\n";
        }
        if ($tgifStaticError !== '') {
            echo '<tr><td colspan="3" role="alert"><b>' . htmlspecialchars($tgifStaticError, ENT_QUOTES, 'UTF-8') . '</b></td></tr>'."\n";
        }

        if ($tgifStaticToken === '') {
            echo '<tr><td colspan="3">';
            echo '<form action="' . htmlentities($_SERVER['PHP_SELF']) . '" method="post" style="margin:0;max-width:100%">';
            echo csrf_field_html();
            echo '<label for="tgifStaticToken"><b>API Token</b></label> ';
            echo '<input id="tgifStaticToken" type="password" name="tgifStaticToken" size="28" maxlength="180" autocomplete="new-password" required="required" style="max-width:55%;box-sizing:border-box" /> ';
            echo '<input type="submit" value="Save" name="tgifStaticSaveToken" /> ';
            echo '<a href="https://tgif.network/static_tg_api_tokens.php" target="_blank" rel="noopener noreferrer">Create token</a> | <a href="https://tgif.network/api_helper.php" target="_blank" rel="noopener noreferrer">Help</a>';
            echo '</form></td></tr>'."\n";
        } else {
            $memberships = isset($tgifStaticState['memberships']) && is_array($tgifStaticState['memberships']) ? $tgifStaticState['memberships'] : array();
            $supportsTs1 = !empty($tgifStaticState['supports_ts1']);
            $supportsTs2 = !empty($tgifStaticState['supports_ts2']);
            $apiEnabled = !empty($tgifStaticState['enabled']);
            $limit = isset($tgifStaticState['limit']) ? (int)$tgifStaticState['limit'] : 0;
            $ts1Count = 0; $ts2Count = 0;
            foreach ($memberships as $membership) {
                $membershipSlot = (int)($membership['slot'] ?? 0);
                if ($membershipSlot === 1) $ts1Count++;
                if ($membershipSlot === 2) $ts2Count++;
            }

            echo '<tr><td colspan="3">';
            if ($supportsTs1) echo '<b>TS1: ' . $ts1Count . ($limit > 0 ? '/' . $limit : '') . '</b>' . ($supportsTs2 ? ' &nbsp; ' : '');
            if ($supportsTs2) echo '<b>TS2: ' . $ts2Count . ($limit > 0 ? '/' . $limit : '') . '</b>';
            echo '</td></tr>'."\n";

            if (!empty($memberships)) {
                echo '<tr><th>Slot</th><th>Talkgroup</th><th>Action</th></tr>'."\n";
                foreach ($memberships as $membership) {
                    $slot=(int)($membership['slot']??0); $tg=(int)($membership['talkgroup']??0);
                    echo '<tr><td>TS'.$slot.'</td><td><b>'.$tg.'</b></td><td>';
                    echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post" style="margin:0">'.csrf_field_html();
                    echo '<input type="hidden" name="tgifStaticTalkgroup" value="'.$tg.'" /><input type="hidden" name="tgifStaticSlot" value="'.$slot.'" /><input type="hidden" name="tgifStaticAction" value="DEL" /><input type="submit" value="Remove" name="tgifStaticModify" /></form></td></tr>'."\n";
                }
            } else {
                echo '<tr><td colspan="3">No static talkgroups configured.</td></tr>'."\n";
            }

            if (!$apiEnabled && !empty($tgifStaticState)) {
                echo '<tr><td colspan="3"><b>Static Talkgroups are disabled by TGIF.</b></td></tr>'."\n";
            } elseif (!$supportsTs1 && !$supportsTs2 && !empty($tgifStaticState)) {
                echo '<tr><td colspan="3"><b>No supported timeslot reported.</b></td></tr>'."\n";
            } elseif ($tgifStaticError === '') {
                echo '<tr><td colspan="3"><form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post" style="margin:0;white-space:normal">'.csrf_field_html();
                echo '<label for="tgifStaticTalkgroup"><b>Talkgroup</b></label> <input id="tgifStaticTalkgroup" type="text" inputmode="numeric" name="tgifStaticTalkgroup" size="8" maxlength="8" required="required" /> ';
                if ($supportsTs1) echo '<input id="tgifStaticTS1" type="radio" name="tgifStaticSlot" value="1"'.(!$supportsTs2?' checked="checked"':'').' /><label for="tgifStaticTS1">TS1</label> ';
                if ($supportsTs2) echo '<input id="tgifStaticTS2" type="radio" name="tgifStaticSlot" value="2" checked="checked" /><label for="tgifStaticTS2">TS2</label> ';
                echo '<input type="hidden" name="tgifStaticAction" value="ADD" /><input type="submit" value="Add" name="tgifStaticModify" /></form></td></tr>'."\n";
            }

            echo '<tr><td colspan="3"><small>API: <b>Connected</b> &nbsp; <a href="https://tgif.network/api_helper.php" target="_blank" rel="noopener noreferrer">Help</a> &nbsp; ';
            echo '<form action="'.htmlentities($_SERVER['PHP_SELF']).'" method="post" style="display:inline;margin:0">'.csrf_field_html().'<input type="submit" value="Clear Token" name="tgifStaticClearToken" onclick="return confirm(\'Clear the saved TGIF Static TG API token?\');" /></form></small></td></tr>'."\n";
        }
        echo '</table><br />'."\n";
    }
}
