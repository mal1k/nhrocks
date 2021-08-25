<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/configuration/general-information/index.php
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

    mixpanel_track('Accessed section Basic Information');

    # ----------------------------------------------------------------------------------------------------
	# AUX
	# ----------------------------------------------------------------------------------------------------

    /* ModStores Hooks */
    HookFire('sitemgr_configuration_basic_information_before_extract_post_get', [
        'http_post_array' => &$_POST,
        'http_get_array'  => &$_GET
    ]);

	extract($_POST);
	extract($_GET);

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
    include INCLUDES_DIR.'/code/content_basic_settings.php';

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
            <h1><?=system_showText(LANG_SITEMGR_BASIC_INFO);?></h1>
            <p><?=system_showText(LANG_SITEMGR_BASIC_INFO_TIP);?></p>
        </section>

        <section class="row section-form">
                <div class="col-md-9">
                    <form name="header" id="header" method="post" action="<?=system_getFormAction($_SERVER['PHP_SELF'])?>" enctype="multipart/form-data">
                        <?
                        MessageHandler::render();

                        include INCLUDES_DIR.'/forms/form-logo.php';
                        include INCLUDES_DIR.'/forms/form-siteinfo.php';

                        /* ModStores Hooks */
                        HookFire('sitemgr_configuration_basic_information_after_include_form_siteinfo', [
                            'http_post_array' => &$_POST,
                            'http_get_array'  => &$_GET
                        ]);
                        ?>
                    </form>
                </div>
        </section>
    </main>

<?php
    # ----------------------------------------------------------------------------------------------------
	# CUSTOM JAVASCRIPT
	# ----------------------------------------------------------------------------------------------------
    /* This will change the text and color on the buttons when the user has set a file for upload. */
    JavaScriptHandler::registerOnReady('
        $(".morphOnSelect").change( function(){
            $("label[for=\'"+$(this).prop("id")+"\']").removeClass("btn-primary").addClass("btn-success").html("'. system_showText( stripslashes( LANG_SITEMGR_SETTINGS_GENERAL_HITTOCONFIRM_BUTTON ) ) .'");
        });
    ');

    $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/settings.php';

	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    include SM_EDIRECTORY_ROOT.'/layout/footer.php';
