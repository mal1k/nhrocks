<?

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
	# * FILE: /functions/front_funct.php
	# ----------------------------------------------------------------------------------------------------

    function front_getHeaderTag(&$headertag_title, &$headertag_author) {
        if (!$headertag_title) {
            setting_get('header_title', $headertag_title);
            $headertag_title = $headertag_title ?: EDIRECTORY_TITLE;
        }

        if (!$headertag_author) {
            setting_get('header_author', $headertag_author);
            $headertag_author = $headertag_author ?: 'Arca Solutions';
        }

        $headertag_title .= " | ".EDIRECTORY_TITLE;

    }

    function front_googleTagManager($section = 'head') {

        setting_get('google_tagmanager_status', $google_tagmanager_status);
        setting_get('google_tagmanager_clientID', $google_tagmanager_clientID);

        if ($google_tagmanager_status == "on" && $google_tagmanager_clientID) {

            if ($section === 'head') { ?>
                <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
                    })(window,document,'script','dataLayer','<?=$google_tagmanager_clientID?>');</script>
            <? }

            if ($section === 'body') { ?>
                <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?=$google_tagmanager_clientID?>"
                                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <? }

        }
    }

    function front_errorPage() {
        header("Location: ".DEFAULT_URL."/404.html");
        exit;
    }

    function front_colorScheme()
    {
        setting_get('colorscheme_'. EDIR_THEME,$colorscheme);

        $colorschemeArr = json_decode($colorscheme, true);

        $colorBlock = '';

        if(!empty($colorschemeArr)) {
            foreach ($colorschemeArr as $key => $colorVar) {
                if (strpos($key, '-base') !== false) {
                    $colorBlock .= $colorVar;
                }
            }
        }

        $defaultFontFamilyParagraph = [
            'default'    => '',
            'doctor'     => 'Rubik:300,500,700,900',
            'restaurant' => 'Merriweather Sans:300,700,800',
            'wedding'    => 'Raleway:100,200,300,500,600,700,800,900',
        ];

        $defaultFontFamilyHeading = [
            'default'    => '',
            'doctor'     => 'Poppins:100,200,300,500,600,700,800,900',
            'restaurant' => 'Titillium Web:200,300,600,700,900',
            'wedding'    => 'Playfair Display:700,900',
        ];

        $paragraphFont = !empty($colorschemeArr['paragraph_font']) ? $colorschemeArr['paragraph_font'] : $defaultFontFamilyParagraph[$theme];
        $headingFont = !empty($colorschemeArr['heading_font']) ? $colorschemeArr['heading_font'] : $defaultFontFamilyHeading[$theme];

        $paragraphFontName = $paragraphFont;
        $headingFontName = $headingFont;

        if (strpos($paragraphFont, ':') !== false) {
            preg_match('/.+?(?=:)/', $paragraphFont, $paragraphFontName);
            $paragraphFontName = $paragraphFontName[0];
        }
        if (strpos($headingFont, ':') !== false) {
            preg_match('/.+?(?=:)/', $headingFont, $headingFontName);
            $headingFontName = $headingFontName[0];
        }

        $colorSchemeBlock = '';

        if(!empty($paragraphFont)) {
            $colorSchemeBlock .= '<link href="https://fonts.googleapis.com/css?family='. $paragraphFontName .'" rel="stylesheet">';
        } else {
            $colorSchemeBlock .= '<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">';
        }
        if(!empty($headingFont)) {
            $colorSchemeBlock .= '<link href="https://fonts.googleapis.com/css?family='. $headingFontName .'" rel="stylesheet">';
        } else {
            $colorSchemeBlock .= '<link href="https://fonts.googleapis.com/css?family=Rubik:300,400,500,700,900" rel="stylesheet">';
        }

        $colorSchemeBlock .= '<style>';

        $colorSchemeBlock .= ':root {' . $colorBlock;

        if(!empty($paragraphFont)) {
            $colorSchemeBlock .= '--paragraph-font: ' . $paragraphFont . ';';
            $colorSchemeBlock .= '--font-family-base: ' . $paragraphFont . ';';
        }
        
        if(!empty($headingFont)) {
            $colorSchemeBlock .= '--heading-font: ' . $headingFont . ';';
        }

        $colorSchemeBlock .= '</style>';

        echo $colorSchemeBlock;
    }
