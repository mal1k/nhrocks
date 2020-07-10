<?

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
    # * FILE: /includes/code/custominvoice-send.php
    # ----------------------------------------------------------------------------------------------------
    extract($_POST);
    extract($_GET);

    if (!$id)
    {
		header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices/");
		exit;
	}

    if ( $_SERVER["REQUEST_METHOD"] === "POST" )
    {
        $customInvoice = new CustomInvoice( $id );

        if ( $customInvoice->getString( "paid" ) == "y" )
        {
            header( "Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices/" );
            exit;
        }

        if ( validate_form( 'custominvoicesend', $_POST, $error_msg ) )
        {
            mixpanel_track("Sent a Custom Invoice");

            /* updating status */
            $sent_date = ($customInvoice->getString( "sent_date" ).$customInvoice->getString( "sent_date" )) ? "\n" : "".date( "Y-m-d" );

            $customInvoice->setString( "sent", "y" );
            $customInvoice->setString( "sent_date", $sent_date );
            $customInvoice->Save();

            $emailNotification = new EmailNotification( SYSTEM_NEW_CUSTOMINVOICE );
            $domain = new Domain( SELECTED_DOMAIN_ID );

            $subject = stripslashes( $subject );
            $body    = stripslashes( $body_message );
            $body    = str_replace( $_SERVER["HTTP_HOST"], $domain->getstring( "url" ), $body );


            SymfonyCore::getContainer()->get('core.mailer')
                ->newMail($subject, $body, $emailNotification->getString( "content_type" ))
                ->setTo($to)
                ->setCc($cc)
                ->setBcc($bcc)
                ->send($failedRecipients);

            if ( count($failedRecipients) > 0 ) {
                $error   = false;
                $message = 2;
            } else {
                $message = urlencode( system_showText(LANG_CONTACTMSGFAILED) );
            }

            header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/activity/custominvoices/index.php?message=$message&error=$error&screen=$screen&letter=$letter".(($url_search_params) ? "&$url_search_params" : "")."");
            exit;
        }
        else
        {
            MessageHandler::registerError( $error_msg );
        }
    }

    # ----------------------------------------------------------------------------------------------------
    # FORMS DEFINES
    # ----------------------------------------------------------------------------------------------------
    setting_get( "sitemgr_email", $sitemgr_email );
    setting_get( "payment_tax_status", $payment_tax_status );
    setting_get( "payment_tax_label", $payment_tax_label );

    $sitemgr_emails = explode( ",", $sitemgr_email );
    $sitemgr_emails[0] and $sitemgr_email = $sitemgr_emails[0];

    $customInvoice = new CustomInvoice( $id );

    $account = new Account( $customInvoice->getNumber( "account_id" ) );
    $contact = db_getFromDB( "contact", "account_id", $account->getNumber( "id" ) );

    $emailNotification = new EmailNotification( SYSTEM_NEW_CUSTOMINVOICE );
    $domain = new Domain( SELECTED_DOMAIN_ID );

    /* determine the type of body message field */
    $content_type = $emailNotification->getString("content_type");

    $_body = $emailNotification->getString( "body" );
    $_body = str_replace( "EDIRECTORY_TITLE", EDIRECTORY_TITLE, $_body );
    $_body = str_replace( "DEFAULT_URL", DEFAULT_URL, $_body );
    $_body = str_replace( "MEMBERS_URL", MEMBERS_ALIAS, $_body );
    $_body = str_replace( "ACCOUNT_NAME", $contact->getString( "first_name" )." ".$contact->getString( "last_name" ), $_body );
    $_body = str_replace( "ACCOUNT_USERNAME", $account->getString( "username" ), $_body );
    $_body = str_replace( "SITEMGR_EMAIL", $sitemgr_email, $_body );
    $_body = str_replace( "CUSTOM_INVOICE_AMOUNT", PAYMENT_CURRENCY_SYMBOL."".$customInvoice->getPrice(), $_body );
    $_body = str_replace( "CUSTOM_INVOICE_TAX", ($payment_tax_status ? "+ ".$payment_tax_label : "" ), $_body );
    $_body = str_replace( "LOGO", image_getLogoImage(), $_body);
    $_body = str_replace( $_SERVER["HTTP_HOST"], $domain->getstring( "url" ), $_body );

    $body = empty($body_message) ? $_body : $body_message;
    $_subject = str_replace( "EDIRECTORY_TITLE", EDIRECTORY_TITLE, $emailNotification->getString( "subject" ) );
    $subject = empty($subject) ? $_subject : $subject;
    $bcc = empty($bcc) ? $emailNotification->getString("bcc") : $bcc;
