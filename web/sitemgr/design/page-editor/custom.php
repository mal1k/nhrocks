<?php
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /ed-admin/design/page-editor/custom.php
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

# ----------------------------------------------------------------------------------------------------
# CODE
# ----------------------------------------------------------------------------------------------------

/*
 * Contact Us page action
 * TO DO: add validation to only add this code if the page being edited is the Contact Us page
 */
require CLASSES_DIR.'/class_Formbuilder.php';

if ($_GET['action'] === 'load') {

    $fileName = 'save.json';
    $widgetType = $_GET['widgetType'];

    if (isset($_GET['pageWidgetId']) && $_GET['pageWidgetId']) {
        $fileName = 'save_'.$_GET['pageWidgetId'].'.json';
    }

    $editorFolder = EDIRECTORY_ROOT.'/custom/domain_'.$_GET['domain_id'].'/editor/lead';

    if (!is_dir($editorFolder)) {
        //create folder custom/domain_x/editor/lead

        $editorFolderAux = EDIRECTORY_ROOT.'/custom/domain_'.$_GET['domain_id'].'/editor';

        if (!mkdir($editorFolderAux) && !is_dir($editorFolderAux)) {
            $errorFolder = true;
        }

        if (!mkdir($editorFolder) && !is_dir($editorFolder)) {
            $errorFolder = true;
        }
    }

    $jsonstr = '';
    if (file_exists($editorFolder.'/'.$fileName)) {
        $jsonstr = file_get_contents($editorFolder.'/'.$fileName);
    } else if ($widgetType == 'leadgen') {
        $fields = [
            [
                'cssClass' => 'input_text',
                'required' => 'true',
                'values'   => 'Name'
            ],
            [
                'cssClass' => 'input_text',
                'required' => 'true',
                'values'   => 'Email'
            ],
            [
                'cssClass' => 'input_text',
                'required' => 'true',
                'values'   => 'Phone Number'
            ],
            [
                'cssClass' => 'input_text',
                'required' => 'true',
                'values'   => 'Subject'
            ],
            [
                'cssClass' => 'textarea',
                'required' => 'true',
                'values'   => 'Message'
            ],
        ];

        $jsonstr = json_encode($fields);
    }
    $arrayJson = ['form_structure' => $jsonstr];
    $form = new Formbuilder($arrayJson);
    $form->render_json();
    exit;

} elseif ($_GET['action'] === 'save') {

    $editorFolder = EDIRECTORY_ROOT.'/custom/domain_'.$_GET['domain_id'].'/editor/lead';

    //create folder custom/domain_x/editor/lead
    if (!is_dir($editorFolder) && !mkdir($editorFolder)) {
        $errorFolder = true;
    }

    $fileName = 'save.json';

    if (isset($_GET['pageWidgetId']) && $_GET['pageWidgetId']) {
        $fileName = 'save_'.$_GET['pageWidgetId'].'.json';
    }

    $form_data = isset($_POST['frmb']) ? $_POST : false;
    $form = new Formbuilder($form_data);
    $arrayJson = $form->get_encoded_form_array();
    file_put_contents($editorFolder.'/'.$fileName, $arrayJson['form_structure']);
    exit;
} elseif ($_GET['action'] === 'upload') {
    include INCLUDES_DIR.'/code/wysiwygimage.php';
    exit;
}

$container = SymfonyCore::getContainer();
$widgetService = $container->get('widget.service');
$pageWidgetService = $container->get('pagewidget.service');
$pageService = $container->get('page.service');
$pageTypeService = $container->get('pagetype.service');

$translator = $container->get('translator');
setting_get('sitemgr_language', $sitemgr_language);
$sitemgrLanguage = substr($sitemgr_language, 0, 2);

$url_redirect = ''.DEFAULT_URL.'/'.SITEMGR_ALIAS.'/design/page-editor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['resetPageId']) {
        $id = $_POST['resetPageId'];
        $saveReturn = $pageWidgetService->resetPage($_POST['resetPageId']);
    } else {
        $id = $_POST['id'];
        //Save Page widgets on database
        $saveReturn = $pageWidgetService->savePageWidgets($_POST['id'], $_POST);
    }
    header("Location: $url_redirect/custom.php?".http_build_query(['id' => $id, 'saveReturn' => $saveReturn]));
}

