<?php

use ArcaSolutions\ApiBundle\Helper\CategoryHelper;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use \ArcaSolutions\WysiwygBundle\Services\CardService;
use ArcaSolutions\CoreBundle\Inflector;

setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);
if ($sitemgrLanguage === 'ge') $sitemgrLanguage = 'de';
$widgets = $groupedWidgets[ArcaSolutions\WysiwygBundle\Entity\Widget::CARDS_TYPE];
$container = $container ?: SymfonyCore::getContainer();
$trans = $container->get('translator');
$modules = explode(',',$container->get('multi_domain.parameter')->get('modules.available'));
if(!in_array('listing', $modules, true)) {
    $modules[] = 'listing';
}
$moduleLevelInfo = $container->get('modules')->getLevelsFromAllModules();
$auxArrayPages = $container->get('navigation.service')->getNavigationPages('Header');
system_retrieveLocationsInfo($locationLevels, $defaultLocations);
$content = $content ?: [];
$defaultLocationLevels = [];
$categoryHelper = $container->get('category.helper');
$categories = [];
$cardService = $container->get('wysiwyg.card_service');

$itemCountEditable = ($content['cardType'] === Widget::HORIZONTAL_CARDS_TYPE || $content['cardType'] === Widget::VERTICAL_CARDS_TYPE);
$fourColumnsWidgetException = ($content['cardType'] === Widget::HORIZONTAL_CARDS_TYPE);
$oneColumnsWidgetException = ($content['cardType'] === Widget::ONE_HORIZONTAL_CARD_TYPE || $content['cardType'] === Widget::LIST_OF_HORIZONTAL_CARDS_TYPE);

foreach ($modules as $key => $module) {
    if($module === 'banner' or $module == '' ) {
        unset($modules[$key]);
        continue;
    }
    $categoryModule = $module === 'promotion' ? 'listing' : $module;
    $repoName = CategoryHelper::getRepositoryNameByModule($categoryModule);
    $categories[$module] = $categoryHelper->getCategories($repoName);
}
?>

