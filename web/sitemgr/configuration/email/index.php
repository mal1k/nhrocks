<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/configuration/email.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include '../../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();
permission_hasSMPerm();

mixpanel_track('Accessed section Email Configuration');

# ----------------------------------------------------------------------------------------------------
# SUBMIT
# ----------------------------------------------------------------------------------------------------
extract($_POST);
extract($_GET);

$domain = new Domain(SELECTED_DOMAIN_ID);
$classSymfonyYml = new Symfony('domains/'.$domain->getString('url').'.configs.yml');
$domainConfig = $classSymfonyYml->getConfig('parameters');

if ($ajaxVerify == 1) {
    $return_json = [];

    $mailer = SymfonyCore::getContainer()->get('core.mailer');

    try {
        $result = $mailer->testSmtpTransport([
            'host'       => $emailconf_host,
            'port'       => $emailconf_port,
            'username'   => $emailconf_username,
            'password'   => $emailconf_password,
            'encryption' => $emailconf_protocol,
            'auth'       => $emailconf_auth === 'noauth' ? null : $emailconf_auth,
            'from'       => $emailconf_email,
            'to'         => $emailconf_email,
            'subject'    => EDIRECTORY_TITLE.' - Config SMTP Email',
            'body'       => EDIRECTORY_TITLE.' - Config SMTP Email',
        ]);

        $return_json['status'] = 'success';
    } catch (\Exception $e) {
        $return_json['status'] = 'failed';
        $return_json['msg_error'] = $e->getMessage();
    }

    die(json_encode($return_json));
}

