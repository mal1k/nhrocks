<?php

class BlogCategory extends Handle
{
    public $id;
    public $title;
    public $page_title;
    public $friendly_url;
    public $category_id;
    public $image_id;
    public $icon_id;
    public $featured;
    public $summary_description;
    public $seo_description;
    public $keywords;
    public $seo_keywords;
    public $content;
    public $level;
    public $enabled;
    const SYNCHRONIZATION_SERVICE_NAME = 'blog.category.synchronization';

    /**
     * <code>
     *        $categObj = new BlogCategory($id);
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @param integer $var
     */
    public function __construct($var = '')
    {
        if (is_numeric($var) && ($var)) {
            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if (defined('SELECTED_DOMAIN_ID')) {
                $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $db = db_getDBObject();
            }
            unset($dbMain);
            $sql = "SELECT * FROM BlogCategory WHERE id = $var";
            $result = $db->query($sql, MYSQLI_USE_RESULT);
            $row = mysqli_fetch_array($result);
            mysqli_free_result($result);
            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = [];
            }
            $this->makeFromRow($var);
        }

        /* ModStores Hooks */
        HookFire('classblogcategory_contruct', [
            'that' => &$this
        ]);
    }

    /**
     * <code>
     *        $this->makeFromRow($row);
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @param array $row
     */
    public function makeFromRow($row = '')
    {
        /* ModStores Hooks */
        HookFire('classblogcategory_before_makerow', [
            'that' => &$this,
            'row'  => &$row,
        ]);

        $this->id = ($row['id']) ? $row['id'] : ($this->id ? $this->id : 0);
        $this->title = ($row['title']) ? $row['title'] : ($this->title ? $this->title : '');
        $this->page_title = ($row['page_title']) ? $row['page_title'] : ($this->page_title ? $this->page_title : '');
        $this->friendly_url = ($row['friendly_url']) ? $row['friendly_url'] : ($this->friendly_url ? $this->friendly_url : '');
        $this->category_id = ($row['category_id']) ? $row['category_id'] : ($this->category_id ? $this->category_id : 'NULL');
        $this->featured = ($row['featured']) ? $row['featured'] : ($this->featured ? $this->featured : 'n');
        $this->summary_description = ($row['summary_description']) ? $row['summary_description'] : '';
        $this->seo_description = ($row['seo_description']) ? $row['seo_description'] : '';
        $this->keywords = ($row['keywords']) ? $row['keywords'] : ($this->keywords ? $this->keywords : '');
        $this->seo_keywords = ($row['seo_keywords']) ? $row['seo_keywords'] : ($this->seo_keywords ? $this->seo_keywords : '');
        $this->content = ($row['content']) ? $row['content'] : '';
        $this->level = ($row['level']) ? $row['level'] : 0;
        $this->enabled = ($row['enabled']) ? $row['enabled'] : ($this->enabled ? $this->enabled : 'n');

        if ($row['image_id']) {
            $this->image_id = $row['image_id'];
        } else {
            if (!$this->image_id) {
                $this->image_id = 'NULL';
            }
        }

        if ($row['icon_id']) {
            $this->icon_id = $row['icon_id'];
        } else {
            if (!$this->icon_id) {
                $this->icon_id = 'NULL';
            }
        }

        /* ModStores Hooks */
        HookFire('classblogcategory_after_makerow', [
            'that' => &$this,
            'row'  => &$row,
        ]);
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $categObj->Save();
     * <br /><br />
     *        //Using this in BlogCategory() class.
     *        $this->Save();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     */
    public function Save()
    {
        /* ModStores Hooks */
        HookFire('classblogcategory_before_preparesave', [
            'that' => &$this
        ]);

        $this->prepareToSave();

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $this->friendly_url = string_strtolower($this->friendly_url);

        if ($this->id) {

            $sql = 'UPDATE BlogCategory SET'
                . " title = $this->title,"
                . " page_title = $this->page_title,"
                . " friendly_url = $this->friendly_url,"
                . " category_id = $this->category_id,"
                . " image_id = $this->image_id,"
                . " icon_id = $this->icon_id,"
                . " featured = $this->featured,"
                . " summary_description = $this->summary_description,"
                . " seo_description = $this->seo_description,"
                . " keywords = $this->keywords,"
                . " seo_keywords = $this->seo_keywords,"
                . " content = $this->content,"
                . " enabled = $this->enabled,"
                . " level = $this->level"
                . " WHERE id = $this->id";

            /* ModStores Hooks */
            HookFire('classblogcategory_before_updatequery', [
                'that' => &$this,
                'sql'  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire('classblogcategory_after_updatequery', [
                'that' => &$this
            ]);

        } else {

            $sql = 'INSERT INTO BlogCategory'
                .' (title,'
                .' page_title,'
                .' friendly_url,'
                .' category_id,'
                .' image_id,'
                .' icon_id,'
                .' featured,'
                .' summary_description,'
                .' seo_description,'
                .' keywords,'
                .' seo_keywords,'
                .' content,'
                .' enabled,'
                .' level)'
                .' VALUES'
                . " ($this->title,"
                . " $this->page_title,"
                . " $this->friendly_url,"
                . " $this->category_id,"
                . " $this->image_id,"
                . " $this->icon_id,"
                . " $this->featured,"
                . " $this->summary_description,"
                . " $this->seo_description,"
                . " $this->keywords,"
                . " $this->seo_keywords,"
                . " $this->content,"
                . " $this->enabled,"
                . " $this->level)";

            /* ModStores Hooks */
            HookFire('classblogcategory_before_insertquery', [
                'that' => &$this,
                'sql'  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire('classblogcategory_after_insertquery', [
                'that'  => &$this,
                'dbObj' => &$dbObj,
            ]);


            $this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);

        }

        /* ModStores Hooks */
        HookFire('classblogcategory_before_prepareuse', [
            'that' => &$this
        ]);

        $this->prepareToUse();

        $this->synchronize();

        /* ModStores Hooks */
        HookFire('classblogcategory_after_save', [
            'that' => &$this
        ]);
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $categObj->findRootCategoryId($category_id);
     * <br /><br />
     *        //Using this in BlogCategory() class.
     *        $this->findRootCategoryId($category_id);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name findRootCategoryId
     * @param integer $category_id
     * @return integer
     * @access Public
     */
    public function findRootCategoryId($category_id)
    {
        $parentIds = $this->getParentIds($category_id);
        $count = count($parentIds);

        return $count > 0? $parentIds[($count-1)] : $category_id;
    }


    /**
     * @param integer $categoryId
     * @return array
     */
    public function getParentIds($categoryId)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $parentIds = [];

        while ($categoryId && $categoryId > 0) {
            $sql = "SELECT category_id, id FROM BlogCategory WHERE id = $categoryId";
            $result = $dbObj->query($sql);
            $row = mysqli_fetch_assoc($result);
            $categoryId = $row['category_id'];
            $parentIds[] = $row['id'];
        }

        return $parentIds;
    }

    /**
     * @param $categoryId
     * @return array
     */
    public function getChildrenIds($categoryId)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        /*
         * Remove "'" if need
         */
        $categoryId = str_replace("'", '', $categoryId);
        $childrenIds = [];

        $sql = "SELECT id FROM BlogCategory WHERE category_id = $categoryId";
        $result = $dbObj->query($sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $childrenIds[] = $row['id'];
                $childrenIds = array_merge($childrenIds, $this->getChildrenIds($row['id']));
            }
        }

        return $childrenIds;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $categObj->getHierarchy($id, $get_parents, $get_children);
     * <br /><br />
     *        //Using this in BlogCategory() class.
     *        $this->getHierarchy($id, $get_parents, $get_children);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name getHierarchy
     * @param integer $id
     * @param boolean $get_parents
     * @param boolean $get_children
     * @return string
     * @access Public
     */
    public function getHierarchy($id, $get_parents = false, $get_children = false)
    {
        unset($dbObj, $string_hierarchy);
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $sql = 'SELECT postcategory.id,
						   postcategory.category_id
						FROM BlogCategory postcategory
						WHERE postcategory.id = '. $id;

        $result = $dbObj->query($sql);

        if (mysqli_num_rows($result) > 0) {
            $aux_array = mysqli_fetch_assoc($result);

            //To keep the old rules
            if (!$get_parents && !$get_children) {
                if ($aux_array['category_id'] == 0) {
                    $get_parents = false;
                    $get_children = true;
                } else {
                    $get_parents = true;
                    $get_children = false;
                }
            }

            $array_hierarchy = null;
            if ($get_children) {
                // Get children
                $array_hierarchy = $this->getChildrenIds($id);
            } else {
                if ($get_parents) {
                    // Get Parents
                    $array_hierarchy = $this->getParentIds($id);
                }
            }

            if (is_array($array_hierarchy) && count($array_hierarchy) > 0) {
                $string_hierarchy = implode(',', $array_hierarchy);
            }

            if (string_strlen($string_hierarchy) > 0) {
                $string_hierarchy .= ',' . $id;
            } else {
                $string_hierarchy = $id;
            }

            return $string_hierarchy;
        } else {
            return false;
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $categObj->Delete();
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name Delete
     * @access Public
     */
    public function Delete()
    {
        if ($this->id != 0) {

            foreach ($this->getFullPath() as $cat_path) {
                $cat_id[] = $cat_path['id'];
            }

            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if (defined('SELECTED_DOMAIN_ID')) {
                $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $dbObj = db_getDBObject();
            }

            unset($dbMain);

            $category_ids = $this->getHierarchy($this->id, $get_parents = false, $get_children = true);

            if ($category_ids) {
                $sql = "SELECT post_id FROM Blog_Category WHERE category_id IN ($category_ids)";
                $posts_ids = [];
                $result = $dbObj->query($sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    $posts_ids[] = $row['post_id'];
                }

                $sql_delete = "DELETE FROM Blog_Category WHERE category_id IN ($category_ids)";
                $dbObj->query($sql_delete);

                /* ModStores Hooks */
                HookFire('classblogcategory_before_delete', [
                    'that' => &$this
                ]);

                $sql_delete = "DELETE FROM BlogCategory WHERE id IN ($category_ids)";
                $dbObj->query($sql_delete);
            }
            $sql = "UPDATE Banner SET category_id = NULL WHERE category_id = $this->id AND section = 'blog'";
            $dbObj->query($sql);

            $this->updateFullTextItems();

            ### IMAGE
            if ($this->image_id) {
                $image = new Image($this->image_id);
                if ($image) {
                    $image->Delete();
                }
            }

            if ($this->icon_id) {
                $image = new Image($this->icon_id);
                if ($image) {
                    $image->Delete();
                }
            }

            if ($symfonyContainer = SymfonyCore::getContainer()) {
                $symfonyContainer->get(self::SYNCHRONIZATION_SERVICE_NAME)->addDelete($category_ids);
            }
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $categObj->getFullPath();
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name getFullPath
     * @access Public
     */
    public function getFullPath()
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $fields = '`id`, `category_id`, `featured`, `enabled`, `friendly_url`, `title`';

        $category_id = $this->id;
        $i = 0;
        while ($category_id != 0) {
            $sql = "SELECT $fields FROM BlogCategory WHERE id = $category_id";

            $result = $dbObj->query($sql, MYSQLI_USE_RESULT);
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            $path[$i]['id'] = $row['id'];
            $path[$i]['dad'] = $row['category_id'];
            $path[$i]['title'] = $row['title'];
            $path[$i]['friendly_url'] = $row['friendly_url'];
            $path[$i]['featured'] = $row['featured'];
            $path[$i]['enabled'] = $row['enabled'];
            $i++;
            $category_id = $row['category_id'];
        }
        if ($path) {
            $path = array_reverse($path);
            for ($i = 0; $i < count($path); $i++) {
                $path[$i]['level'] = $i + 1;
            }

            return ($path);
        } else {
            return false;
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $categObj->updateFullTextItems();
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 9.5.00
     * @name updateFullTextItems
     * @access Public
     */
    public function updateFullTextItems($posts_ids = false)
    {
        if (!$posts_ids) {

            if ($this->id) {
                $category_ids = $this->getHierarchy($this->id, $get_parents = true, $get_children = false);
                $category_ids .= (string_strlen($category_ids) ? ',' : '');
                $category_ids .= $this->getHierarchy($this->id, $get_parents = false, $get_children = true);

                if ($category_ids) {
                    $dbMain = db_getDBObject(DEFAULT_DB, true);
                    if (defined('SELECTED_DOMAIN_ID')) {
                        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                    } else {
                        $dbObj = db_getDBObject();
                    }
                    unset($dbMain);

                    $sql = "SELECT post_id FROM Blog_Category WHERE category_id IN ($category_ids)";
                    $result = $dbObj->query($sql);

                    while ($row = mysqli_fetch_array($result)) {
                        if ($row['post_id']) {
                            $postObj = new Post($row['post_id']);
                            $postObj->setFullTextSearch();
                            unset($postObj);
                        }
                    }
                }

                return true;
            }

            return false;
        } else {
            foreach ($posts_ids as $post_id) {
                if ($post_id) {
                    $postObj = new Post($post_id);
                    $postObj->setFullTextSearch();
                    unset($postObj);
                }
            }

            return true;
        }
    }

    public function synchronize()
    {
        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $synchronizable = $symfonyContainer->get(self::SYNCHRONIZATION_SERVICE_NAME);

            $synchronizable->addUpsert($this->id);
            $this->category_id and $synchronizable->addUpsert($this->category_id);
        }
    }
}
