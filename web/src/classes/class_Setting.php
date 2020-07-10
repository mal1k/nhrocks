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
	# * FILE: /classes/class_setting.php
	# ----------------------------------------------------------------------------------------------------

	class Setting extends Handle {

		var $name;
		var $value;
		var $in_main_db = Array("sitemgr_username",
							    "sitemgr_password",
			                    "sitemgr_faillogin_count",
			                    "sitemgr_faillogin_datetime",
			                    "sitemgr_first_login",
			                    "sitemgr_language",
                                "loaded_locations",
                                "added_location_manually",
                                "mixpanel_distinct_id",
                                "freetrial_end_date",
                                "install_name",
			                    "complementary_info",
								"listing_limit_count");

        public function __construct($var='') {
			if ($var) {
				$dbMain = db_getDBObject(DEFAULT_DB, true);
				if (in_array($var, $this->in_main_db)) {
					$db = $dbMain;
				} else if (defined("SELECTED_DOMAIN_ID")) {
					$db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$db = db_getDBObject();
				}
				unset($dbMain);

				$sql = "SELECT * FROM Setting WHERE name = ".db_formatString($var);
				$row = mysqli_fetch_array($db->query($sql));
				$this->makeFromRow($row);
			} else {
                if (!is_array($var)) {
                    $var = array();
                }
				$this->makeFromRow($var);
			}

            /* ModStores Hooks */
            HookFire("classsetting_contruct", [
                "that" => &$this
            ]);
		}

		function makeFromRow($row='') {

            /* ModStores Hooks */
            HookFire("classsetting_before_makerow", [
                "that" => &$this,
                "row"  => &$row,
            ]);

			$this->name		= ($row["name"])	? $row["name"]	: ($this->name	? $this->name	: 0);
			$this->value	= ($row["value"])	? $row["value"]	: "";

            /* ModStores Hooks */
            HookFire("classsetting_after_makerow", [
                "that" => &$this,
                "row"  => &$row,
            ]);
		}

		function Save($update = true) {

			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (in_array($this->name, $this->in_main_db)) {
				$dbObj = $dbMain;
			} else if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
//			$dbMain->close();
			unset($dbMain);

            /* ModStores Hooks */
            HookFire("classsetting_before_preparesave", [
                "that" => &$this
            ]);

			$this->prepareToSave();

			if ($update) {

				$sql = "UPDATE Setting SET"
					. " value      = $this->value"
					. " WHERE name = $this->name";

                /* ModStores Hooks */
                HookFire("classsetting_before_updatequery", [
                    "that" => &$this,
                    "sql"  => &$sql,
                ]);

				$dbObj->query($sql);

                /* ModStores Hooks */
                HookFire("classsetting_after_updatequery", [
                    "that" => &$this
                ]);

			} else {

				$sql = "INSERT INTO Setting"
					. " (name,"
					. " value)"
					. " VALUES"
					. " ($this->name,"
					. " $this->value)";

                /* ModStores Hooks */
                HookFire("classsetting_before_insertquery", [
                    "that" => &$this,
                    "sql"  => &$sql,
                ]);

				$dbObj->query($sql);

                /* ModStores Hooks */
                HookFire("classsetting_after_insertquery", [
                    "that"  => &$this,
                    "dbObj" => &$dbObj,
                ]);

			}

            /* ModStores Hooks */
            HookFire("classsetting_before_prepareuse", [
                "that" => &$this
            ]);

			$this->prepareToUse();
			setting_constants();

            /* ModStores Hooks */
            HookFire("classsetting_after_save", [
                "that" => &$this
            ]);
		}

		function Delete() {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (in_array($this->name, $this->in_main_db)) {
				$dbObj = $dbMain;
			} else if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
//			$dbMain->close();
			unset($dbMain);

            /* ModStores Hooks */
            HookFire("classsetting_before_delete", [
                "that" => &$this
            ]);

			$sql = "DELETE FROM Setting WHERE name = ".db_formatString($this->name);
			$dbObj->query($sql);
			if (mysqli_affected_rows($dbObj->link_id)) {
				setting_constants();
				return true;
			}
			return false;
		}

		function convertTableToArray(){
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined("SELECTED_DOMAIN_ID")) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);

            $sql    = "SELECT * FROM Setting";
            $result = $dbObj->query($sql);
			if(mysqli_num_rows($result)){
				unset($array_setting);
				$array_setting = array();
				while($row = mysqli_fetch_assoc($result)){
					$array_setting[$row["name"]]["name"]	= $row["name"];
					$array_setting[$row["name"]]["value"]	= $row["value"];
				}
				return $array_setting;
			}else{
				return false;
			}

		}

        function isSetFieldByArray($array) {

            if (is_array($array)) {
                $dbMain = db_getDBObject(DEFAULT_DB, true);
                if (defined("SELECTED_DOMAIN_ID")) {
                    $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                } else {
                    $dbObj = db_getDBObject();
                }
                unset($dbMain);

                $aux_where = implode(",",$array);

                $sql = "SELECT name, value FROM Setting WHERE name IN (".$aux_where.")";

                $result = $dbObj->query($sql);
                if (mysqli_num_rows($result)) {
                    unset($aux_return);
                    $aux_return = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                        $aux_return[] = $row["value"];
                    }
                    return $aux_return;
                } else {
                    return false;
                }

            }

        }

        function isSetField() {

            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if (defined("SELECTED_DOMAIN_ID")) {
                $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $dbObj = db_getDBObject();
            }
            unset($dbMain);

            $sql    = "SELECT * FROM Setting WHERE name='$this->name'";

            $result = $dbObj->query($sql);

            if (mysqli_fetch_array($result)) {
                return true;
            }

        }

	}

?>
