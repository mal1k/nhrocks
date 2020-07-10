<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/promote/promotions/discountcode.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include '../../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# VALIDATE FEATURE
# ----------------------------------------------------------------------------------------------------
if (PAYMENT_FEATURE !== 'on') {
    header('Location:'.DEFAULT_URL.'/'.SITEMGR_ALIAS.'');
    exit;
}
if ((CREDITCARDPAYMENT_FEATURE !== 'on') && (PAYMENT_INVOICE_STATUS !== 'on')) {
    header('Location:'.DEFAULT_URL.'/'.SITEMGR_ALIAS.'');
    exit;
}
if (PAYMENTSYSTEM_FEATURE !== 'on') {
    exit;
}

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();
permission_hasSMPerm();

mixpanel_track(($id ? 'Edited an existing discount code' : 'Added a new discount code'));

# ----------------------------------------------------------------------------------------------------
# AUX
# ----------------------------------------------------------------------------------------------------
extract($_GET);
extract($_POST);
$url_base = ''.DEFAULT_URL.'/'.SITEMGR_ALIAS.'';

require_once CLASSES_DIR.'/class_StripeInterface.php';
include EDIRECTORY_ROOT.'/includes/code/discountcode.php';

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
include SM_EDIRECTORY_ROOT.'/layout/sidebar-promote.php';
?>

<main class="wrapper togglesidebar container-fluid">

    <?php
    require SM_EDIRECTORY_ROOT.'/registration.php';
    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
    ?>

    <form name="discountcode" role="form" action="<?= system_getFormAction($_SERVER['PHP_SELF']) ?>" method="post">
        <input type="hidden" name="x_id" value="<?= $x_id ?>"/>
        <section class="row heading">
            <div class="container">
                <?php include SM_EDIRECTORY_ROOT.'/layout/back-navigation.php'; ?>
                <div class="row">
                    <div class="col-sm-8">
                        <?php
                        if ($id) {
                            $prefix = system_showText(LANG_SITEMGR_EDIT);
                        } else {
                            $prefix = system_showText(LANG_SITEMGR_MENU_ADD);
                        }
                        ?>
                        <h1><?= $prefix ?> <?= string_ucwords(LANG_LABEL_DISCOUNTCODE) ?></h1>
                    </div>
                    <div class="col-sm-4 text-right">
                        <div class="top-actions">
                            <button type="submit" name="submit_button" value="Submit"
                                    class="btn btn-primary action-save"
                                    data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                        </div>
                    </div>
                </div>
                <?php if ($message_discountcode) { ?>
                    <p class="alert alert-warning"><?= $message_discountcode; ?></p>
                <?php } ?>
            </div>
        </section>

        <section class="section-form row">
            <div class="container">
                <?php include INCLUDES_DIR.'/forms/form-discountcode.php'; ?>
            </div>
        </section>

        <section class="row footer-action">
            <div class="container">
                <div class="col-xs-12 text-right">
                    <a href="<?= DEFAULT_URL.'/'.SITEMGR_ALIAS.'/promote/promotions/' ?>"
                       class="btn btn-default btn-xs"><?= system_showText(LANG_CANCEL) ?></a>
                    <span class="separator"> <?= system_showText(LANG_OR) ?> </span>
                    <button type="submit" name="submit_button" value="Submit" class="btn btn-primary action-save"
                            data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>
                </div>
            </div>
        </section>

    </form>

</main>

<?php include SM_EDIRECTORY_ROOT.'/layout/footer.php'; ?>
