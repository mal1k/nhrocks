<?php

/**
 * Class Event
 */
class Event extends Handle
{
    public $id;
    public $account_id;
    public $title;
    public $seo_title;
    public $friendly_url;
    public $image_id;
    public $cover_id;
    public $description;
    public $seo_description;
    public $long_description;
    public $video_snippet;
    public $video_url;
    public $keywords;
    public $seo_keywords;
    public $updated;
    public $entered;
    public $start_date;
    public $start_time;
    public $end_date;
    public $end_time;
    public $location;
    public $address;
    public $zip_code;
    public $location_1;
    public $location_2;
    public $location_3;
    public $location_4;
    public $location_5;
    public $url;
    public $contact_name;
    public $phone;
    public $email;
    public $renewal_date;
    public $discount_id;
    public $facebook_page;
    public $status;
    public $level;
    public $recurring;
    public $day;
    public $dayofweek;
    public $week;
    public $month;
    public $until_date;
    public $repeat_event;
    public $number_views;
    public $latitude;
    public $longitude;
    public $map_zoom;
    public $locationManager;
    public $data_in_array;
    public $domain_id;
    public $package_id;
    public $package_price;

    /**
     * <code>
     *        $eventObj = new Event($id);
     * <code>
     * @param string $var
     * @param bool $domain_id
     * @version 8.0.00
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     */
    public function __construct($var = '', $domain_id = false)
    {

        if (is_numeric($var) && $var) {
            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if ($domain_id) {
                $this->domain_id = $domain_id;
                $db = db_getDBObjectByDomainID($domain_id, $dbMain);
            } else if (defined('SELECTED_DOMAIN_ID')) {
                $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $db = db_getDBObject();
            }
            unset($dbMain);
            $sql = "SELECT * FROM Event WHERE id = $var";
            $row = mysqli_fetch_array($db->query($sql));

            $this->old_account_id = $row['account_id'];

            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = array();
            }
            $this->makeFromRow($var);
        }

