
<?php if (string_strpos($_SERVER['PHP_SELF'], '/'.SITEMGR_ALIAS.'/configuration/payment/') === false) { ?>
<br>
<?php } ?>

<div class="upgradeplan-banner">
    <div class="upgradeplan-content">
        <div class="upgradeplan-icon">
            <img src="<?= DEFAULT_URL.'/'.SITEMGR_ALIAS ?>/assets/img/upgradeplan-icon.png"/>
        </div>
        <div class="upgradeplan-text">
            <?php if (string_strpos($_SERVER['PHP_SELF'], '/'.SITEMGR_ALIAS.'/configuration/payment/') !== false) { ?>
                <h3><?= LANG_SITEMGR_UPGRADE_PLAN5 ?></h3>
                <h3><?= LANG_SITEMGR_UPGRADE_PLAN6 ?></h3>
            <?php } else { ?>
                <h3><?= LANG_SITEMGR_UPGRADE_PLAN ?></h3>
                <small><?= LANG_SITEMGR_UPGRADE_PLAN3 ?></small>
            <?php } ?>
        </div>
    </div>
    <div class="upgradeplan-action">
        <a href="https://www.edirectory.com/upgrade-from-basic/?domain=<?=DEFAULT_URL?>" target="_blank" class="button"><?= LANG_SITEMGR_UPGRADE_PLAN4 ?></a>
    </div>
</div>
