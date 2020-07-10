<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/configuration/payment/index.php
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

    mixpanel_track('Accessed section Manage Levels & Pricing');

	# ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
    include INCLUDES_DIR.'/code/paymentgateway.php';

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
	include SM_EDIRECTORY_ROOT.'/layout/sidebar-configuration.php';

    # ----------------------------------------------------------------------------------------------------
	# FUNCTIONS
	# ----------------------------------------------------------------------------------------------------

    /**
     * Checks if a tab is "active" judging from what comes from $_SESSION['PaymentOptions']['type'].
     *
     * @param string $name the name of the post action
     * @param boolean $default is this the default option?
     * @return string "active" or nothing.
     */
    function checkActiveTab( $name, $default = false )
    {
        $return = null;

        if( !empty( $_SESSION['PaymentOptions']['type'] ) )
        {
            $postResponseType = $_SESSION['PaymentOptions']['type'];

            if( is_array( $name ) )
            {
                $isSelected = in_array( $postResponseType, $name );
            }
            else
            {
                $isSelected = $name == $postResponseType;
            }

            $isSelected and $return = 'active';
        }

        return $default && empty( $_SESSION['PaymentOptions']['type'] ) ? 'active' : $return;
    }

    /**
     * Adds HTML to create a checkbox for each of a module's levels.
     * Used in the level options page.
     *
     * @param string $name The input name value
     * @param string $title The option title
     * @param string $tip The option explanation
     * @param array $levelvalues an array containing the levels
     * @param mixed $levelObj an isntance of the module listinglevel class
     * @param array $array_fields additional fields not contained in class properties
     * @param string $type module type
     */
    function createCheckboxField( $name, $title, $tip, $levelvalues, $levelObj, $array_fields, $type, $class )
    {
        echo "<td> $title <small class=\"help-block\"> $tip </small></td>";

        foreach ($levelvalues as $key => $levelvalue)
        {
            $checked = null;

            $disabled = PAYMENTSYSTEM_FEATURE === 'off' && $levelvalue > 10 ? 'disabled' : '';

            if( ( isset( $levelObj->{$name}[$key] ) && $levelObj->{$name}[$key] == 'y' ) || ( is_array( $array_fields[$levelvalue] )&& in_array( $name, $array_fields[$levelvalue] ) ) )
            {
                $checked = 'checked="checked"';
            }

            echo '<td class="checkbox-table">'
               . "    <input name=\"levelOption[$type][$name][$levelvalue]\" data-module=\"$type\" data-level=\"$levelvalue\" class=\"$class\" type=\"checkbox\" $checked $disabled>"
               .'</td>';
        }
    }

    /**
     * Adds HTML to create a numeric text field for each of a module's levels.
     * Used in the level options page.
     *
     * @param string $name The input name value
     * @param string $title The option title
     * @param string $tip The option explanation
     * @param int $max the maximum value allowed. HTML5
     * @param int $min the minimum value allowed. HTML5
     * @param array $levelvalues an array containing the levels
     * @param mixed $levelObj an isntance of the module listinglevel class
     * @param array $array_fields additional fields not contained in class properties
     * @param string $type module type
     */
    function createNumericField( $name, $title, $tip, $max, $min, $levelvalues, $levelObj, $array_fields, $type )
    {
        echo "<td> $title <small class=\"help-block\"> $tip </small></td>";

        foreach ($levelvalues as $key => $levelvalue)
        {
            $default = 'value="'.sprintf('%d', $levelObj->{$name}[$key]).'"';

            $disabled = PAYMENTSYSTEM_FEATURE === 'off' && $levelvalue > 10 ? 'disabled' : '';

            echo '<td class="form-group-table">'
               . "    <input name=\"levelOption[$type][$name][$levelvalue]\" type=\"number\" min=\"$min\" max=\"$max\" class=\"form-control input-sm\" $default $disabled>"
               .'</td>';
        }
    }

    /* Loads messages from session, if any.*/
    MessageHandler::unserialize();