        /* ModStores Hooks */
        HookFire('classevent_contruct', [
            'that' => &$this
        ]);
    }

    /**
     * <code>
     *        $this->makeFromRow($row);
     * <code>
     * @param string $row
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @copyright Copyright 2018 Arca Solutions, Inc.
     */
    public function makeFromRow($row = '')
    {
        /* ModStores Hooks */
        HookFire('classevent_before_makerow', [
            'that' => &$this,
            'row'  => &$row,
        ]);

        $statusObj = new ItemStatus();
        $level = new EventLevel();

        $this->id = $row['id'] ?: ($this->id ?: 0);
        $this->account_id = $row['account_id'] ?: 0;
        $this->title = $row['title'] ?: ($this->title ?: '');
        $this->seo_title = $row['seo_title'] ?: ($this->seo_title ?: '');
        $this->friendly_url = $row['friendly_url'] ?: '';
        $this->image_id = $row['image_id'] ?: ($this->image_id ?: 'NULL');
        $this->cover_id = $row['cover_id'] ?: ($this->cover_id ?: 'NULL');
        $this->description = $row['description'] ?? $this->description ?: '';
        $this->seo_description = $row['seo_description'] ?? $this->seo_description ?: '';
        $this->long_description = $row['long_description'] ?? $this->long_description ?: '';
        $this->video_snippet = $row['video_snippet'] ?? $this->video_snippet ?: '';
        $this->video_url = $row['video_url'] ?? $this->video_url ?: '';
        $this->keywords = $row['keywords'] ?: '';
        $this->seo_keywords = $row['seo_keywords'] ?? $this->seo_keywords ?: '';
        $this->updated = $row['updated'] ?: ($this->updated ?: '');
        $this->entered = $row['entered'] ?: ($this->entered ?: '');
        $this->setDate('start_date', $row['start_date']);
        $this->start_time = isset($row['start_time']) ? (empty($row['start_time']) ? 'NULL' : $row['start_time']) : ($this->start_time ?: 'NULL');
        $this->setDate('end_date', $row['end_date']);
        $this->end_time = isset($row['end_time']) ? (empty($row['end_time']) ? 'NULL' : $row['end_time']) : ($this->end_time ?: 'NULL');
        $this->location = $row['location'] ?? $this->location ?: '';
        $this->address = $row['address'] ?: '';
        $this->zip_code = $row['zip_code'] ?: '';
        $this->location_1 = $row['location_1'] ?: 0;
        $this->location_2 = $row['location_2'] ?: 0;
        $this->location_3 = $row['location_3'] ?: 0;
        $this->location_4 = $row['location_4'] ?: 0;
        $this->location_5 = $row['location_5'] ?: 0;
        $this->url = $row['url'] ?? $this->url ?: '';
        $this->contact_name = $row['contact_name'] ?? $this->contact_name ?: '';
        $this->phone = $row['phone'] ?? $this->phone ?: '';
        $this->email = $row['email'] ?? $this->email ?: '';
        $this->renewal_date = $row['renewal_date'] ?: ($this->renewal_date ?: 0);
        $this->discount_id = $row['discount_id'] ?: '';
        $this->facebook_page = $row['facebook_page'] ?? $this->facebook_page ?: '';
        $this->status = $row['status'] ?: $statusObj->getDefaultStatus();
        $this->level = $row['level'] ?: ($this->level ?: $level->getDefaultLevel());
        $this->recurring = $row['recurring'] ?: 'N';
        $this->day = $row['day'] ?: 0;
        $this->dayofweek = $row['dayofweek'] ?: '';
        $this->week = $row['week'] ?: '';
        $this->month = $row['month'] ?: 0;
        $this->setDate('until_date', ($row['until_date'] ?: '0000-00-00'));
        $this->repeat_event = $row['repeat_event'] ?: 'N';

        if ($this->recurring == 'N') {
            $this->day = 0;
            $this->dayofweek = '';
            $this->week = '';
            $this->month = 0;
            $this->until_date = '0000-00-00';
        }

        $this->number_views = $row['number_views'] ?: ($this->number_views ?: 0);
        $this->latitude = $row['latitude'] ?: '';
        $this->longitude = $row['longitude'] ?: '';
        $this->map_zoom = is_numeric($row['map_zoom']) ? $row['map_zoom'] : 0;
        $this->data_in_array = $row;
        $this->package_id = $row['package_id'] ?: ($this->package_id ?: 0);
        $this->package_price = $row['package_price'] ?: ($this->package_price ?: 0);

        //video_url added on v10.4. This will get the url for existing videos (iframe)
        if ($this->video_snippet && !$this->video_url) {
            $this->video_url = system_getVideoURL($this->video_snippet);
        }

        /* ModStores Hooks */
        HookFire('classevent_after_makerow', [
            'that' => &$this,
            'row'  => &$row,
        ]);
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->Save();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->Save();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @access Public
     */
    public function Save()
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);

        if ($this->domain_id) {
            $dbObj = db_getDBObjectByDomainID($this->domain_id, $dbMain);
        } else if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        /* ModStores Hooks */
        HookFire('classevent_before_preparesave', [
            'that' => &$this
        ]);

        $this->prepareToSave();

        $aux_old_account = str_replace("'", '', $this->old_account_id);
        $aux_account = str_replace("'", '', $this->account_id);

        $this->friendly_url = string_strtolower($this->friendly_url);

        /*
         * TODO
         * Review calls of method save when adding/editing an item
         * Right now it's been called several times messing up some attributes values
         */
        if ($this->image_id == "''") {
            $this->image_id = 'NULL';
        }
        if ($this->cover_id == "''") {
            $this->cover_id = 'NULL';
        }

        if ($this->id) {

            $updateItem = true;

            $sql = 'UPDATE Event SET'
                . " account_id        = $this->account_id,"
                . " title             = $this->title,"
                . " seo_title         = $this->seo_title,"
                . " friendly_url      = $this->friendly_url,"
                . " image_id          = $this->image_id,"
                . " cover_id          = $this->cover_id,"
                . " description       = $this->description,"
                . " seo_description   = $this->seo_description,"
                . " long_description  = $this->long_description,"
                . " video_snippet     = $this->video_snippet,"
                . " video_url         = $this->video_url,"
                . " keywords          = $this->keywords,"
                . " seo_keywords      = $this->seo_keywords,"
                .' updated           = NOW(),'
                . " start_date        = $this->start_date,"
                . " start_time        = $this->start_time,"
                . " end_date          = $this->end_date,"
                . " end_time          = $this->end_time,"
                . " location          = $this->location,"
                . " address           = $this->address,"
                . " zip_code          = $this->zip_code,"
                . " location_1        = $this->location_1,"
                . " location_2        = $this->location_2,"
                . " location_3        = $this->location_3,"
                . " location_4        = $this->location_4,"
                . " location_5        = $this->location_5,"
                . " url               = $this->url,"
                . " contact_name      = $this->contact_name,"
                . " phone             = $this->phone,"
                . " email             = $this->email,"
                . " renewal_date      = $this->renewal_date,"
                . " discount_id       = $this->discount_id,"
                . " facebook_page     = $this->facebook_page,"
                . " status            = $this->status,"
                . " level             = $this->level,"
                . " recurring         = $this->recurring,"
                . " day               = $this->day,"
                . " dayofweek         = $this->dayofweek,"
                . " week              = $this->week,"
                . " month             = $this->month,"
                . " until_date        = $this->until_date,"
                . " repeat_event      = $this->repeat_event,"
                . " number_views      = $this->number_views,"
                . " latitude          = $this->latitude,"
                . " longitude         = $this->longitude,"
                . " map_zoom          = $this->map_zoom,"
                . " package_id        = $this->package_id,"
                . " package_price     = $this->package_price"
                . " WHERE id          = $this->id";

            /* ModStores Hooks */
            HookFire('classevent_before_updatequery', [
                'that' => &$this,
                'sql'  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire('classevent_after_updatequery', [
                'that' => &$this
            ]);

            if ($aux_old_account != $aux_account && $aux_account != 0) {
                domain_SaveAccountInfoDomain($aux_account, $this);
            }

            if ($this->status == "'S'") {
                //Check if this event has an active subscription on Stripe to cancel it
                $sql = "SELECT payment_log_id FROM Payment_Event_Log WHERE event_id = $this->id ORDER BY renewal_date DESC";
                $r = $dbObj->query($sql);

                while ($row = mysqli_fetch_assoc($r)) {
                    $sql = "SELECT transaction_id FROM Payment_Log WHERE id = {$row['payment_log_id']} AND system_type = 'stripe' AND recurring = 'y'";
                    $resultLog = $dbObj->query($sql);
                    $log_data = mysqli_fetch_assoc($resultLog);
                    $stripe_apikey = crypt_decrypt(setting_get('payment_stripe_apikey'));
                    $dataStripe['subscription_id'] = $log_data['transaction_id'];
                    StripeInterface::StripeRequest('cancelsubscription', $stripe_apikey, $dataStripe);
                }
            }

        } else {
            $sql = 'INSERT INTO Event'
                .' (account_id,'
                .' title,'
                .' seo_title,'
                .' friendly_url,'
                .' image_id,'
                .' cover_id,'
                .' description,'
                .' seo_description,'
                .' long_description,'
                .' video_snippet,'
                .' video_url,'
                .' keywords,'
                .' seo_keywords,'
                .' updated,'
                .' entered,'
                .' start_date,'
                .' start_time,'
                .' end_date,'
                .' end_time,'
                .' location,'
                .' address,'
                .' zip_code,'
                .' location_1,'
                .' location_2,'
                .' location_3,'
                .' location_4,'
                .' location_5,'
                .' url,'
                .' contact_name,'
                .' phone,'
                .' email,'
                .' renewal_date,'
                .' discount_id,'
                .' facebook_page,'
                .' status,'
                .' level,'
                .' fulltextsearch_keyword,'
                .' fulltextsearch_where,'
                .' recurring,'
                .' day,'
                .' dayofweek,'
                .' week,'
                .' month,'
                .' until_date,'
                .' repeat_event,'
                .' number_views,'
                .' latitude,'
                .' longitude,'
                .' map_zoom,'
                .' package_id,'
                .' package_price)'
                .' VALUES'
                . " ($this->account_id,"
                . " $this->title,"
                . " $this->seo_title,"
                . " $this->friendly_url,"
                . " $this->image_id,"
                . " $this->cover_id,"
                . " $this->description,"
                . " $this->seo_description,"
                . " $this->long_description,"
                . " $this->video_snippet,"
                . " $this->video_url,"
                . " $this->keywords,"
                . " $this->seo_keywords,"
                .' NOW(),'
                .' NOW(),'
                . " $this->start_date,"
                . " $this->start_time,"
                . " $this->end_date,"
                . " $this->end_time,"
                . " $this->location,"
                . " $this->address,"
                . " $this->zip_code,"
                . " $this->location_1,"
                . " $this->location_2,"
                . " $this->location_3,"
                . " $this->location_4,"
                . " $this->location_5,"
                . " $this->url,"
                . " $this->contact_name,"
                . " $this->phone,"
                . " $this->email,"
                . " $this->renewal_date,"
                . " $this->discount_id,"
                . " $this->facebook_page,"
                . " $this->status,"
                . " $this->level,"
                . " '',"
                . " '',"
                . " $this->recurring,"
                . " $this->day,"
                . " $this->dayofweek,"
                . " $this->week,"
                . " $this->month,"
                . " $this->until_date,"
                . " $this->repeat_event,"
                . " $this->number_views,"
                . " $this->latitude,"
                . " $this->longitude,"
                . " $this->map_zoom,"
                . " $this->package_id,"
                . " $this->package_price)";

            /* ModStores Hooks */
            HookFire('classevent_before_insertquery', [
                'that' => &$this,
                'sql'  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire('classevent_after_insertquery', [
                'that'  => &$this,
                'dbObj' => &$dbObj,
            ]);

            $this->id = (is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id)) ? false : $___mysqli_res);

            if ($aux_account != 0) {
                domain_SaveAccountInfoDomain($aux_account, $this);
            }

        }

        if ((sess_getAccountIdFromSession() && string_strpos($_SERVER['PHP_SELF'],
                    'event.php') !== false) || string_strpos($_SERVER['PHP_SELF'], 'order_') !== false
        ) {
            $rowTimeline = array();
            $rowTimeline['item_type'] = 'event';
            $rowTimeline['action'] = ($updateItem ? 'edit' : 'new');
            $rowTimeline['item_id'] = str_replace("'", '', $this->id);
            $timelineObj = new Timeline($rowTimeline);
            $timelineObj->Save();
        }

        /* ModStores Hooks */
        HookFire('classevent_before_prepareuse', [
            'that' => &$this,
        ]);

        $this->prepareToUse();

        $this->setFullTextSearch();

        /* ModStores Hooks */
        HookFire('classevent_after_save', [
            'that' => &$this,
        ]);
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->Delete();
     * <code>
     * @param bool $domain_id
     * @access Public
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @copyright Copyright 2018 Arca Solutions, Inc.
     */
    public function Delete($domain_id = false)
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if ($domain_id) {
            $dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
        } else {
            if (defined('SELECTED_DOMAIN_ID')) {
                $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $dbObj = db_getDBObject();
            }
            unset($dbMain);
        }

        ### GALERY
        //before deleting the gallery, it needs to clear event image ids
        $sql = "UPDATE Event SET image_id = NULL, cover_id = NULL WHERE id = $this->id";
        $dbObj->query($sql);

        $sql = "SELECT gallery_id FROM Gallery_Item WHERE item_id = $this->id AND item_type = 'event'";
        $row = mysqli_fetch_array($dbObj->query($sql));
        $gallery_id = $row['gallery_id'];
        if ($gallery_id) {
            $gallery = new Gallery($gallery_id);
            $gallery->Delete();
        }

        ### IMAGE
        if ($this->image_id) {
            $image = new Image($this->image_id);
            if ($image) {
                $image->Delete($domain_id);
            }
        }
        if (is_numeric($this->cover_id)) {
            $image = new Image($this->cover_id);
            if ($image) {
                $image->Delete($domain_id);
            }
        }

        ### INVOICE
        $sql = "UPDATE Invoice_Event SET event_id = '0' WHERE event_id = $this->id";
        $dbObj->query($sql);

        ### PAYMENT
        $sql = "UPDATE Payment_Event_Log SET event_id = '0' WHERE event_id = $this->id";
        $dbObj->query($sql);

        ### Timeline
        $sql = "DELETE FROM Timeline WHERE item_type = 'event' AND item_id = $this->id";
        $dbObj->query($sql);

        ### Quicklist (Favorites)
        $sql = "DELETE FROM Quicklist WHERE item_type = 'event' AND item_id = $this->id";
        $dbObj->query($sql);

        /* ModStores Hooks */
        HookFire('classevent_before_delete', [
            'that' => &$this
        ]);

        ### EVENT
        $sql = "DELETE FROM Event WHERE id = $this->id";
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get('event.synchronization')->addDelete($this->id);
        }

    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getCategories();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getCategories();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getCategories
     * @access Public
     * @return array
     */
    public function getCategories()
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);
        $sql = "SELECT cat_1_id, cat_2_id, cat_3_id, cat_4_id, cat_5_id FROM Event WHERE id = $this->id";
        $r = $dbObj->query($sql);
        while ($row = mysqli_fetch_array($r)) {
            if ($row['cat_1_id']) {
                $categories[] = new EventCategory($row['cat_1_id']);
            }
            if ($row['cat_2_id']) {
                $categories[] = new EventCategory($row['cat_2_id']);
            }
            if ($row['cat_3_id']) {
                $categories[] = new EventCategory($row['cat_3_id']);
            }
            if ($row['cat_4_id']) {
                $categories[] = new EventCategory($row['cat_4_id']);
            }
            if ($row['cat_5_id']) {
                $categories[] = new EventCategory($row['cat_5_id']);
            }
        }

        return $categories;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->setCategories();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->setCategories();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name setCategories
     * @access Public
     */
    public function setCategories($array)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        $cat_1_id = 'null';
        $parcat_1_level1_id = 0;
        $parcat_1_level2_id = 0;
        $parcat_1_level3_id = 0;
        $parcat_1_level4_id = 0;
        $cat_2_id = 'null';
        $parcat_2_level1_id = 0;
        $parcat_2_level2_id = 0;
        $parcat_2_level3_id = 0;
        $parcat_2_level4_id = 0;
        $cat_3_id = 'null';
        $parcat_3_level1_id = 0;
        $parcat_3_level2_id = 0;
        $parcat_3_level3_id = 0;
        $parcat_3_level4_id = 0;
        $cat_4_id = 'null';
        $parcat_4_level1_id = 0;
        $parcat_4_level2_id = 0;
        $parcat_4_level3_id = 0;
        $parcat_4_level4_id = 0;
        $cat_5_id = 'null';
        $parcat_5_level1_id = 0;
        $parcat_5_level2_id = 0;
        $parcat_5_level3_id = 0;
        $parcat_5_level4_id = 0;
        if ($array) {
            $count_category_aux = 1;
            foreach ($array as $category) {
                if ($category) {
                    unset($parents);
                    $cat_id = $category;
                    $i = 0;
                    while ($cat_id != 0) {
                        $sql = "SELECT * FROM EventCategory WHERE id = $cat_id";
                        $rs1 = $dbObj->query($sql);
                        if (mysqli_num_rows($rs1) > 0) {
                            $cat_info = mysqli_fetch_assoc($rs1);
                            $cat_id = $cat_info['category_id'];
                            $parents[$i++] = $cat_id;
                        } else {
                            $cat_id = 0;
                        }
                    }
                    for ($j = count($parents) - 1; $j < 4; $j++) {
                        $parents[$j] = 0;
                    }
                    ${'cat_'. $count_category_aux .'_id'} = $category;
                    ${'parcat_'. $count_category_aux .'_level1_id'} = $parents[0];
                    ${'parcat_'. $count_category_aux .'_level2_id'} = $parents[1];
                    ${'parcat_'. $count_category_aux .'_level3_id'} = $parents[2];
                    ${'parcat_'. $count_category_aux .'_level4_id'} = $parents[3];
                    $count_category_aux++;
                }
            }
        }
        $sql = 'UPDATE Event SET cat_1_id = '. $cat_1_id .', parcat_1_level1_id = '. $parcat_1_level1_id .', parcat_1_level2_id = '. $parcat_1_level2_id .', parcat_1_level3_id = '. $parcat_1_level3_id .', parcat_1_level4_id = '. $parcat_1_level4_id .', cat_2_id = '. $cat_2_id .', parcat_2_level1_id = '. $parcat_2_level1_id .', parcat_2_level2_id = '. $parcat_2_level2_id .', parcat_2_level3_id = '. $parcat_2_level3_id .', parcat_2_level4_id = '. $parcat_2_level4_id .', cat_3_id = '. $cat_3_id .', parcat_3_level1_id = '. $parcat_3_level1_id .', parcat_3_level2_id = '. $parcat_3_level2_id .', parcat_3_level3_id = '. $parcat_3_level3_id .', parcat_3_level4_id = '. $parcat_3_level4_id .', cat_4_id = '. $cat_4_id .', parcat_4_level1_id = '. $parcat_4_level1_id .', parcat_4_level2_id = '. $parcat_4_level2_id .', parcat_4_level3_id = '. $parcat_4_level3_id .', parcat_4_level4_id = '. $parcat_4_level4_id .', cat_5_id = '. $cat_5_id .', parcat_5_level1_id = '. $parcat_5_level1_id .', parcat_5_level2_id = '. $parcat_5_level2_id .', parcat_5_level3_id = '. $parcat_5_level3_id .', parcat_5_level4_id = '. $parcat_5_level4_id . " WHERE id = $this->id";
        $dbObj->query($sql);
        $this->setFullTextSearch();
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getCategories();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getCategories();
     * </code>
     * @copyright Copyrighion getPrice(t 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getCategories
     * @access Public
     * @return real
     */
    public function getPrice($renewal_period = '')
    {
        /*
         * Fix to normalize variable standard. It should be monthly or yearly, but some places are sending it as M or Y.
         * Sorry.
         */
        if ($renewal_period == 'M') {
            $renewal_period = 'monthly';
        } elseif ($renewal_period == 'Y') {
            $renewal_period = 'yearly';
        }
        $price = 0;

        $dbMain = db_getDBObject(DEFAULT_DB, true);

        if ($this->domain_id) {
            $dbObj = db_getDBObjectByDomainID($this->domain_id, $dbMain);
        } else if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        $levelObj = new EventLevel();

        /*
         * Workaround for the scenario where the monthly price is 0 and the yearly price > 0, but the variable $renewal_period comes empty
         * In this case, the system reads the monthly price and considers the item as a free item
         */
        if (!$renewal_period && $levelObj->getPrice($this->level) <= 0 && $levelObj->getPrice($this->level, 'yearly') > 0) {
            $renewal_period = 'yearly';
        }

        if ($this->package_id) {
            $price = $this->package_price;
        } else {
            $price += $levelObj->getPrice($this->level, ($renewal_period == 'monthly' ? '' : $renewal_period));
        }

        if ($this->discount_id) {

            $discountCodeObj = new DiscountCode($this->discount_id);

            if (is_valid_discount_code($this->discount_id, 'event', $this->id, $discount_message, $discount_error)) {

                if ($discountCodeObj->getString('id') && $discountCodeObj->expire_date >= date('Y-m-d')) {

                    if ($discountCodeObj->getString('type') == 'percentage') {
                        $price *= (1 - $discountCodeObj->getString('amount') / 100);
                    } elseif ($discountCodeObj->getString('type') == 'monetary value') {
                        $price -= $discountCodeObj->getString('amount');
                    }

                }

            } else {

                $sql = "UPDATE Event SET discount_id = '' WHERE id = " . $this->id;
                $result = $dbObj->query($sql);

            }

        }

        if ($price <= 0) {
            $price = 0;
        }

        return $price;

    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->hasRenewalDate();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->hasRenewalDate();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name hasRenewalDate
     * @access Public
     * @return boolean
     */
    public function hasRenewalDate()
    {
        if (PAYMENT_FEATURE != 'on') {
            return false;
        }
        if ((CREDITCARDPAYMENT_FEATURE != 'on') && (PAYMENT_INVOICE_STATUS != 'on') && (PAYMENT_MANUAL_STATUS != 'on')) {
            return false;
        }
        if ($this->getPrice('monthly') <= 0 && $this->getPrice('yearly') <= 0) {
            return false;
        }

        return true;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->needToCheckOut();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->needToCheckOut();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name needToCheckOut
     * @access Public
     * @return boolean
     */
    public function needToCheckOut()
    {

        if ($this->hasRenewalDate()) {

            $today = date('Y-m-d');
            $today = explode('-', $today);
            $today_year = $today[0];
            $today_month = $today[1];
            $today_day = $today[2];
            $timestamp_today = mktime(0, 0, 0, $today_month, $today_day, $today_year);

            $this_renewaldate = $this->renewal_date;
            $renewaldate = explode('-', $this_renewaldate);
            $renewaldate_year = $renewaldate[0];
            $renewaldate_month = $renewaldate[1];
            $renewaldate_day = $renewaldate[2];
            $timestamp_renewaldate = mktime(0, 0, 0, $renewaldate_month, $renewaldate_day, $renewaldate_year);

            if (($this->status == 'E') || ($this_renewaldate == '0000-00-00') || ($timestamp_today > $timestamp_renewaldate)) {
                return true;
            }

        }

        return false;

    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getNextRenewalDate($times);
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getNextRenewalDate($times);
     * </code>
     * @param integer $times
     * @param string $renewalunit
     * @return date
     * @access Public
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     */
    public function getNextRenewalDate($times = 1, $renewalunit = 'M')
    {

        $nextrenewaldate = '0000-00-00';

        if ($this->hasRenewalDate()) {

            if ($this->needToCheckOut()) {

                $today = date('Y-m-d');
                $today = explode('-', $today);
                $start_year = $today[0];
                $start_month = $today[1];
                $start_day = $today[2];

            } else {

                $this_renewaldate = $this->renewal_date;
                $renewaldate = explode('-', $this_renewaldate);
                $start_year = $renewaldate[0];
                $start_month = $renewaldate[1];
                $start_day = $renewaldate[2];

            }

            $renewalcycle = 1;

            if ($renewalunit == 'Y') {
                $nextrenewaldate = date('Y-m-d',
                    mktime(0, 0, 0, (int)$start_month, (int)$start_day, (int)$start_year + ($renewalcycle * $times)));
            } elseif ($renewalunit == 'M') {
                $nextrenewaldate = date('Y-m-d',
                    mktime(0, 0, 0, (int)$start_month + ($renewalcycle * $times), (int)$start_day, (int)$start_year));
            } elseif ($renewalunit == 'D') {
                $nextrenewaldate = date('Y-m-d',
                    mktime(0, 0, 0, (int)$start_month, (int)$start_day + ($renewalcycle * $times), (int)$start_year));
            } else {
                $nextrenewaldate = date('Y-m-d', mktime(0, 0, 0, (int)$start_month, (int)$start_day, (int)$start_year + ($renewalcycle * $times)));
            }

        }

        return $nextrenewaldate;

    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getDateString();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getDateString();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getDateString
     * @access Public
     * @return string
     */
    public function getDateString($use_text = false)
    {
        $str_date = '';

        if ($this->getDate('start_date') == $this->getDate('end_date')) {
            $str_date = $this->getDate('start_date');
        } elseif ($this->getString('recurring') != 'Y') {
            if ($use_text) {
                $str_date = '<p><strong>'. ucfirst(system_showText(LANG_LABEL_FROM)) .': </strong>'.'<span>'. $this->getDate('start_date') .'</span></p>'.'<p><strong>'. ucfirst(system_showText(LANG_LABEL_DATE_TO)) .': </strong>'.'<span>'. $this->getDate('end_date') .'</span></p>';
            } else {
                $str_date = $this->getDate('start_date') .' - '. $this->getDate('end_date');
            }
        } else {
            $str_date = $this->getDateStringRecurring();
        }

        return $str_date;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getDateStringEnd();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getDateStringEnd();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getDateStringEnd
     * @access Public
     * @return string
     */
    public function getDateStringEnd()
    {
        return $this->getDate('until_date');
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getDateStringRecurring();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getDateStringRecurring();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getDateStringRecurring
     * @access Public
     * @return string
     */
    public function getDateStringRecurring()
    {
        $str_date = '';

        if ($this->getString('recurring') == 'Y') {

            $month_names = explode(',', LANG_DATE_MONTHS);
            $weekday_names = explode(',', LANG_DATE_WEEKDAYS);

            if ($this->getString('dayofweek') && $this->getNumber('week') && $this->getNumber('month')) { //yearly with determined week and random days

                $aux = system_getRecurringWeeks($this->getString('week'));
                $checkDays = system_checkDay($this->getString('dayofweek'));
                $str_date .= $checkDays;
                if ($aux) {
                    $str_date .= ', '. LANG_EVERY2 .' '. $aux . system_showText(LANG_WEEK) .' '. system_showText(LANG_OF2) .' '. ucfirst($month_names[$this->getNumber('month') - 1]);
                } else {
                    $str_date .= ' '. system_showText(LANG_OF2) .' '. ucfirst($month_names[$this->getNumber('month') - 1]);
                }

            } elseif ($this->getNumber('day')) { //monthly or yearly with determined day

                if ($this->getNumber('month')) {
                    if (EDIR_LANGUAGE == 'en_us') {
                        $str_date .= ucfirst($month_names[$this->getNumber('month') - 1]) .' '. system_getOrdinalLabel($this->getNumber('day')) .', '. LANG_EVERY_YEAR;
                    } else {
                        $str_date .= ucfirst(system_showText(LANG_DAY)) .' '. $this->getNumber('day') .' '. system_showText(LANG_OF2) .' '. ucfirst($month_names[$this->getNumber('month') - 1]);
                    }
                } else if (EDIR_LANGUAGE == 'en_us') {
                    $str_date .= system_getOrdinalLabel($this->getNumber('day')) .' '. ucfirst(system_showText(LANG_DAY)) .' '. LANG_OF .' '. LANG_THE_MONTH;
                } else {
                    $str_date .= system_showText(LANG_EVERY2) .' '. system_showText(LANG_DAY) .' '. $this->getNumber('day');
                }

            } elseif ($this->getString('dayofweek')) { //weekly or monthly, with determined week and random days

                if ($this->getNumber('week')) {

                    $aux = system_getRecurringWeeks($this->getString('week'));
                    $checkDays = system_checkDay($this->getString('dayofweek'));
                    $str_date .= $checkDays .' ';
                    if ($aux) {
                        $str_date = str_replace(LANG_EVERY2 .' '. ucfirst(LANG_EVENT_WEEKEND),
                            ucfirst(LANG_EVENT_WEEKENDS) .', ', $str_date);
                        $str_date .= LANG_EVERY2 .' '. $aux . LANG_WEEK . (EDIR_LANGUAGE == 'en_us' ? ' '. LANG_OF2 : '') .' '. LANG_THE_MONTH;
                    }
                } else {
                    $checkDays = system_checkDay($this->getString('dayofweek'));
                    $str_date .= $checkDays;
                }

            } else { //daily
                $str_date .= system_showText(LANG_DAILY2);
            }

        }

        return $str_date;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getTimeString();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getTimeString();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getTimeString
     * @access Public
     * @return string
     */
    public function getTimeString()
    {
        $str_time = '';
        if ($this->getString('start_time') && $this->getString('end_time') && $this->getString('start_time') != 'NULL' && $this->getString('end_time') != 'NULL') {
            if ($this->getString('start_time')) {
                $str_time = format_getTimeString($this->getString('start_time'));
            } else {
                $str_time .= LANG_NA;
            }
            $str_time .= ' - ';
            if ($this->getString('end_time')) {
                $str_time .= format_getTimeString($this->getString('end_time'));
            } else {
                $str_time .= LANG_NA;
            }
        }

        return $str_time;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getMonthAbbr();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getMonthAbbr();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getMonthAbbr
     * @access Public
     * @return string
     */
    public function getMonthAbbr()
    {
        $aux = explode('/', $this->getDate('start_date'));
        $months = explode(',', LANG_DATE_MONTHS);
        if (DEFAULT_DATE_FORMAT == 'm/d/Y') {
            $month = $aux[0];
        } else {
            $month = $aux[1];
        }

        switch ($month) {
            case '01' :
                return string_substr($months[0], 0, 3);
                break;
            case '02' :
                return string_substr($months[1], 0, 3);
                break;
            case '03' :
                return string_substr($months[2], 0, 3);
                break;
            case '04' :
                return string_substr($months[3], 0, 3);
                break;
            case '05' :
                return string_substr($months[4], 0, 3);
                break;
            case '06' :
                return string_substr($months[5], 0, 3);
                break;
            case '07' :
                return string_substr($months[6], 0, 3);
                break;
            case '08' :
                return string_substr($months[7], 0, 3);
                break;
            case '09' :
                return string_substr($months[8], 0, 3);
                break;
            case '10' :
                return string_substr($months[9], 0, 3);
                break;
            case '11' :
                return string_substr($months[10], 0, 3);
                break;
            case '12' :
                return string_substr($months[11], 0, 3);
                break;
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->checkStartDate();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->checkStartDate();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name checkStartDate
     * @access Public
     * @return string
     */
    public function checkStartDate()
    {
        if ($this->getString('recurring') != 'Y') {
            $today = date('Y-m-d');
            $auxStartDate = explode('/', $this->getDate('start_date'));
            if (DEFAULT_DATE_FORMAT == 'm/d/Y') {
                $startDate = $auxStartDate[2] .'-'. $auxStartDate[0] .'-'. $auxStartDate[1];
            } else {
                $startDate = $auxStartDate[2] .'-'. $auxStartDate[1] .'-'. $auxStartDate[0];
            }

            return $today == $startDate;
        }

        return false;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getMonthAbbr();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getMonthAbbr();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getTimeString
     * @access Public
     * @return string
     */
    public function getDayStr()
    {
        $aux = explode('/', $this->getDate('start_date'));
        if (DEFAULT_DATE_FORMAT == 'm/d/Y') {
            return $aux[1];
        }

        return $aux[0];

    }

    /**
     * <code>
     *        //Using this in Event() class.
     *        $this->setLocationManager(&$locationManager);
     * </code>
     * @param string $locationManager
     * @access Public
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @copyright Copyright 2018 Arca Solutions, Inc.
     */
    public function setLocationManager(&$locationManager)
    {
        $this->locationManager =& $locationManager;
    }

    /**
     * <code>
     *        //Using this in Event() class.
     *        $this->getLocationManager();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getLocationManager
     * @access Public
     * @return array
     */
    public function &getLocationManager()
    {
        return $this->locationManager; /* NEVER auto-instantiate this*/
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getLocationString($format,$forceManagerCreation);
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getLocationString();
     * </code>
     * @param string $format , boolean $forceManagerCreation
     * @param bool $forceManagerCreation
     * @param bool $lineBreak
     * @return string
     * @access Public
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @copyright Copyright 2018 Arca Solutions, Inc.
     */
    public function getLocationString($format, $forceManagerCreation = false, $lineBreak = true)
    {
        if ($forceManagerCreation && !$this->locationManager) {
            $this->locationManager = new LocationManager();
        }

        return db_getLocationString($this, $format, true, $lineBreak);
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->setFullTextSearch();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->setFullTextSearch();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name setFullTextSearch
     * @access Public
     */
    public function setFullTextSearch()
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        if ($this->title) {
            $string = str_replace(' || ', ' ', $this->title);
            $fulltextsearch_keyword[] = $string;
            $addkeyword = format_addApostWords($string);
            if ($addkeyword != '') {
                $fulltextsearch_keyword[] = $addkeyword;
            }
            unset($addkeyword);
        }

        if ($this->keywords) {
            $string = str_replace(' || ', ' ', $this->keywords);
            $fulltextsearch_keyword[] = $string;
            $addkeyword = format_addApostWords($string);
            if ($addkeyword != '') {
                $fulltextsearch_keyword[] = $addkeyword;
            }
            unset($addkeyword);
        }

        if ($this->description) {
            $fulltextsearch_keyword[] = string_substr($this->description, 0, 100);
        }

        if ($this->address) {
            $fulltextsearch_where[] = $this->address;
        }

        if ($this->location) {
            $fulltextsearch_where[] = $this->location;
        }

        if ($this->zip_code) {
            $fulltextsearch_where[] = $this->zip_code;
        }

        $Location1 = new Location1($this->location_1);
        if ($Location1->getNumber('id')) {
            $fulltextsearch_where[] = $Location1->getString('name', false);
            if ($Location1->getString('abbreviation')) {
                $fulltextsearch_where[] = $Location1->getString('abbreviation', false);
            }
        }

        $_locations = explode(',', EDIR_LOCATIONS);
        foreach ($_locations as $each_location) {
            unset ($objLocation);
            $objLocationLabel = 'Location'. $each_location;
            $attributeLocation = 'location_' . $each_location;
            $objLocation = new $objLocationLabel;
            $objLocation->SetString('id', $this->$attributeLocation);
            $locationsInfo = $objLocation->retrieveLocationById();
            if ($locationsInfo['id']) {
                $fulltextsearch_where[] = $locationsInfo['name'];
                if ($locationsInfo['abbreviation']) {
                    $fulltextsearch_where[] = $locationsInfo['abbreviation'];
                }
            }
        }

        $categories = $this->getCategories();
        if ($categories) {
            foreach ($categories as $category) {
                unset($parents);
                $category_id = $category->getNumber('id');
                while (!is_null($category_id) && $category_id != 0) {
                    $sql = "SELECT * FROM EventCategory WHERE id = $category_id";
                    $result = $dbObj->query($sql);
                    if (mysqli_num_rows($result) > 0) {
                        $category_info = mysqli_fetch_assoc($result);
                        if ($category_info['enabled'] == 'y') {
                            if ($category_info['title']) {
                                $fulltextsearch_keyword[] = $category_info['title'];
                            }

                            if ($category_info['keywords']) {
                                $fulltextsearch_keyword[] = str_replace(array("\r\n", "\n"), ' ',
                                    $category_info['keywords']);
                            }
                        }
                        $category_id = $category_info['category_id'];
                    } else {
                        $category_id = 'NULL';
                    }
                }
            }
        }

        /* ModStores Hooks */
        HookFire('classevent_before_update_fulltextkeyword', [
            'that'                   => &$this,
            'fulltextsearch_keyword' => &$fulltextsearch_keyword
        ]);

        if (is_array($fulltextsearch_keyword)) {
            $fulltextsearch_keyword_sql = db_formatString(implode(' ', $fulltextsearch_keyword));
            $sql = "UPDATE Event SET fulltextsearch_keyword = {$fulltextsearch_keyword_sql} WHERE id = {$this->id}";
            $result = $dbObj->query($sql);
        }

        /* ModStores Hooks */
        HookFire('classevent_before_update_fulltextwhere', [
            'that'                 => &$this,
            'fulltextsearch_where' => &$fulltextsearch_where
        ]);

        if (is_array($fulltextsearch_where)) {
            $fulltextsearch_where_sql = db_formatString(implode(' ', $fulltextsearch_where));
            $sql = "UPDATE Event SET fulltextsearch_where = {$fulltextsearch_where_sql} WHERE id = {$this->id}";
            $result = $dbObj->query($sql);
        }

        $this->synchronize();
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getGalleries();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getGalleries();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getGalleries
     * @access Public
     * @return array
     */
    public function getGalleries()
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);
        $sql = "SELECT * FROM Gallery_Item WHERE item_type='event' AND item_id = $this->id ORDER BY gallery_id";
        $r = $dbObj->query($sql);
        if ($this->id > 0) {
            while ($row = mysqli_fetch_array($r)) {
                $galleries[] = $row['gallery_id'];
            }
        }

        return $galleries;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->setGalleries($gallery);
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->setGalleries($gallery);
     * </code>
     * @param bool $gallery
     * @access Public
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @copyright Copyright 2018 Arca Solutions, Inc.
     */
    public function setGalleries($gallery = false)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);
        $sql = "DELETE FROM Gallery_Item WHERE item_type='event' AND item_id = $this->id";
        $dbObj->query($sql);
        if ($gallery) {
            $sql = "INSERT INTO Gallery_Item (item_id, gallery_id, item_type) VALUES ($this->id, $gallery, 'event')";
            $rs3 = $dbObj->query($sql);
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->hasDetail();
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->hasDetail();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name hasDetail
     * @access Public
     * @return char
     */
    public function hasDetail()
    {
        $eventLevel = new EventLevel();
        $detail = $eventLevel->getDetail($this->level);
        unset($eventLevel);

        return $detail;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->setNumberViews($id);
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->setNumberViews($id);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param integer $id
     * @access Public
     */
    public function setNumberViews($id)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);
        $sql = 'UPDATE Event SET number_views = '. $this->number_views .' + 1 WHERE Event.id = '. $id;
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get('event.synchronization')->addViewUpdate($id);
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->deletePerAccount($account_id);
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->deletePerAccount($account_id);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param integer $account_id
     * @param bool $domain_id
     * @access Public
     */
    public function deletePerAccount($account_id = 0, $domain_id = false)
    {
        if (is_numeric($account_id) && $account_id > 0) {
            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if ($domain_id) {
                $dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
            } else {
                if (defined('SELECTED_DOMAIN_ID')) {
                    $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                } else {
                    $dbObj = db_getDBObject();
                }
                unset($dbMain);
            }
            $sql = "SELECT * FROM Event WHERE account_id = $account_id";
            $result = $dbObj->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                $this->makeFromRow($row);
                $this->Delete($domain_id);
            }
        }
    }


    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $eventObj->getEventByFriendlyURL($friendly_url);
     * <br /><br />
     *        //Using this in Event() class.
     *        $this->getEventByFriendlyURL($friendly_url);
     * </code>
     * @param string $friendly_url
     * @return bool
     * @access Public
     * @version 8.0.00
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     */
    public function getEventByFriendlyURL($friendly_url)
    {
        $dbObj = db_getDBObject();
        $sql = "SELECT * FROM Event WHERE friendly_url = '" . $friendly_url . "'";
        $result = $dbObj->query($sql);
        if (mysqli_num_rows($result)) {
            $this->makeFromRow(mysqli_fetch_assoc($result));

            return true;
        }

        return false;
    }

    /**
     * Synchronizes this instance in elasticsearch
     */
    public function synchronize()
    {
        if ($symfonyContainer = SymfonyCore::getContainer()) {
            if($this->status == 'A'){
                $symfonyContainer->get('event.synchronization')->addUpsert($this->id);
            } else {
                $symfonyContainer->get('event.synchronization')->addDelete($this->id);
            }
        }
    }
}