<form id="widget-card-form">
    <?php if (isset($content['cardType'])) { ?>
        <input type="hidden" name="card_type" id="card_type" value="<?= $content['cardType'] ?>">
    <?php } ?>

    <input type="hidden" id="pageWidgetId" name="pageWidgetId" value="<?= $pageWidgetId ?>">
    <input type="hidden" id="itemEditable" value="<?= $itemCountEditable ?>">

    <?php if ($widgets) { ?>
        <div id="card-type-pane" class="grid-pinterest card-panel">
            <?php foreach ($widgets as $widget) { ?>
                <div class="item thumbnail add-card" id="<?= 'add-'.system_generateFriendlyURL($widget['title']) ?>" data-widget-id="<?= $widget['id'] ?>" data-widget-content='<?= $widget['content'] ?>'>
                    <div class="caption">
                        <h4><?=/** @Ignore */ $trans->trans($widget['title'], [], 'widgets') ?></h4>
                        <?php
                        $imgPath = '/assets/img/widget-placeholder/'.system_generateFriendlyURL($widget['title']).'.jpg';

                        $imgPath = file_exists(EDIRECTORY_ROOT.'/'.SITEMGR_ALIAS.$imgPath) ?
                            DEFAULT_URL.'/'.SITEMGR_ALIAS.$imgPath :
                            DEFAULT_URL.'/'.SITEMGR_ALIAS.'/assets/img/widget-placeholder/custom-content.jpg';
                        ?>
                        <img src="<?= $imgPath ?>"/>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <div id="card-config-pane" class="form-card card-panel <?= empty($content) ? 'hide' : '' ?>">
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="widget_title"><?= $trans->trans('Widget Title', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?> *</label>
                <input type="text" class="form-control" id="widget_title" name="widget_title" value="<?= isset($content['widgetTitle']) ? $content['widgetTitle'] : '' ?>" required>
            </div>
            <div class="col-md-6 form-group">
                <label for="card_module"><?= $trans->trans('Pick a module to show on this widget', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?> *</label>
                <select id="card_module" name="card_module" class="form-control navLink" data-selectize required>
                    <option value=""><?= $trans->trans('Choose an Option') ?></option>
                    <?php foreach ($modules as $module) {
                        $aliasModule = $module === 'deal' ? 'alias_promotion_module' : 'alias_'.$module.'_module';
                        $alias = ucwords($container->get('multi_domain.parameter')->get($aliasModule));
                        $contentModule = $content['module'] === 'promotion' ? 'deal' : $content['module'];
                        ?>
                        <option value="<?= $module ?>" <?= !empty($content['module']) && $contentModule === $module ? 'selected=selected' : '' ?>>
                            <?=/** @Ignore */ $trans->trans($alias, [], 'messages', $sitemgrLanguage) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group">
                <label for="link_label">
                    <?= $trans->trans('Link Label', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" title="<?= $trans->trans('Leave it blank to not display a link', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>"></i>
                </label>
                <input type="text" class="form-control" id="link_label" name="link_label" value="<?= isset($content['widgetLink']['label']) ? /** @Ignore */ $trans->trans($content['widgetLink']['label'], [], 'messages', $sitemgrLanguage) : '' ?>">
            </div>
            <?php $selectedPageId = isset($content['widgetLink']['page_id']) ? $content['widgetLink']['page_id'] : null ?>
            <div class="col-md-6 form-group">
                <label for="card_link_page_id"><?= $trans->trans('URL', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?><?= $selectedPageId ? ' *' : ''?></label>
                <select class="form-control navLink" data-selectize id="card_link_page_id" name="card_link_page_id" <?= $selectedPageId ? 'required' : ''?>>
                    <option value=""><?= $trans->trans('Choose an Option', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></option>
                    <optgroup label="<?= system_showText(LANG_SITEMGR_MAIN_PAGES) ?>">
                        <?php foreach ($auxArrayPages['mainPages'] as $page) { ?>
                            <option value="<?= $page['page_id'] ?>" <?= $selectedPageId == $page['page_id'] ? 'selected=selected' : '' ?>>
                                <?= string_ucwords($page['name']) ?>
                            </option>
                        <?php } ?>
                    </optgroup>
                    <?php if (isset($auxArrayPages['customPages']) && count($auxArrayPages['customPages']) > 0) { ?>
                        <optgroup label="<?= system_showText(LANG_SITEMGR_CUSTOM_PAGES) ?>">
                            <?php foreach ($auxArrayPages['customPages'] as $page) { ?>
                                <option value="<?= $page['page_id'] ?>" <?= $selectedPageId == $page['page_id'] ? 'selected=selected' : '' ?>>
                                    <?= string_ucwords($page['name']) ?>
                                </option>
                            <?php } ?>
                        </optgroup>
                    <?php } ?>
                    <optgroup label="<?= $trans->trans('Custom Link', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>">
                        <option value="custom" <?= isset($content['widgetLink']['page_id']) && $content['widgetLink']['page_id'] === 'custom' ? 'selected=selected' : '' ?>>
                            <?= $trans->trans('Custom Link', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                        </option>
                    </optgroup>
                </select>
            </div>
        </div>

        <div id="custom-link-div" class="row <?= $content['widgetLink']['page_id'] !== 'custom' ? 'hide' : '' ?>">
            <?php
            $isExternal = false;
            if (isset($content['widgetLink']['link'])) {
                $isExternal = preg_match('/http(s?):\/\//', $content['widgetLink']['link']) == 1;
            }
            ?>
            <div class="col-md-12 form-group clearfix">
                <label for="custom_link"><?= $trans->trans('Custom Link', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></label>
                <div id="custom_link_div" class="<?= !$isExternal ? 'input-group' : '' ?>">
                        <span class="input-group-addon <?= $isExternal ? 'hide' : '' ?>">
                            <?= $container->get('pagetype.service')->getBaseUrl(\ArcaSolutions\WysiwygBundle\Entity\PageType::HOME_PAGE).'/' ?>
                        </span>
                    <input type="text" class="form-control" id="custom_link" name="custom_link" aria-describedby="custom_link"
                           value="<?= !empty($content['widgetLink']['link']) ? $content['widgetLink']['link'] : '' ?>" <?= $content['widgetLink']['page_id'] !== 'custom' ? '' : 'required' ?> data-modalaux="<?='card-'.$pageWidgetId?>">
                </div>
            </div>
            <div class="col-md-12 form-group">
                <div class="radio-group" style="margin-top: 0;">
                    <label>
                        <input type="radio" name="custom_link_type" value="internal" <?= !$isExternal ? 'checked=checked' : '' ?> data-modalaux="<?='card-'.$pageWidgetId?>">
                        <?= $trans->trans('Internal Page', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                        <input type="hidden" id="cardInternalValue" value="" data-modalaux="<?='card-'.$pageWidgetId?>"/>
                    </label>
                    <label>
                        <input type="radio" name="custom_link_type" value="external" <?= $isExternal ? 'checked=checked' : '' ?> data-modalaux="<?='card-'.$pageWidgetId?>">
                        <?= $trans->trans('External Link', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                        <input type="hidden" id="cardExternalValue" value="" data-modalaux="<?='card-'.$pageWidgetId?>"/>
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div id="banner_input_wrapper" class="col-md-6 form-group <?=$itemCountEditable ? '' : 'hide'?>">
                <label for="card_banner"><?= $trans->trans('Display banners', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></label>
                <select class="form-control navLink" data-selectize id="card_banner" name="card_banner">
                    <option value=""><?= $trans->trans('Do not display banners', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></option>
                    <option value="empty" <?= isset($content['banner']) && $content['banner'] === 'empty' ? 'selected=selected' : '' ?>>
                        <?= $trans->trans('Do not display banners', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                    </option>
                    <option value="square" <?= isset($content['banner']) && $content['banner'] === 'square' ? 'selected=selected' : '' ?>>
                        <?= $trans->trans('Display Square banners', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                    </option>
                    <option value="skyscraper" <?= isset($content['banner']) && $content['banner'] === 'skyscraper' ? 'selected=selected' : '' ?>>
                        <?= $trans->trans('Display wideskyscrapper banners', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                    </option>
                </select>
            </div>
            <div id="columns_input_wrapper" class="col-md-6 form-group <?=$itemCountEditable ? '' : 'hide'?>">
                <label for="card_columns">
                    <?= $trans->trans('Number of columns', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?> *
<!--                    <a href="#preview-image" class="preview-column">--><?//= $trans->trans('preview', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?><!--</a>-->
                </label>
                <select class="form-control navLink" data-selectize id="card_columns" name="card_columns" required>
                    <option value=""><?= $trans->trans('Choose an Option', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></option>
                    <?php if ($oneColumnsWidgetException) { ?>
                        <option value="1" <?= isset($content['columns']) && $content['columns'] == 1 ? 'selected=selected' : '' ?>>
                            1
                        </option>
                    <?php } ?>
                    <option value="2" <?= isset($content['columns']) && $content['columns'] == 2 ? 'selected=selected' : '' ?>>
                        2
                    </option>
                    <option value="3" <?= isset($content['columns']) && $content['columns'] == 3 ? 'selected=selected' : '' ?>>
                        3
                    </option>
                    <?php if (!$fourColumnsWidgetException) { ?>
                        <option value="4" <?= isset($content['columns']) && $content['columns'] == 4 ? 'selected=selected' : '' ?>>
                            4
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row">
            <?php
            $hasNeutralColor = true;
            $isCard = true;

            include INCLUDES_DIR . '/forms/form-design-settings.php';
            ?>
        </div>

        <div id="widget-rule-div" class="row <?= $content['module'] ? '' : 'hide' ?>">
            <div class="col-md-12 form-group">
                <label for=""><?= $trans->trans('How would you like to display your items on this widget?', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></label>
                <?php
                $isCustom = null;
                if (!empty($content['items'])) {
                    $isCustom = false;
                } elseif (!empty($content['custom'])) {
                    $isCustom = true;
                }
                ?>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="widget_rule_type" value="individual" <?= $isCustom === false ? 'checked=checked' : '' ?>>
                        <?= $trans->trans('Pick individual items', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                    </label>
                    <label>
                        <input type="radio" name="widget_rule_type" value="custom" <?= $isCustom ? 'checked=checked' : '' ?>>
                        <?= $trans->trans('Customize rules', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                    </label>
                </div>

                <div class="col-sm-6 alert alert-danger" id="widget_rule_type_alert" style="display: none;">
                    <?= $trans->trans('Please Select an Option', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
                </div>
            </div>
        </div>

    </div>

    <div id="card-pick-itens-pane" class="form-card <?= $isCustom === false ? '' : 'hide' ?>">
        <div class="card-items">
            <ul id="sortableItem" class="list-sortable list-lg clearfix">
                <?php if (!empty($content['items'])) { ?>
                    <?php foreach ($content['items'] as $itemId) { ?>
                        <?= $cardService->getIndividualItemTemplate($itemId, $content['module'], $sitemgrLanguage) ?>
                    <?php } ?>
                <?php } ?>
                <li class="ui-sortable-handle ui-sortable-add sortableCard" id="addItem">
                    <a class="card-item add-item" id="add-item-div" href="javascript:void(0)">
                        <i class="fa fa-plus-circle" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div id="card-custom-rules-pane" class="form-card <?= $isCustom ? '' : 'hide' ?>">
        <div class="row">
            <div id="card-custom-rules-form" class="col-md-6 form-group <?= isset($moduleLevelInfo[$content['module']]) ? '' : 'hide' ?>">
                <label for=""><?= $trans->trans('From which level would you like to display these items?', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></label>
                <?php foreach ($moduleLevelInfo as $module => $levels) { ?>
                    <div class="checkbox-group <?= isset($content['module']) && $content['module'] === $module ? '' : 'hide' ?>" data-item-type="<?= $module ?>">
                        <?php foreach ($levels as $level) { ?>
                            <label>
                                <?php
                                $isChecked = $content['module'] === $module &&
                                    isset($content['custom']['level']) &&
                                    in_array($level->getValue(), $content['custom']['level']);
                                ?>
                                <input type="checkbox" name="card_levels[<?= $module ?>][]" value="<?= $level->getValue() ?>" <?= $isChecked ? 'checked=checked' : '' ?>>
                                <?= string_ucwords($level->getName()) ?>
                            </label>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 form-group" id="card_order1_div">
                <label for="card_order1">
                    <?= $trans->trans('How would you like to order the results? Choose two criterias', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?> *
                </label>
                <select data-trans="<?= $trans->trans('Alphabetical', [], 'administrator');?>" data-trans="<?= $trans->trans('Average reviews', [], 'administrator');?>" data-trans="<?= $trans->trans('Level', [], 'administrator');?>" data-trans="<?= $trans->trans('Most viewed', [], 'administrator');?>"
                        data-trans="<?= $trans->trans('Random', [], 'administrator');?>" data-trans="<?= $trans->trans('Recently added', [], 'administrator');?>" data-trans="<?= $trans->trans('Recently updated', [], 'administrator');?>" data-trans="<?= $trans->trans('Upcoming', [], 'administrator');?>"
                        class="form-control navLink" id="card_order1" name="card_order1" data-lastoption="<?=isset($content['custom']['order1']) ? $content['custom']['order1'] : ''?>"
                        data-lastlabel="<?=isset($content['custom']['order1']) ? /** @Ignore */ $trans->trans(CardService::CRITERIA[$content['custom']['order1']], [], 'administrator',/** @Ignore */ $sitemgrLanguage) : ''?>" data-selectize-valuesort
                        <?= $isCustom && $itemCountEditable ? 'required' : '' ?>>
                    <option value=""><?= $trans->trans('Choose an Option', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></option>
                    <?php foreach (CardService::CRITERIA as $key => $value) { ?>
                        <?php if($content['custom']['order2'] !== $key && (CardService::CRITERIA_MODULES[$key] === null || in_array($content['module'],
                                    CardService::CRITERIA_MODULES[$key], true))) { ?>
                            <option value="<?= $key ?>" <?= isset($content['custom']['order1']) && $content['custom']['order1'] === $key ? 'selected=selected' : '' ?>>
                                <?=/** @Ignore */ $trans->trans($value, [], 'administrator', $sitemgrLanguage) ?>
                            </option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-6 form-group" id="card_order2_div">
                <select class="form-control navLink has-fake-label" id="card_order2" name="card_order2" data-lastoption="<?=isset($content['custom']['order2']) ? $content['custom']['order2'] : ''?>"
                        data-lastlabel="<?=isset($content['custom']['order2']) ? /** @Ignore */ $trans->trans(CardService::CRITERIA[$content['custom']['order2']], [], 'administrator',/** @Ignore */ $sitemgrLanguage) : ''?>" data-selectize-valuesort>
                    <option value=""><?= $trans->trans('Choose an Option', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></option>
                    <?php foreach (CardService::CRITERIA as $key => $value) { ?>
                        <?php if($content['custom']['order1'] !== $key && (CardService::CRITERIA_MODULES[$key] === null || in_array($content['module'],
                                    CardService::CRITERIA_MODULES[$key], true))) { ?>
                            <option value="<?= $key ?>" <?= isset($content['custom']['order2']) && $content['custom']['order2'] === $key ? 'selected=selected' : '' ?>>
                                <?=/** @Ignore */  $trans->trans($value, [], 'administrator', $sitemgrLanguage) ?>
                            </option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div id="itens_count_input_wrapper" class="row <?=$itemCountEditable ? '' : 'hide'?>">
            <div class="col-md-6 form-group">
                <label for="card_itens_count">
                    <?= $trans->trans('How many items would you like to display?', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?> *
                </label>
                <input type="number" min="1" class="form-control" id="card_itens_count" name="card_itens_count" value="<?= isset($content['custom']['quantity']) ? $content['custom']['quantity'] : '' ?>" <?= (empty($content['items']) && $itemCountEditable) ? 'required' : '' ?>>
            </div>
        </div>

        <div class="form-group">
            <label for="card_categories">
                <?= $trans->trans('Would you like to filter your listings by specific categories? Leave it blank for all categories.', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
            </label>
            <div class="row">
                <?php foreach ($modules as $module) {
                    $hide = '';
                    $contentModule = $content['module'] === 'promotion' ? 'deal' : $content['module'];
                    if (empty($contentModule) || $contentModule !== $module) {
                        $hide = 'hide';
                    }
                    ?>
                    <div class="col-md-6 <?= $hide ?>" data-category-select="<?= $module ?>">
                        <select id="card_<?= $module ?>_categories" class="form-control" name="card_categories[<?= $module ?>]" data-selectize multiple>
                            <option value=""><?= $trans->trans('Click to pick categories', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?></option>
                            <?php if (isset($categories[$module])) { ?>
                                <?php foreach ($categories[$module] as $category) { ?>
                                    <option value="<?= $category->getId() ?>"
                                        <?= isset($content['custom']['categories']) && in_array($category->getId(),
                                            $content['custom']['categories']) ? 'selected=selected' : '' ?>>
                                        <?= $category->getTitle() ?>
                                    </option>
                                <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div id="itens_filter_location_wrapper" <?= ($content['module'] === 'article' || $content['module'] === 'blog' ) ? 'class="hide"' : '' ?>>
            <label>
                <?= $trans->trans('Would you like to filter your listings by specific locations? Leave it blank for all locations.', [], 'administrator', /** @Ignore */ $sitemgrLanguage) ?>
            </label>
            <div class="row" id="location_list">
                <?php foreach ($defaultLocations as $defaultLocation) { ?>
                    <?php
                    $defaultLocationLevels[$defaultLocation['type']] = $defaultLocation['id'];

                    if ($defaultLocation['show'] !== 'y') {
                        continue;
                    }

                    system_retrieveLocationRelationship($locationLevels, $defaultLocation['type'],
                        $fatherLevel,
                        $childLevel);
                    ?>

                    <div class="col-md-6 form-group">
                        <input type="hidden" id="location<?= $defaultLocation['type'] ?>" name="location<?= $defaultLocation['type'] ?>" value="<?= $defaultLocation['id'] ?>" data-location-default data-location-level="<?= $defaultLocation['type'] ?>" data-location-child-level="<?= $childLevel ?>">
                        <input type="text" class="form-control" readonly value="<?= $defaultLocation['name'] ?>">
                    </div>
                <?php } ?>

                <?php foreach ($locationLevels as $level) {
                    system_retrieveLocationRelationship(
                        array_merge(array_keys($defaultLocationLevels), $locationLevels),
                        $level,
                        $fatherLevel,
                        $childLevel
                    );

                    $locations = [];
                    $fatherSelectedLocationId = null;
                    $selectedLocationId = isset($content['custom']['locations']['location_'.$level]) ?
                        $content['custom']['locations']['location_'.$level] : null;

                    if (array_key_exists($fatherLevel, $defaultLocationLevels)) {
                        $fatherSelectedLocationId = $defaultLocationLevels[$fatherLevel];
                    } elseif (isset($content['custom']['locations']['location_'.$fatherLevel])) {
                        $fatherSelectedLocationId = $content['custom']['locations']['location_'.$fatherLevel];
                    }

                    if (!$fatherLevel) {
                        $class = 'Location'.$level;
                        $locationClass = new $class();

                        $locations = $locationClass->retrieveAllLocation();
                    }

                    if ($fatherSelectedLocationId) {
                        $class = 'Location'.$level;
                        $locationClass = new $class();
                        $locationClass->setString('location_'.$fatherLevel, $fatherSelectedLocationId);

                        $locations = $locationClass->retrieveLocationByLocation($fatherLevel, false,
                            $fatherSelectedLocationId);
                    }

                    ?>
                    <div class="col-md-6 form-group select-location <?=!empty($locations) ? '' : 'hide'?>" data-location-level="<?= $level ?>">
                        <select class="form-control navLink" data-selectize id="location<?= $level ?>" name="location<?= $level ?>" data-location-level="<?= $level ?>" data-location-child-level="<?= $childLevel ?>">
                            <option value=""><?= constant('LANG_SITEMGR_LABEL_'.constant('LOCATION'.$level.'_SYSTEM')) ?></option>
                            <?php if($locations) {
                                foreach ($locations as $location) { ?>
                                    <option value="<?= $location['id'] ?>" <?= $selectedLocationId == $location['id'] ? 'selected=selected' : '' ?>><?= $location['name'] ?></option>
                                <?php }
                            } ?>
                        </select>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>
</form>

<script>
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-selectize]').selectize();
    $('[data-selectize-valuesort]').selectize({sortField: [{field: 'value', direction: 'asc'}]});
</script>