// Default CSS class for message
$message_style = 'warning';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!$adminemail) {

        $error = false;

        $emailconf_host = str_replace(' ', '', $emailconf_host);
        $emailconf_port = str_replace(' ', '', $emailconf_port);
        $emailconf_auth = str_replace(' ', '', $emailconf_auth);
        $emailconf_email = str_replace(' ', '', $emailconf_email);
        $emailconf_protocol = str_replace(' ', '', $emailconf_protocol);
        $emailconf_username = str_replace(' ', '', $emailconf_username);

        if (!setting_set("emailconf_email", $emailconf_email)) {
            if (!setting_new("emailconf_email", $emailconf_email)) {
                $error = true;
            }
        }

        if (isset($emailconf_password) && trim($emailconf_password) != '') {
            $password = str_replace(' ', '', $emailconf_password);
            $emailconf_password = crypt_encrypt(str_replace(' ', '', $emailconf_password));
        }

        $yamlFile = [
            'mailer_transport'  => 'smtp',
            'mailer_host'       => $emailconf_host,
            'mailer_user'       => $emailconf_username,
            'mailer_password'   => isset($password) ? $password : null,
            'mailer_port'       => $emailconf_port,
            'mailer_encryption' => !empty($emailconf_protocol) ? $emailconf_protocol : null,
            'mailer_sender'     => $emailconf_email,
            'mailer_auth_mode' => $emailconf_auth === 'noauth' ? null : $emailconf_auth
        ];

        if ($emailconf_protocol !== 'tls' && strpos($emailconf_host, 'gmail') !== false) {
            $yamlFile['mailer_transport'] = 'gmail';
            $yamlFile['mailer_host'] = '~';
        }

        // Save YAML File
        $classSymfonyYml->save('Configs', ['parameters' => $yamlFile]);

        if (!$error) {
            $message_style = 'successSmtp';
            mixpanel_track('SMTP Server information updated');
        } else {
            $message_confemail = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_MSGERROR_SYSTEMERROR);
        }

    } else {

        if (validate_form('adminemail', $_POST, $message_adminemail)) {

            $error = false;

            $sitemgr_email = str_replace(' ', '', $sitemgr_email);
            if ($sitemgr_email) {
                if (!setting_set('sitemgr_email', $sitemgr_email)) {
                    if (!setting_new('sitemgr_email', $sitemgr_email)) {
                        $error = true;
                    }
                }
            }

            if (!setting_set('sitemgr_send_email', $send_email)) {
                if (!setting_new('sitemgr_send_email', $send_email)) {
                    $error = true;
                }
            }

            $sitemgr_listing_email = str_replace(' ', '', $sitemgr_listing_email);
            if (!setting_set('sitemgr_listing_email', $sitemgr_listing_email)) {
                if (!setting_new('sitemgr_listing_email', $sitemgr_listing_email)) {
                    $error = true;
                }
            }

            $sitemgr_event_email = str_replace(' ', '', $sitemgr_event_email);
            if (!setting_set('sitemgr_event_email', $sitemgr_event_email)) {
                if (!setting_new('sitemgr_event_email', $sitemgr_event_email)) {
                    $error = true;
                }
            }

            $sitemgr_banner_email = str_replace(' ', '', $sitemgr_banner_email);
            if (!setting_set('sitemgr_banner_email', $sitemgr_banner_email)) {
                if (!setting_new('sitemgr_banner_email', $sitemgr_banner_email)) {
                    $error = true;
                }
            }

            $sitemgr_classified_email = str_replace(' ', '', $sitemgr_classified_email);
            if (!setting_set('sitemgr_classified_email', $sitemgr_classified_email)) {
                if (!setting_new('sitemgr_classified_email', $sitemgr_classified_email)) {
                    $error = true;
                }
            }

            $sitemgr_article_email = str_replace(' ', '', $sitemgr_article_email);
            if (!setting_set('sitemgr_article_email', $sitemgr_article_email)) {
                if (!setting_new('sitemgr_article_email', $sitemgr_article_email)) {
                    $error = true;
                }
            }

            $sitemgr_account_email = str_replace(' ', '', $sitemgr_account_email);
            if (!setting_set('sitemgr_account_email', $sitemgr_account_email)) {
                if (!setting_new('sitemgr_account_email', $sitemgr_account_email)) {
                    $error = true;
                }
            }

            $sitemgr_contactus_email = str_replace(' ', '', $sitemgr_contactus_email);
            if (!setting_set('sitemgr_contactus_email', $sitemgr_contactus_email)) {
                if (!setting_new('sitemgr_contactus_email', $sitemgr_contactus_email)) {
                    $error = true;
                }
            }

            $sitemgr_support_email = str_replace(' ', '', $sitemgr_support_email);
            if (!setting_set('sitemgr_support_email', $sitemgr_support_email)) {
                if (!setting_new('sitemgr_support_email', $sitemgr_support_email)) {
                    $error = true;
                }
            }

            $sitemgr_payment_email = str_replace(' ', '', $sitemgr_payment_email);
            if (!setting_set('sitemgr_payment_email', $sitemgr_payment_email)) {
                if (!setting_new('sitemgr_payment_email', $sitemgr_payment_email)) {
                    $error = true;
                }
            }

            $sitemgr_rate_email = str_replace(' ', '', $sitemgr_rate_email);
            if (!setting_set('sitemgr_rate_email', $sitemgr_rate_email)) {
                if (!setting_new('sitemgr_rate_email', $sitemgr_rate_email)) {
                    $error = true;
                }
            }

            $sitemgr_claim_email = str_replace(' ', '', $sitemgr_claim_email);
            if (!setting_set('sitemgr_claim_email', $sitemgr_claim_email)) {
                if (!setting_new('sitemgr_claim_email', $sitemgr_claim_email)) {
                    $error = true;
                }
            }

            $sitemgr_blog_email = str_replace(' ', '', $sitemgr_blog_email);
            if (!setting_set('sitemgr_blog_email', $sitemgr_blog_email)) {
                if (!setting_new('sitemgr_blog_email', $sitemgr_blog_email)) {
                    $error = true;
                }
            }

//            $sitemgr_import_email = str_replace(' ', '', $sitemgr_import_email);
//            if (!setting_set('sitemgr_import_email', $sitemgr_import_email)) {
//                if (!setting_new('sitemgr_import_email', $sitemgr_import_email)) {
//                    $error = true;
//                }
//            }

            if (!$error) {
                $message_style = 'successEmail';
                mixpanel_track('Administrator E-mails updated', $_POST);
            } else {
                $message_adminemail = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_MSGERROR_SYSTEMERROR);
            }

        }

    }

    if ($message_style != 'warning') {
        header( 'Location: ' . DEFAULT_URL.'/'.SITEMGR_ALIAS.'/configuration/email/index.php?msg='.$message_style );
        exit;
    }

}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'successSmtp') {
        $message_confemail = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_SETTINGS_YOURSETTINGSWERECHANGED);
    } else {
        $message_adminemail = '&#149;&nbsp;'.system_showText(LANG_SITEMGR_SETTINGS_YOURSETTINGSWERECHANGED);
    }
    $message_style = 'success';
}

