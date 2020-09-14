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
	# * FILE: /includes/code/add_account.php
	# ----------------------------------------------------------------------------------------------------

	if ($_SERVER["REQUEST_METHOD"] == "POST" || $AppRequest) {

		if (system_isHoneypotFilled()) {
			$message_account = LANG_ACCOUNTINFO_ERROR;

			return;
		}

		if (!$AppRequest) {
			$_POST["retype_password"] = $_POST["password"];
		}
		$validate_account = validate_addAccount($_POST, $message_account);
		$validate_contact = validate_form("contact", $_POST, $message_contact);

		if (!empty($_FILES["fileToUpload"])) {
			// ADDED BY BRIAN G. START
			$target_dir = EDIRECTORY_ROOT . "/../image_uploads/";
			if ($_FILES["fileToUpload"]['error'] !== 0) {
				$validate_contact = false;
				$message_account .= "&#149;&nbsp;" . "File upload too large</br>";
			} else {
				$imageFileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));

				if($imageFileType === 'jpeg'){
                    $imageFileType = 'jpg';
                }

				if (in_array($imageFileType, ['jpg', 'png', 'pdf'])) {
					// Check if image file is a actual image or fake image
					$tmp_name = $_FILES["fileToUpload"]["tmp_name"];
					$check = getimagesize($tmp_name);
					$isPdf = mime_content_type($tmp_name) === 'application/pdf';
					if ($check !== false || $isPdf) {
						$uploadOk = 1;

						if (!file_exists($target_dir)) {
							if (!mkdir($target_dir) && !is_dir($target_dir)) {
								throw new \RuntimeException(sprintf('Directory "%s" was not created', $target_dir));
							}
						}

						$target_file = $target_dir . $_POST["username"] . '.' . $imageFileType;
						@unlink($target_dir . $_POST["username"] . '.jpg');
						@unlink($target_dir . $_POST["username"] . '.png');
						@unlink($target_dir . $_POST["username"] . '.pdf');
						move_uploaded_file($tmp_name, $target_file);
					} else {
						$message_account .= "&#149;&nbsp;" . "File must be a jpg, png or pdf</br>";
						$validate_contact = false;
					}
				} else {
					$message_account .= "&#149;&nbsp;" . "File must be a jpg, png or pdf</br>";
					$validate_contact = false;
				}
			}
		}
		// ADDED BY BRIAN G. DONE

		if ($message_account && $message_contact) {
			$message_account .= "<br />";
		}

		if ($validate_account && $validate_contact) {
            $_POST["publish_contact"] = ($_POST["publish_contact"] ? "y" : "n");
            if (!$_POST["email"]) {
                $_POST["email"] = $_POST["username"];
            }

            if (SOCIALNETWORK_FEATURE == "off") {
                $_POST["is_sponsor"] = "y";
            }

			$account = new Account($_POST);
			$account->Save();
			$contact = new Contact($_POST);
			$contact->setNumber("account_id", $account->getNumber("id"));
			$contact->Save();

            if ($_POST["newsletter"]) {
                $_POST["name"] = $_POST["first_name"]." ".$_POST["last_name"];
                $_POST["type"] = "profile";
                arcamailer_addSubscriber($_POST, $success, $account->getNumber("id"));
            }

			$profileObj = new Profile(sess_getAccountIdFromSession());
			$profileObj->setNumber("account_id", $account->getNumber("id"));
            $profileObj->setString("nickname", $_POST["first_name"]." ".$_POST["last_name"]);
			$profileObj->Save();

			$accDomain = new Account_Domain($account->getNumber("id"), SELECTED_DOMAIN_ID);
			$accDomain->Save();
			$accDomain->saveOnDomain($account->getNumber("id"), $account, $contact, $profileObj);

            sess_registerAccountInSession($_POST["username"]);
            if (!$AppRequest) {
                setcookie("username_members", $_POST['username'], time()+60*60*24*30, "".EDIRECTORY_FOLDER."/");
            }

            /* ModStores Hooks */
            HookFire("accountcode_after_save", [
                "account"    => &$account,
                "contact"    => &$contact,
                "profileObj" => &$profileObj,
                "accDomain"  => &$accDomain,
            ]);

			/*****************************************************
			*
			* E-mail notify
			*
			******************************************************/
			setting_get("sitemgr_account_email", $sitemgr_account_email);
			$sitemgr_account_emails = explode(",", $sitemgr_account_email);

			// sending e-mail to user //////////////////////////////////////////////////////////////////////////
			if ($emailNotificationObj = system_checkEmail(SYSTEM_NEW_PROFILE)) {

                $linkActivation = system_getAccountActivationLink($account->getNumber("id"));

				$subject = $emailNotificationObj->getString("subject");
				$body = $emailNotificationObj->getString("body");
				$login_info = trim(system_showText(LANG_LABEL_USERNAME)).": ".$_POST["username"];
				$login_info .= ($emailNotificationObj->getString("content_type") == "text/html"? "<br />": "\n");
				$login_info .= trim(system_showText(LANG_LABEL_PASSWORD)).": ".$_POST["password"];
				$body = str_replace("ACCOUNT_LOGIN_INFORMATION", $login_info, $body);
                $body = str_replace("LINK_ACTIVATE_ACCOUNT", $linkActivation, $body);

				$body = system_replaceEmailVariables($body, $account->getNumber("id"), "account");

				$subject = system_replaceEmailVariables($subject, $account->getNumber("id"), "account");
				$body = html_entity_decode($body);
				$subject = html_entity_decode($subject);

                SymfonyCore::getContainer()->get('core.mailer')
                    ->newMail($subject, $body, $emailNotificationObj->getString( "content_type" ))
                    ->setTo($contact->getString( "email" ))
                    ->setBcc($emailNotificationObj->getString( "bcc" ))
                    ->send();
            }
			////////////////////////////////////////////////////////////////////////////////////////////////////

			// site manager warning message /////////////////////////////////////
            $accountViewLink = $account->getString("is_sponsor") == "y" ? "sponsor/sponsor" : "visitor/visitor";
            $emailSubject = system_showText(LANG_NOTIFY_NEWACCOUNT);
            $sitemgr_msg = system_showText(LANG_LABEL_SITE_MANAGER).",<br /><br />".system_showText(LANG_NOTIFY_NEWACCOUNT_1)." ".EDIRECTORY_TITLE.".<br />".system_showText(LANG_NOTIFY_NEWACCOUNT_2)."<br /><br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_USERNAME).": </b>".$account->getString("username")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_FIRST_NAME).": </b>".$contact->getString("first_name")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_LAST_NAME).": </b>".$contact->getString("last_name")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_COMPANY).": </b>".$contact->getString("company")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_ADDRESS).": </b>".$contact->getString("address")." ".$contact->getString("address2")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_CITY).": </b>".$contact->getString("city")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_STATE).": </b>".$contact->getString("state")."<br />";
            $sitemgr_msg .= "<b>".string_ucwords(ZIPCODE_LABEL).": </b>".$contact->getString("zip")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_PHONE).": </b>".$contact->getString("phone")."<br />";
            $sitemgr_msg .= "<b>".system_showText(LANG_LABEL_URL).": </b>".$contact->getString("url")."<br />";
            $sitemgr_msg .="<br /><a href=\"".DEFAULT_URL."/".SITEMGR_ALIAS."/account/$accountViewLink.php?id=".$account->getNumber("id")."\" target=\"_blank\">".DEFAULT_URL."/".SITEMGR_ALIAS."/account/$accountViewLink.php?id=".$account->getNumber("id")."</a><br /><br />";
            $sitemgr_msg .= EDIRECTORY_TITLE;

            system_notifySitemgr($sitemgr_account_emails, $emailSubject, $sitemgr_msg);

            // SAVE to AirTable
            $data = [
                "fields" => [
                    'FirstName' => $_POST['first_name'] ?? '',
                    'LastName' => $_POST['last_name'] ?? '',
                    'Email' => $_POST['email'] ?? '',
                ]
            ];
            $data_json = json_encode($data);

            $ch = curl_init('https://api.airtable.com/v0/appL7BljlwkiI6zKH/New%20Visitor%20Signups?api_key=keyliqKng8eLukP2r');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));

            $result = curl_exec($ch);
            curl_close($ch);

            if (!$AppRequest) {
                $location = SOCIALNETWORK_URL."/";

                if (true == $_GET['userperm']) {
                    /*
                     * Workaround to pin a bookmark without login
                     */
                    if ($_GET['bookmark_remember'] || $_POST['action'] === 'bookmark') {
                        // Sets a cookie to use in front JS
                        setcookie('open_bookmark', $_GET['bookmark_remember'] ? : $_POST['item_id'], time() + 60 * 60, '/');
                    } elseif ($_GET['redeem_remember'] || $_POST['action'] === 'redeem') {
                        /*
                         * Workaround for make a redeem without login
                         */
                        // Sets a cookie to use in front JS
                        setcookie('open_redeem', $_GET['redeem_remember'] ? : $_POST['item_id'], time() + 60 * 60, '/');
                    } elseif ($_POST['action'] === 'review') {
                        setcookie('open_review', $_POST['item_id'], time()+60*60, '/');
                    } else {
                        // Opens modal automatically
                        $_SESSION['_sf2_attributes']['modal'] = 1;
                    }
                    $location = $_POST['referer'];
                }

                /* ModStores Hooks */
                HookFire("accountcode_before_redirect", [
                    "account"    => &$account,
                    "contact"    => &$contact,
                    "profileObj" => &$profileObj,
                    "accDomain"  => &$accDomain,
                    "location"   => &$location,
                ]);

                header("Location: ".$location);
                exit;
            } else {
                $arrayAccount = array();
                $arrayAccount["id"] = (int)$account->getNumber("id");
                $arrayAccount["first_name"] = $contact->getString("first_name");;
                $arrayAccount["last_name"] = $contact->getString("last_name");
                $arrayAccount["email"] = $account->getString("username");
                $arrayAccount["member_img"] = system_getUserImage($account->getNumber("id"));
            }

		} elseif (!$AppRequest) {
			// removing slashes added if required
			$_POST = format_magicQuotes($_POST);
			$_GET  = format_magicQuotes($_GET);
			extract($_POST);
			extract($_GET);
		}

	}
