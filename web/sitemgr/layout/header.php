<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/header.php
	# ----------------------------------------------------------------------------------------------------

    header("Content-Type: text/html; charset=".EDIR_CHARSET, TRUE);
	header("Accept-Encoding: gzip, deflate");
	header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check", FALSE);
	header("Pragma: no-cache");

    setting_get("sitemgr_language", $sitemgr_language);
    setting_get("header_title", $headertag_title);
    $headertag_title = $headertag_title ? $headertag_title : EDIRECTORY_TITLE;
    $checkIE = is_ie(false, $ieVersion);
    $sitemgr_languageArr = explode("_", $sitemgr_language);

    if (!$container) $container = SymfonyCore::getContainer();

?>

<!DOCTYPE html>
<html class="no-js" lang="<?=system_getHeaderLang();?>">
    <head>
        <title><?=((string_strpos($_SERVER["PHP_SELF"], "registration.php")) ? "" : system_showText(LANG_SITEMGR_HOME_WELCOME). " - ").$headertag_title;?></title>
        <meta name="author" content="Arca Solutions" />
        <meta charset="<?=EDIR_CHARSET;?>" />
        <meta name="ROBOTS" content="noindex, nofollow" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>

        <? if ($facebookScript) { ?>
            <meta property="fb:app_id" content="<?= FACEBOOK_API_ID ?>">
		<? } ?>

        <?=system_getFavicon();?>

        <!-- Custom styles for this template -->
        <link href="<?= auto_version('styles.min.css') ?>" rel="stylesheet" type="text/css">

        <?php if ($container->getParameter("mixpanel_token")) { ?>
        <!-- start Mixpanel -->
        <script type="text/javascript">(function(e,a){if(!a.__SV){var b=window;try{var c,l,i,j=b.location,g=j.hash;c=function(a,b){return(l=a.match(RegExp(b+"=([^&]*)")))?l[1]:null};g&&c(g,"state")&&(i=JSON.parse(decodeURIComponent(c(g,"state"))),"mpeditor"===i.action&&(b.sessionStorage.setItem("_mpcehash",g),history.replaceState(i.desiredHash||"",e.title,j.pathname+j.search)))}catch(m){}var k,h;window.mixpanel=a;a._i=[];a.init=function(b,c,f){function e(b,a){var c=a.split(".");2==c.length&&(b=b[c[0]],a=c[1]);b[a]=function(){b.push([a].concat(Array.prototype.slice.call(arguments,
                0)))}}var d=a;"undefined"!==typeof f?d=a[f]=[]:f="mixpanel";d.people=d.people||[];d.toString=function(b){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);b||(a+=" (stub)");return a};d.people.toString=function(){return d.toString(1)+".people (stub)"};k="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");
                for(h=0;h<k.length;h++)e(d,k[h]);a._i.push([b,c,f])};a.__SV=1.2;b=e.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";c=e.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}})(document,window.mixpanel||[]);
            mixpanel.init("<?=$container->getParameter("mixpanel_token")?>");</script>
            <!-- end Mixpanel -->
        <?php } ?>

        <?php
        /* ModStores Hooks */
        HookFire('sitemgrheader_after_render_metatags');
        ?>

    </head>

    <body <?=(BRANDED_PRINT == "on" ? "class=\"branded\"" : "")?>>
