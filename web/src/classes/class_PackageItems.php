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
	# * FILE: /classes/class_PackageItems.php
	# ----------------------------------------------------------------------------------------------------

	/**
	 * <code>
	 *		$PackageItemsObj = new PackageItems($id);
	 * <code>
	 * @copyright Copyright 2018 Arca Solutions, Inc.
	 * @author Arca Solutions, Inc.
	 * @version 8.0.00
	 * @package Classes
	 * @name PackageItems
	 * @access Public
	 */

	class PackageItems extends Handle {

		/**
		 * @var integer
		 * @access Private
		 */
		var $id;
		/**
		 * @var integer
		 * @access Private
		 */
		var $package_id;
		/**
		 * @var integer
		 * @access Private
		 */
		var $domain_id;
		/**
		 * @var string
		 * @access Private
		 */
		var $module;
		/**
		 * @var integer
		 * @access Private
		 */
		var $level;
		/**
		 * @var decimal
		 * @access Private
		 */
		var $price;


		/**
		 * <code>
		 *		$packageItemObj = new PackageItem($id);
		 * <code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
		 * @name Package
		 * @access Public
		 * @param integer $var
		 */
        public function __construct($var="", $pack_id="") {
			if (is_numeric($var) && ($var)) {
				$db = db_getDBObject(DEFAULT_DB, true);
				$sql = "SELECT * FROM PackageItems WHERE id = $var";
				$row = mysqli_fetch_array($db->query($sql));
				$this->makeFromRow($row);
			} else if (is_numeric($pack_id) && ($pack_id)) {
				$db = db_getDBObject(DEFAULT_DB, true);
				$sql = "SELECT * FROM PackageItems WHERE package_id = $pack_id";
				$row = mysqli_fetch_array($db->query($sql));
				$this->makeFromRow($row);
			} else {
                if (!is_array($var)) {
                    $var = array();
                }
				$this->makeFromRow($var);
			}

            /* ModStores Hooks */
            HookFire("classpackageitem_contruct", [
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
		 * @name makeFromRow
		 * @access Public
		 * @param array $row
		 */
		function makeFromRow($row="") {

            /* ModStores Hooks */
            HookFire("classpackageitem_before_makerow", [
                "that" => &$this,
                "row"  => &$row,
            ]);

			$this->id				= ($row["id"])					? $row["id"]				: ($this->id					? $this->id				: 0);
			$this->package_id		= ($row["package_id"])			? $row["package_id"]		: ($this->package_id			? $this->package_id		: 0);
			$this->domain_id		= ($row["domain_id"])			? $row["domain_id"]			: ($this->domain_id				? $this->domain_id		: 0);
			$this->module			= ($row["module"])				? $row["module"]			: ($this->module				? $this->module			: "");
			$this->level			= ($row["level"])				? $row["level"]				: ($this->level					? $this->level			: 0);
			$this->price			= ($row["price"])				? $row["price"]				: ($this->price					? $this->price			: "");

            /* ModStores Hooks */
            HookFire("classpackageitem_after_makerow", [
                "that" => &$this,
                "row"  => &$row,
            ]);

		}



		/**
		 * <code>
		 *		//Using this in forms or other pages.
		 *		$packageItemObj->Save();
		 * <br /><br />
		 *		//Using this in PackageItem() class.
		 *		$this->Save();
		 * </code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
		 * @name Save
		 * @access Public
		 */
		function Save() {

            /* ModStores Hooks */
            HookFire("classpackageitem_before_preparesave", [
                "that" => &$this
            ]);

			$this->prepareToSave();

			$dbMain = db_getDBObject(DEFAULT_DB, true);

			if ($this->id) {

				$sql = "UPDATE PackageItems SET"
					. " package_id		= $this->package_id,"
					. " domain_id	    = $this->domain_id,"
					. " module		    = $this->module,"
					. " level		    = $this->level,"
					. " price		    = $this->price"
					. " WHERE id        = $this->id";

                /* ModStores Hooks */
                HookFire("classpackageitem_before_updatequery", [
                    "that" => &$this
                ]);

				$dbMain->query($sql);

                /* ModStores Hooks */
                HookFire("classpackageitem_after_updatequery", [
                    "that" => &$this
                ]);

			} else {

				$sql = "INSERT INTO PackageItems"
					. " (package_id,"
					. " domain_id,"
					. " module,"
					. " level,"
					. " price)"
					. " VALUES"
					. " ($this->package_id,"
					. " $this->domain_id,"
					. " $this->module,"
					. " $this->level,"
					. " $this->price)";

                /* ModStores Hooks */
                HookFire("classpackageitem_before_insertquery", [
                    "that"   => &$this,
                    "dbMain" => &$dbMain,
                ]);

				$dbMain->query($sql);

                /* ModStores Hooks */
                HookFire("classpackageitem_after_insertquery", [
                    "that"   => &$this,
                    "dbMain" => &$dbMain,
                ]);

				$this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbMain->link_id))) ? false : $___mysqli_res);
			}

            /* ModStores Hooks */
            HookFire("classpackageitem_before_prepareuse", [
                "that" => &$this
            ]);

			$this->prepareToUse();

            /* ModStores Hooks */
            HookFire("classpackageitem_after_save", [
                "that" => &$this
            ]);
		}

		/**
		 * <code>
		 *		//Using this in forms or other pages.
		 *		$packageItemObj->Delete();
		 * <code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
		 * @name Delete
		 * @access Public
		 */
		function Delete() {

			$dbMain = db_getDBObject(DEFAULT_DB, true);

            /* ModStores Hooks */
            HookFire("classpackageitem_before_delete", [
                "that" => &$this
            ]);

			$sql = "DELETE FROM PackageItems WHERE id = $this->id";
			$dbObj->query($sql);

		}

		/**
		 * <code>
		 *		//Using this in forms or other pages.
		 *		$packageItemObj->getItemsByPackageId($package_id);
		 * <code>
		 * @copyright Copyright 2018 Arca Solutions, Inc.
		 * @author Arca Solutions, Inc.
		 * @version 8.0.00
		 * @name getItemsByPackageId
		 * @access Public
		 * @param integer $package_id
		 */
		function getItemsByPackageId($package_id){

			if($package_id){
				/*
				 * Get properties of object
				 */
				unset($aux_fields);
				foreach($this as $key => $value){
					$aux_fields[] = $key;
				}

				$dbMain = db_getDBObject(DEFAULT_DB,true);
				$sql = "SELECT ".implode(", ",$aux_fields)." FROM PackageItems WHERE package_id = ".$package_id;
				$result = $dbMain->query($sql);
				if(mysqli_num_rows($result)){
					unset($array_package_items);
					while($row = mysqli_fetch_assoc($result)){
						if ($row["domain_id"]){
							$domain = new Domain($row["domain_id"]);
							if ($domain->getString("status") == "A")
								$array_package_items[] = $row;
						} else {
							$array_package_items[] = $row;
						}
					}
					return $array_package_items;
				}else{
					return false;
				}
			}else{
				return false;

			}

		}


		function DeleteItemsByPackageID($package_id){

			if(is_numeric($package_id)){
				$dbMain = db_getDBObject(DEFAULT_DB,true);
				$sql = "DELETE FROM PackageItems WHERE package_id =".$package_id;
				$dbMain->query($sql);
				return true;
			}else{
				return false;
			}

		}

	}

?>
