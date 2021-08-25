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
# * FILE: /includes/code/google_analytics.php
# ----------------------------------------------------------------------------------------------------

# ----------------------------------------------------------------------------------------------------
# * DEFINES
# ----------------------------------------------------------------------------------------------------
setting_get('google_analytics_status', $google_analytics_status);

if ($google_analytics_page == "front") {
    setting_get('google_analytics_front', $setting_id);
} elseif ($google_analytics_page == "members") {
    setting_get('google_analytics_members', $setting_id);
} elseif ($google_analytics_page == "sitemgr") {
    setting_get('google_analytics_sitemgr', $setting_id);
}

if ($setting_id == "on" && $google_analytics_status) {
    ?>
    <script type="text/javascript">
        var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
        document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script type="text/javascript">
        try {
            var pageTracker = _gat._getTracker("<?= $google_analytics_status ?>");
            pageTracker._initData();
            pageTracker._trackPageview();
        } catch (err) {
        }
    </script>
    <?
}

?>