if (!isset($_GET['id'])) {
    header("Location: $url_redirect/index.php");
}

$page = $pageService->getPage($_GET['id']);

if (!$page) {
    header("Location: $url_redirect/index.php?error=1");
}

$pageTypeId = $page->getPageTypeId();
$pageType = $page->getPageType()->getTitle();
$pageWidgets = $pageWidgetService->getWidgetsPerPage($_GET['id']);

$domainObj = new Domain(SELECTED_DOMAIN_ID);
$newurl = $domainObj->getString('url');

extract($_GET, null);

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
include SM_EDIRECTORY_ROOT.'/layout/sidebar-design.php';
?>

    <div id="loading_ajax" class="alert alert-loading alert-loading-fullscreen"
         style="display: none;">
        <img src="<?= DEFAULT_URL; ?>/<?= SITEMGR_ALIAS ?>/assets/img/loading-128.gif" class="alert-img-center">
    </div>

    <div class="wysiwyg">

        <main class="wrapper togglesidebar container-fluid">
            <?php
            require(SM_EDIRECTORY_ROOT."/registration.php");
            require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
            ?>

            <form role="form" name="form_widgets" id="form_widgets"
                  action="<?= system_getFormAction($_SERVER['PHP_SELF']) ?>?id=<?= $_GET['id'] ?>" method="post">

                <input id="pageId" name="id" type="hidden" value="<?= $_REQUEST['id'] ?>">
                <input id="openWidgetId" type="hidden" value="">
                <input type="hidden" name="submit_button" id="submit_button">
                <input type="hidden" name="serializedPost" id="serializedPost">
                <input type="hidden" name="changed" id="changed">
                <input type="hidden" name="resetPage" id="resetPage">
                <input type="hidden" name="selectedDomainId" id="selectedDomainId" value="<?= SELECTED_DOMAIN_ID ?>">
                <input type="hidden" name="oldUrl" id="oldPageUrl" value="<?= $page->getUrl() ?>">
                <input type="submit" style="display: none">

                <section class="heading">

                    <div class="pull-right">
                        <a id="button-cancel" class="btn btn-lg"
                           href="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/design/page-editor/"><?= system_showText(LANG_SITEMGR_CANCEL) ?></a>
                        <?php if (!in_array($page->getPageType()->getTitle(),
                                $container->get('pagetype.service')->pageViewNotAllowed, true) or $page->getPageType()->getTitle() === \ArcaSolutions\WysiwygBundle\Entity\PageType::HOME_PAGE) { ?>
                            <a id="button-view" class="btn btn-default btn-lg" target="_blank"
                               href="<?= $pageService->getActiveHostFinalPageUrl($page) ?>"><?= system_showText(LANG_SITEMGR_VIEW) ?></a>
                        <?php } ?>
                        <button id="button-save" class="btn btn-primary btn-lg action-save" type="button"
                                data-loading-text="<?= system_showText(LANG_LABEL_FORM_WAIT); ?>"
                                id="button-save"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES) ?></button>
                    </div>
                    <h1>
                        <?= system_showText(LANG_SITEMGR_PAGE_BUILDER) ?>
                    </h1>
                    <p><?= system_showText(LANG_SITEMGR_PAGE_BUILDER_TIP); ?></p>

                    <div class="alert alert-success" id="successAlert" style="display: none">
                        <div></div>
                    </div>
                    <div class="alert alert-danger" id="errorAlert" style="display: none">
                        <div></div>
                    </div>
                    <?php
                    //Success and Error Messages
                    if ($saveReturn) {
                        if ($saveReturn['success']) {
                            echo '<p id="alert-save" class="alert alert-success" style="display: none">'.$saveReturn['message'].'</p>';
                        } else {
                            echo '<p id="alert-save" class="alert alert-warning" style="display: none">'.$saveReturn['message'].'</p>';
                        }
                    }
                    ?>
                </section>

                <?php if (!in_array($pageType, $container->get('pagetype.service')->getPagesWithoutSEO(), true)) { ?>
                    <section>
                        <div class="row">
                            <div class="col-md-12">
                                <p>
                                    <a role="button" class="arrow-toggle collapsed" data-toggle="collapse"
                                       href="#collapseAdvOptions" aria-expanded="false"
                                       aria-controls="collapseAdvOptions">
                                        <?= system_showText(LANG_SITEMGR_SHOW_SEO_ADVANCED_OPTIONS) ?>
                                        <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="row nopadd collapse-advOptions">
                        <div class="collapse" id="collapseAdvOptions">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label
                                                for="description"><?= system_showText(LANG_SITEMGR_META_DESCRIPTION) ?></label>
                                        <textarea id="description" name="description" rows="9"
                                                  class="form-control"><?= $_POST['descriptions'] ?: $page->getMetaDescription() ?></textarea>
                                    </div>
                                    <?php if (SITEMAP_FEATURE === 'on' && strpos($pageType, 'Custom Page') !== false) { ?>
                                        <div class="form-group checkbox">
                                            <label>
                                                <input type="checkbox" class="inputCheck" name="sitemap"
                                                       value="1" <?= $page->isSitemap() ? 'checked' : ''; ?>>
                                                <?= system_showText(LANG_SITEMGR_LABEL_SITEMAP) ?>
                                                <p class="help-block small"><?= system_showText(LANG_SITEMGR_CONTENT_SITEMAP_CHECKBOX) ?></p>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="tour-keywords">
                                        <label for="keywords">
                                            <?= system_showText(LANG_SITEMGR_META_KEYWORD) ?>
                                        </label>
                                        <input type="text" class="form-control tag-input" id="keywords" name="keywords"
                                               value="<?= $_POST['keywords'] ?: $page->getMetaKey() ?>"
                                               placeholder="<?= system_showText(LANG_HOLDER_KEYWORDS); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="customTag">
                                            <?= system_showText(LANG_SITEMGR_ADD_TAG_PAGE_HEADER) ?>
                                        </label>
                                        <textarea id="customTag" name="customTag" rows="4" class="form-control"
                                                  placeholder="<?= system_showText(LANG_SITEMGR_ADD_TAG_PAGE_PLACEHOLDER) ?>"
                                                  tabindex=""><?= $_POST['customTag'] ?: $page->getCustomTag() ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                <?php } ?>

                <section class="row inverse pb-0">

                    <div class="row text-right">
                        <div class="col-md-12">
                            <a class="btn btn-lg resetPageButton" href="#">
                                <?= system_showText(LANG_SITEMGR_RESET_TO_DEFAULT) ?>
                            </a>
                            <a class="btn btn-primary btn-lg btn-new-widget" id="new-widget"
                               href="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/design/page-editor/add-new-widget.php?page=<?= $page->getId(); ?>&type=<?= $pageTypeId; ?>">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                                <?= system_showText(LANG_SITEMGR_ADD_WIDGET) ?>
                            </a>
                        </div>
                    </div>

                    <div class="gradient-border">
                        <div class="content">
                            <div class="tab-page">
                                <div class="form-group">
                                    <?php
                                    $onblur = '';
                                    if ($page->getPageType()->getTitle() === \ArcaSolutions\WysiwygBundle\Entity\PageType::CUSTOM_PAGE) {
                                        $onblur = "onblur=\"easyFriendlyUrl(this.value, 'url', '".FRIENDLYURL_VALIDCHARS."', '".FRIENDLYURL_SEPARATOR."');\"";
                                    }
                                    ?>
                                    <input id="type" type="text" name="title"
                                           value="<?= $_POST['title'] ?: $page->getTitle() ?>"
                                           class="form-control"
                                           placeholder="<?= system_showText(LANG_SITEMGR_LABEL_PAGETITLE) ?>"
                                           required <?= $onblur ?>>
                                </div>
                            </div>
                            <?php if (!in_array($page->getPageType()->getTitle(), $pageTypeService->urlNonEditable)) { ?>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="input-group">
                                            <span
                                                    class="input-group-addon"><?= $pageTypeService->getBaseUrl($page->getPageType()->getTitle()).'/' ?></span>
                                            <input type="text" name="url" id="url"
                                                   value="<?= $_POST['url'] ?: $page->getUrl() ?>"
                                                   class="form-control " maxlength="100" required
                                                   onblur="easyFriendlyUrl(this.value, 'url', '<?= FRIENDLYURL_VALIDCHARS ?>', '<?= FRIENDLYURL_SEPARATOR ?>');">
                                            <?php if (strpos($pageType, 'Custom Page') !== false) { ?>
                                                <span
                                                        class="input-group-addon">.html</span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </section>
            </form>

            <section class="row inverse pt-0">
                <div class="row sortableDiv">
                    <div class="col-md-12">
                        <div id="sortWidgets" class="sortable">
                            <?php
                            $i = 0;
                            if ($pageWidgets) {
                                /* @var \ArcaSolutions\WysiwygBundle\Entity\PageWidget $pageWidget */
                                foreach ($pageWidgets as $pageWidget) {
                                    $i++;
                                    $widgetId = $pageWidget->getWidget()->getId();
                                    $pageWidgetId = $pageWidget->getId();
                                    $widgetModal = $pageWidget->getWidget()->getModal();
                                    $title = json_decode($pageWidget->getContent())->widgetTitle ? : $pageWidget->getWidget()->getTitle();
                                    $widgetTitle = /** @Ignore */
                                        $translator->trans($title, [], 'widgets',
                                            $sitemgrLanguage);
                                    $widgetTitleImg = $pageWidget->getWidget()->getTitle();
                                    $widgetType = $pageWidget->getWidget()->getType();

                                    include INCLUDES_DIR.'/lists/list-widgets.php';
                                }
                            } ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="add-widget add-new-widget row">
                            <div class="col-md-12">
                                <a href="#" class="btn-new-widget">
                                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                                    <?= system_showText(LANG_SITEMGR_ADD_WIDGET) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

        </main>

        <?php include INCLUDES_DIR.'/views/template-thumb-image-modal.php'; ?>

        <!--add new widget modal-->
        <div class="modal fade" id="add-new-widget-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog modal-lg vertical-scroll" role="document">
                <div class="modal-content"></div>
            </div>
        </div>
    </div>
<?php

# ----------------------------------------------------------------------------------------------------
# MODAIS
# ----------------------------------------------------------------------------------------------------
include INCLUDES_DIR.'/modals/modal-reset-page.php';
include INCLUDES_DIR.'/modals/widget/modal-widget-remove.php';
?>

    <!--edit this widget modal-->
    <div class="modal fade wysiwyg" id="edit-widget-modal" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel"></div>

    <!--edit navigation link modal-->
    <div class="modal fade wysiwyg" id="edit-navigation-link-modal" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content"></div>
        </div>
    </div>

<?php
$confirmPages = $pageTypeService->urlConfirmation;
$confirmPages = array_merge($confirmPages['location'], $confirmPages['category'], $confirmPages['review']);
if (in_array($page->getPageType()->getTitle(), $confirmPages)) {
    $messages = $pageTypeService->getMessageUrlConfirmation($page->getPageType()->getTitle());
    ?>
    <div class="modal fade" id="modal-confirmation" role="dialog" aria-labelledby="modal-delete" aria-hidden="true">
        <div class="modal-dialog modal-danger">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span
                                class="sr-only">Close</span></button>
                    <h4 class="modal-title"><?= system_showText(LANG_SITEMGR_PAGE_URL_UPDATE) ?></h4>
                </div>
                <div class="modal-body text-center">
                    <p><?= $messages['text'] ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-dismiss="modal"
                            onclick="resetSaveButton()"><?= system_showText(LANG_SITEMGR_CANCEL) ?></button>
                    <?php if (!empty($messages['no'])) { ?>
                        <button type="button" class="btn confirmation-save"
                                data-dismiss="modal"><?= $messages['no'] ?></button>
                    <?php } ?>
                    <button type="button" data-replica="<?= $messages['replica'] ?>"
                            class="btn btn-primary confirmation-save"><?= $messages['yes'] ?></button>
                </div>
            </div>
        </div>
    </div>


    <?php
}

JavaScriptHandler::registerFile(DEFAULT_URL . '/assets/js/lib/unsplash.js');

# ----------------------------------------------------------------------------------------------------
# FOOTER
# ----------------------------------------------------------------------------------------------------
$customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/widget.php';

include SM_EDIRECTORY_ROOT.'/layout/footer.php';
