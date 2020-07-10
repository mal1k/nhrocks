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
# * FILE: /classes/class_Listing_Category.php
# ----------------------------------------------------------------------------------------------------

/**
 * Class Listing_Category
 */
class Listing_Category extends Handle
{

    /**
     * @var int
     */
    protected $listing_id = 0;

    /**
     * @var int
     */
    protected $category_id = 0;

    public function Save()
    {
        /* ModStores Hooks */
        HookFire("classlistingxcategory_before_preparesave", [
            "that" => &$this
        ]);

        $this->prepareToSave();

        $dbMain = db_getDBObject(DEFAULT_DB, true);

        if (defined("SELECTED_DOMAIN_ID")) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        $sql = "INSERT INTO Listing_Category"
            ." (listing_id, category_id)"
            ." VALUES"
            ." ($this->listing_id, $this->category_id)";

        /* ModStores Hooks */
        HookFire("classlistingxcategory_before_insertquery", [
            "that" => &$this,
            "sql"  => &$sql,
        ]);

        $dbObj->query($sql);

        /* ModStores Hooks */
        HookFire("classlistingxcategory_after_insertquery", [
            "that"  => &$this,
            "dbObj" => &$dbObj,
        ]);

        $this->prepareToUse();

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get("listing.synchronization")->addUpsert($this->listing_id);
        }

        /* ModStores Hooks */
        HookFire("classlistingxcategory_after_save", [
            "that" => &$this
        ]);
    }

    function Delete()
    {
        /**
         * Deleting this object
         **/
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined("SELECTED_DOMAIN_ID")) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);


        /* ModStores Hooks */
        HookFire("classlistingxcategory_before_delete", [
            "that" => &$this
        ]);

        $sql = "DELETE FROM Listing_Category WHERE listing_id = $this->listing_id AND category_id = $this->category_id";
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get("listing.synchronization")->addUpsert($this->listing_id);
        }
    }
}
