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
	# * FILE: /classes/class_listingChoice.php
	# ----------------------------------------------------------------------------------------------------

	class ListingChoice extends Handle {

		var $id;
		var $editor_choice_id;
		var $listing_id;

		/**
		* Listing Choice
		******************************************************/
        public function __construct($editor="", $listing="") {

			if ( (is_numeric($editor) && ($editor)) && (is_numeric($listing) && ($listing)) ) {

				$dbMain = db_getDBObject(DEFAULT_DB, true);
				if (defined("SELECTED_DOMAIN_ID")) {
					$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$dbObj = db_getDBObject();
				}

				unset($dbMain);
				$sql  = "SELECT * FROM Listing_Choice";
				$sql .= " WHERE editor_choice_id = $editor";
				$sql .= " AND   listing_id       = $listing";

				$row = mysqli_fetch_array($dbObj->query($sql));

				$this->makeFromRow($row);

			} else {
                if (!is_array($var)) {
                    $var = array();
                }
				$this->makeFromRow($row);

			}
		}

		/**
		* makeFromRow
		******************************************************/
		function makeFromRow($row="") {

		  	$this->id				= ($row["id"])				 ? $row["id"]				: 0;
			$this->editor_choice_id = ($row["editor_choice_id"]) ? $row["editor_choice_id"] : 0;
			$this->listing_id       = ($row["listing_id"])       ? $row["listing_id"]       : 0;

		}

		/**
		* Save
		******************************************************/
		function Save() {
			$this->prepareToSave();

			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
//			$dbMain->close();
			unset($dbMain);
			$sql = "INSERT INTO Listing_Choice"
				. " (editor_choice_id,"
				. "  listing_id)"
				. " VALUES"
				. " ($this->editor_choice_id,"
				. "  $this->listing_id)";

			$dbObj->query($sql);
			$this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);

			$this->prepareToUse();

		}

		/**
		* Delete
		******************************************************/
		function Delete() {

			$sql  = "DELETE FROM Listing_Choice";
			$sql .= " WHERE listing_id = $this->listing_id";

			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
//			$dbMain->close();
			unset($dbMain);
			$dbObj->query($sql);

		}

		/**
		* Delete Listing Choice that are Available
		*******************************************************/
		function DeleteAvailable() {

			$sql  = "DELETE FROM Listing_Choice";
			$sql .= " WHERE listing_id       = $this->listing_id";
			$sql .= " AND   editor_choice_id = $this->editor_choice_id";

			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
//			$dbMain->close();
			unset($dbMain);
			$dbObj->query($sql);

		}

	}

?>
