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
# * FILE: /includes/code/checkregistration.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# AUX
# ----------------------------------------------------------------------------------------------------
$edirectory_checkregistration_file = "yes";
$edirectory_checkregistration_aux = md5("217413e28563be686aa871241300624a".session_id());

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------
if (($edirectory_registration_file != "yes") || ($edirectory_registration_aux != md5("499bb0ce1391c3d8497d79097726bfa7".session_id()))) {
    echo "<p class=\"alert alert-warning\">eDirectory IS NOT FREE SOFTWARE!</p>";
    echo "<p class=\"alert alert-warning\">eDirectory activation required!</p>";
    echo "<p class=\"alert alert-warning\">Registration Process was corrupted!</p>";
}
