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
# * FILE: /classes/class_Post.php
# ----------------------------------------------------------------------------------------------------

/**
 * <code>
 *        $postObj = new Post($id);
 * <code>
 * @copyright Copyright 2018 Arca Solutions, Inc.
 * @author Arca Solutions, Inc.
 * @version 9.5.00
 * @package Classes
 * @name Post
 * @access Public
 */
class Post extends Handle
{

    var $id;
    var $image_id;
    var $cover_id;
    var $updated;
    var $entered;
    var $title;
    var $seo_title;
    var $friendly_url;
    var $image_caption;
    var $alt_caption;
    var $content;
    var $keywords;
    var $seo_keywords;
    var $seo_abstract;
    var $status;
    var $number_views;
    var $data_in_array;

    /**
     * <code>
     *        $postObj = new Post($id);
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name Post
     * @access Public
     * @param mixed $var
     */
    public function __construct($var = "", $domain_id = false)
    {
        if (is_numeric($var) && ($var)) {
            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if ($domain_id) {
                $db = db_getDBObjectByDomainID($domain_id, $dbMain);
            } else {
                if (defined("SELECTED_DOMAIN_ID")) {
                    $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                } else {
                    $db = db_getDBObject();
                }
            }

            unset($dbMain);
            $sql = "SELECT * FROM Post WHERE id = $var";
            $row = mysqli_fetch_array($db->query($sql));
            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = array();
            }
            $this->makeFromRow($var);
        }
    }

    /**
     * <code>
     *        $this->makeFromRow($row);
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name makeFromRow
     * @access Public
     * @param array $row
     */
    function makeFromRow($row = "")
    {

        $this->id = ($row["id"]) ? $row["id"] : ($this->id ? $this->id : 0);
        $this->image_id = ($row["image_id"]) ? $row["image_id"] : ($this->image_id ? $this->image_id : "NULL");
        $this->cover_id = ($row["cover_id"]) ? $row["cover_id"] : ($this->cover_id ? $this->cover_id : "NULL");
        $this->updated = ($row["updated"]) ? $row["updated"] : ($this->updated ? $this->updated : "");
        $this->entered = ($row["entered"]) ? $row["entered"] : ($this->entered ? $this->entered : "");
        $this->title = ($row["title"]) ? $row["title"] : ($this->title ? $this->title : "");
        $this->seo_title = ($row["seo_title"]) ? $row["seo_title"] : ($this->seo_title ? $this->seo_title : "");
        $this->friendly_url = ($row["friendly_url"]) ? $row["friendly_url"] : "";
        $this->image_caption = ($row["image_caption"]) ? $row["image_caption"] : ($this->image_caption ? $this->image_caption : "");
        $this->alt_caption = ($row["alt_caption"]) ? $row["alt_caption"] : ($this->alt_caption ? $this->alt_caption : "");
        $this->content = ($row["content"]) ? $row["content"] : "";
        $this->keywords = ($row["keywords"]) ? $row["keywords"] : "";
        $this->seo_keywords = ($row["seo_keywords"]) ? $row["seo_keywords"] : ($this->seo_keywords ? $this->seo_keywords : "");
        $this->seo_abstract = ($row["seo_abstract"]) ? $row["seo_abstract"] : ($this->seo_abstract ? $this->seo_abstract : "");
        $this->status = ($row["status"]) ? $row["status"] : "A";
        $this->number_views = ($row["number_views"]) ? $row["number_views"] : ($this->number_views ? $this->number_views : 0);
        $this->data_in_array = $row;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $postObj->Save();
     * <br /><br />
     *        //Using this in Post() class.
     *        $this->Save();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name Save
     * @access Public
     */
    function Save()
    {
        $this->prepareToSave();

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined("SELECTED_DOMAIN_ID")) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        $this->friendly_url = string_strtolower($this->friendly_url);

        /*
         * TODO
         * Review calls of method save when adding/editing an item
         * Right now it's been called several times messing up some attributes values
         */
        if ($this->image_id == "''") {
            $this->image_id = "NULL";
        }
        if ($this->cover_id == "''") {
            $this->cover_id = "NULL";
        }

        if ($this->id) {

            $sql = "UPDATE Post SET"
                . " image_id      = $this->image_id,"
                . " cover_id      = $this->cover_id,"
                . " updated       = NOW(),"
                . " title         = $this->title,"
                . " seo_title     = $this->seo_title,"
                . " friendly_url  = $this->friendly_url,"
                . " image_caption = $this->image_caption,"
                . " alt_caption   = $this->alt_caption,"
                . " content       = $this->content,"
                . " keywords      = $this->keywords,"
                . " seo_keywords  = $this->seo_keywords,"
                . " seo_abstract  = $this->seo_abstract,"
                . " status        = $this->status,"
                . " number_views  = $this->number_views"
                . " WHERE id      = $this->id";

            $dbObj->query($sql);

        } else {

            $sql = "INSERT INTO Post"
                . " (image_id,"
                . " cover_id,"
                . " updated,"
                . " entered,"
                . " title,"
                . " seo_title,"
                . " friendly_url,"
                . " image_caption,"
                . " alt_caption,"
                . " content,"
                . " keywords,"
                . " seo_keywords,"
                . " seo_abstract,"
                . " fulltextsearch_keyword,"
                . " status,"
                . " number_views)"
                . " VALUES"
                . " ($this->image_id,"
                . " $this->cover_id,"
                . " NOW(),"
                . " NOW(),"
                . " $this->title,"
                . " $this->seo_title,"
                . " $this->friendly_url,"
                . " $this->image_caption,"
                . " $this->alt_caption,"
                . " $this->content,"
                . " $this->keywords,"
                . " $this->seo_keywords,"
                . " $this->seo_abstract,"
                . " '',"
                . " $this->status,"
                . " $this->number_views)";

            $dbObj->query($sql);
            $this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);
        }

        $this->prepareToUse();
        $this->setFullTextSearch();
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $postObj->Delete();
     * <br /><br />
     *        //Using this in Post() class.
     *        $this->Delete();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name Delete
     * @access Public
     */
    function Delete($domain_id = SELECTED_DOMAIN_ID)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined("SELECTED_DOMAIN_ID")) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        ### COMMENTS
        $sql = "SELECT id FROM Comments WHERE post_id = $this->id";
        $result = $dbObj->query($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $commentObj = new Comments($row["id"]);
            $commentObj->Delete();
        }

        ### BLOG_CATEOGRY
        $sql = "DELETE FROM Blog_Category WHERE post_id = $this->id";
        $dbObj->query($sql);

        //before deleting the image, it needs to clear image ids
        $sql = "UPDATE Post SET image_id = NULL, cover_id = NULL WHERE id = $this->id";
        $dbObj->query($sql);

        ### IMAGE
        if ($this->image_id) {
            $image = new Image($this->image_id);
            if ($image) {
                $image->Delete();
            }
        }
        if (is_numeric($this->cover_id)) {
            $image = new Image($this->cover_id);
            if ($image) {
                $image->Delete();
            }
        }

        ### POST
        $sql = "DELETE FROM Post WHERE id = $this->id";
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get("blog.synchronization")->addDelete($this->id);
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $postObj->setFullTextSearch();
     * <br /><br />
     *        //Using this in Post() class.
     *        $this->setFullTextSearch();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name setFullTextSearch
     * @access Public
     */
    function setFullTextSearch()
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined("SELECTED_DOMAIN_ID")) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        if ($this->title) {
            $fulltextsearch_keyword[] = $this->title;
            $addkeyword = format_addApostWords($this->title);
            if ($addkeyword) {
                $fulltextsearch_keyword[] = $addkeyword;
            }
            unset($addkeyword);
        }

        if ($this->keywords) {
            $string = str_replace(" || ", " ", $this->keywords);
            $fulltextsearch_keyword[] = $string;
            $addkeyword = format_addApostWords($string);
            if ($addkeyword != '') {
                $fulltextsearch_keyword[] = $addkeyword;
            }
            unset($addkeyword);
        }

        $categories = $this->getCategories();
        if ($categories) {
            foreach ($categories as $category) {
                unset($parents);
                $category_id = $category->getNumber("id");
                while (!is_null($category_id) && $category_id != 0) {
                    $sql = "SELECT * FROM BlogCategory WHERE id = $category_id";
                    $result = $dbObj->query($sql);
                    if (mysqli_num_rows($result) > 0) {
                        $category_info = mysqli_fetch_assoc($result);
                        if ($category_info["enabled"] == "y") {
                            if ($category_info["title"]) {
                                $fulltextsearch_keyword[] = $category_info["title"];
                            }

                            if ($category_info["keywords"]) {
                                $fulltextsearch_keyword[] = str_replace(array("\r\n", "\n"), " ",
                                    $category_info["keywords"]);
                            }
                        }
                        $category_id = $category_info["category_id"];
                    } else {
                        $category_id = 'NULL';
                    }
                }
            }
        }

        if (is_array($fulltextsearch_keyword)) {
            $fulltextsearch_keyword_sql = db_formatString(implode(" ", $fulltextsearch_keyword));
            $sql = "UPDATE Post SET fulltextsearch_keyword = $fulltextsearch_keyword_sql WHERE id = $this->id";
            $dbObj->query($sql);
        }

        $this->synchronize();
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $postObj->setNumberViews($id);
     * <br /><br />
     *        //Using this in Post() class.
     *        $this->setNumberViews($id);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name setNumberViews
     * @access Public
     * @param integer $id
     */
    function setNumberViews($id)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined("SELECTED_DOMAIN_ID")) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);
        $sql = "UPDATE Post SET number_views = " . $this->number_views . " + 1 WHERE Post.id = " . $id;
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get("blog.synchronization")->addViewUpdate($id);
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $postObj->getCategories(...);
     * <br /><br />
     *        //Using this in Post() class.
     *        $this->getCategories(...);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getCategories
     * @access Public
     * @return array $categories
     */
    function getCategories() {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined("SELECTED_DOMAIN_ID")) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        $sql_main = "SELECT post_category.category_id
                                FROM Blog_Category post_category
                                INNER JOIN BlogCategory category ON category.id = post_category.category_id
                                WHERE post_category.post_id = " . $this->id;

        $result_main = $dbObj->query($sql_main, MYSQLI_USE_RESULT);

        if ($result_main) {

            $aux_array_categories = array();
            while ($row = mysqli_fetch_assoc($result_main)) {
                $aux_array_categories[] = $row["category_id"];
            }
            mysqli_free_result($result_main);

            if (count($aux_array_categories) > 0) {
                $sql = "SELECT	id,
                                    title,
                                    page_title,
                                    friendly_url,
                                    enabled,
                                    category_id
                                FROM BlogCategory
                                WHERE id IN (" . implode(",", $aux_array_categories) . ")";

                $result = $dbObj->query($sql);

                if ($result) {
                    $categories = array();
                    while ($row = mysqli_fetch_assoc($result)) {
                        $categories[] = new BlogCategory($row);
                    }
                }
            }
        }

        if (count($categories) > 0) {
            return $categories;
        } else {
            return false;
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $postObj->setCategories($categories);
     * <br /><br />
     *        //Using this in Post() class.
     *        $this->setCategories($categories);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name setCategories
     * @access Public
     * @param array $array
     */
    function setCategories($array)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined("SELECTED_DOMAIN_ID")) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        if ($this->id) {

            $sql = "DELETE FROM Blog_Category WHERE post_id = " . $this->id;
            $dbObj->query($sql);

            if ($array) {
                foreach ($array as $category) {
                    if ($category) {
                        unset($b_catObj);
                        $b_catObj = new Blog_Category();
                        $b_catObj->setNumber("post_id", $this->id);
                        $b_catObj->setNumber("category_id", $category);
                        $b_catObj->Save();
                    }
                }
            }

            $this->setFullTextSearch();
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $postObj->getPostByFriendlyURL($friendly_url);
     * <br /><br />
     *        //Using this in Post() class.
     *        $this->getPostByFriendlyURL($friendly_url);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getPostByFriendlyURL
     * @param string $friendly_url
     * @access Public
     */
    function getPostByFriendlyURL($friendly_url)
    {
        $dbObj = db_getDBObject();
        $sql = "SELECT * FROM Post WHERE friendly_url = '" . $friendly_url . "'";
        $result = $dbObj->query($sql);
        if (mysqli_num_rows($result)) {
            $this->makeFromRow(mysqli_fetch_assoc($result));

            return true;
        } else {
            return false;
        }
    }

    /**
     * Synchronizes this instance in elasticsearch
     */
    public function synchronize()
    {
        if ($symfonyContainer = SymfonyCore::getContainer()) {
            if($this->status == 'A'){
                $symfonyContainer->get("blog.synchronization")->addUpsert($this->id);
            } else {
                $symfonyContainer->get("blog.synchronization")->addDelete($this->id);
            }
        }
    }
}
