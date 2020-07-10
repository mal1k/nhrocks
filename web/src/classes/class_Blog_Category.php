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
# * FILE: /classes/class_Blog_Category.php
# ----------------------------------------------------------------------------------------------------

class Blog_Category extends Handle
{
    var $id;
    var $post_id;
    var $category_id;

    /*
     * Dont save this field
     */
    var $total_posts;

    public function __construct($var = '')
    {
        if (is_numeric($var) && ($var)) {
            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if (defined("SELECTED_DOMAIN_ID")) {
                $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $db = db_getDBObject();
            }
            unset($dbMain);
            $sql = "SELECT * FROM Blog_Category WHERE id = $var";
            $row = mysqli_fetch_array($db->query($sql));
            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = array();
            }
            $this->makeFromRow($var);
        }

        /* ModStores Hooks */
        HookFire("classblogxcategory_contruct", [
            "that" => &$this
        ]);
    }

    function makeFromRow($row = '')
    {
        /* ModStores Hooks */
        HookFire("classblogxcategory_before_makerow", [
            "that" => &$this,
            "row"  => &$row,
        ]);

        if ($row['id']) {
            $this->id = $row['id'];
        } else {
            if (!$this->id) {
                $this->id = 0;
            }
        }
        if ($row['post_id']) {
            $this->post_id = $row['post_id'];
        } else {
            if (!$this->post_id) {
                $this->post_id = 0;
            }
        }
        if ($row['category_id']) {
            $this->category_id = $row['category_id'];
        } else {
            if (!$this->category_id) {
                $this->category_id = 0;
            }
        }

        /* ModStores Hooks */
        HookFire("classblogxcategory_after_makerow", [
            "that" => &$this,
            "row"  => &$row,
        ]);
    }

    function Save()
    {
        /* ModStores Hooks */
        HookFire("classblogxcategory_before_preparesave", [
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

        if ($this->id) {
            $sql = "UPDATE Blog_Category SET"
                . " post_id = $this->post_id,"
                . " category_id = $this->category_id"
                . " WHERE id = $this->id";

            /* ModStores Hooks */
            HookFire("classblogxcategory_before_updatequery", [
                "that" => &$this,
                "sql"  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire("classblogxcategory_after_updatequery", [
                "that" => &$this
            ]);
        } else {
            $sql = "INSERT INTO Blog_Category"
                . " (post_id, category_id)"
                . " VALUES"
                . " ($this->post_id, $this->category_id)";

            /* ModStores Hooks */
            HookFire("classblogxcategory_before_insertquery", [
                "that" => &$this,
                "sql"  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire("classblogxcategory_after_insertquery", [
                "that"  => &$this,
                "dbObj" => &$dbObj,
            ]);

            $this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);
        }

        /* ModStores Hooks */
        HookFire("classblogxcategory_before_prepareuse", [
            "that" => &$this
        ]);

        $this->prepareToUse();

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get("blog.synchronization")->addUpsert($this->post_id);
        }

        /* ModStores Hooks */
        HookFire("classblogxcategory_after_save", [
            "that" => &$this
        ]);
    }

    function Delete()
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);

        if (defined("SELECTED_DOMAIN_ID")) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        /* ModStores Hooks */
        HookFire("classblogxcategory_before_delete", [
            "that" => &$this
        ]);

        $sql = "DELETE FROM Blog_Category WHERE id = $this->id";
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get("blog.synchronization")->addUpsert($this->post_id);
        }
    }

}
