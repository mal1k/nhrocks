<?php
/* ==================================================================*\
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
  \*================================================================== */

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/code/content_basic_settings.php
# ----------------------------------------------------------------------------------------------------

if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !DEMO_LIVE_MODE )
{
    setting_get( 'header_title', $lastheader_title );
    setting_get( 'header_author', $lastheader_author );

    if ($lastheader_title != $header_title) {
        mixpanel_track('Changed Website Name');
    }

    if ($lastheader_author != $header_author) {
        mixpanel_track('Changed Website Author');
    }

    $header_title = htmlspecialchars($header_title);

    setting_set( 'header_title', $header_title )                   or setting_new( 'header_title', $header_title )                   or MessageHandler::registerError( array('DBerror' => system_showText( LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_DATABASE ) ) );
    setting_set( 'header_author', $header_author )                 or setting_new( 'header_author', $header_author )                 or MessageHandler::registerError( array('DBerror' => system_showText( LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_DATABASE ) ) );

    $domain = new Domain(SELECTED_DOMAIN_ID);

    /* Writes new header title to config file. */
    if (!MessageHandler::haveErrors() && ($header_title = trim($header_title))) {
        $fileConstPath = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/conf/constants.inc.php';
        system_writeConstantsFile($fileConstPath, SELECTED_DOMAIN_ID,
            ['name' => $header_title]) or MessageHandler::registerError(['DBerror' => system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_DATABASE)]);

        // saves name site in yml file
        $classSymfonyYml = new Symfony('domain.yml');
        $theme_domain = [
            'multi_domain' => [
                'hosts' => [
                    $domain->getString('url') => [
                        'title' => $header_title,
                    ],
                ],
            ],
        ];
        $classSymfonyYml->save('Configs', $theme_domain);
        unset($classSymfonyYml);
    }

    if ($_FILES['header_image']['tmp_name'] && $_FILES['header_image']['error'] == 0) {

        if (image_LogoUploaded()) {
            mixpanel_track('Logo updated', [
                    'section' => 'Basic Information'
                ]
            );
        } else {
            mixpanel_track('Logo uploaded', [
                'section' => 'Basic Information'
            ]);
        }

        $imgPath = image_uploadImage(IMAGE_HEADER_PATH, $_FILES['header_image']['tmp_name'], true);
        $imgPath or MessageHandler::registerError(system_showText(LANG_SITEMGR_MSGERROR_ALERTUPLOADIMAGE2));

        // @todo image cte
        $classSymfonyYml = new Symfony('domains/'.$domain->getString('url').'.configs.yml');
        $classSymfonyYml->save('Configs', [
            'parameters' => [
                'domain.header.image' => $imgPath,
            ],
        ]);
        unset($classSymfonyYml);
    }

    /* noimage image file */
    if ($_FILES['noimage_image']['tmp_name'] && $_FILES['noimage_image']['error'] == 0) {
        $filename = NOIMAGE_PATH.'/'.NOIMAGE_NAME.'.'.NOIMAGE_IMGEXT;

        if (image_DefaultImageUploaded()) {
            mixpanel_track('Default image updated', [
                'section' => 'Basic Information'
            ]);
        } else {
            mixpanel_track('Default image uploaded', [
                'section' => 'Basic Information'
            ]);
        }

        $imgPath = image_uploadForNoImage($filename, $_FILES['noimage_image']['tmp_name']);
        $imgPath or MessageHandler::registerError(system_showText(LANG_SITEMGR_MSGERROR_ALERTUPLOADIMAGE2));

        // @todo image cte
        $domain = new Domain(SELECTED_DOMAIN_ID);
        $classSymfonyYml = new Symfony('domains/'.$domain->getString('url').'.configs.yml');
        $classSymfonyYml->save('Configs', [
            'parameters' => [
                'domain.noimage' => $imgPath,
            ],
        ]);
    }

    if ($_FILES['favicon_file']['name']) {
        $arr_favicon = explode('.', $_FILES['favicon_file']['name']);
        $favicon_extension = $arr_favicon[count($arr_favicon) - 1];

        if (string_strtolower($favicon_extension) == 'ico') {
            setting_get('last_favicon_id', $last_favicon_id);
            $last_favicon_id or ($last_favicon_id = '1' and setting_new('last_favicon_id', '1'));

            // FAVICON FILE UPLOAD
            if (file_exists($_FILES['favicon_file']['tmp_name']) && filesize($_FILES['favicon_file']['tmp_name'])) {
                /* Let's open it and check if there is php code inside*/
                if ($handle = fopen($_FILES['favicon_file']['tmp_name'], 'r')) {
                    while (($line = fgets($handle)) !== false) {
                        if (strpos($line, '<?') !== false || strpos($line, '<script') !== false) {
                            MessageHandler::registerError(system_showText(LANG_MSGERROR_ERRORUPLOADINGIMAGE));
                            break;
                        }
                    }
                }
                fclose($handle);

                if (!MessageHandler::haveErrors()) {
                    if (file_exists(EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/content_files/favicon_'.$last_favicon_id.'.ico')) {
                        mixpanel_track('Favicon updated', [
                            'section' => 'Basic Information'
                        ]);
                        @unlink(EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/content_files/favicon_'.$last_favicon_id.'.ico');
                    } else {
                        mixpanel_track('Favicon uploaded', [
                            'section' => 'Basic Information'
                        ]);
                    }
                    $last_favicon_id++;

                    $file_path = EDIRECTORY_ROOT.'/custom/domain_'.SELECTED_DOMAIN_ID.'/content_files/favicon_'.$last_favicon_id.'.ico';

                    // adds favicon
                    // @todo image cte
                    $domain = new Domain(SELECTED_DOMAIN_ID);
                    $classSymfonyYml = new Symfony('domains/'.$domain->getString('url').'.configs.yml');
                    $classSymfonyYml->save('Configs', [
                        'parameters' => [
                            'domain.favicon' => '/custom/domain_'.SELECTED_DOMAIN_ID.'/content_files/favicon_'.$last_favicon_id.'.ico',
                        ],
                    ]);
                    unset($classSymfonyYml);

                    copy($_FILES['favicon_file']['tmp_name'], $file_path);
                    setting_set('last_favicon_id', $last_favicon_id);
                }
            } else {
                MessageHandler::registerError(system_showText(LANG_MSGERROR_ERRORUPLOADINGIMAGE));
            }
        } else {
            MessageHandler::registerError(system_showText(LANG_UPLOAD_MSG_NOTALLOWED_WRONGFILETYPE.' '.LANG_MSG_ALLOWED_FILE_TYPES.': <b>.ico</b>'));
        }
    }

    if (!empty($contact_email)) {
        if (!validate_email($contact_email)) {
            MessageHandler::registerError(system_showText(LANG_MSG_ENTER_VALID_EMAIL_ADDRESS));
        }
    }

    /* ModStores Hooks */
    HookFire('sitemgr_code_content_basic_settings_before_check_contact_info_errors', [
        'http_post_array'      => &$_POST,
        'http_get_array'       => &$_GET,
        'error_messages_array' => &MessageHandler::$errorMessages
    ]);

    if (!MessageHandler::haveErrors()) {
        $contactInfo = [
            'contact_company',
            'contact_address',
            'contact_zipcode',
            'contact_country',
            'contact_state',
            'contact_city',
            'contact_phone',
            'contact_email',
            'contact_mapzoom',
            'contact_latitude',
            'contact_longitude',
            'setting_facebook_link',
            'twitter_account',
            'setting_linkedin_link',
            'setting_instagram_link',
            'setting_pinterest_link',
        ];

        $contactArray = [];

        $trackMixpanel = false;

        if(
            empty($contact_address) &&
            empty($contact_city) &&
            empty($contact_state) &&
            empty($contact_zipcode) &&
            empty($contact_country)
        ) {
            $contact_latitude = $contact_latitude = null;
        }

        foreach ($contactInfo as $info) {

            setting_get($info, $lastInfo);
            if ($lastInfo != $$info) {
                $trackMixpanel = true;
            }

            setting_set($info, $$info) or setting_new($info,
                $$info) or MessageHandler::registerError(['DBerror' => system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_ERROR_DATABASE)]);

            $contactArray['domain.'.str_replace('_', '.', $info)] = $$info;
        }

        /* ModStores Hooks */
        HookFire('sitemgr_code_content_basic_settings_after_save_contact_info', [
            'http_post_array'             => &$_POST,
            'http_get_array'              => &$_GET,
            'contact_information_changed' => &$trackMixpanel
        ]);


        if ($trackMixpanel) mixpanel_track('Changed Contact Information');
    }

    /* ModStores Hooks */
    HookFire('sitemgr_code_content_basic_settings_post_request_handle_before_check_success', [
        'http_post_array'      => &$_POST,
        'http_get_array'       => &$_GET,
        'error_messages_array' => &MessageHandler::$errorMessages
    ]);

    if(!MessageHandler::haveErrors()) {
        MessageHandler::registerSuccess(LANG_SITEMGR_SETTINGS_GENERAL_HEADER_SUCCESS);

        /* ModStores Hooks */
        $additionalMixpanelTrack = false;
        $additionalMixpanelTrackEventName = '';
        if (HookFire('sitemgr_code_content_basic_settings_post_request_handle_after_register_success', [
            'http_post_array'           => &$_POST,
            'http_get_array'            => &$_GET,
            'success_messages_array'    => &MessageHandler::$successMessages,
            'do_mixpanel_track'         => &$additionalMixpanelTrack,
            'mixpanel_track_event_name' => &$additionalMixpanelTrackEventName
        ])) {
            if ($additionalMixpanelTrack && !empty($additionalMixpanelTrackEventName)) {
                mixpanel_track($additionalMixpanelTrackEventName);
            }
        }
    }
} else {
    # ----------------------------------------------------------------------------------------------------
    # FORMS DEFINES
    # ----------------------------------------------------------------------------------------------------

    setting_get('header_title', $header_title);
    setting_get('header_author', $header_author);
    setting_get('last_favicon_id', $last_favicon_id);

    if (!$last_favicon_id) {
        setting_new('last_favicon_id', '1');
        $last_favicon_id = '1';
    }

    setting_get('contact_company', $contact_company);
    setting_get('contact_address', $contact_address);
    setting_get('contact_zipcode', $contact_zipcode);
    setting_get('contact_country', $contact_country);
    setting_get('contact_state', $contact_state);
    setting_get('contact_city', $contact_city);
    setting_get('contact_phone', $contact_phone);
    setting_get('contact_email', $contact_email);
    setting_get('contact_latitude', $contact_latitude);
    setting_get('contact_longitude', $contact_longitude);
    setting_get('contact_mapzoom', $contact_mapzoom);
    setting_get('setting_facebook_link', $setting_facebook_link);
    setting_get('twitter_account', $twitter_account);
    setting_get('setting_linkedin_link', $setting_linkedin_link);
    setting_get('setting_instagram_link', $setting_instagram_link);
    setting_get('setting_pinterest_link', $setting_pinterest_link);

    //Map Control
    $loadMap = false;
    setting_get('google_map_status', $google_map_status);

    if (GOOGLE_MAPS_ENABLED == 'on' && $google_map_status == 'on') {
        $loadMap = true;
        $hasValidCoord = false;

        if ($contact_latitude && $contact_longitude && is_numeric($contact_latitude) && is_numeric($contact_longitude)) {
            $hasValidCoord = true;
        }
    }
}
