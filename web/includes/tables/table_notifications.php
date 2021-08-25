<?
/*
* # Admin Panel for eDirectory
* @copyright Copyright 2018 Arca Solutions, Inc.
* @author Basecode - Arca Solutions, Inc.
*/

# ----------------------------------------------------------------------------------------------------
# * FILE: /includes/tables/table-notification.php
# ----------------------------------------------------------------------------------------------------
?>

<table class="table table-bordered">
    <tr>
        <th>
            <?= system_showText(LANG_SITEMGR_LABEL_NAME) ?>
        </th>
        <th nowrap>
            <?= system_showText(LANG_SITEMGR_LABEL_ENABLED) ?>
        </th>
        <th nowrap>
            <?= system_showText(LANG_SITEMGR_LABEL_TYPE) ?>
        </th>
        <th nowrap>
            <?= system_showText(LANG_SITEMGR_LASTUPDATE) ?>
        </th>
    </tr>
    <?
    if ($emails) {
        /* @var $email EmailNotification */
        foreach ($emails as $email) {
            $id = $email->getNumber("id");
            ?>

            <tr>

                <td class="table-help">
                    <a href="email.php?id=<?= $id ?>">
                        <?= system_showText(@constant("LANG_SITEMGR_EMAILNOTIF_TYPE_".$id)) ?>
                    </a>
                    <i class="icon-help8"
                       title="<?= system_showText(@constant("LANG_SITEMGR_EMAILNOTIF_DESC_".$id)) ?>"></i>
                </td>

                <td class="text-center">
                    <input type="checkbox" data-id="<?= $email->getNumber('id') ?>" data-status="<?= $email->getString('deactivate') ?>" <?= $email->getString('deactivate') == '0' ? 'checked' : '' ?> onclick="changeEmailStatus($(this))">
                </td>

                <td>
                    <?= !$email->getNumber("days") ? system_showText(LANG_SITEMGR_SYSTEMNOTIFICATION) : system_showText(LANG_SITEMGR_RENEWALREMINDER) ?>
                </td>

                <td>
                    <?php
                    if (is_null($email->getDate("updated", null))) {
                        echo system_showText(LANG_SITEMGR_NOTUPDATED);
                    } else {
                        echo format_date($email->getNumber("updated"), DEFAULT_DATE_FORMAT,
                                "datetime")." - ".format_getTimeString($email->getNumber("updated"));
                    }
                    ?>
                </td>

            </tr>
            <?
        }
    } ?>
</table>
