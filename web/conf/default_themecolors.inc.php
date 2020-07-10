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
	# * FILE: /conf/default_themecolors.inc.php
	# ----------------------------------------------------------------------------------------------------

	//Theme: Default

	//Main palette colors
	$arrayColors['default']['brand'] = '022e69';
	$arrayColors['default']['highlight'] = '137df7';
	$arrayColors['default']['neutral'] = '3e455e';
	$arrayColors['default']['badge'] = 'ffc800';
	$arrayColors['default']['link'] = '3ebaf6';
	$arrayColors['default']['success'] = '6cbf13';
	$arrayColors['default']['warning'] = 'fc5353';
	$arrayColors['default']['attention'] = 'ffae00';

	//Border radius
    $arrayColors['default']['image_border'] = '3';
    $arrayColors['default']['input_border'] = '3';

    //Fonts
    $arrayColors['default']['heading_font'] = 'Rubik:300,500,700,900';
    $arrayColors['default']['paragraph_font'] = 'Roboto:100,300,500,700,900';
    $arrayColors['default']['font'] = '16';

    //Theme: Doctors

    //Main palette colors
    $arrayColors['doctor']['brand'] = '294481';
    $arrayColors['doctor']['highlight'] = '294481';
    $arrayColors['doctor']['neutral'] = '4b4b4b';
    $arrayColors['doctor']['badge'] = '294481';
    $arrayColors['doctor']['link'] = '23d5ae';
    $arrayColors['doctor']['success'] = '6cbf13';
    $arrayColors['doctor']['warning'] = 'fc5353';
    $arrayColors['doctor']['attention'] = 'ffae00';

    //Border radius
    $arrayColors['doctor']['image_border'] = '3';
    $arrayColors['doctor']['input_border'] = '3';

    //Fonts
    $arrayColors['doctor']['heading_font'] = 'Poppins:100,200,300,500,600,700,800,900';
    $arrayColors['doctor']['paragraph_font'] = 'Rubik:300,500,700,900';
    $arrayColors['doctor']['font'] = '18';

    //Theme: Wedding

    //Main palette colors
    $arrayColors['wedding']['brand'] = 'fe8b90';
    $arrayColors['wedding']['highlight'] = 'fe8b90';
    $arrayColors['wedding']['neutral'] = '333333';
    $arrayColors['wedding']['badge'] = 'ffc800';
    $arrayColors['wedding']['link'] = 'fe8b90';
    $arrayColors['wedding']['success'] = '6cbf13';
    $arrayColors['wedding']['warning'] = 'fc5353';
    $arrayColors['wedding']['attention'] = 'ffae00';

    //Border radius
    $arrayColors['wedding']['image_border'] = '12';
    $arrayColors['wedding']['input_border'] = '24';

    //Fonts
    $arrayColors['wedding']['heading_font'] = 'Playfair Display:700,900';
    $arrayColors['wedding']['paragraph_font'] = 'Raleway:100,200,300,500,600,700,800,900';
    $arrayColors['wedding']['font'] = '18';

    //Theme: Restaurant

    //Main palette colors
    $arrayColors['restaurant']['brand'] = '29272e';
    $arrayColors['restaurant']['highlight'] = 'f15f2a';
    $arrayColors['restaurant']['neutral'] = '29272e';
    $arrayColors['restaurant']['badge'] = 'f15f2a';
    $arrayColors['restaurant']['link'] = 'f15f2a';
    $arrayColors['restaurant']['success'] = '6cbf13';
    $arrayColors['restaurant']['warning'] = 'fc5353';
    $arrayColors['restaurant']['attention'] = 'ffae00';

    //Border radius
    $arrayColors['restaurant']['image_border'] = '6';
    $arrayColors['restaurant']['input_border'] = '6';

    //Fonts
    $arrayColors['restaurant']['heading_font'] = 'Titillium Web:200,300,600,700,900';
    $arrayColors['restaurant']['paragraph_font'] = 'Merriweather Sans:300,700,800';
    $arrayColors['restaurant']['font'] = '18';

	define('ARRAY_DEFAULT_COLORS', serialize($arrayColors));
