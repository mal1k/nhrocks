<?php
$r = rand(1, 9999);

$linkPage = $arrayOptions[$i]['custom'] ? $arrayOptions[$i]['link'] : $arrayOptions[$i]['pageId'];

$navbarHtml .= <<<HTML
<li class="sortableNav" id="$r">
    <div class="navigation-item">
        <ul class="nav nav-pills">
            <li>
                <a href="#" data-toggle="modal" class="editNavItem" data-id="$r" data-modalaux="$area" data-label="{$arrayOptions[$i]['label']}" data-link="{$arrayOptions[$i]['page_id']}" data-custom="{$arrayOptions[$i]['custom']}">
                    <i class="fa fa-cog" aria-hidden="true"></i>
                </a>
            </li>
            <li>
                <a class="sortable-remove removeNavItem" href="#" data-id="$r" title="{$translator->trans('Remove', [],
    'widgets', /** @Ignore */
    $sitemgrLanguage)}">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </a>
            </li>
        </ul>
        <i class="fa fa-bars" aria-hidden="true"></i>
        <input type="text" class="form-control navTitle" name="navigation_text_$r" id="navigation_text_$r" value="{$arrayOptions[$i]['label']}" />
        <input type="hidden" id="custom_$r" name="custom_$r" value="{$arrayOptions[$i]['custom']}" />
        <input type="hidden" id="link_$r" name="link_$r" value="$linkPage" />
    </div>
</li>
HTML;
