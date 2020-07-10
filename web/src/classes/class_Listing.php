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
# * FILE: /classes/class_listing.php
# ----------------------------------------------------------------------------------------------------

class Listing extends Handle
{
    public $id;
    public $account_id;
    public $image_id;
    public $cover_id;
    public $logo_id;
    public $location_1;
    public $location_2;
    public $location_3;
    public $location_4;
    public $location_5;
    public $renewal_date;
    public $discount_id;
    public $reminder;
    public $updated;
    public $entered;
    public $title;
    public $seo_title;
    public $claim_disable;
    public $friendly_url;
    public $email;
    public $url;
    public $display_url;
    public $address;
    public $address2;
    public $zip_code;
    public $phone;
    public $label_additional_phone;
    public $additional_phone;
    public $description;
    public $seo_description;
    public $long_description;
    public $video_snippet;
    public $video_url;
    public $video_description;
    public $keywords;
    public $seo_keywords;
    public $attachment_file;
    public $attachment_caption;
    public $features;
    public $price;
    public $social_network;
    public $status;
    public $level;
    public $locations;
    public $hours_work;
    public $listingtemplate_id;
    public $custom_text0;
    public $custom_text1;
    public $custom_text2;
    public $custom_text3;
    public $custom_text4;
    public $custom_text5;
    public $custom_text6;
    public $custom_text7;
    public $custom_text8;
    public $custom_text9;
    public $custom_short_desc0;
    public $custom_short_desc1;
    public $custom_short_desc2;
    public $custom_short_desc3;
    public $custom_short_desc4;
    public $custom_short_desc5;
    public $custom_short_desc6;
    public $custom_short_desc7;
    public $custom_short_desc8;
    public $custom_short_desc9;
    public $custom_long_desc0;
    public $custom_long_desc1;
    public $custom_long_desc2;
    public $custom_long_desc3;
    public $custom_long_desc4;
    public $custom_long_desc5;
    public $custom_long_desc6;
    public $custom_long_desc7;
    public $custom_long_desc8;
    public $custom_long_desc9;
    public $custom_checkbox0;
    public $custom_checkbox1;
    public $custom_checkbox2;
    public $custom_checkbox3;
    public $custom_checkbox4;
    public $custom_checkbox5;
    public $custom_checkbox6;
    public $custom_checkbox7;
    public $custom_checkbox8;
    public $custom_checkbox9;
    public $custom_dropdown0;
    public $custom_dropdown1;
    public $custom_dropdown2;
    public $custom_dropdown3;
    public $custom_dropdown4;
    public $custom_dropdown5;
    public $custom_dropdown6;
    public $custom_dropdown7;
    public $custom_dropdown8;
    public $custom_dropdown9;
    public $number_views;
    public $avg_review;
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
     *        $listingObj = new Listing($id);
     *        //OR
     *        $listingObj = new Listing($row);
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name Listing
     * @access Public
     * @param mixed $var
     */
    public function __construct($var = '', $domain_id = false)
    {
        if (is_numeric($var) && $var) {
            $dbMain = db_getDBObject(DEFAULT_DB, true);
            if ($domain_id) {
                $this->domain_id = $domain_id;
                $db = db_getDBObjectByDomainID($domain_id, $dbMain);
            } else {
                if (defined('SELECTED_DOMAIN_ID')) {
                    $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                } else {
                    $db = db_getDBObject();
                }
            }
            unset($dbMain);
            $sql = "SELECT * FROM Listing WHERE id = $var";

            $row = mysqli_fetch_array($db->query($sql));

            unset($db);

            $this->old_account_id = $row['account_id'];

            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = array();
            }
            $this->makeFromRow($var);
        }

