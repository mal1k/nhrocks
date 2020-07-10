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
# * FILE: /frontend/footer_menu.php
# ----------------------------------------------------------------------------------------------------
use ArcaSolutions\WysiwygBundle\Entity\PageType;

$container = SymfonyCore::getContainer();
$translator = $container->get('translator');
$items = $container->get('navigation.service')->getFooter();
?>

<?php if(!empty($items)) { ?>
    <div class="footer-item" data-content="site-content">
        <div class="heading footer-item-title">
            <?=$translator->trans($widgetContent['labelSiteContent'], [], 'widgets')?>
        </div>
        <div class="footer-item-content">
            <?php if($items) {
                foreach ($items as $item) {
                    if ($item['custom']) {
                        $pageUrl = strpos( $item['link'], '://') ? $item['link'] : $container->get('pagetype.service')->getBaseUrl(PageType::HOME_PAGE) . '/' . $item['link'];
                    } else {
                        $page = $container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($item['pageId']);
                        $pageUrl = $container->get('page.service')->getFinalPageUrl($page);
                    }
                    ?>
                    <a href="<?=$pageUrl?>" class="link-footer">
                        <?= $item['label']; ?>
                    </a>
                <?php }
            } ?>
        </div>
    </div>
<?php } ?>