?>

    <main class="wrapper togglesidebar container-fluid">

        <?php
        require SM_EDIRECTORY_ROOT.'/registration.php';
        require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
        ?>

        <section class="heading">
            <h1><?=system_showText(LANG_SITEMGR_PAYMENT_OPTIONS);?></h1>
            <p><?=system_showText(LANG_SITEMGR_SETTINGS_MANAGE_LEVELS_TIP1);?></p>
        </section>

        <form name="header" id="header" method="post" action="<?=system_getFormAction($_SERVER['PHP_SELF'])?>">
            <input type="hidden" name="save-pricing" id="save-pricing" value="">
            <div class="row tab-options">
                <ul role="tablist" class="nav nav-tabs">
                    <li id="pricing-tab" class="<?=checkActiveTab( 'levels', true )?>"><a href="#payment-pricing" data-toggle="tab" role="tab" ><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_LEVELS_TAB);?></a></li>
                    <li id="options-tab" class="<?=checkActiveTab('currencyOptions')?>"><a href="#payment-options" data-toggle="tab" role="tab" ><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_CURRENCY_TAB);?></a></li>
                    <?php if (PAYMENTSYSTEM_FEATURE === 'on') { ?>
                        <li id="gateways-tab" class="<?=checkActiveTab('gateways')?>"><a href="#payment-gateways" data-toggle="tab" role="tab" ><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_TAB);?></a></li>
                    <?php } ?>
                </ul>

                <div class="row tab-content">

                    <section id="payment-pricing" class="tab-pane <?=checkActiveTab( 'levels', true )?>">
                        <div class="col-sm-12">
                            <ul class="nav nav-pills" role="tablist">
                                <li class="<?=(!$_GET['option']) || $_GET['option'] === 'listing' ? 'active' : ''?>"><a href="#pricing-listing" data-toggle="tab" role="tab"><?=system_showText(LANG_SITEMGR_NAVBAR_LISTING);?></a></li>

                                <?php
                                foreach ( $availableModules as $type => $value )
                                {
                                    if( $value['active'] )
                                    {
                                        $active = $_GET['option'] === $type ? 'active' : '';
                                        echo "<li class=\"{$active}\"><a href=\"#pricing-{$type}\" data-toggle=\"tab\" role=\"tab\">{$value['name']}</a></li>";
                                    }
                                }
                                ?>
                            </ul>

                            <div class="row tab-content content-pills">

                                <?php
                                if (PAYMENTSYSTEM_FEATURE === 'off' && system_getListingCount()) {
                                    include INCLUDES_DIR.'/views/upgrade_plan_banner.php';
                                }

                                if (STRIPEPAYMENT_FEATURE === 'on' && PAYMENTSYSTEM_FEATURE === 'on') { ?>

                                    <p class="alert alert-warning"><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_STRIPE_TIP2)?></p>

                                <? } ?>

                                <div class="tab-pane <?=(!$_GET['option']) || $_GET['option'] === 'listing' ? 'active' : ''?>" id="pricing-listing">
                                    <?php
                                    $type = 'listing';
                                    include INCLUDES_DIR.'/forms/form-payment-pricing.php';?>
                                </div>
                                <?php
                                foreach ( $availableModules as $type => $value )
                                {
                                    if( $value['active'] )
                                    {
                                        $active = $_GET['option'] === $type ? 'active' : '';
                                        echo "<div class=\"tab-pane {$active}\" id=\"pricing-{$type}\">";
                                        include INCLUDES_DIR.'/forms/form-payment-pricing.php';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </section>

                    <section id="payment-options" class="tab-pane <?=checkActiveTab( [
                        'currencyOptions',
                        'taxOptions',
                        'invoiceOptions'
                    ] )?>">
                        <?include INCLUDES_DIR.'/forms/form-payment-options.php';?>
                    </section>

                    <?php if (PAYMENTSYSTEM_FEATURE === 'on') { ?>
                        <section id="payment-gateways" class="tab-pane <?=checkActiveTab('gateways')?>">
                            <?include INCLUDES_DIR.'/forms/form-payment-gateways.php';?>
                        </section>
                    <?php } ?>
                </div>
            </div>
        </form>
    </main>

<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/footer.php';
