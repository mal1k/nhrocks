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
	# * FILE: /classes/class_Quicklist.php
	# ----------------------------------------------------------------------------------------------------

	/**
	 * <code>
	 *		$quicklistObj = new Quicklist($id);
	 * <code>
	 * @copyright Copyright 2018 Arca Solutions, Inc.
	 * @author Arca Solutions, Inc.
	 * @version 7.5.00
	 * @package Classes
	 * @name Quicklist
	 * @access Public
	 */
	class Quicklist extends Handle {

		/**
		 * @var integer
		 * @access Private
		 */
		var $id;
		/**
		 * @var integer
		 * @access Private
		 */
		var $account_id;
		/**
		 * @var integer
		 * @access Private
		 */
		var $item_id;
		/**
		 * @var string
		 * @access Private
		 */
		var $item_type;

		/**
		 * <code>
		 *		$quicklistObj = new Quicklist($id);
		 * <code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 7.5.00
		 * @name Quicklist
		 * @access Public
		 * @param mixed $var
		 */
        public function __construct($var='', $account_id='', $item_id='', $item_type='') {
			if (is_numeric($var) && ($var) && !$account_id && !$item_id && !$item_type) {
				$dbMain = db_getDBObject(DEFAULT_DB, true);
				if (defined("SELECTED_DOMAIN_ID")) {
					$db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$db = db_getDBObject();
				}
				unset($dbMain);
				$sql = "SELECT * FROM Quicklist WHERE id = $var";
				$row = mysqli_fetch_array($db->query($sql));
				$this->makeFromRow($row);
			} else if (!is_numeric($var) && (!$var) && !$account_id && !$item_id && !$item_type)  {
                if (!is_array($var)) {
                    $var = array();
                }
				$this->makeFromRow($var);
			} else if (is_numeric($account_id) && $account_id != 0 && is_numeric($item_id) && $item_id != 0 && $item_type) {
				$dbMain = db_getDBObject(DEFAULT_DB, true);
				if (defined("SELECTED_DOMAIN_ID")) {
					$db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$db = db_getDBObject();
				}
				unset($dbMain);
				$sql = "SELECT * FROM Quicklist WHERE account_id = $account_id AND item_id = $item_id AND item_type = '".$item_type."'";
				$row = mysqli_fetch_array($db->query($sql));
				$this->makeFromRow($row);
			}

            /* ModStores Hooks */
            HookFire("classquicklist_contruct", [
                "that" => &$this
            ]);
		}

		/**
		 * <code>
		 *		$this->makeFromRow($row);
		 * <code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 7.5.00
		 * @name makeFromRow
		 * @access Private
		 * @param array $row
		 */
		function makeFromRow($row='') {

            /* ModStores Hooks */
            HookFire("classquicklist_before_makerow", [
                "that" => &$this,
                "row"  => &$row,
            ]);

			if ($row['id']) $this->id = $row['id'];
			else if (!$this->id) $this->id = 0;
			if ($row['account_id']) $this->account_id = $row['account_id'];
			else if (!$this->account_id) $this->account_id = 0;
			if ($row['item_id']) $this->item_id = $row['item_id'];
			else if (!$this->item_id) $this->item_id = 0;
			if ($row['item_type']) $this->item_type = $row['item_type'];
			else if (!$this->item_type) $this->item_type = "";

            /* ModStores Hooks */
            HookFire("classquicklist_after_makerow", [
                "that" => &$this,
                "row"  => &$row,
            ]);

		}

		function getQuicklist($from = "all", $acc = 0) {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
//			$dbMain->close();
			unset($dbMain);

			if (is_numeric($acc)) {
				if ($from == "all") {
					$sql = "SELECT * FROM Quicklist WHERE id = $acc";
				} else if ($from == "article" || $from == "classified" || $from == "event" || $from == "listing" || $from == "promotion") {
					$sql = "SELECT item_id FROM Quicklist WHERE account_id = $acc AND item_type = '".$from."'";
				}

				$result = $dbObj->Query($sql);

				unset($items);
				while ($row = mysqli_fetch_array($result)) {
					$items .= $row["item_id"].",";
				}

				$items = string_substr($items, 0, -1);

				return $items;
			} else {
				return null;
			}
		}

		/**
		 * <code>
		 *		//Using this in forms or other pages.
		 *		$quicklistObj->Add();
		 * <br /><br />
		 *		//Using this in Quicklist() class.
		 *		$this->Add();
		 * </code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 7.5.00
		 * @name Add
		 * @access Public
		 */
		function Add() {

            /* ModStores Hooks */
            HookFire("classquicklist_before_preparesave", [
                "that" => &$this
            ]);

			$this->prepareToSave();
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
//			$dbMain->close();
			unset($dbMain);

			if ($this->account_id && $this->item_id && $this->item_type) {
				$sql = "INSERT INTO Quicklist (account_id, item_id, item_type) VALUES
						($this->account_id, $this->item_id, $this->item_type);";

                /* ModStores Hooks */
                HookFire("classquicklist_before_insertquery", [
                    "that" => &$this,
                    "sql"  => &$sql,
                ]);

				$dbObj->query($sql);

                /* ModStores Hooks */
                HookFire("classquicklist_after_insertquery", [
                    "that"  => &$this,
                    "dbObj" => &$dbObj,
                ]);

				$this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);
			}

            /* ModStores Hooks */
            HookFire("classquicklist_before_prepareuse", [
                "that" => &$this
            ]);

			$this->prepareToUse();

            /* ModStores Hooks */
            HookFire("classquicklist_after_save", [
                "that" => &$this
            ]);
		}

		/**
		 * <code>
		 *		//Using this in forms or other pages.
		 *		$quicklistObj->Delete();
		 * <br /><br />
		 *		//Using this in Quicklist() class.
		 *		$this->Delete();
		 * <code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 7.5.00
		 * @name Delete
		 * @access Public
		 */
		function Delete() {
			$dbObj = db_getDBObject();

			/**
			* Deleting this object
			**/
			$dbObj = db_getDBObject();

            /* ModStores Hooks */
            HookFire("classquicklist_before_delete", [
                "that" => &$this
            ]);

			$sql = "DELETE FROM Quicklist WHERE id = $this->id";
			$dbObj->query($sql);
		}
	}