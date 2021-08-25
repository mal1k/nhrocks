<?php

class EventCategory extends Handle
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
    public $enabled;

    public const SYNCHRONIZATION_SERVICE_NAME = 'event.category.synchronization';

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
            $sql = "SELECT * FROM EventCategory WHERE id = $var";
            $row = mysqli_fetch_array($db->query($sql));
            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = [];
            }
            $this->makeFromRow($var);
        }

        /* ModStores Hooks */
        HookFire('classeventcategory_contruct', [
            'that' => &$this
        ]);
    }

    public function makeFromRow($row = '')
    {
        /* ModStores Hooks */
        HookFire('classeventcategory_before_makerow', [
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
        $this->seo_keywords = ($row['seo_keywords']) ? $row['seo_keywords'] : '';
        $this->content = ($row['content']) ? $row['content'] : '';
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
        HookFire('classeventcategory_after_makerow', [
            'that' => &$this,
            'row'  => &$row,
        ]);
    }

    public function Save()
    {
        /* ModStores Hooks */
        HookFire('classeventcategory_before_preparesave', [
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

            $sql = 'UPDATE EventCategory SET'
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
                . " enabled = $this->enabled"
                . " WHERE id = $this->id";

            /* ModStores Hooks */
            HookFire('classeventcategory_before_updatequery', [
                'that' => &$this,
                'sql'  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire('classeventcategory_after_updatequery', [
                'that' => &$this
            ]);

        } else {

            $sql = 'INSERT INTO EventCategory'
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
                .' enabled)'
                .' VALUES'
                ." ($this->title,"
                ." $this->page_title,"
                ." $this->friendly_url,"
                ." $this->category_id,"
                ." $this->image_id,"
                ." $this->icon_id,"
                ." $this->featured,"
                ." $this->summary_description,"
                ." $this->seo_description,"
                ." $this->keywords,"
                ." $this->seo_keywords,"
                ." $this->content,"
                ." $this->enabled)";

            /* ModStores Hooks */
            HookFire('classeventcategory_before_insertquery', [
                'that' => &$this,
                'sql'  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire('classeventcategory_after_insertquery', [
                'that'  => &$this,
                'dbObj' => &$dbObj
            ]);

            $this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);
        }

        /* ModStores Hooks */
        HookFire('classeventcategory_before_prepareuse', [
            'that' => &$this
        ]);

        $this->prepareToUse();

        $this->synchronize();

        /* ModStores Hooks */
        HookFire('classeventcategory_after_save', [
            'that' => &$this
        ]);
    }

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

            $sql = "SELECT * FROM EventCategory WHERE category_id = {$this->id}";
            $r = $dbObj->query($sql);

            while ($row = mysqli_fetch_array($r)) {

                $sql = "SELECT * FROM EventCategory WHERE category_id = {$row['id']}";
                $r2 = $dbObj->query($sql);

                while ($row2 = mysqli_fetch_array($r2)) {

                    $sql = "SELECT * FROM EventCategory WHERE category_id = {$row2['id']}";
                    $r3 = $dbObj->query($sql);

                    while ($row3 = mysqli_fetch_array($r3)) {

                        $sql = "SELECT * FROM EventCategory WHERE category_id = {$row3['id']}";
                        $r4 = $dbObj->query($sql);

                        while ($row4 = mysqli_fetch_array($r4)) {

                            $sql = "UPDATE Event SET cat_1_id = NULL, parcat_1_level1_id = 0, parcat_1_level2_id = 0, parcat_1_level3_id = 0, parcat_1_level4_id = 0 WHERE cat_1_id = {$row4['id']}";
                            $dbObj->query($sql);
                            $sql = "UPDATE Event SET cat_2_id = NULL, parcat_2_level1_id = 0, parcat_2_level2_id = 0, parcat_2_level3_id = 0, parcat_2_level4_id = 0 WHERE cat_2_id = {$row4['id']}";
                            $dbObj->query($sql);
                            $sql = "UPDATE Event SET cat_3_id = NULL, parcat_3_level1_id = 0, parcat_3_level2_id = 0, parcat_3_level3_id = 0, parcat_3_level4_id = 0 WHERE cat_3_id = {$row4['id']}";
                            $dbObj->query($sql);
                            $sql = "UPDATE Event SET cat_4_id = NULL, parcat_4_level1_id = 0, parcat_4_level2_id = 0, parcat_4_level3_id = 0, parcat_4_level4_id = 0 WHERE cat_4_id = {$row4['id']}";
                            $dbObj->query($sql);
                            $sql = "UPDATE Event SET cat_5_id = NULL, parcat_5_level1_id = 0, parcat_5_level2_id = 0, parcat_5_level3_id = 0, parcat_5_level4_id = 0 WHERE cat_5_id = {$row4['id']}";
                            $dbObj->query($sql);
                        }

                        $sql = "UPDATE Event SET cat_1_id = NULL, parcat_1_level1_id = 0, parcat_1_level2_id = 0, parcat_1_level3_id = 0, parcat_1_level4_id = 0 WHERE cat_1_id = {$row3['id']}";
                        $dbObj->query($sql);
                        $sql = "UPDATE Event SET cat_2_id = NULL, parcat_2_level1_id = 0, parcat_2_level2_id = 0, parcat_2_level3_id = 0, parcat_2_level4_id = 0 WHERE cat_2_id = {$row3['id']}";
                        $dbObj->query($sql);
                        $sql = "UPDATE Event SET cat_3_id = NULL, parcat_3_level1_id = 0, parcat_3_level2_id = 0, parcat_3_level3_id = 0, parcat_3_level4_id = 0 WHERE cat_3_id = {$row3['id']}";
                        $dbObj->query($sql);
                        $sql = "UPDATE Event SET cat_4_id = NULL, parcat_4_level1_id = 0, parcat_4_level2_id = 0, parcat_4_level3_id = 0, parcat_4_level4_id = 0 WHERE cat_4_id = {$row3['id']}";
                        $dbObj->query($sql);
                        $sql = "UPDATE Event SET cat_5_id = NULL, parcat_5_level1_id = 0, parcat_5_level2_id = 0, parcat_5_level3_id = 0, parcat_5_level4_id = 0 WHERE cat_5_id = {$row3['id']}";
                        $dbObj->query($sql);
                    }

                    $sql = "UPDATE Event SET cat_1_id = NULL, parcat_1_level1_id = 0, parcat_1_level2_id = 0, parcat_1_level3_id = 0, parcat_1_level4_id = 0 WHERE cat_1_id = {$row2['id']}";
                    $dbObj->query($sql);
                    $sql = "UPDATE Event SET cat_2_id = NULL, parcat_2_level1_id = 0, parcat_2_level2_id = 0, parcat_2_level3_id = 0, parcat_2_level4_id = 0 WHERE cat_2_id = {$row2['id']}";
                    $dbObj->query($sql);
                    $sql = "UPDATE Event SET cat_3_id = NULL, parcat_3_level1_id = 0, parcat_3_level2_id = 0, parcat_3_level3_id = 0, parcat_3_level4_id = 0 WHERE cat_3_id = {$row2['id']}";
                    $dbObj->query($sql);
                    $sql = "UPDATE Event SET cat_4_id = NULL, parcat_4_level1_id = 0, parcat_4_level2_id = 0, parcat_4_level3_id = 0, parcat_4_level4_id = 0 WHERE cat_4_id = {$row2['id']}";
                    $dbObj->query($sql);
                    $sql = "UPDATE Event SET cat_5_id = NULL, parcat_5_level1_id = 0, parcat_5_level2_id = 0, parcat_5_level3_id = 0, parcat_5_level4_id = 0 WHERE cat_5_id = {$row2['id']}";
                    $dbObj->query($sql);
                }

                $sql = "UPDATE Event SET cat_1_id = NULL, parcat_1_level1_id = 0, parcat_1_level2_id = 0, parcat_1_level3_id = 0, parcat_1_level4_id = 0 WHERE cat_1_id = {$row['id']}";
                $dbObj->query($sql);
                $sql = "UPDATE Event SET cat_2_id = NULL, parcat_2_level1_id = 0, parcat_2_level2_id = 0, parcat_2_level3_id = 0, parcat_2_level4_id = 0 WHERE cat_2_id = {$row['id']}";
                $dbObj->query($sql);
                $sql = "UPDATE Event SET cat_3_id = NULL, parcat_3_level1_id = 0, parcat_3_level2_id = 0, parcat_3_level3_id = 0, parcat_3_level4_id = 0 WHERE cat_3_id = {$row['id']}";
                $dbObj->query($sql);
                $sql = "UPDATE Event SET cat_4_id = NULL, parcat_4_level1_id = 0, parcat_4_level2_id = 0, parcat_4_level3_id = 0, parcat_4_level4_id = 0 WHERE cat_4_id = {$row['id']}";
                $dbObj->query($sql);
                $sql = "UPDATE Event SET cat_5_id = NULL, parcat_5_level1_id = 0, parcat_5_level2_id = 0, parcat_5_level3_id = 0, parcat_5_level4_id = 0 WHERE cat_5_id = {$row['id']}";
                $dbObj->query($sql);
            }

            $sql = "UPDATE Event SET cat_1_id = NULL, parcat_1_level1_id = 0, parcat_1_level2_id = 0, parcat_1_level3_id = 0, parcat_1_level4_id = 0 WHERE cat_1_id = $this->id";
            $dbObj->query($sql);
            $sql = "UPDATE Event SET cat_2_id = NULL, parcat_2_level1_id = 0, parcat_2_level2_id = 0, parcat_2_level3_id = 0, parcat_2_level4_id = 0 WHERE cat_2_id = $this->id";
            $dbObj->query($sql);
            $sql = "UPDATE Event SET cat_3_id = NULL, parcat_3_level1_id = 0, parcat_3_level2_id = 0, parcat_3_level3_id = 0, parcat_3_level4_id = 0 WHERE cat_3_id = $this->id";
            $dbObj->query($sql);
            $sql = "UPDATE Event SET cat_4_id = NULL, parcat_4_level1_id = 0, parcat_4_level2_id = 0, parcat_4_level3_id = 0, parcat_4_level4_id = 0 WHERE cat_4_id = $this->id";
            $dbObj->query($sql);
            $sql = "UPDATE Event SET cat_5_id = NULL, parcat_5_level1_id = 0, parcat_5_level2_id = 0, parcat_5_level3_id = 0, parcat_5_level4_id = 0 WHERE cat_5_id = $this->id";
            $dbObj->query($sql);

            /* In here we'll collect all categories which will meet their doom */
            $categoryDump[] = $this->id;

            $sql = "SELECT * FROM EventCategory WHERE category_id = {$this->id}";
            $r = $dbObj->query($sql);

            while ($row = mysqli_fetch_array($r)) {

                $sql = "SELECT * FROM EventCategory WHERE category_id = {$row['id']}";
                $r2 = $dbObj->query($sql);

                while ($row2 = mysqli_fetch_array($r2)) {

                    $sql = "SELECT * FROM EventCategory WHERE category_id = {$row2['id']}";
                    $r3 = $dbObj->query($sql);

                    while ($row3 = mysqli_fetch_array($r3)) {

                        $sql = "SELECT * FROM EventCategory WHERE category_id = {$row3['id']}";
                        $r4 = $dbObj->query($sql);

                        while ($row4 = mysqli_fetch_array($r4)) {
                            $categoryDump[] = $row4['id'];
                            $sql = "DELETE FROM EventCategory WHERE id = {$row4['id']}";
                            $dbObj->query($sql);
                        }

                        $categoryDump[] = $row3['id'];
                        $sql = "DELETE FROM EventCategory WHERE id = {$row3['id']}";
                        $dbObj->query($sql);
                    }

                    $categoryDump[] = $row2['id'];
                    $sql = "DELETE FROM EventCategory WHERE id = {$row2['id']}";
                    $dbObj->query($sql);
                }

                $categoryDump[] = $row['id'];
                $sql = "DELETE FROM EventCategory WHERE id = {$row['id']}";
                $dbObj->query($sql);
            }

            /* ModStores Hooks */
            HookFire('classeventcategory_before_delete', [
                'that' => &$this
            ]);

            $categoryDump[] = $this->id;
            $sql = "DELETE FROM EventCategory WHERE id = $this->id LIMIT 1";
            $dbObj->query($sql);

            $sql = "UPDATE Banner SET category_id = 0 WHERE category_id = $this->id AND section = 'event'";
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
                $symfonyContainer->get(self::SYNCHRONIZATION_SERVICE_NAME)->addDelete($categoryDump);
            }
        }

    }

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
            $sql = "SELECT $fields FROM EventCategory WHERE id = $category_id";
            $result = $dbObj->query($sql);
            $row = mysqli_fetch_assoc($result);
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

    public function updateFullTextItems()
    {

        if ($this->id) {

            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if (defined('SELECTED_DOMAIN_ID')) {
                $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $dbObj = db_getDBObject();
            }

            unset($dbMain);

            $category_id = $this->id;

            $sql = "SELECT id
                    FROM Event
                    WHERE
                        (
                            cat_1_id = {$category_id}
                            OR parcat_1_level1_id = {$category_id}
                            OR parcat_1_level2_id = {$category_id}
                            OR parcat_1_level3_id = {$category_id}
                            OR parcat_1_level4_id = {$category_id}
                            OR cat_2_id = {$category_id}
                            OR parcat_2_level1_id = {$category_id}
                            OR parcat_2_level2_id = {$category_id}
                            OR parcat_2_level3_id = {$category_id}
                            OR parcat_2_level4_id = {$category_id}
                            OR cat_3_id = {$category_id}
                            OR parcat_3_level1_id = {$category_id}
                            OR parcat_3_level2_id = {$category_id}
                            OR parcat_3_level3_id = {$category_id}
                            OR parcat_3_level4_id = {$category_id}
                            OR cat_4_id = {$category_id}
                            OR parcat_4_level1_id = {$category_id}
                            OR parcat_4_level2_id = {$category_id}
                            OR parcat_4_level3_id = {$category_id}
                            OR parcat_4_level4_id = {$category_id}
                            OR cat_5_id = {$category_id}
                            OR parcat_5_level1_id = {$category_id}
                            OR parcat_5_level2_id = {$category_id}
                            OR parcat_5_level3_id = {$category_id}
                            OR parcat_5_level4_id = {$category_id}
                        )";

            $result = $dbObj->query($sql);

            while ($row = mysqli_fetch_array($result)) {
                if ($row['id']) {
                    $eventObj = new Event($row['id']);
                    $eventObj->setFullTextSearch();
                    unset($eventObj);
                }
            }

            return true;
        }

        return false;
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
