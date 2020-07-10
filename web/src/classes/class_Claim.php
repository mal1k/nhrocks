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
	# * FILE: /classes/class_claim.php
	# ----------------------------------------------------------------------------------------------------

	class Claim extends Handle {

		public $id;
		public $account_id;
		public $username;
		public $listing_id;
		public $listing_title;
		public $date_time;
		public $step;
		public $status;
		public $old_location_1;
		public $new_location_1;
		public $old_location_2;
		public $new_location_2;
		public $old_location_3;
		public $new_location_3;
		public $old_location_4;
		public $new_location_4;
		public $old_location_5;
		public $new_location_5;
		public $old_title;
		public $new_title;
		public $old_friendly_url;
		public $new_friendly_url;
		public $old_email;
		public $new_email;
		public $old_url;
		public $new_url;
		public $old_phone;
		public $new_phone;
        public $old_label_additional_phone;
        public $new_label_additional_phone;
        public $old_additional_phone;
        public $new_additional_phone;
		public $old_address;
		public $new_address;
		public $old_address2;
		public $new_address2;
		public $old_zip_code;
		public $new_zip_code;
		public $old_level;
		public $new_level;
		public $old_listingtemplate_id;
        public $new_listingtemplate_id;
        public $old_description;
        public $new_description;
        public $old_long_description;
        public $new_long_description;
        public $old_keywords;
        public $new_keywords;
        public $old_locations;
        public $new_locations;
        public $old_features;
        public $new_features;
        public $old_hours_work;
        public $new_hours_work;
        public $old_seo_title;
        public $new_seo_title;
        public $old_seo_keywords;
        public $new_seo_keywords;
        public $old_seo_description;
        public $new_seo_description;
        public $old_social_network;
        public $new_social_network;
        public $old_latitude;
        public $new_latitude;
        public $old_longitude;
        public $new_longitude;
        public $old_categories;
        public $new_categories;
        public $old_additional_fields;
        public $new_additional_fields;

        public function __construct($var='') {
			if (is_numeric($var) && $var) {
				$dbMain = db_getDBObject(DEFAULT_DB, true);
				if (defined('SELECTED_DOMAIN_ID')) {
					$db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
				} else {
					$db = db_getDBObject();
				}
				unset($dbMain);
				$sql = "SELECT * FROM Claim WHERE id = $var";
				$row = mysqli_fetch_array($db->query($sql));
				$this->makeFromRow($row);
			} else {
                if (!is_array($var)) {
                    $var = array();
                }
				$this->makeFromRow($var);
			}

            /* ModStores Hooks */
            HookFire("classclaim_contruct", [
                "that" => &$this
            ]);
		}

		public function makeFromRow($row = []) {

			$this->id						= $row['id']                     ?: ($this->id						    ?: 0);
			$this->account_id				= $row['account_id']             ?: ($this->account_id				    ?: 0);
			$this->username					= $row['username']               ?: ($this->username					?: '');
			$this->listing_id				= $row['listing_id']             ?: ($this->listing_id				    ?: 0);
			$this->listing_title			= $row['listing_title']          ?: ($this->listing_title				?: '');
			$this->date_time				= $row['date_time']              ?: '';
			$this->step						= $row['step']                   ?: '';
			$this->status					= $row['status']                 ?: '';
			$this->old_location_1			= $row['old_location_1']         ?: ($this->old_location_1			    ?: 0);
			$this->new_location_1			= $row['new_location_1']         ?: 0;
			$this->old_location_2			= $row['old_location_2']         ?: ($this->old_location_2			    ?: 0);
			$this->new_location_2			= $row['new_location_2']         ?: 0;
			$this->old_location_3			= $row['old_location_3']         ?: ($this->old_location_3			    ?: 0);
			$this->new_location_3			= $row['new_location_3']         ?: 0;
			$this->old_location_4			= $row['old_location_4']         ?: ($this->old_location_4			    ?: 0);
			$this->new_location_4			= $row['new_location_4']         ?: 0;
			$this->old_location_5			= $row['old_location_5']         ?: ($this->old_location_5			    ?: 0);
			$this->new_location_5			= $row['new_location_5']         ?: 0;
			$this->old_title				= $row['old_title']              ?: ($this->old_title					?: '');
			$this->new_title				= $row['new_title']              ?: '';
			$this->old_friendly_url			= $row['old_friendly_url']       ?: ($this->old_friendly_url			?: '');
			$this->new_friendly_url			= $row['new_friendly_url']       ?: '';
			$this->old_email				= $row['old_email']              ?: ($this->old_email					?: '');
			$this->new_email				= $row['new_email']              ?: '';
			$this->old_url					= $row['old_url']                ?: ($this->old_url					    ?: '');
			$this->new_url					= $row['new_url']                ?: '';
			$this->old_phone				= $row['old_phone']              ?: ($this->old_phone					?: '');
			$this->new_phone				= $row['new_phone']              ?: '';
            $this->old_label_additional_phone = $row['old_label_additional_phone'] ?: ($this->old_label_additional_phone ?: '');
            $this->new_label_additional_phone = $row['new_label_additional_phone'] ?: '';
            $this->old_additional_phone = $row['old_additional_phone'] ?: ($this->old_additional_phone ?: '');
            $this->new_additional_phone = $row['new_additional_phone'] ?: '';
			$this->old_address				= $row['old_address']            ?: ($this->old_address				    ?: '');
			$this->new_address				= $row['new_address']            ?: '';
			$this->old_address2				= $row['old_address2']           ?: ($this->old_address2				?: '');
			$this->new_address2				= $row['new_address2']           ?: '';
			$this->old_zip_code				= $row['old_zip_code']           ?: ($this->old_zip_code				?: '');
			$this->new_zip_code				= $row['new_zip_code']           ?: '';
			$this->old_level				= $row['old_level']              ?: ($this->old_level					?: 0);
			$this->new_level				= $row['new_level']              ?: 0;
			$this->old_listingtemplate_id	= $row['old_listingtemplate_id'] ?: ($this->old_listingtemplate_id	    ?: 0);
			$this->new_listingtemplate_id	= $row['new_listingtemplate_id'] ?: 0;
            $this->old_description		    = $row['old_description']        ?: ($this->old_description					?: '');
            $this->new_description			= $row['new_description']        ?: '';
            $this->old_long_description		= $row['old_long_description']   ?: ($this->old_long_description					?: '');
            $this->new_long_description		= $row['new_long_description']   ?: '';
            $this->old_keywords				= $row['old_keywords']           ?: ($this->old_keywords					?: '');
            $this->new_keywords				= $row['new_keywords']           ?: '';
            $this->old_locations			= $row['old_locations']          ?: ($this->old_locations					?: '');
            $this->new_locations			= $row['new_locations']          ?: '';
            $this->old_features				= $row['old_features']           ?: ($this->old_features					?: '');
            $this->new_features				= $row['new_features']           ?: '';
            $this->old_hours_work			= $row['old_hours_work']         ?: ($this->old_hours_work					?: '');
            $this->new_hours_work			= $row['new_hours_work']         ?: '';
            $this->old_seo_title			= $row['old_seo_title']          ?: ($this->old_seo_title					?: '');
            $this->new_seo_title			= $row['new_seo_title']          ?: '';
            $this->old_seo_keywords			= $row['old_seo_keywords']       ?: ($this->old_seo_keywords					?: '');
            $this->new_seo_keywords			= $row['new_seo_keywords']       ?: '';
            $this->old_seo_description		= $row['old_seo_description']    ?: ($this->old_seo_description					?: '');
            $this->new_seo_description		= $row['new_seo_description']    ?: '';
            $this->old_social_network		= $row['old_social_network']     ?: ($this->old_social_network					?: '');
            $this->new_social_network		= $row['new_social_network']     ?: '';
            $this->old_latitude				= $row['old_latitude']           ?: ($this->old_latitude					?: '');
            $this->new_latitude				= $row['new_latitude']           ?: '';
            $this->old_longitude		    = $row['old_longitude']          ?: ($this->old_longitude					?: '');
            $this->new_longitude			= $row['new_longitude']          ?: '';
            $this->old_categories			= $row['old_categories']         ?: ($this->old_categories					?: '');
            $this->new_categories			= $row['new_categories']         ?: '';
            $this->old_additional_fields	= $row['old_additional_fields']  ?: ($this->old_additional_fields					?: '');
            $this->new_additional_fields	= $row['new_additional_fields']  ?: '';

		}

		public function Save() {

            /* ModStores Hooks */
            HookFire("classclaim_before_preparesave", [
                "that" => &$this
            ]);

			$this->prepareToSave();

			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined('SELECTED_DOMAIN_ID')) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);

			if ($this->id) {

				$sql = 'UPDATE Claim SET'
					. " account_id             = $this->account_id,"
					. " username               = $this->username,"
					. " listing_id             = $this->listing_id,"
					. " listing_title          = $this->listing_title,"
					. " old_location_1         = $this->old_location_1,"
					. " new_location_1         = $this->new_location_1,"
					. " old_location_2         = $this->old_location_2,"
					. " new_location_2         = $this->new_location_2,"
					. " old_location_3         = $this->old_location_3,"
					. " new_location_3         = $this->new_location_3,"
					. " old_location_4         = $this->old_location_4,"
					. " new_location_4         = $this->new_location_4,"
					. " old_location_5         = $this->old_location_5,"
					. " new_location_5         = $this->new_location_5,"
					. " old_title              = $this->old_title,"
					. " new_title              = $this->new_title,"
					. " old_friendly_url       = $this->old_friendly_url,"
					. " new_friendly_url       = $this->new_friendly_url,"
					. " old_email              = $this->old_email,"
					. " new_email              = $this->new_email,"
					. " old_url                = $this->old_url,"
					. " new_url                = $this->new_url,"
					. " old_phone              = $this->old_phone,"
					. " new_phone              = $this->new_phone,"
                    . " old_label_additional_phone = $this->old_label_additional_phone,"
                    . " new_label_additional_phone = $this->new_label_additional_phone,"
                    . " old_additional_phone = $this->old_additional_phone,"
                    . " new_additional_phone = $this->new_additional_phone,"
					. " old_address            = $this->old_address,"
					. " new_address            = $this->new_address,"
					. " old_address2           = $this->old_address2,"
					. " new_address2           = $this->new_address2,"
					. " old_zip_code           = $this->old_zip_code,"
					. " new_zip_code           = $this->new_zip_code,"
					. " old_level              = $this->old_level,"
					. " new_level              = $this->new_level,"
					. " old_listingtemplate_id = $this->old_listingtemplate_id,"
					. " new_listingtemplate_id = $this->new_listingtemplate_id,"
                    . " old_description = $this->old_description,"
                    . " new_description = $this->new_description,"
                    . " old_long_description = $this->old_long_description,"
                    . " new_long_description = $this->new_long_description,"
                    . " old_keywords = $this->old_keywords,"
                    . " new_keywords = $this->new_keywords,"
                    . " old_locations = $this->old_locations,"
                    . " new_locations = $this->new_locations,"
                    . " old_features = $this->old_features,"
                    . " new_features = $this->new_features,"
                    . " old_hours_work = $this->old_hours_work,"
                    . " new_hours_work = $this->new_hours_work,"
                    . " old_seo_title = $this->old_seo_title,"
                    . " new_seo_title = $this->new_seo_title,"
                    . " old_seo_keywords = $this->old_seo_keywords,"
                    . " new_seo_keywords = $this->new_seo_keywords,"
                    . " old_seo_description = $this->old_seo_description,"
                    . " new_seo_description = $this->new_seo_description,"
                    . " old_social_network = $this->old_social_network,"
                    . " new_social_network = $this->new_social_network,"
                    . " old_latitude = $this->old_latitude,"
                    . " new_latitude = $this->new_latitude,"
                    . " old_longitude = $this->old_longitude,"
                    . " new_longitude = $this->new_longitude,"
                    . " old_categories = $this->old_categories,"
                    . " new_categories = $this->new_categories,"
                    . " old_additional_fields = $this->old_additional_fields,"
                    . " new_additional_fields = $this->new_additional_fields,"
					. " step                   = $this->step,"
					. " status                 = $this->status"
					. " WHERE id               = $this->id";

                /* ModStores Hooks */
                HookFire("classclaim_before_updatequery", [
                    "that" => &$this,
                    "sql"  => &$sql,
                ]);

				$dbObj->query($sql);

                /* ModStores Hooks */
                HookFire("classclaim_after_updatequery", [
                    "that" => &$this
                ]);

			} else {

				$sql = 'INSERT INTO Claim'
					.' (account_id,'
					.' username,'
					.' listing_id,'
					.' listing_title,'
					.' date_time,'
					.' old_location_1,'
					.' new_location_1,'
					.' old_location_2,'
					.' new_location_2,'
					.' old_location_3,'
					.' new_location_3,'
					.' old_location_4,'
					.' new_location_4,'
					.' old_location_5,'
					.' new_location_5,'
					.' old_title,'
					.' new_title,'
					.' old_friendly_url,'
					.' new_friendly_url,'
					.' old_email,'
					.' new_email,'
					.' old_url,'
					.' new_url,'
					.' old_phone,'
					.' new_phone,'
                    .' old_label_additional_phone,'
                    .' new_label_additional_phone,'
                    .' old_additional_phone,'
                    .' new_additional_phone,'
					.' old_address,'
					.' new_address,'
					.' old_address2,'
					.' new_address2,'
					.' old_zip_code,'
					.' new_zip_code,'
					.' old_level,'
					.' new_level,'
					.' old_listingtemplate_id,'
					.' new_listingtemplate_id,'
                    .' old_description,'
                    .' new_description,'
                    .' old_long_description,'
                    .' new_long_description,'
                    .' old_keywords,'
                    .' new_keywords,'
                    .' old_locations,'
                    .' new_locations,'
                    .' old_features,'
                    .' new_features,'
                    .' old_hours_work,'
                    .' new_hours_work,'
                    .' old_seo_title,'
                    .' new_seo_title,'
                    .' old_seo_keywords,'
                    .' new_seo_keywords,'
                    .' old_seo_description,'
                    .' new_seo_description,'
                    .' old_social_network,'
                    .' new_social_network,'
                    .' old_latitude,'
                    .' new_latitude,'
                    .' old_longitude,'
                    .' new_longitude,'
                    .' old_categories,'
                    .' new_categories,'
                    .' old_additional_fields,'
                    .' new_additional_fields,'
					.' step,'
					.' status)'
					.' VALUES'
					. " ($this->account_id,"
					. " $this->username,"
					. " $this->listing_id,"
					. " $this->listing_title,"
					.' NOW(),'
					. " $this->old_location_1,"
					. " $this->new_location_1,"
					. " $this->old_location_2,"
					. " $this->new_location_2,"
					. " $this->old_location_3,"
					. " $this->new_location_3,"
					. " $this->old_location_4,"
					. " $this->new_location_4,"
					. " $this->old_location_5,"
					. " $this->new_location_5,"
					. " $this->old_title,"
					. " $this->new_title,"
					. " $this->old_friendly_url,"
					. " $this->new_friendly_url,"
					. " $this->old_email,"
					. " $this->new_email,"
					. " $this->old_url,"
					. " $this->new_url,"
					. " $this->old_phone,"
					. " $this->new_phone,"
                    . " $this->old_label_additional_phone,"
                    . " $this->new_label_additional_phone,"
                    . " $this->old_additional_phone,"
                    . " $this->new_additional_phone,"
					. " $this->old_address,"
					. " $this->new_address,"
					. " $this->old_address2,"
					. " $this->new_address2,"
					. " $this->old_zip_code,"
					. " $this->new_zip_code,"
					. " $this->old_level,"
					. " $this->new_level,"
					. " $this->old_listingtemplate_id,"
					. " $this->new_listingtemplate_id,"
                    . " $this->old_description,"
                    . " $this->new_description,"
                    . " $this->old_long_description,"
                    . " $this->new_long_description,"
                    . " $this->old_keywords,"
                    . " $this->new_keywords,"
                    . " $this->old_locations,"
                    . " $this->new_locations,"
                    . " $this->old_features,"
                    . " $this->new_features,"
                    . " $this->old_hours_work,"
                    . " $this->new_hours_work,"
                    . " $this->old_seo_title,"
                    . " $this->new_seo_title,"
                    . " $this->old_seo_keywords,"
                    . " $this->new_seo_keywords,"
                    . " $this->old_seo_description,"
                    . " $this->new_seo_description,"
                    . " $this->old_social_network,"
                    . " $this->new_social_network,"
                    . " $this->old_latitude,"
                    . " $this->new_latitude,"
                    . " $this->old_longitude,"
                    . " $this->new_longitude,"
                    . " $this->old_categories,"
                    . " $this->new_categories,"
                    . " $this->old_additional_fields,"
                    . " $this->new_additional_fields,"
					. " $this->step,"
					. " $this->status)";

                /* ModStores Hooks */
                HookFire("classclaim_before_insertquery", [
                    "that" => &$this,
                    "sql"  => &$sql,
                ]);

				$dbObj->query($sql);

                /* ModStores Hooks */
                HookFire("classclaim_after_insertquery", [
                    "that"  => &$this,
                    "dbObj" => &$dbObj,
                ]);

				$this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);

                $rowTimeline = array();
                $rowTimeline['item_type'] = 'claim';
                $rowTimeline['action'] = 'new';
                $rowTimeline['item_id'] = $this->id;
                $timelineObj = new Timeline($rowTimeline);
                $timelineObj->Save();

			}

            /* ModStores Hooks */
            HookFire("classclaim_before_prepareuse", [
                "that" => &$this,
            ]);

			$this->prepareToUse();

            /* ModStores Hooks */
            HookFire("classclaim_after_save", [
                "that" => &$this,
            ]);
		}

		public function Delete() {
			$dbMain = db_getDBObject(DEFAULT_DB, true);
			if (defined('SELECTED_DOMAIN_ID')) {
				$dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
			} else {
				$dbObj = db_getDBObject();
			}
			unset($dbMain);

            /* ModStores Hooks */
            HookFire("classclaim_before_delete", [
                "that" => &$this
            ]);

			$sql = "DELETE FROM Claim WHERE id = $this->id";
			$dbObj->query($sql);

            ### Timeline
            $sql = "DELETE FROM Timeline WHERE item_type = 'claim' AND item_id = $this->id";
            $dbObj->query($sql);
		}

		public function canApprove() {
            if (($this->status === 'complete') && $this->account_id && $this->listing_id) {
                return true;
            }
            if ($this->status === 'progress') {
                return true;
            }

            return false;
        }

		public function canDeny() {
            if ((($this->status === 'progress') || ($this->status === 'incomplete') || ($this->status === 'complete')) && $this->account_id && $this->listing_id) {
                return true;
            }
            if ($this->status === 'progress') {
                return true;
            }

            return false;
        }

        public static function isUndergoingClaim($id, $account_id) {

            setting_get( 'claim_approve', $claim_approve );
            if ( !$claim_approve ) return false;

            if (is_numeric($id) && $id) {
                $dbMain = db_getDBObject(DEFAULT_DB, true);
                if (defined('SELECTED_DOMAIN_ID')) {
                    $db = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);
                } else {
                    $db = db_getDBObject();
                }
                unset($dbMain);
                $sql = 'SELECT id FROM Claim WHERE listing_id = '.db_formatNumber($id).' AND account_id = '.db_formatNumber($account_id)." AND status != 'approved' ORDER BY date_time DESC LIMIT 1";
                $result = $db->query($sql);
                if (mysqli_num_rows($result) > 0) {
                    return true;
                }
            }
            return false;
        }

	}
