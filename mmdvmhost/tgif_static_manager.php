<?php
/**
 * TGIF Static Talkgroups add/remove form (admin-only).
 * TGIF Development: Andy G7LRR.
 *
 * Inline include, loaded only on the admin path. Structured exactly like
 * bm_manager.php: the API is touched only when an operator submits the form,
 * never during a plain page render. The read-only state display lives in the
 * companion AJAX partial tgif_static_links.php, refreshed on the same 180
 * second cadence bm_links.php uses.
 *
 * Like bm_manager.php it offers both timeslots unconditionally and lets the
 * API reject an unsupported one (reported as "That timeslot is not supported
 * by this hotspot session") rather than pre-filtering on capability flags —
 * that is what keeps the render path free of API calls.
 *
 * Complements the legacy tgif_manager.php link/unlink control; /index.php
 * shows one or the other, never both. Token management is not offered here:
 * the credential belongs to Expert > API Keys, just as /etc/bmapi.key belongs
 * to fulledit_bmapikey.php rather than to bm_manager.php.
 */

if ($_SERVER["PHP_SELF"] == "/admin/index.php") {
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tgif_static_common.php';

    // Single source of truth for whether this feature appears at all — see
    // tgif_static_context() for the three conditions.
    $tgifStaticContext = tgif_static_context($mmdvmconfigs);

    // Re-checked here rather than trusted from the caller so this include is
    // self-contained; index.php makes the same call to decide whether to show
    // this pair or the legacy TGIF manager.
    if ($tgifStaticContext !== false) {
        $tgifStaticMessage = '';
        $tgifStaticError = '';

        if (!empty($_POST) && isset($_POST['tgifStaticModify'])) {
            $slot = isset($_POST['tgifStaticSlot']) ? (int)$_POST['tgifStaticSlot'] : 0;
            $talkgroup = preg_replace(
                '/[^0-9]/',
                '',
                (string)(isset($_POST['tgifStaticTalkgroup']) ? $_POST['tgifStaticTalkgroup'] : '')
            );
            $action = (string)(isset($_POST['tgifStaticAction']) ? $_POST['tgifStaticAction'] : '');

            if (($slot !== 1 && $slot !== 2)
                || !preg_match('/^[0-9]{1,8}$/', $talkgroup)
                || (int)$talkgroup < 1) {
                $tgifStaticError = 'Enter a valid talkgroup and timeslot.';
            } elseif ($action !== 'ADD' && $action !== 'DEL') {
                $tgifStaticError = 'Choose Add or Remove.';
            } else {
                $response = tgif_static_api_request(
                    $tgifStaticContext['token'],
                    $tgifStaticContext['dmrID'],
                    ($action === 'ADD') ? 'POST' : 'DELETE',
                    array('slot' => $slot, 'talkgroup' => (int)$talkgroup)
                );
                if ((isset($response['status']) ? (int)$response['status'] : 0) === 200
                    && !empty($response['json']['ok'])) {
                    $tgifStaticMessage = ($action === 'ADD')
                        ? 'Static Talkgroup added.'
                        : 'Static Talkgroup removed.';
                } else {
                    $tgifStaticError = tgif_static_error_text($response);
                }
            }
            unset($_POST);
        }

        if ($tgifStaticMessage !== '' || $tgifStaticError !== '') {
            // Command Output plus a delayed reload, exactly as bm_manager.php
            // does after a submit. The reload re-renders the status partial,
            // which is what picks up the new state - so this file never makes
            // a second API call of its own.
            echo '<b>TGIF Static TG Manager</b>'."\n";
            echo "<table>\n<tr><th>Command Output</th></tr>\n<tr><td>";
            echo htmlspecialchars(
                $tgifStaticMessage !== '' ? $tgifStaticMessage : $tgifStaticError,
                ENT_QUOTES,
                'UTF-8'
            );
            echo "</td></tr>\n</table>\n";
            echo "<br />\n";
            echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},3000);</script>'."\n";
        } else {
            // Manager form — column-for-column the same layout as
            // bm_manager.php's static TG manager, including leaving the
            // talkgroup field without a required attribute (the value is
            // validated server-side above).
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
            echo '<td role="radiogroup" aria-labelledby="lblTgifSlot"><input id="rbTgifTS1" type="radio" name="tgifStaticSlot" value="1" /><label for="rbTgifTS1">TS1</label> <input id="rbTgifTS2" type="radio" name="tgifStaticSlot" value="2" checked="checked" /><label for="rbTgifTS2">TS2</label></td>';
            echo '<td role="radiogroup" aria-labelledby="lblTgifAddRemove"><input id="rbTgifAdd" type="radio" name="tgifStaticAction" value="ADD" checked="checked" /><label for="rbTgifAdd">Add</label> <input id="rbTgifDel" type="radio" name="tgifStaticAction" value="DEL" /><label for="rbTgifDel">Remove</label></td>';
            echo '<td><input type="submit" value="Modify Static" name="tgifStaticModify" /></td>';
            echo '</tr>'."\n";
            echo '  </table>'."\n";
            echo '  <br />'."\n";
            echo '</form>'."\n";
        }
    }
}
