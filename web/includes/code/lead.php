<?

	/*==================================================================*\
	######################################################################
	#                                                                    #
	# Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
	#                                                                    #
	# This file may not be redistributed in whole or part.               #
	# eDirectory is licensed on a per-domain basis.                      #
	#                                                                    #
	# ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
	#                                                                    #
	# http://www.edirectory.com | http://www.edirectory.com/license.html #
	######################################################################
	\*==================================================================*/

	# ----------------------------------------------------------------------------------------------------
	# * FILE: /includes/code/lead.php
	# ----------------------------------------------------------------------------------------------------

	if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $response = $url_base."/activity/leads/index.php?item_type=$item_type&item_id=$item_id&screen=$screen&letter=$letter";
        $errorMessage = "";

		if ($action == "delete" && $id) {
            mixpanel_track("Deleted a lead");
            $leadObj = new Lead($id);
            $leadObj->Delete();

            $response .= "&message=0";
            header("Location: ".$response);
            exit;
        } elseif ($action == "reply") {

            if (!validate_email($to)) {
                $errorMessage .= "&#149;&nbsp;".system_showText(LANG_MSG_ENTER_VALID_EMAIL_ADDRESS)."<br />";
            }

            if (!$message) {
                $errorMessage .= "&#149;&nbsp;".system_showText(LANG_LEAD_TYPEMESSAGE);
            }

            if (!$errorMessage) {
                mixpanel_track("Replied to a lead");
                $leadObj = new Lead($idLead);

                /* ModStores Hooks */
                if (!HookFire( "enhanced_lead_reply", [
                    "leadObj" => $leadObj,
                    "message" => $message,
                    "to"      => $to
                ])) {
                    $leadObj->Reply($message, $to, $reply_to);
                }

                $response .= "&message=1";
                if (!$isAjax) {
                    header("Location: ".$response);
                    exit;
                } else {
                    echo "ok";
                }
            } elseif($isAjax) {
                echo $errorMessage;
            }

        } elseif ($action == "forward") {

            if (!validate_email($to)) {
                $errorMessage .= "&#149;&nbsp;".system_showText(LANG_MSG_ENTER_VALID_EMAIL_ADDRESS)."<br />";
            }

            if (!$message) {
                $errorMessage .= "&#149;&nbsp;".system_showText(LANG_LEAD_TYPEMESSAGE);
            }

            if (!$errorMessage) {
                mixpanel_track("Forwarded a lead");
                $leadObj = new Lead($idLead);
                $leadObj->Forward($message, $to);
                $response .= "&message=2";
                header("Location: ".$response);
                exit;
            }

        }
    }
