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
# * FILE: /frontend/header_menu.php
# ----------------------------------------------------------------------------------------------------
use ArcaSolutions\WysiwygBundle\Entity\PageType;

$container = SymfonyCore::getContainer();
$items = $container->get('navigation.service')->getHeader();

/* ModStores Hooks */
if (!HookFire("headermenu_overwrite_menu", [
    "items" => &$items
])) {
    if($items){
        foreach ($items as $item) {
            if ($item['custom']) {
                $pageUrl = strpos( $item['link'], '://') ? $item['link'] : $container->get('pagetype.service')->getBaseUrl(PageType::HOME_PAGE) . '/' . $item['link'];
            } else {
                $page = $container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($item['pageId']);
                $pageUrl = $container->get('page.service')->getFinalPageUrl($page);
            }
    ?>
            <a href="<?= $pageUrl ?>" class="navbar-link"><?= $container->get('translator')->trans($item['label']); ?></a>
    <?php
        }
    }
}
