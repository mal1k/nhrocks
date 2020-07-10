<?php

$container = SymfonyCore::getContainer();
$advertiseHandler = $container->get('advertise.handler');
$plans = $advertiseHandler->getLevels($signupItem);
$trans = $container->get('translator');

setting_get('review_listing_enabled', $review_enabled);
setting_get('payment_tax_status', $payment_tax_status);
setting_get('payment_tax_value', $payment_tax_value);
setting_get('payment_tax_label', $payment_tax_label);

?>

<div class="pricing-plans">
    <div class="container">
        <div class="pricing-table">
            <div class="pricing-wrapper is-active">
                <div class="pricing-list">
                <?php foreach ($plans as $levelValue => $plan) {
                    $pricing = $advertiseHandler->getAdvertisePrice($plan);
                    $priceAux = explode('.', $pricing['main_price']);
                    $hasMonthlyValue = !empty($pricing['monthly']['value']);
                    $hasYearlyValue = !empty($pricing['yearly']['value']);
                    ?>
                    <div class="pricing-item is-collapsed">
                        <div class="paragraph p-3 pricing-plan"><?= $plan->name ?></div>
                        <div class="pricing-price">
                            <?php if($plan->trial > 0) { ?>
                                <div class="heading h-3 pricing-value"><?=$plan->trial . ' ' . LANG_ADVERTISE_TRIAL?></div>
                                <?php if($hasMonthlyValue || $hasYearlyValue) {
                                    $pricingPeriod = LANG_AFTER . ' ';
                                    if($hasMonthlyValue) {
                                        $pricingPeriod .= $pricing['main']['symbol'] . $pricing['monthly']['value'] . '/' . LANG_MONTH;
                                        if($hasYearlyValue) {
                                            $pricingPeriod .= ' ' . LANG_OR . ' ' . $pricing['main']['symbol'] . $pricing['yearly']['value'] . '/' . LANG_YEAR;
                                        }
                                    } elseif($hasYearlyValue) {
                                        $pricingPeriod .= $pricing['main']['symbol'] . $pricing['yearly']['value'] . '/' . LANG_YEAR;
                                    }
                                    ?>
                                    <div class="paragraph p-4 pricing-period"><?=$pricingPeriod ?></div>
                                <?php } ?>
                            <?php } elseif($hasMonthlyValue) { ?>
                                <div class="heading h-3 pricing-value"><?=$pricing['main']['symbol'] . $pricing['monthly']['value'] . '/' . ucfirst(LANG_MONTH) ?></div>
                                <?php if($hasYearlyValue) { ?>
                                    <div class="paragraph p-4 pricing-period"><?=LANG_OR . ' ' . $pricing['main']['symbol'] . $pricing['yearly']['value'] . '/' . LANG_YEAR ?></div>
                                <?php } ?>
                            <?php } elseif($hasYearlyValue) { ?>
                                <div class="heading h-3 pricing-value"><?=$pricing['main']['symbol'] . $pricing['yearly']['value'] . '/' . ucfirst(LANG_YEAR)?></div>
                            <?php } else { ?>
                                <div class="heading h-3 pricing-value"><?=ucfirst(LANG_LABEL_FREE)?></div>
                            <?php } ?>
                        </div>
                        <div class="pricing-action">
                            <button type="button" class="button button-bg is-secondary choose-plan" data-level="<?= $levelValue ?>">
                                <?= LANG_LABEL_CHOOSEPLAN ?>
                            </button>
                        </div>
                        <div class="pricing-collapse is-open">
                            <ul class="price-advantages">
                                <li class="price-advantages-item has-advantages">
                                    <div class="icon icon-md"><i class="fa"></i></div>
                                    <div class="item-name"><?= LANG_ADVERTISE_LIST_TITLE_ADDRESS ?></div>
                                </li>
                                <?php if (!empty($pricing['description'])) { ?>
                                    <div class="price-description">
                                        <p class="pragraph p-2"><?=$pricing['description']?></p>
                                    </div>
                                <?php } else {
                                    foreach ($plan as $feature => $value) {
                                        if ($value !== null && !in_array($feature, $advertiseHandler->getNonFeatures())) { ?>
                                            <li class="price-advantages-item <?= !$value ? '' : 'has-advantages' ?>">
                                                <div class="icon icon-md"><i class="fa"></i></div>
                                                <?php if ($feature === 'hasDetail' && $value) { ?>
                                                    <div class="item-name"><a href="<?= DEFAULT_URL.'/'.constant(sprintf('ALIAS_%s_MODULE', strtoupper($signupItem))).'/sample-'.$levelValue.'.html' ?>" class="link" rel="nofollow"
                                                                              title="<?= LANG_ADVERTISE_LIST_DETAIL_VIEW ?>" target="_blank"><?= /** @Ignore */ $trans->transChoice($feature, $value, [], 'advertise') ?></a></div>
                                                <?php } else { ?>
                                                    <div class="item-name"><?= /** @Ignore */ $trans->transChoice($feature, $value, ['%count%' => $value], 'advertise') ?></div>
                                                <?php } ?>
                                            </li>
                                        <?php }
                                    }
                                } ?>
                            </ul>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