# ----------------------------------------------------------------------------------------------------
# FORMS DEFINES
# ----------------------------------------------------------------------------------------------------
$styleButtonChange = 'onchange="disableButton();"';

if (!$sitemgr_email) {
    setting_get('sitemgr_email', $sitemgr_email);
}
setting_get('sitemgr_send_email', $send_email);
if ($send_email) {
    $send_email_checked = 'checked';
}
if (!$sitemgr_listing_email) {
    setting_get('sitemgr_listing_email', $sitemgr_listing_email);
}
if (!$sitemgr_event_email) {
    setting_get('sitemgr_event_email', $sitemgr_event_email);
}
if (!$sitemgr_banner_email) {
    setting_get('sitemgr_banner_email', $sitemgr_banner_email);
}
if (!$sitemgr_classified_email) {
    setting_get('sitemgr_classified_email', $sitemgr_classified_email);
}
if (!$sitemgr_article_email) {
    setting_get('sitemgr_article_email', $sitemgr_article_email);
}
if (!$sitemgr_account_email) {
    setting_get('sitemgr_account_email', $sitemgr_account_email);
}
if (!$sitemgr_contactus_email) {
    setting_get('sitemgr_contactus_email', $sitemgr_contactus_email);
}
if (!$sitemgr_support_email) {
    setting_get('sitemgr_support_email', $sitemgr_support_email);
}
if (!$sitemgr_payment_email) {
    setting_get('sitemgr_payment_email', $sitemgr_payment_email);
}
if (!$sitemgr_rate_email) {
    setting_get('sitemgr_rate_email', $sitemgr_rate_email);
}
if (!$sitemgr_claim_email) {
    setting_get('sitemgr_claim_email', $sitemgr_claim_email);
}
if (!$sitemgr_blog_email) {
    setting_get('sitemgr_blog_email', $sitemgr_blog_email);
}
//if (!$sitemgr_import_email) {
//    setting_get('sitemgr_import_email', $sitemgr_import_email);
//}

# ----------------------------------------------------------------------------------------------------
# HEADER
# ----------------------------------------------------------------------------------------------------
include SM_EDIRECTORY_ROOT.'/layout/header.php';

# ----------------------------------------------------------------------------------------------------
# NAVBAR
# ----------------------------------------------------------------------------------------------------
include SM_EDIRECTORY_ROOT.'/layout/navbar.php';

# ----------------------------------------------------------------------------------------------------
# SIDEBAR
# ----------------------------------------------------------------------------------------------------
include SM_EDIRECTORY_ROOT.'/layout/sidebar-configuration.php';

?>

<main class="wrapper togglesidebar container-fluid">

    <?php
    require SM_EDIRECTORY_ROOT.'/registration.php';
    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
    ?>

    <section class="heading">
        <h1><?= system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_EMAILSENDINGCONFIGURATION); ?></h1>
        <p><?= system_showText(LANG_SITEMGR_SETTINGS_TIP_2); ?></p>
    </section>

    <div class="row tab-options">

        <ul class="nav nav-tabs" role="tablist">
            <li class="<?= (($message_confemail || !$_GET['msg']) && !$message_adminemail ? 'active' : '') ?>"><a href="#config" role="tab"
                                                                                  data-toggle="tab"><?= system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_EMAILSENDINGCONFIGURATION); ?></a>
            </li>
            <li class="<?= ($message_adminemail ? 'active' : '') ?>"><a href="#admin" role="tab"
                                                                        data-toggle="tab"><?= system_showText(LANG_SITEMGR_SETTINGS_EMAIL_ADMINISTRATOREMAIL) ?></a>
            </li>
        </ul>

        <div class="row tab-content">
            <section id="config" class="tab-pane <?= (($message_confemail || !$_GET['msg']) && !$message_adminemail ? 'active' : '') ?>">
                <? include INCLUDES_DIR.'/forms/form-emailconfiguration.php'; ?>
            </section>

            <section id="admin" class="tab-pane <?= ($message_adminemail ? 'active' : '') ?>">
                <? include INCLUDES_DIR.'/forms/form-adminemail.php'; ?>
            </section>
        </div>
    </div>

</main>

<?php
# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
$customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/emailconfig.php';
include SM_EDIRECTORY_ROOT.'/layout/footer.php';
