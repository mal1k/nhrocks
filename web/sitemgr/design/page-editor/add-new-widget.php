<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/design/page-editor/custom.php
# ----------------------------------------------------------------------------------------------------

use ArcaSolutions\WysiwygBundle\Entity\Widget;

# ----------------------------------------------------------------------------------------------------
# LOAD CONFIG
# ----------------------------------------------------------------------------------------------------
include '../../../conf/loadconfig.inc.php';

# ----------------------------------------------------------------------------------------------------
# SESSION
# ----------------------------------------------------------------------------------------------------
sess_validateSMSession();
permission_hasSMPerm();

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------

/* Gets the container */
$container = SymfonyCore::getContainer();

/* Gets the WYSIWYG and Translation services */
$widgetService = $container->get('widget.service');
$trans = $container->get('translator');

/* Gets Lang */
setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);

// @formatter:off
$widgetTypes = [
    'all'                   => $trans->trans('All', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::COMMON_TYPE     => $trans->trans('Common', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::HEADER_TYPE     => $trans->trans('Headers', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::FOOTER_TYPE     => $trans->trans('Footers', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::CARDS_TYPE      => $trans->trans('Listings & Cards', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::LEAD_TYPE       => $trans->trans('Lead Forms', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::PRICING_TYPE    => $trans->trans('Pricing & Plans', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::BANNER_TYPE     => $trans->trans('Banners', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::SEARCH_TYPE     => $trans->trans('Search', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::EVENT_TYPE      => $trans->trans('Events', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::ARTICLE_TYPE    => $trans->trans('Articles', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::BLOG_TYPE       => $trans->trans('Blog', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
    Widget::NEWSLETTER_TYPE => $trans->trans('Newsletter', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
];
// @formatter:on

$customTab = [
    Widget::NEWSLETTER_TYPE => [
        'label'    => $trans->trans('Newsletter', [], 'widgets', /** @Ignore */
            $sitemgrLanguage),
        'template' => __DIR__.'/custom-tabs/newsletter.php',
    ],

    Widget::CARDS_TYPE => [
        'label'    => $trans->trans('Cards', [], 'widgets', /** @Ignore */ $sitemgrLanguage),
        'template' => __DIR__.'/custom-tabs/cards.php',
    ]
];

/* ModStores Hooks */
HookFire('addnewwidget_after_add_widgettype', [
    "widgetTypes"     => &$widgetTypes,
    "sitemgrLanguage" => &$sitemgrLanguage
]);

/* Gets Page and Widgets */
$page = $container->get('page.service')->getPage($_GET['page']);
$groupedWidgets = $widgetService->getGroupedWidgets($_GET['type']);
$settings = $container->get('settings')->getDomainSetting('arcamailer_customer_listid');

/* Gets Page Widgets */
$pageWidgets = $container->get('pagewidget.service')->getPageWidget($page->getId());
$excludeWidgets = [];
foreach ($widgetService->getWidgetNonDuplicate() as $widgetGroup => $widgetsTitle) {
    $titleWidgets = array_flip($widgetsTitle);
    if (count(array_intersect_key($titleWidgets, $pageWidgets)) > 0) {
        $excludeWidgets = array_merge($excludeWidgets, $widgetsTitle);
    }
}

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="modal-title"
        id="myModalLabel"><?= system_showText(LANG_SITEMGR_INSERT_WIDGET) ?></h4>
</div>
<div class="modal-body">

    <div class="insert-widget">
        <div class="row">
            <div class="col-sm-3">
                <div class="tab-options">
                    <ul class="nav nav-tabs" role="tablist">
                        <?php
                        $i = 0;
                        foreach ($widgetTypes as $widgetType => $widgetTypeName) { ?>
                            <?php if (!isset($groupedWidgets[$widgetType])) {
                                continue;
                            } ?>

                            <li role="presentation" class="<?= ($i == 0 ? 'active' : '') ?>">
                                <a href="#tab-<?= ($widgetType === 'header' ? 'headers' : $widgetType) ?>"
                                   id="<?= ($widgetType === 'header' ? 'headers' : $widgetType) ?>"
                                   aria-controls="<?= ($widgetType === 'header' ? 'headers' : $widgetType) ?>" role="tab"
                                   data-toggle="tab">
                                    <?= ucfirst($widgetTypeName) ?>
                                </a>
                            </li>
                            <?php
                            $i++;
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="col-sm-9">
                <div class="tab-content">
                    <?php
                    $i = 0;

                    foreach ($widgetTypes as $widgetType => $widgetTypeName) {
                        if (!isset($groupedWidgets[$widgetType])) {
                            continue;
                        } ?>

                        <?php if (array_key_exists($widgetType, $customTab)) {
                            continue;
                        } ?>

                        <div role="tabpanel" class="tab-pane<?= ($i == 0 ? ' active' : '') ?>"
                             id="tab-<?= ($widgetType === 'header' ? 'headers' : $widgetType) ?>">
                            <div class="grid-pinterest">
                                <?php
                                foreach ($groupedWidgets[$widgetType] as $widget) {

                                    $classItem = 'addWidget';

                                    if ($widget['title'] == 'Signup for our newsletter' && empty($settings) ){
                                        $linkForward = "/promote/newsletter/";
                                        $classItem = 'linkWidget';
                                    }

                                    /* ModStores Hooks */
                                    HookFire('addnewwidget_after_add_widgettype', [
                                    "imgPath" => &$imgPath,
                                    "widget"  => &$widget,
                                    "settings"  => &$settings,
                                    "linkForward" => &$linkForward
                                    ]);

                                    ?>
                                    <div class="item thumbnail <?= (in_array($widget['title'],
                                        $excludeWidgets) ? 'unavailable' : $classItem) ?>"
                                         data-widgetid="<?= $widget['id'] ?>" data-pageId="<?= $page->getId() ?>"
                                         data-title="<?= $widget['title'] ?>" data-type="<?= $widget['type'] ?>" data-link="<?= $linkForward; ?>">
                                        <div class="caption">
                                            <h4><?= /** @Ignore */
                                                $trans->trans($widget['title'], [], 'widgets', /** @Ignore */
                                                    $sitemgrLanguage) ?></h4>
                                            <? $imgPath = '../../assets/img/widget-placeholder/'.system_generateFriendlyURL($widget['title']).'.jpg';
                                            if (!file_exists(EDIRECTORY_ROOT.'/'.SITEMGR_ALIAS.'/assets/img/widget-placeholder/'.system_generateFriendlyURL($widget['title']).'.jpg')) {
                                                $imgPath = '../../assets/img/widget-placeholder/custom-content.jpg';
                                            } ?>
                                            <img
                                                    src="<?= $imgPath ?>"/>
                                        </div>
                                    </div>
                                <? } ?>
                            </div>
                        </div>
                        <?php
                        $i++;
                    }
                    ?>

                    <?php foreach ($customTab as $type => $tab) { ?>
                        <?php include $tab['template'] ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="modal-footer" style="position: fixed;width: calc(100% - 32px);left: 16px;">
    <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">
        <?= system_showText(LANG_SITEMGR_CANCEL) ?>
    </button>

    <button id="bt_save_card" type="button" class="btn btn-primary btn-lg"><?= $trans->trans('Save Widget') ?></button>
</div>
