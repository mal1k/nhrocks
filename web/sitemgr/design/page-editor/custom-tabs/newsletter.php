<?php

$widget = $groupedWidgets[ArcaSolutions\WysiwygBundle\Entity\Widget::NEWSLETTER_TYPE][0];
$customerID =  setting_get('arcamailer_customer_id');

/* ModStores Hooks */
$linkForward = null;

HookFire('newsletter_before_add_widgettype', [
    "linkForward" => &$linkForward,
    "classItem" => &$classItem
]);
?>

<div role="tabpanel"
     class="tab-pane <?= ($customerID) ? 'has-list' : ''; ?>"
     id="tab-newsletter"
     data-customer-id="<?= $customerID ?>"
     data-customer-listid="<?= setting_get('arcamailer_customer_listid') ?>">

    <div id="newsletter-message-wrapper"></div>
    <div id="newsletter-widget" class="grid-pinterest" style="display: none;">
        <div class="item thumbnail <?= (in_array($widget['title'],
            $excludeWidgets) ? 'unavailable' : ($widget['title'] == 'Signup for our newsletter' && $settings == null ? 'linkWidget' : 'addWidget')) ?>"
             data-widgetid="<?= $widget['id'] ?>" data-pageId="<?= $page->getId() ?>"
             data-title="<?= $widget['title'] ?>" data-type="<?= $widget['type'] ?>">
            <div class="caption">
                <h4>
                    <?= /** @Ignore */
                    $trans->trans($widget['title'], [], 'widgets', /** @Ignore */
                        $sitemgrLanguage) ?>
                </h4>
                <?php
                $imgPath = '/assets/img/widget-placeholder/'.system_generateFriendlyURL($widget['title']).'.jpg';

                $imgPath = file_exists(EDIRECTORY_ROOT.'/'.SITEMGR_ALIAS.$imgPath) ?
                    DEFAULT_URL.'/'.SITEMGR_ALIAS.$imgPath :
                    DEFAULT_URL.'/'.SITEMGR_ALIAS.'/assets/img/widget-placeholder/custom-content.jpg';
                ?>
                <img src="<?= $imgPath ?>"/>
            </div>
        </div>
    </div>

    <div id="newsletter-form" class="mask" style="display: none;">
        <div id="first-panel" class="newsletter-panel">
            <div class="campaignmonitor">
                <img alt="Campaign Monitor Logo"
                     src="<?= DEFAULT_URL.'/'.SITEMGR_ALIAS ?>/assets/img/campaignmonitor_logo.png"/>
            </div>

            <h3><?= $trans->trans('You don’t have synchronized your mail account yet', [], 'messages') ?></h3>
            <p><?= $trans->trans('Please activate the newsletter integration to enable newsletter widgets.', [],
                    'messages') ?></p>
            <div class="newsletter-action">
                <button class="btn btn-primary btn-newsletter" id="new-account-button">
                    <?= $trans->trans('Create account', [], 'messages') ?>
                </button>
                <button class="btn btn-primary btn-newsletter" id="login-button">
                    <?= $trans->trans('Log in', [], 'messages') ?>
                </button>
            </div>
        </div>

        <div id="new-account-panel" class="newsletter-panel" style="display: none;">
            <div class="campaignmonitor">
                <img alt="Campaign Monitor Logo"
                     src="<?= DEFAULT_URL.'/'.SITEMGR_ALIAS ?>/assets/img/campaignmonitor_logo.png"/>
            </div>
            <h3><?= $trans->trans('Create account', [], 'messages') ?></h3>
            <form id="newsletter-register-form" class="form">
                <div class="form-group">
                    <input type="text" class="form-control" id="newsletter_name" required
                           placeholder="<?= $trans->trans('Name', [], 'messages') ?>">
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" id="newsletter_email" required
                           placeholder="<?= $trans->trans('Email', [], 'messages') ?>">
                </div>
                <div class="form-group">
                    <select class="form-control" id="newsletter_country" required
                            title="<?= $trans->trans('Country', [], 'messages') ?>">
                        <option><?= $trans->trans('Country', [], 'messages') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <select class="form-control" id="newsletter_timezone" required
                            title="<?= $trans->trans('Timezone', [], 'messages') ?>">
                        <option><?= $trans->trans('Timezone', [], 'messages') ?></option>
                    </select>
                </div>
                <div class="newsletter-action">
                    <button type="button" class="btn btn-default btn-newsletter cancel">
                        <?= $trans->trans('Nevermind', [], 'messages') ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= $trans->trans('Create account', [], 'messages') ?>
                    </button>
                </div>
            </form>
        </div>

        <div id="newsletter-login-panel" class="newsletter-panel" style="display: none;">
            <div class="campaignmonitor">
                <img alt="Campaign Monitor Logo"
                     src="<?= DEFAULT_URL.'/'.SITEMGR_ALIAS ?>/assets/img/campaignmonitor_logo.png"/>
            </div>
            <h3><?= $trans->trans('Log in', [], 'messages') ?></h3>
            <form id="newsletter-login-form" class="form">
                <div class="form-group">
                    <input type="email" class="form-control" id="newsletter_user" required
                           placeholder="<?= $trans->trans('Email', [], 'messages') ?>">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="newsletter_password" required
                           placeholder="<?= $trans->trans('Password', [], 'messages') ?>">
                </div>
                <div class="newsletter-action">
                    <button type="button" class="btn btn-default btn-newsletter cancel">
                        <?= $trans->trans('Nevermind', [], 'messages') ?>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <?= $trans->trans('Log in', [], 'messages') ?>
                    </button>
                </div>
            </form>
        </div>

        <div id="newsletter-create-list" class="newsletter-panel" style="display: none;">
            <div class="campaignmonitor">
                <img alt="Campaign Monitor Logo"
                     src="<?= DEFAULT_URL.'/'.SITEMGR_ALIAS ?>/assets/img/campaignmonitor_logo.png"/>
            </div>
            <h3><?= $trans->trans('Newsletter', [], 'messages') ?></h3>
            <form id="newsletter-list-form" class="form">
                <div class="form-group">
                    <input type="text" class="form-control" id="newsletter-list-name" required
                           placeholder="<?= $trans->trans('Newsletter Name', [], 'messages') ?>">
                </div>
                <div class="newsletter-action">
                    <button type="submit" class="btn btn-primary">
                        <?= $trans->trans('Create new', [], 'messages') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

if (!HookFire("newsletter_before_render_js")) { ?>
    <script src="<?= DEFAULT_URL ?>/<?= SITEMGR_ALIAS ?>/assets/js/newsletter.js"></script>
<?php }