        /* ModStores Hooks */
        HookFire("classlisting_contruct", [
            "that" => &$this
        ]);
    }

    /**
     * <code>
     *        $this->makeFromRow($row);
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name makeFromRow
     * @access Public
     * @param array $row
     */
    public function makeFromRow($row = '')
    {
        /* ModStores Hooks */
        HookFire("classlisting_before_makerow", [
            "that" => &$this,
            "row"  => &$row,
        ]);

        $status = new ItemStatus();
        $level = new ListingLevel();

        $this->id                    = $row['id']                        ?: ($this->id ?: 0);
        $this->account_id            = $row['account_id']                ?: 'NULL';
        $this->image_id              = $row['image_id']                  ?: ($this->image_id ?: 'NULL');
        $this->cover_id              = $row['cover_id']                  ?: ($this->cover_id ?: 'NULL');
        $this->logo_id                = $row['logo_id']                   ?: ($this->logo_id ?: 'NULL');
        $this->location_1            = $row['location_1']                ?: 0;
        $this->location_2            = $row['location_2']                ?: 0;
        $this->location_3            = $row['location_3']                ?: 0;
        $this->location_4            = $row['location_4']                ?: 0;
        $this->location_5            = $row['location_5']                ?: 0;
        $this->renewal_date          = $row['renewal_date']              ?: ($this->renewal_date ?: 0);
        $this->discount_id           = $row['discount_id']               ?: '';
        $this->reminder              = $row['reminder']                  ?: ($this->reminder ?: 0);
        $this->entered               = $row['entered']                   ?: ($this->entered ?: '');
        $this->updated               = $row['updated']                   ?: ($this->updated ?: '');
        $this->title                 = $row['title']                     ?: ($this->title ?: '');
        $this->seo_title             = $row['seo_title']                 ?: ($this->seo_title ?: '');
        $this->claim_disable         = $row['claim_disable']             ?: 'n';
        $this->friendly_url          = $row['friendly_url']              ?: '';
        $this->email                  = $row['email'] ?? $this->email     ?: '';
        $this->url                    = $row['url'] ?? $this->url         ?: '';
        $this->display_url           = LANG_VISIT_WEBSITE;
        $this->address               = $row['address']                   ?: '';
        $this->address2              = $row['address2']                  ?: '';
        $this->zip_code              = $row['zip_code']                  ?: '';
        $this->phone                  = $row['phone']                     ?? $this->phone ?: '';
        $this->label_additional_phone = $row['label_additional_phone']    ?? $this->label_additional_phone ?: '';
        $this->additional_phone       = $row['additional_phone']          ?? $this->additional_phone ?: '';
        $this->phone                  = $row['phone']                     ?? $this->phone ?: '';
        $this->description            = $row['description']               ?? $this->description ?: '';
        $this->seo_description        = $row['seo_description']           ?? $this->seo_description ?: '';
        $this->long_description       = $row['long_description']          ?? $this->long_description ?: '';
        $this->video_snippet          = $row['video_snippet']             ?? $this->video_snippet ?: '';
        $this->video_url              = $row['video_url']                 ?? $this->video_url ?: '';
        $this->video_description      = $row['video_description']         ?? $this->video_description ?: '';
        $this->keywords               = $row['keywords']                  ?? '';
        $this->seo_keywords           = $row['seo_keywords']              ?? $this->seo_keywords ?: '';
        $this->attachment_file        = $row['attachment_file']           ?? $this->attachment_file ?: '';
        $this->attachment_caption     = $row['attachment_caption']        ?? $this->attachment_caption ?: '';
        $this->features               = $row['features']                  ?? $this->features ?: '';
        $this->price                 = $row['price']                     ?: ($this->price ?: '0');
        $this->social_network         = $row['social_network']            ?? $this->social_network ?: '';
        $this->status                = $row['status']                    ?: $status->getDefaultStatus();
        $this->level                 = $row['level']                     ?: ($this->level ?: $level->getDefaultLevel());
        $this->hours_work             = $row['hours_work']                ?? $this->hours_work ?: '';
        $this->locations              = $row['locations']                 ?? $this->locations ?: '';
        $this->latitude              = $row['latitude']                  ?: '';
        $this->longitude             = $row['longitude']                 ?: '';
        $this->map_zoom              = is_numeric($row['map_zoom'])      ? $row['map_zoom'] : 0;
        $this->listingtemplate_id    = $row['listingtemplate_id']        ?: 'NULL';
        $this->custom_text0          = $row['custom_text0']              ?: '';
        $this->custom_text1          = $row['custom_text1']              ?: '';
        $this->custom_text2          = $row['custom_text2']              ?: '';
        $this->custom_text3          = $row['custom_text3']              ?: '';
        $this->custom_text4          = $row['custom_text4']              ?: '';
        $this->custom_text5          = $row['custom_text5']              ?: '';
        $this->custom_text6          = $row['custom_text6']              ?: '';
        $this->custom_text7          = $row['custom_text7']              ?: '';
        $this->custom_text8          = $row['custom_text8']              ?: '';
        $this->custom_text9          = $row['custom_text9']              ?: '';
        $this->custom_short_desc0    = $row['custom_short_desc0']        ?: '';
        $this->custom_short_desc1    = $row['custom_short_desc1']        ?: '';
        $this->custom_short_desc2    = $row['custom_short_desc2']        ?: '';
        $this->custom_short_desc3    = $row['custom_short_desc3']        ?: '';
        $this->custom_short_desc4    = $row['custom_short_desc4']        ?: '';
        $this->custom_short_desc5    = $row['custom_short_desc5']        ?: '';
        $this->custom_short_desc6    = $row['custom_short_desc6']        ?: '';
        $this->custom_short_desc7    = $row['custom_short_desc7']        ?: '';
        $this->custom_short_desc8    = $row['custom_short_desc8']        ?: '';
        $this->custom_short_desc9    = $row['custom_short_desc9']        ?: '';
        $this->custom_long_desc0     = $row['custom_long_desc0']         ?: '';
        $this->custom_long_desc1     = $row['custom_long_desc1']         ?: '';
        $this->custom_long_desc2     = $row['custom_long_desc2']         ?: '';
        $this->custom_long_desc3     = $row['custom_long_desc3']         ?: '';
        $this->custom_long_desc4     = $row['custom_long_desc4']         ?: '';
        $this->custom_long_desc5     = $row['custom_long_desc5']         ?: '';
        $this->custom_long_desc6     = $row['custom_long_desc6']         ?: '';
        $this->custom_long_desc7     = $row['custom_long_desc7']         ?: '';
        $this->custom_long_desc8     = $row['custom_long_desc8']         ?: '';
        $this->custom_long_desc9     = $row['custom_long_desc9']         ?: '';
        $this->custom_checkbox0      = $row['custom_checkbox0']          ?: 'n';
        $this->custom_checkbox1      = $row['custom_checkbox1']          ?: 'n';
        $this->custom_checkbox2      = $row['custom_checkbox2']          ?: 'n';
        $this->custom_checkbox3      = $row['custom_checkbox3']          ?: 'n';
        $this->custom_checkbox4      = $row['custom_checkbox4']          ?: 'n';
        $this->custom_checkbox5      = $row['custom_checkbox5']          ?: 'n';
        $this->custom_checkbox6      = $row['custom_checkbox6']          ?: 'n';
        $this->custom_checkbox7      = $row['custom_checkbox7']          ?: 'n';
        $this->custom_checkbox8      = $row['custom_checkbox8']          ?: 'n';
        $this->custom_checkbox9      = $row['custom_checkbox9']          ?: 'n';
        $this->custom_dropdown0      = $row['custom_dropdown0']          ?: '';
        $this->custom_dropdown1      = $row['custom_dropdown1']          ?: '';
        $this->custom_dropdown2      = $row['custom_dropdown2']          ?: '';
        $this->custom_dropdown3      = $row['custom_dropdown3']          ?: '';
        $this->custom_dropdown4      = $row['custom_dropdown4']          ?: '';
        $this->custom_dropdown5      = $row['custom_dropdown5']          ?: '';
        $this->custom_dropdown6      = $row['custom_dropdown6']          ?: '';
        $this->custom_dropdown7      = $row['custom_dropdown7']          ?: '';
        $this->custom_dropdown8      = $row['custom_dropdown8']          ?: '';
        $this->custom_dropdown9      = $row['custom_dropdown9']          ?: '';
        $this->number_views          = $row['number_views']              ?: ($this->number_views ?: 0);
        $this->avg_review            = $row['avg_review']                ?: ($this->avg_review ?: 0);
        $this->package_id            = $row['package_id']                ?: ($this->package_id ?: 0);
        $this->package_price         = $row['package_price']             ?: ($this->package_price ?: 0);

        $this->data_in_array = $row;

        //video_url added on v10.4. This will get the url for existing videos (iframe)
        if (!empty($this->video_snippet) && !$this->video_url) {
            $this->video_url = system_getVideoURL($this->video_snippet);
        }

        /* ModStores Hooks */
        HookFire("classlisting_after_makerow", [
            "that" => &$this,
            "row"  => &$row,
        ]);
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->Save();
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->Save();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name Save
     * @access Public
     */
    public function Save()
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);

        if ($this->domain_id) {
            $dbObj = db_getDBObjectByDomainID($this->domain_id, $dbMain);
        } else {
            if (defined('SELECTED_DOMAIN_ID')) {
                $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $dbObj = db_getDBObject();
            }
        }

        unset($dbMain);

        /* it checks if the social_network is already a json, if it's does not encode again */
        if (is_array($this->social_network)) {
            $this->social_network = count($this->social_network) > 0 ? json_encode($this->social_network) : null;
        }

        /* ModStores Hooks */
        HookFire("classlisting_before_preparesave", [
            "that" => &$this
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
        if ($this->image_id === "''") {
            $this->image_id = 'NULL';
        }

        if ($this->cover_id === "''") {
            $this->cover_id = 'NULL';
        }
        if ($this->logo_id === "''") {
            $this->logo_id = 'NULL';
        }
        if($this->account_id === "''") {
            $this->account_id = 'NULL';
        }
        if($this->listingtemplate_id === "''") {
            $this->listingtemplate_id = 'NULL';
        }

        if ($this->id) {

            $updateItem = true;

            $sql = 'UPDATE Listing SET'
                . " account_id            = $this->account_id,"
                . " image_id              = $this->image_id,"
                . " cover_id              = $this->cover_id,"
                . " logo_id               = $this->logo_id,"
                . " location_1            = $this->location_1,"
                . " location_2            = $this->location_2,"
                . " location_3            = $this->location_3,"
                . " location_4            = $this->location_4,"
                . " location_5            = $this->location_5,"
                . " renewal_date          = $this->renewal_date,"
                . " discount_id           = $this->discount_id,"
                . " reminder              = $this->reminder,"
                . ' updated               = NOW(),'
                . " title                 = $this->title,"
                . " seo_title             = $this->seo_title,"
                . " claim_disable         = $this->claim_disable,"
                . " friendly_url          = $this->friendly_url,"
                . " email                 = $this->email,"
                . " url                   = $this->url,"
                . " display_url           = $this->display_url,"
                . " address               = $this->address,"
                . " address2              = $this->address2,"
                . " zip_code              = $this->zip_code,"
                . " phone                 = $this->phone,"
                . " label_additional_phone = $this->label_additional_phone,"
                . " additional_phone      = $this->additional_phone,"
                . " description           = $this->description,"
                . " seo_description       = $this->seo_description,"
                . " long_description      = $this->long_description,"
                . " video_snippet         = $this->video_snippet,"
                . " video_url             = $this->video_url,"
                . " video_description     = $this->video_description,"
                . " keywords              = $this->keywords,"
                . " seo_keywords          = $this->seo_keywords,"
                . " attachment_file       = $this->attachment_file,"
                . " attachment_caption    = $this->attachment_caption,"
                . " features              = $this->features,"
                . " price                 = $this->price,"
                . " social_network        = $this->social_network,"
                . " status                = $this->status,"
                . " level                 = $this->level,"
                . " hours_work            = $this->hours_work,"
                . " locations             = $this->locations,"
                . " listingtemplate_id    = $this->listingtemplate_id,"
                . " custom_text0          = $this->custom_text0,"
                . " custom_text1          = $this->custom_text1,"
                . " custom_text2          = $this->custom_text2,"
                . " custom_text3          = $this->custom_text3,"
                . " custom_text4          = $this->custom_text4,"
                . " custom_text5          = $this->custom_text5,"
                . " custom_text6          = $this->custom_text6,"
                . " custom_text7          = $this->custom_text7,"
                . " custom_text8          = $this->custom_text8,"
                . " custom_text9          = $this->custom_text9,"
                . " custom_short_desc0    = $this->custom_short_desc0,"
                . " custom_short_desc1    = $this->custom_short_desc1,"
                . " custom_short_desc2    = $this->custom_short_desc2,"
                . " custom_short_desc3    = $this->custom_short_desc3,"
                . " custom_short_desc4    = $this->custom_short_desc4,"
                . " custom_short_desc5    = $this->custom_short_desc5,"
                . " custom_short_desc6    = $this->custom_short_desc6,"
                . " custom_short_desc7    = $this->custom_short_desc7,"
                . " custom_short_desc8    = $this->custom_short_desc8,"
                . " custom_short_desc9    = $this->custom_short_desc9,"
                . " custom_long_desc0     = $this->custom_long_desc0,"
                . " custom_long_desc1     = $this->custom_long_desc1,"
                . " custom_long_desc2     = $this->custom_long_desc2,"
                . " custom_long_desc3     = $this->custom_long_desc3,"
                . " custom_long_desc4     = $this->custom_long_desc4,"
                . " custom_long_desc5     = $this->custom_long_desc5,"
                . " custom_long_desc6     = $this->custom_long_desc6,"
                . " custom_long_desc7     = $this->custom_long_desc7,"
                . " custom_long_desc8     = $this->custom_long_desc8,"
                . " custom_long_desc9     = $this->custom_long_desc9,"
                . " custom_checkbox0      = $this->custom_checkbox0,"
                . " custom_checkbox1      = $this->custom_checkbox1,"
                . " custom_checkbox2      = $this->custom_checkbox2,"
                . " custom_checkbox3      = $this->custom_checkbox3,"
                . " custom_checkbox4      = $this->custom_checkbox4,"
                . " custom_checkbox5      = $this->custom_checkbox5,"
                . " custom_checkbox6      = $this->custom_checkbox6,"
                . " custom_checkbox7      = $this->custom_checkbox7,"
                . " custom_checkbox8      = $this->custom_checkbox8,"
                . " custom_checkbox9      = $this->custom_checkbox9,"
                . " custom_dropdown0      = $this->custom_dropdown0,"
                . " custom_dropdown1      = $this->custom_dropdown1,"
                . " custom_dropdown2      = $this->custom_dropdown2,"
                . " custom_dropdown3      = $this->custom_dropdown3,"
                . " custom_dropdown4      = $this->custom_dropdown4,"
                . " custom_dropdown5      = $this->custom_dropdown5,"
                . " custom_dropdown6      = $this->custom_dropdown6,"
                . " custom_dropdown7      = $this->custom_dropdown7,"
                . " custom_dropdown8      = $this->custom_dropdown8,"
                . " custom_dropdown9      = $this->custom_dropdown9,"
                . " number_views          = $this->number_views,"
                . " avg_review            = $this->avg_review,"
                . " latitude              = $this->latitude,"
                . " longitude             = $this->longitude,"
                . " map_zoom              = $this->map_zoom,"
                . " package_id            = $this->package_id,"
                . " package_price         = $this->package_price"
                . " WHERE id              = $this->id";

            /* ModStores Hooks */
            HookFire("classlisting_before_updatequery", [
                "that" => &$this,
                "sql"  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire("classlisting_after_updatequery", [
                "that"  => &$this,
            ]);

            if ($aux_old_account != $aux_account && $aux_account != 0) {
                $accDomain = new Account_Domain($aux_account, SELECTED_DOMAIN_ID);
                $accDomain->Save();
                $accDomain->saveOnDomain($aux_account, $this);
            }

        } else {
            $sql = 'INSERT INTO Listing'
                .' (account_id,'
                .' image_id,'
                .' cover_id,'
                .' logo_id,'
                .' location_1,'
                .' location_2,'
                .' location_3,'
                .' location_4,'
                .' location_5,'
                .' renewal_date,'
                .' discount_id,'
                .' reminder,'
                .' fulltextsearch_keyword,'
                .' fulltextsearch_where,'
                .' updated,'
                .' entered,'
                .' title,'
                .' seo_title,'
                .' claim_disable,'
                .' friendly_url,'
                .' email,'
                .' url,'
                .' display_url,'
                .' address,'
                .' address2,'
                .' zip_code,'
                .' phone,'
                .' label_additional_phone,'
                .' additional_phone,'
                .' description,'
                .' seo_description,'
                .' long_description,'
                .' video_snippet,'
                .' video_url,'
                .' video_description,'
                .' keywords,'
                .' seo_keywords,'
                .' attachment_file,'
                .' attachment_caption,'
                .' features,'
                .' price,'
                .' social_network,'
                .' status,'
                .' level,'
                .' hours_work,'
                .' locations,'
                .' listingtemplate_id,'
                .' custom_text0,'
                .' custom_text1,'
                .' custom_text2,'
                .' custom_text3,'
                .' custom_text4,'
                .' custom_text5,'
                .' custom_text6,'
                .' custom_text7,'
                .' custom_text8,'
                .' custom_text9,'
                .' custom_short_desc0,'
                .' custom_short_desc1,'
                .' custom_short_desc2,'
                .' custom_short_desc3,'
                .' custom_short_desc4,'
                .' custom_short_desc5,'
                .' custom_short_desc6,'
                .' custom_short_desc7,'
                .' custom_short_desc8,'
                .' custom_short_desc9,'
                .' custom_long_desc0,'
                .' custom_long_desc1,'
                .' custom_long_desc2,'
                .' custom_long_desc3,'
                .' custom_long_desc4,'
                .' custom_long_desc5,'
                .' custom_long_desc6,'
                .' custom_long_desc7,'
                .' custom_long_desc8,'
                .' custom_long_desc9,'
                .' custom_checkbox0,'
                .' custom_checkbox1,'
                .' custom_checkbox2,'
                .' custom_checkbox3,'
                .' custom_checkbox4,'
                .' custom_checkbox5,'
                .' custom_checkbox6,'
                .' custom_checkbox7,'
                .' custom_checkbox8,'
                .' custom_checkbox9,'
                .' custom_dropdown0,'
                .' custom_dropdown1,'
                .' custom_dropdown2,'
                .' custom_dropdown3,'
                .' custom_dropdown4,'
                .' custom_dropdown5,'
                .' custom_dropdown6,'
                .' custom_dropdown7,'
                .' custom_dropdown8,'
                .' custom_dropdown9,'
                .' number_views,'
                .' avg_review,'
                .' latitude,'
                .' longitude,'
                .' map_zoom,'
                .' package_id,'
                .' package_price,'
                .' last_traffic_sent)'
                .' VALUES'
                . " ($this->account_id,"
                . " $this->image_id,"
                . " $this->cover_id,"
                . " $this->logo_id,"
                . " $this->location_1,"
                . " $this->location_2,"
                . " $this->location_3,"
                . " $this->location_4,"
                . " $this->location_5,"
                . " $this->renewal_date,"
                . " $this->discount_id,"
                . " $this->reminder,"
                . " '',"
                . " '',"
                .' NOW(),'
                .' NOW(),'
                . " $this->title,"
                . " $this->seo_title,"
                . " $this->claim_disable,"
                . " $this->friendly_url,"
                . " $this->email,"
                . " $this->url,"
                . " $this->display_url,"
                . " $this->address,"
                . " $this->address2,"
                . " $this->zip_code,"
                . " $this->phone,"
                . " $this->label_additional_phone,"
                . " $this->additional_phone,"
                . " $this->description,"
                . " $this->seo_description,"
                . " $this->long_description,"
                . " $this->video_snippet,"
                . " $this->video_url,"
                . " $this->video_description,"
                . " $this->keywords,"
                . " $this->seo_keywords,"
                . " $this->attachment_file,"
                . " $this->attachment_caption,"
                . " $this->features,"
                . " $this->price,"
                . " $this->social_network,"
                . " $this->status,"
                . " $this->level,"
                . " $this->hours_work,"
                . " $this->locations,"
                . " $this->listingtemplate_id,"
                . " $this->custom_text0,"
                . " $this->custom_text1,"
                . " $this->custom_text2,"
                . " $this->custom_text3,"
                . " $this->custom_text4,"
                . " $this->custom_text5,"
                . " $this->custom_text6,"
                . " $this->custom_text7,"
                . " $this->custom_text8,"
                . " $this->custom_text9,"
                . " $this->custom_short_desc0,"
                . " $this->custom_short_desc1,"
                . " $this->custom_short_desc2,"
                . " $this->custom_short_desc3,"
                . " $this->custom_short_desc4,"
                . " $this->custom_short_desc5,"
                . " $this->custom_short_desc6,"
                . " $this->custom_short_desc7,"
                . " $this->custom_short_desc8,"
                . " $this->custom_short_desc9,"
                . " $this->custom_long_desc0,"
                . " $this->custom_long_desc1,"
                . " $this->custom_long_desc2,"
                . " $this->custom_long_desc3,"
                . " $this->custom_long_desc4,"
                . " $this->custom_long_desc5,"
                . " $this->custom_long_desc6,"
                . " $this->custom_long_desc7,"
                . " $this->custom_long_desc8,"
                . " $this->custom_long_desc9,"
                . " $this->custom_checkbox0,"
                . " $this->custom_checkbox1,"
                . " $this->custom_checkbox2,"
                . " $this->custom_checkbox3,"
                . " $this->custom_checkbox4,"
                . " $this->custom_checkbox5,"
                . " $this->custom_checkbox6,"
                . " $this->custom_checkbox7,"
                . " $this->custom_checkbox8,"
                . " $this->custom_checkbox9,"
                . " $this->custom_dropdown0,"
                . " $this->custom_dropdown1,"
                . " $this->custom_dropdown2,"
                . " $this->custom_dropdown3,"
                . " $this->custom_dropdown4,"
                . " $this->custom_dropdown5,"
                . " $this->custom_dropdown6,"
                . " $this->custom_dropdown7,"
                . " $this->custom_dropdown8,"
                . " $this->custom_dropdown9,"
                . " $this->number_views,"
                . " $this->avg_review,"
                . " $this->latitude,"
                . " $this->longitude,"
                . " $this->map_zoom,"
                . " $this->package_id,"
                . " $this->package_price,"
                .' NOW())';

            /* ModStores Hooks */
            HookFire("classlisting_before_insertquery", [
                "that" => &$this,
                "sql"  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire("classlisting_after_insertquery", [
                "that"  => &$this,
                "dbObj" => &$dbObj,
            ]);


            $this->id = (($___mysqli_res = mysqli_insert_id($dbObj->link_id)) === null ? false : $___mysqli_res);

            /*
             * Used to package
             */
            $this->prepareToUse(); //prevent some fields to be saved with empty quotes

            //Reload the Listing object variables

            $sql = "SELECT * FROM Listing WHERE id = $this->id";
            $row = mysqli_fetch_array($dbObj->query($sql));
            $this->makeFromRow($row);
            $this->prepareToSave();

            if ($aux_account != 0) {
                domain_SaveAccountInfoDomain($aux_account, $this);
            }

        }

        if ((sess_getAccountIdFromSession() && string_strpos($_SERVER['PHP_SELF'],
                    'listing.php') !== false) || string_strpos($_SERVER['PHP_SELF'], 'order_') !== false
        ) {
            $rowTimeline = array();
            $rowTimeline['item_type'] = 'listing';
            $rowTimeline['action'] = ($updateItem ? 'edit' : 'new');
            $rowTimeline['item_id'] = str_replace("'", '', $this->id);
            $timelineObj = new Timeline($rowTimeline);
            $timelineObj->Save();
        }

        /* ModStores Hooks */
        HookFire("classlisting_before_prepareuse", [
            "that" => &$this,
        ]);

        $this->prepareToUse();

        $this->setFullTextSearch();

        /* ModStores Hooks */
        HookFire("classlisting_after_save", [
            "that" => &$this,
        ]);
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->Delete();
     * <code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name Delete
     * @access Public
     */
    public function Delete($domain_id = false)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if ($domain_id) {
            $dbObj = db_getDBObjectByDomainID($domain_id, $dbMain);
            $domain_extra_file_dir = EDIRECTORY_ROOT . "/custom/domain_$domain_id/extra_files/";
        } else {
            if (defined('SELECTED_DOMAIN_ID')) {
                $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $dbObj = db_getDBObject();
            }
            $domain_extra_file_dir = EXTRAFILE_DIR;
            unset($dbMain);
        }

        ### REVIEWS
        $sql = "SELECT id FROM Review WHERE item_type='listing' AND item_id= $this->id";
        $result = $dbObj->query($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $reviewObj = new Review($row['id']);
            $reviewObj->Delete($domain_id);
        }

        ### LISTING_CATEOGRY
        $sql = "DELETE FROM Listing_Category WHERE listing_id = $this->id";
        $dbObj->query($sql);

        ### CHOICES
        $sql = "DELETE FROM Listing_Choice WHERE listing_id = $this->id";
        $dbObj->query($sql);

        ### GALLERY
        //before deleting the gallery, it needs to clear listing image ids
        $sql = "UPDATE Listing SET image_id = NULL, cover_id = NULL, logo_id = NULL WHERE id = $this->id";
        $dbObj->query($sql);

        $sql = "SELECT gallery_id FROM Gallery_Item WHERE item_id = $this->id AND item_type = 'listing'";
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
        if (is_numeric($this->logo_id)) {
            $image = new Image($this->logo_id);
            if ($image) {
                $image->Delete($domain_id);
            }
        }

        ### ATTACHMENT
        if ($this->attachment_file) {
            if (file_exists($domain_extra_file_dir .'/'. $this->attachment_file)) {
                @unlink($domain_extra_file_dir .'/'. $this->attachment_file);
            }
        }

        ### INVOICE
        $sql = "UPDATE Invoice_Listing SET listing_id = '0' WHERE listing_id = $this->id";
        $dbObj->query($sql);

        ### PAYMENT
        $sql = "UPDATE Payment_Listing_Log SET listing_id = '0' WHERE listing_id = $this->id";
        $dbObj->query($sql);

        ### CLAIM
        $sql = "UPDATE Claim SET status = 'incomplete' WHERE listing_id = $this->id AND status = 'progress'";
        $dbObj->query($sql);
        $sql = "UPDATE Claim SET listing_id = '0' WHERE listing_id = $this->id";
        $dbObj->query($sql);

        ### Promotion
        $sql = "UPDATE Promotion SET    fulltextsearch_where = '',
                                            listing_id = NULL,
                                            listing_status = '',
                                            listing_level = 0,
                                            listing_location1 = 0,
                                            listing_location2 = 0,
                                            listing_location3 = 0,
                                            listing_location4 = 0,
                                            listing_location5 = 0,
                                            listing_address = '',
                                            listing_address2 = '',
                                            listing_zipcode = '',
                                            listing_latitude = '',
                                            listing_longitude = ''
                   WHERE listing_id = $this->id";
        $dbObj->query($sql);

        ### Classified
        $sql = "UPDATE Classified SET listing_id = NULL WHERE listing_id = $this->id";
        $dbObj->query($sql);

        ### Timeline
        $sql = "DELETE FROM Timeline WHERE (item_type = 'listing' or item_type = 'claim') AND item_id = $this->id";
        $dbObj->query($sql);

        ### Quicklist (Favorites)
        $sql = "DELETE FROM Quicklist WHERE item_type = 'listing' AND item_id = $this->id";
        $dbObj->query($sql);

        /* ModStores Hooks */
        HookFire("classlisting_before_delete", [
            "that" => &$this
        ]);

        ### LISTING
        $sql = "DELETE FROM Listing WHERE id = $this->id";
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get('listing.synchronization')->addDelete($this->id);
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->getCategories(...);
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->getCategories(...);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @return array|bool
     * @access Public
     */
    public function getCategories() {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);

        $sql_main = 'SELECT listing_category.category_id
                                FROM Listing_Category listing_category
                                INNER JOIN ListingCategory category ON category.id = listing_category.category_id
                                WHERE listing_category.listing_id = '. $this->id;

        $result_main = $dbObj->query($sql_main, MYSQLI_USE_RESULT);

        if ($result_main) {

            $aux_array_categories = array();
            while ($row = mysqli_fetch_assoc($result_main)) {
                $aux_array_categories[] = $row['category_id'];
            }
            mysqli_free_result($result_main);

            if (count($aux_array_categories) > 0) {
                $sql = 'SELECT    id,
                                    title,
                                    page_title,
                                    friendly_url,
                                    enabled,
                                    category_id
                                FROM ListingCategory
                                WHERE id IN ('. implode(',', $aux_array_categories) .')';

                $result = $dbObj->query($sql);

                if ($result) {
                    $categories = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $categories[] = new ListingCategory($row);
                    }

                    if (count($categories) > 0) {
                        return $categories;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $classifiedsId
     * @return void
     * @throws Exception
     */
    public function savesAssociationClassifieds($classifiedsId)
    {
        if (!$this->id) {
            throw new \Exception('You must have a listing setted in the object.');
        }

        $classified = new Classified();
        $classified->cleanListingAssociation($this);
        for ($i = 0, $iMax = count($classifiedsId); $i < $iMax; $i++) {
            $classified->setListingAssociation(new Classified($classifiedsId[$i]), $this);
        }
    }

    /**
     * Get listings for the given account. If account is 0, this function will return all listing without account.
     * Listing for the given account already that are already binded with a classified will appear first in the resultset.
     *
     * @param int $accountId
     * @param int $classifiedId
     * @return array
     */
    public static function getListingBySitemgrRulesUsingClassified($accountId, $classifiedId = 0)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        /**
         * Get level with classified
         */
        $levelObj = new ListingLevel();
        $levels   = $levelObj->getValues();
        $classifiedLevels = [];
        foreach ( $levels as $level )
        {
            if ( $levelObj->getClassifiedQuantityAssociation( $level ) > 0)
            {
                $classifiedLevels[] = $level;
            }
        }

        if(count($classifiedLevels) == 0){
            return [];
        }

        $classifiedLevels = implode(',', $classifiedLevels);

        $where = sprintf(' level IN (%s) ', $classifiedLevels);

        if ((int)$accountId > 0) {
            // with account
            $where .= ' AND account_id = '.$accountId;
        } else {
            $where .= ' AND (account_id = 0 OR account_id IS NULL) ';
        }

        /* the limit in SQL is linked with the limit of the plugin used, improve it */
        $sql = "SELECT id, title
                FROM Listing
                WHERE {$where}
                ORDER BY title
                LIMIT 1000";

        if($classifiedId) {
            $union = sprintf('SELECT l.id, l.title
                      FROM Classified c
                      INNER JOIN Listing l ON l.id = c.listing_id
                      WHERE c.id = %d', $classifiedId);

            $sql = sprintf('(%s) UNION (%s)', $union, $sql);
        }

        $result = $dbObj->query($sql);

        $array = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $array[] = $row;
            }
        }

        return $array;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->setCategories($categories);
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->setCategories($categories);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name setCategories
     * @access Public
     * @param array $array
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

        if ($this->id) {
            $sql = 'DELETE FROM Listing_Category WHERE listing_id = '. $this->id;
            $dbObj->query($sql);

            if ($array) {
                foreach ($array as $category) {
                    if ($category) {
                        unset($l_catObj);
                        $l_catObj = new Listing_Category();
                        $l_catObj->setNumber('listing_id', $this->id);
                        $l_catObj->setNumber('category_id', $category);
                        $l_catObj->Save();
                    }
                }
            }

            $this->setFullTextSearch();
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->getPrice();
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->getPrice();
     * </code>
     * @param string $renewal_period
     * @param bool $applyDiscount
     * @return double $price
     * @access Public
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     */
    public function getPrice($renewal_period = '', $applyDiscount = true)
    {

        /*
         * Fix to normalize variable standard. It should be monthly or yearly, but some places are sending it as M or Y.
         * Sorry.
         */
        if ($renewal_period === 'M') {
            $renewal_period = 'monthly';
        } elseif ($renewal_period === 'Y') {
            $renewal_period = 'yearly';
        }
        $price = 0;

        $dbMain = db_getDBObject(DEFAULT_DB, true);

        if ($this->domain_id) {
            $dbObj = db_getDBObjectByDomainID($this->domain_id, $dbMain);
        } else {
            if (defined('SELECTED_DOMAIN_ID')) {
                $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
            } else {
                $dbObj = db_getDBObject();
            }
        }

        unset($dbMain);

        /*
             * Check if have price by package
             */
        $levelObj = new ListingLevel();

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
            $price += $levelObj->getPrice($this->level, ($renewal_period === 'monthly' ? '' : $renewal_period));
        }

        $sql = 'SELECT COUNT(listing_id) AS total FROM Listing_Category WHERE listing_id = '. $this->id;
        $result = $dbObj->query($sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $category_amount = $row['total'];
        }

        if ($this->categories && !$this->id) {
            $category_amount = $this->categories;
        }

        if (($category_amount > 0) && (($category_amount - $levelObj->getFreeCategory($this->level)) > 0)) {
            $extra_category_amount = $category_amount - $levelObj->getFreeCategory($this->level);
        } else {
            $extra_category_amount = 0;
        }

        $yearlyPrice = (float)$levelObj->getPrice($this->level, 'yearly');
        $monthlyPrice = (float)$levelObj->getPrice($this->level, '');

        if ($extra_category_amount > 0) {
            if ($renewal_period === 'yearly' && !empty($yearlyPrice) && !empty($monthlyPrice)) {
                $price += (($levelObj->getCategoryPrice($this->level) * $extra_category_amount) * ($yearlyPrice / $monthlyPrice));
            } else {
                $price += ($levelObj->getCategoryPrice($this->level) * $extra_category_amount);
            }
        }

        if (LISTINGTEMPLATE_FEATURE === 'on' && CUSTOM_LISTINGTEMPLATE_FEATURE === 'on') {
            if ($this->listingtemplate_id) {
                $listingTemplateObj = new ListingTemplate($this->listingtemplate_id);
                if ($listingTemplateObj->getString('status') === 'enabled') {
                    if ($renewal_period === 'yearly' && !empty($yearlyPrice) && !empty($monthlyPrice)) {
                        $price += (((float)$listingTemplateObj->getString('price')) * ($yearlyPrice / $monthlyPrice));
                    } else {
                        $price += (float)$listingTemplateObj->getString('price');
                    }
                } else {
                    $sql = 'UPDATE Listing SET listingtemplate_id = NULL WHERE id = '. $this->id;
                    $dbObj->query($sql);
                }
            }
        }

        if ($this->discount_id && $applyDiscount) {

            $discountCodeObj = new DiscountCode($this->discount_id);

            if (is_valid_discount_code($this->discount_id, 'listing', $this->id, $discount_message, $discount_error)) {

                if ($discountCodeObj->getString('id') && $discountCodeObj->expire_date >= date('Y-m-d')) {

                    if ($discountCodeObj->getString('type') === 'percentage') {
                        $price *= (1 - $discountCodeObj->getString('amount') / 100);
                    } elseif ($discountCodeObj->getString('type') === 'monetary value') {
                        $price -= $discountCodeObj->getString('amount');
                    }

                }

            } else {
                $sql = "UPDATE Listing SET discount_id = '' WHERE id = " . $this->id;
                $dbObj->query($sql);
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
     *        $listingObj->hasRenewalDate();
     * <br /><br />
     *        //Using this in Listing() class.
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
        if (PAYMENT_FEATURE !== 'on') {
            return false;
        }
        if ((CREDITCARDPAYMENT_FEATURE !== 'on') && (PAYMENT_INVOICE_STATUS !== 'on') && (PAYMENT_MANUAL_STATUS !== 'on')) {
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
     *        $listingObj->needToCheckOut();
     * <br /><br />
     *        //Using this in Listing() class.
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

            if (($this->status === 'E') || ($this_renewaldate === '0000-00-00') || ($timestamp_today > $timestamp_renewaldate)) {
                return true;
            }

        }

        return false;

    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->getNextRenewalDate($times);
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->getNextRenewalDate($times);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param integer $times
     * @param string $renewalunit
     * @return date|false|string
     * @access Public
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

            if ($renewalunit === 'Y') {
                $nextrenewaldate = date('Y-m-d',
                    mktime(0, 0, 0, (int)$start_month, (int)$start_day, (int)$start_year + ($renewalcycle * $times)));
            } elseif ($renewalunit === 'M') {
                $nextrenewaldate = date('Y-m-d',
                    mktime(0, 0, 0, (int)$start_month + ($renewalcycle * $times), (int)$start_day, (int)$start_year));
            } elseif ($renewalunit === 'D') {
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
     *        $listingObj->setLocationManager($locationManager);
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->setLocationManager($locationManager);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param mixed &$locationManager
     * @access Public
     */
    public function setLocationManager(&$locationManager)
    {
        $this->locationManager =& $locationManager;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->getLocationManager();
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->getLocationManager();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getLocationManager
     * @access Public
     * @return mixed &$this->locationManager
     */
    public function &getLocationManager()
    {
        return $this->locationManager; /* NEVER auto-instantiate this*/
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->getLocationString(...);
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->getLocationString(...);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param varchar $format
     * @param boolean $forceManagerCreation
     * @param bool $lineBreak
     * @return string locationString
     * @access Public
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
     *        $listingObj->setFullTextSearch();
     * <br /><br />
     *        //Using this in Listing() class.
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
            $fulltextsearch_keyword[] = $this->title;
            $addkeyword = format_addApostWords($this->title);
            if ($addkeyword) {
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

        if ($this->zip_code) {
            $fulltextsearch_where[] = $this->zip_code;
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
                while (null !== $category_id && $category_id != 0) {
                    $sql = "SELECT * FROM ListingCategory WHERE id = $category_id";
                    $result = $dbObj->query($sql);
                    if (mysqli_num_rows($result) > 0) {
                        $category_info = mysqli_fetch_assoc($result);

                        if ($category_info['enabled'] === 'y') {
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
        HookFire("classlisting_before_update_fulltextkeyword", [
            "that"                   => &$this,
            "fulltextsearch_keyword" => &$fulltextsearch_keyword
        ]);

        if (is_array($fulltextsearch_keyword)) {
            $fulltextsearch_keyword_sql = db_formatString(implode(' ', $fulltextsearch_keyword));
            $sql = "UPDATE Listing SET fulltextsearch_keyword = $fulltextsearch_keyword_sql WHERE id = $this->id";
            $result = $dbObj->query($sql);

        }

        /* ModStores Hooks */
        HookFire("classlisting_before_update_fulltextwhere", [
            "that"                 => &$this,
            "fulltextsearch_where" => &$fulltextsearch_where
        ]);

        if (is_array($fulltextsearch_where)) {
            $fulltextsearch_where_sql = db_formatString(implode(' ', $fulltextsearch_where));
            $sql = "UPDATE Listing SET fulltextsearch_where = $fulltextsearch_where_sql WHERE id = $this->id";
            $dbObj->query($sql);

            $sql = "UPDATE Promotion SET fulltextsearch_where = $fulltextsearch_where_sql WHERE listing_id = $this->id";
            $dbObj->query($sql);
        }

        $this->synchronize();
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->getGalleries();
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->getGalleries();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name getGalleries
     * @access Public
     * @return array $galleries
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
        $sql = "SELECT * FROM Gallery_Item WHERE item_type='listing' AND item_id = $this->id ORDER BY gallery_id";
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
     *        $listingObj->setGalleries($gallery);
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->setGalleries($gallery);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param bool $gallery
     * @access Public
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
        $sql = "DELETE FROM Gallery_Item WHERE item_type='listing' AND item_id = $this->id";
        $dbObj->query($sql);

        if ($gallery) {
            $sql = "INSERT INTO Gallery_Item (item_id, gallery_id, item_type) VALUES ($this->id, $gallery, 'listing')";
            $rs3 = $dbObj->query($sql);
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->setNumberViews($id);
     * <br /><br />
     *        //Using this in Listing() class.
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
        $sql = 'UPDATE Listing SET number_views = '. $this->number_views .' + 1 WHERE Listing.id = '. $id;
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get('listing.synchronization')->addViewUpdate($id);
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->setAvgReview(...);
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->setAvgReview(...);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param integer $avg
     * @param integer $id
     * @access Public
     */
    public function setAvgReview($avg, $id)
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }

        unset($dbMain);
        $sql = 'UPDATE Listing SET avg_review = '. $avg .' WHERE Listing.id = '. $id;
        $dbObj->query($sql);

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get('listing.synchronization')->addAverageReviewUpdate($id, $avg);
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->hasDetail();
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->hasDetail();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name hasDetail
     * @access Public
     * @return mixed $detail
     */
    public function hasDetail()
    {
        $listingLevel = new ListingLevel();
        $detail = $listingLevel->getDetail($this->level);
        unset($listingLevel);

        return $detail;
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->deletePerAccount($account_id);
     * <br /><br />
     *        //Using this in Listing() class.
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
            $sql = "SELECT * FROM Listing WHERE account_id = $account_id";
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
     *        $listingObj->removePromotionLinks();
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->removePromotionLinks();
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @name removePromotionLinks
     * @access Public
     * @return bool
     */
    public function removePromotionLinks()
    {
        if (!$this->id) {
            return false;
        }

        $promotionObj = new Promotion();
        if ($deals = $promotionObj->getMultipleDealByListing($this->id)){
            $deals = array_map(function ($deal){
                return $deal['id'];
            }, $deals);

            $promotionObj->unLinkPromotionListing($deals, $this->id);

            $this->synchronize();
            //synchronize deals unlinked
            if ($symfonyContainer = SymfonyCore::getContainer()) {
                foreach ($deals as $dealId) {
                    $symfonyContainer->get('deal.synchronization')->addDelete($dealId);
                }
            }
        }
    }

    /**
     * <code>
     *        //Using this in forms or other pages.
     *        $listingObj->getListingByFriendlyURL($friendly_url);
     * <br /><br />
     *        //Using this in Listing() class.
     *        $this->getListingByFriendlyURL($friendly_url);
     * </code>
     * @copyright Copyright 2018 Arca Solutions, Inc.
     * @author Arca Solutions, Inc.
     * @version 8.0.00
     * @param string $friendly_url
     * @return bool
     * @access Public
     */
    public function getListingByFriendlyURL($friendly_url)
    {
        $dbObj = db_getDBObject();
        $sql = "SELECT * FROM Listing WHERE friendly_url = '" . $friendly_url . "'";
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
            $promotionObj = new Promotion();
            $thisListingPromotions = $promotionObj->getPromotionsOfListing($this->id);
            if($this->status === 'A'){
                $symfonyContainer->get('listing.synchronization')->addUpsert($this->id);
                while($row = mysqli_fetch_assoc( $thisListingPromotions )){
                    $symfonyContainer->get('deal.synchronization')->addUpsert($row['id']);
                }
            } else {
                $symfonyContainer->get('listing.synchronization')->addDelete($this->id);
                while($row = mysqli_fetch_assoc( $thisListingPromotions )){
                    $symfonyContainer->get('deal.synchronization')->addDelete($row['id']);
                }
            }
        }
    }

    public function getListingDetail($listing_id)
    {
        if (!$listing_id){ return false; }
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $db = db_getDBObject();
        }
        unset($dbMain);

        $sql = "SELECT * FROM Listing WHERE id = '".$listing_id."'";
        $result = $db->query($sql);

        return mysqli_fetch_assoc($result);
    }

    public function getLevel($listing_id)
    {
        if (!$listing_id){ return false; }
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $db = db_getDBObject();
        }
        unset($dbMain);

        $sql = "SELECT level FROM Listing WHERE id = '".$listing_id."'";
        $result = $db->query($sql);
        $row = mysqli_fetch_assoc($result);

        return $row['level'];
    }

    public function countDeals($listing_id)
    {
        if (!$listing_id){ return false; }
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $db = db_getDBObject();
        }
        unset($dbMain);

        $sql = "SELECT COUNT(*) FROM Promotion WHERE listing_id = '".$listing_id."'";
        $result = $db->query($sql);
        $row = mysqli_fetch_row($result);
        return $row[0];
    }


    public function thisPromoIsThisListing($promo_id,$listing_id)
    {
        if (!$listing_id) {
            return false;
        }

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $db = db_getDBObject();
        }
        unset($dbMain);

        $sql = "SELECT * FROM Promotion WHERE id = '".$promo_id."'";
        $result = $db->query($sql);
        $row = mysqli_fetch_assoc($result);

        return $row['listing_id'] == $listing_id;
    }

    public function hasCategoriesAvailable() {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        if (defined('SELECTED_DOMAIN_ID')) {
            $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
        } else {
            $dbObj = db_getDBObject();
        }
        unset($dbMain);

        $levelObj = new ListingLevel();

        $sql = 'SELECT COUNT(listing_id) AS total FROM Listing_Category WHERE listing_id = '. $this->id;
        $result = $dbObj->query($sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $category_amount = $row['total'];
        }

        if ($this->categories && !$this->id) {
            $category_amount = $this->categories;
        }

        if (!$category_amount) {
            return true;
        }

        if ($levelObj->getFreeCategory($this->level) - $category_amount > 0) {
            return true;
        }

        return false;
    }

    public static function enableCategorySelection($listing, $url_base, $blockPaidListing = false)
    {
        /*
         * Scenarios where the listing categories can be changed
         * 0) Payment gateway disabled: in case the payment feature is disabled, categories can always be changed
         * 1) Sitemgr edition: if the listing is being edited by the sitemgr, the categories can always be changed
         * 2) New listings: while creating new listings, both listing owner and sitemgr can change the categories whenever they want
         * 3) Non paid listings: existing listing that needs to be paid (both listing owner and sitemgr can change the categories whenever they want)
         * 4) Free listings without additional categories: both listing owner and sitemgr can change the categories whenever they want
         * 5) For sponsors, paid listings that still have available categories to be added can also be change at any time
         */

        /*
         * Scenario 0
         */
        if (PAYMENT_FEATURE === 'off') {
            return true;
        }

        /*
         * Scenario 1
         */
        if (string_strpos($url_base, '/'.SITEMGR_ALIAS.'')) {
            return true;
        }

        /*
         * Scenario 2
         */
        if (!$listing || !$listing->getNumber('id')) {
            return true;
        }

        /*
         * Scenario 3
         */
        if ($listing && $listing->needToCheckOut()) {
            return true;
        }

        /*
         * Scenario 4
         */
        if ($listing && ($listing->getPrice('monthly') <= 0 && $listing->getPrice('yearly') <= 0)) {
            return true;
        }

        /*
         * Scenario 5
         */
        if (string_strpos($url_base, '/'.MEMBERS_ALIAS.'') && $listing->hasCategoriesAvailable() && !$blockPaidListing) {
            return true;
        }

        return false;
    }
}
