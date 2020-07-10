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
	# * FILE: /conf/scalability.inc.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# FLAGS - on/off
	# ----------------------------------------------------------------------------------------------------

	// suggestion: turn on if edirectory has more than 100.000 listings and/or more than 50.000 listings on the highest level
	define("LISTING_SCALABILITY_OPTIMIZATION", "off");

	// suggestion: turn on if edirectory has more than 100.000 events and/or more than 50.000 events on the highest level
	define("EVENT_SCALABILITY_OPTIMIZATION", "off");

	// suggestion: turn on if edirectory has more than 100.000 classifieds and/or more than 50.000 classifieds on the highest level
	define("CLASSIFIED_SCALABILITY_OPTIMIZATION", "off");

	// suggestion: turn on if edirectory has more than 20 main listing categories
	define("LISTINGCATEGORY_SCALABILITY_OPTIMIZATION", "off");

	// suggestion: turn on if edirectory has more than 20 main event categories
	define("EVENTCATEGORY_SCALABILITY_OPTIMIZATION", "off");

	// suggestion: turn on if edirectory has more than 20 main classified categories
	define("CLASSIFIEDCATEGORY_SCALABILITY_OPTIMIZATION", "off");

	// suggestion: turn on if edirectory has more than 20 main article categories
	define("ARTICLECATEGORY_SCALABILITY_OPTIMIZATION", "off");

    // suggestion: turn on if edirectory has more than 20 main blog categories
	define("BLOGCATEGORY_SCALABILITY_OPTIMIZATION", "off");

	# ----------------------------------------------------------------------------------------------------
	# AUTOMATIC FEATURES
	# ----------------------------------------------------------------------------------------------------
	// *** AUTOMATIC FEATURE *** (DONT CHANGE THESE LINES)
	if ((LISTINGCATEGORY_SCALABILITY_OPTIMIZATION == "on") || (EVENTCATEGORY_SCALABILITY_OPTIMIZATION == "on") || (CLASSIFIEDCATEGORY_SCALABILITY_OPTIMIZATION == "on") || (ARTICLECATEGORY_SCALABILITY_OPTIMIZATION == "on") || (BLOGCATEGORY_SCALABILITY_OPTIMIZATION == "on")) {
		define("CATEGORY_SCALABILITY_OPTIMIZATION", "on");
	} else {
		define("CATEGORY_SCALABILITY_OPTIMIZATION", "off");
	}
	// *** AUTOMATIC FEATURE *** (DONT CHANGE THESE LINES)

?>
