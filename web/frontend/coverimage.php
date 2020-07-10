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
    # * FILE: /frontend/coverimage.php
    # ----------------------------------------------------------------------------------------------------
?>

    <div class="section-headers" data-align="<?=!empty($widgetContent['dataAlignment']) ? $widgetContent['backgroundColor'] : ''?>" data-bg="<?=!empty($widgetContent['backgroundColor']) && $widgetContent['backgroundColor'] === 'base' ? 'brand' : 'base'?>">
        <div class="container">
            <div class="wrapper">
                <h2 class="heading h-2"><?=$cover_title?></h2>
                <?php if ($cover_subtitle) { ?>
                    <div class="paragraph p-2"><?=$cover_subtitle;?></div>
                <?php } ?>
            </div>
        </div>
    </div>
