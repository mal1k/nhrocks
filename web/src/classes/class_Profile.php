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
	# * FILE: /classes/class_Profile.php
	# ----------------------------------------------------------------------------------------------------

    /**
	 * <code>
	 *		$profileObj = new Profile($id);
	 * <code>
	 * @copyright Copyright 2018 Arca Solutions, Inc.
	 * @author Arca Solutions, Inc.
	 * @version 8.0.00
	 * @package Classes
	 * @name Profile
	 * @access Public
	 */

	class Profile extends Handle {

		public $account_id;
		public $image_id;
		public $facebook_image;
		public $nickname;
		public $friendly_url;
		public $entered;
		public $updated;
		public $personal_message;
		public $facebook_uid;
		public $profile_exists;

        /**
		 * <code>
		 *		$profileObj = new Profile($id);
		 * <code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
         * @param mixed $var
		 * @access Public
		 */
        public function __construct($var='') {
			if (is_numeric($var) && $var) {
				$db = db_getDBObject(DEFAULT_DB,true);
				$sql = "SELECT * FROM Profile WHERE account_id = $var";
				$row = mysqli_fetch_array($db->query($sql));
				$this->makeFromRow($row);
			} else {
                if (!is_array($var)) {
                    $var = array();
                }
				$this->makeFromRow($var);
			}

            /* ModStores Hooks */
            HookFire("classprofile_contruct", [
                "that" => &$this
            ]);
		}

        /**
		 * <code>
		 *		$this->makeFromRow($row);
		 * <code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
         * @param string $row
		 * @access Public
		 */
        public function makeFromRow($row='') {

            /* ModStores Hooks */
            HookFire("classprofile_before_makerow", [
                "that" => &$this,
                "row"  => &$row,
            ]);

            $this->account_id           = $row['account_id']        ?: ($this->account_id ?: 0);
            $this->image_id             = $row['image_id']          ?: ($this->image_id ?: 0);
            $this->facebook_image       = $row['facebook_image']    ?: ($this->facebook_image ?: '');
            $this->nickname             = $row['nickname']          ?: ($this->nickname ?: '');
            $this->friendly_url         = $row['friendly_url']      ?: ($this->friendly_url ?: '');
            $this->entered              = $row['entered']           ?: ($this->entered ?: 0);
            $this->updated              = $row['updated']           ?: ($this->updated ?: 0);
            $this->personal_message     = $row['personal_message']  ?: ($this->personal_message ?: '');
            $this->facebook_uid         = $row['facebook_uid']      ?: ($this->facebook_uid ?: '');
            $this->profileExists();

            /* ModStores Hooks */
            HookFire("classprofile_after_makerow", [
                "that" => &$this,
                "row"  => &$row,
            ]);
		}

        /**
		 * <code>
		 *		//Using this in forms or other pages.
		 *		$profileObj->Save();
		 * <br /><br />
		 *		//Using this in Profile() class.
		 *		$this->Save();
		 * </code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
		 * @name Save
		 * @access Public
		 */
		public function Save() {
			$exists = $this->profile_exists;

            /* ModStores Hooks */
            HookFire("classprofile_before_preparesave", [
                "that" => &$this
            ]);

			$this->prepareToSave();
			$dbObj = db_getDBObject(DEFAULT_DB,true);

			if ($exists) {
				$sql  = "UPDATE Profile SET"
					. " image_id = $this->image_id,"
					. " facebook_image = $this->facebook_image,"
					. " nickname = $this->nickname,"
					. " friendly_url = $this->friendly_url,"
					. " updated = NOW(),"
					. " personal_message = $this->personal_message,"
					. " facebook_uid = $this->facebook_uid "
					. " WHERE account_id = $this->account_id";

                /* ModStores Hooks */
                HookFire("classprofile_before_updatequery", [
                    "that" => &$this,
                    "sql"  => &$sql,
                ]);

				$dbObj->query($sql);

                /* ModStores Hooks */
                HookFire("classprofile_after_updatequery", [
                    "that" => &$this
                ]);
			} else {
				$auxAccID = str_replace("'", "", $this->account_id);
				if ($auxAccID > 0) {

                    if ($this->friendly_url == "''") {
                        $this->friendly_url = system_generateFriendlyURL(str_replace("'", "", $this->nickname));
                    }

                    //Check for repeated friendly url
                    $sql = "SELECT account_id FROM Profile WHERE friendly_url = ".db_formatString($this->friendly_url);
                    $result = $dbObj->query($sql);
                    if (mysqli_num_rows($result) > 0) {
                        $this->friendly_url = $this->friendly_url.FRIENDLYURL_SEPARATOR.uniqid();
                    }

                    $this->friendly_url = db_formatString($this->friendly_url);

					$sql = "INSERT INTO Profile"
						. " (account_id, image_id, facebook_image, nickname, friendly_url, entered, personal_message, facebook_uid)"
						. " VALUES"
						. " ($this->account_id, $this->image_id, $this->facebook_image, $this->nickname, $this->friendly_url, NOW(), $this->personal_message, $this->facebook_uid)";

                    /* ModStores Hooks */
                    HookFire("classprofile_before_insertquery", [
                        "that" => &$this,
                        "sql"  => &$sql,
                    ]);

					$dbObj->query($sql);

                    /* ModStores Hooks */
                    HookFire("classprofile_after_insertquery", [
                        "that"  => &$this,
                        "dbObj" => &$dbObj,
                    ]);
				}
			}

            /* ModStores Hooks */
            HookFire("classprofile_before_prepareuse", [
                "that" => &$this
            ]);

			$this->prepareToUse();

            /* ModStores Hooks */
            HookFire("classprofile_after_save", [
                "that" => &$this
            ]);
		}

        /**
        * <code>
        *		//Using this in forms or other pages.
        *		$profileObj->profileExists();
        * <br /><br />
        *		//Using this in Profile() class.
        *		$this->profileExists();
        * </code>
        * @copyright Copyright 2018 Arca Solutions, Inc.
        * @author Arca Solutions, Inc.
        * @version 8.0.00
        * @name Save
        * @access Public
        */
		public function profileExists() {
			if ($this->account_id > 0) $this->profile_exists = true;
			else $this->profile_exists = false;
		}

        /**
		 * <code>
		 *		//Using this in forms or other pages.
		 *		$profileObj->findUid();
		 * <br /><br />
		 *		//Using this in Profile() class.
		 *		$this->findUid();
		 * </code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
		 * @name Save
		 * @access Public
         * @return boolean
		 */
		public function findUid($uid=false){
			if (!$uid) return false;
			$dbObj = db_getDBObject(DEFAULT_DB,true);
			$sql="SELECT * FROM Profile WHERE facebook_uid = '".addslashes($uid)."'";

			$dbObj->query($sql);
			$result = $dbObj->Query($sql);
			$row = mysqli_fetch_assoc($result);
			if ($row["account_id"]){
				$this->makeFromRow($row);
				return true;
			} else {
                return false;
            }

		}

        /**
		 * <code>
		 *		//Using this in forms or other pages.
		 *		$profileObj->Delete();
		 * <br /><br />
		 *		//Using this in Profile() class.
		 *		$this->Delete();
		 * </code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
		 * @name Save
		 * @access Public
		 */
		public function Delete() {
			$dbObj = db_getDBObject(DEFAULT_DB,true);

            ### IMAGE
			if ($this->image_id) {
				$image = new Image($this->image_id, true);
				if ($image) $image->Delete();
            }

            /* ModStores Hooks */
            HookFire("classprofile_before_delete", [
                "that" => &$this
            ]);

			$sql = "DELETE FROM Profile WHERE account_id = $this->account_id";
			$dbObj->query($sql);
		}

        /**
		 * <code>
		 *		//Using this in forms or other pages.
		 *		$profileObj->fUrl_Exists();
		 * <br /><br />
		 *		//Using this in Profile() class.
		 *		$this->fUrl_Exists();
		 * </code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
		 * @name Save
		 * @access Public
		 */
		public function fUrl_Exists($fUrl) {
			if ($fUrl) {
				$dbObj = db_getDBObject(DEFAULT_DB,true);
				$sql = " SELECT account_id FROM Profile WHERE friendly_url = '".$fUrl."'";
				$result = $dbObj->query($sql);
				if (mysqli_num_rows($result) > 0) {
					$row = mysqli_fetch_assoc($result);
					if ($row["account_id"] == sess_getAccountIdFromSession()) {
						return false;
					} else {
						return true;
					}
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
