<?php

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
    # * FILE: /profile/forgot.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("../conf/loadconfig.inc.php");

    if (sess_getAccountIdFromSession()) {
        header("Location: ".DEFAULT_URL."/".SOCIALNETWORK_FEATURE_NAME."/");
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    $cancel_section = SOCIALNETWORK_FEATURE_NAME."/login.php";
    $section = "members";
    include(INCLUDES_DIR."/code/forgot_password.php");

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    $headertag_title = system_showText(LANG_LABEL_FORGOTTEN_PASSWORD);
    include(EDIRECTORY_ROOT."/frontend/header.php");

    $cover_title = system_showText(LANG_LABEL_FORGOTTEN_PASSWORD);
    include(EDIRECTORY_ROOT."/frontend/coverimage.php");
?>

    <div class="modal-default modal-sign" is-page="true">
        <div class="modal-content">
            <div class="modal-body">
                <div class="content-tab content-sign-in">
                    <form role="form" class="modal-form" name="forgotpassword" method="post" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>">
                        <? include(INCLUDES_DIR."/forms/form_forgot_password.php"); ?>
                    </form>
                    <br>
                    <div class="not-member"><a href="<?=DEFAULT_URL;?>/<?=$cancel_section;?>" class="link"><?=system_showText(LANG_MSG_CLICK_IF_YOU_HAVE_PASSWORD);?></a></div>
                </div>
            </div>
        </div>
    </div>
    <?
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    include(EDIRECTORY_ROOT."/frontend/footer.php");
