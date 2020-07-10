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
# * FILE: /classes/class_Location5.php
# ----------------------------------------------------------------------------------------------------

class Location5 extends Handle
{
    var $id;
    var $location_4;
    var $location_3;
    var $location_2;
    var $location_1;
    var $name;
    var $abbreviation;
    var $friendly_url;
    var $page_title;
    var $seo_description;
    var $seo_keywords;
    var $latitude;
    var $longitude;
    var $radius;

    public function __construct($var = '')
    {
        if (is_numeric($var) && ($var)) {
            $db = db_getDBObject(DEFAULT_DB, true);
            $sql = "SELECT * FROM Location_5 WHERE id = $var";
            $row = mysqli_fetch_array($db->query($sql));
            $this->makeFromRow($row);
        } else {
            if (!is_array($var)) {
                $var = array();
            }
            $this->makeFromRow($var);
        }

        /* ModStores Hooks */
        HookFire("classlocation5_contruct", [
            "that" => &$this
        ]);
    }

    function makeFromRow($row = '')
    {
        /* ModStores Hooks */
        HookFire("classlocation5_before_makerow", [
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

        if ($row['location_4']) {
            $this->location_4 = $row['location_4'];
        } else {
            if (!$this->location_4) {
                $this->location_4 = 0;
            }
        }

        if ($row['location_3']) {
            $this->location_3 = $row['location_3'];
        } else {
            if (!$this->location_3) {
                $this->location_3 = 0;
            }
        }

        if ($row['location_2']) {
            $this->location_2 = $row['location_2'];
        } else {
            if (!$this->location_2) {
                $this->location_2 = 0;
            }
        }

        if ($row['location_1']) {
            $this->location_1 = $row['location_1'];
        } else {
            if (!$this->location_1) {
                $this->location_1 = 0;
            }
        }

        if ($row['name']) {
            $this->name = $row['name'];
        } else {
            if (!$this->name) {
                $this->name = "";
            }
        }

        $this->abbreviation = $row['abbreviation'];

        if ($row['friendly_url']) {
            $this->friendly_url = $row['friendly_url'];
        } else {
            if (!$this->friendly_url) {
                $this->friendly_url = "";
            }
        }

        $this->page_title = $row['page_title'];
        $this->seo_description = $row['seo_description'];
        $this->seo_keywords = $row['seo_keywords'];
        $this->latitude = $row['latitude'];
        $this->longitude = $row['longitude'];

        if ($row['radius']) {
            $this->radius = $row['radius'];
        } else {
            if (!$this->radius) {
                $this->radius = 0;
            }
        }

        /* ModStores Hooks */
        HookFire("classlocation5_after_makerow", [
            "that" => &$this,
            "row"  => &$row,
        ]);
    }

    function Save($updFullText = false)
    {
        /* ModStores Hooks */
        HookFire("classlocation5_before_preparesave", [
            "that" => &$this
        ]);

        $this->prepareToSave();

        $dbObj = db_getDBObject(DEFAULT_DB, true);

        $this->friendly_url = string_strtolower($this->friendly_url);

        if ($this->id) {
            $sql = "UPDATE `Location_5` SET
                        `location_1`      = {$this->location_1},
                        `location_2`      = {$this->location_2},
                        `location_3`      = {$this->location_3},
                        `location_4`      = {$this->location_4},
                        `name`            = {$this->name},
                        `abbreviation`    = {$this->abbreviation},
                        `friendly_url`    = {$this->friendly_url},
                        `page_title`      = {$this->page_title},
                        `seo_description` = {$this->seo_description},
                        `seo_keywords`    = {$this->seo_keywords},
                        `latitude`        = {$this->latitude},
                        `longitude`       = {$this->longitude},
                        `radius`          = {$this->radius}
                    WHERE `id` = {$this->id}";

            /* ModStores Hooks */
            HookFire("classlocation5_before_updatequery", [
                "that" => &$this,
                "sql"  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire("classlocation5_after_updatequery", [
                "that" => &$this
            ]);

        } else {
            $sql = "INSERT INTO `Location_5` (
                        `location_4`,
                        `location_3`,
                        `location_2`,
                        `location_1`,
                        `name`,
                        `abbreviation`,
                        `friendly_url`,
                        `page_title`,
                        `seo_description`,
                        `seo_keywords`,
                        `latitude`,
                        `longitude`,
                        `radius`
                    ) VALUES (
                        {$this->location_4},
                        {$this->location_3},
                        {$this->location_2},
                        {$this->location_1},
                        {$this->name},
                        {$this->abbreviation},
                        {$this->friendly_url},
                        {$this->page_title},
                        {$this->seo_description},
                        {$this->seo_keywords},
                        {$this->latitude},
                        {$this->longitude},
                        {$this->radius}
                    )";

            /* ModStores Hooks */
            HookFire("classlocation5_before_insertquery", [
                "that" => &$this,
                "sql"  => &$sql,
            ]);

            $dbObj->query($sql);

            /* ModStores Hooks */
            HookFire("classlocation5_after_insertquery", [
                "that"  => &$this,
                "dbObj" => &$dbObj,
            ]);
            $this->id = ((is_null($___mysqli_res = mysqli_insert_id($dbObj->link_id))) ? false : $___mysqli_res);
        }

        /* ModStores Hooks */
        HookFire("classlocation5_before_prepareuse", [
            "that" => &$this
        ]);

        $this->prepareToUse();

        if ($updFullText) {
            $this->updateFullTextItems();
        }

        if ($symfonyContainer = SymfonyCore::getContainer()) {
            $symfonyContainer->get("location.synchronization")->addLocation5Upsert($this->id);
        }

        /* ModStores Hooks */
        HookFire("classlocation5_after_save", [
            "that" => &$this
        ]);
    }

    function Delete($updFullText = false, $updateOnElasticsearch = true)
    {

        if ($this->id) {
            $dbObjMain = db_getDBObject(DEFAULT_DB, true);

            $sqlDomain = "SELECT `id` FROM `Domain` WHERE `status` = 'A'";
            $resDomain = $dbObjMain->Query($sqlDomain);
            while ($row = mysqli_fetch_assoc($resDomain)) {
                unset($dbObj);
                $dbObj = db_getDBObjectByDomainID($row["id"], $dbObjMain);

                //Listing
                $sql = "UPDATE `Listing`
                        SET `location_5` = 0
                        WHERE `location_5` = {$this->id}";
                $result = $dbObj->query($sql);

                //Promotion
                $sql = "UPDATE `Promotion` SET `listing_location5` = 0 WHERE `listing_location5` = {$this->id}";
                $result = $dbObj->query($sql);

                //Event
                $sql = "UPDATE `Event` SET `location_5` = 0 WHERE `location_5` = {$this->id}";
                $result = $dbObj->query($sql);
                //Classified
                $sql = "UPDATE `Classified` SET `location_5` = 0 WHERE `location_5` = {$this->id}";
                $result = $dbObj->query($sql);
            }
            unset($rowDomain);

            $_locations = explode(",", EDIR_LOCATIONS);
            system_retrieveLocationRelationship($_locations, 5, $_location_father_level, $_location_child_level);
            if ($_location_child_level) {
                $sql = "SELECT id FROM Location_" . $_location_child_level . " WHERE location_5=" . $this->id;
                $result = $dbObjMain->query($sql);
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $objLocationLabel = "Location" . $_location_child_level;
                        unset(${"Location" . $_location_child_level});
                        ${"Location" . $_location_child_level} = new $objLocationLabel;
                        ${"Location" . $_location_child_level}->SetNumber("id", $row["id"]);
                        ${"Location" . $_location_child_level}->Delete(false, false);
                    }
                }
            }

            /* ModStores Hooks */
            HookFire("classlocation5_before_delete", [
                "that" => &$this
            ]);

            $sql = "DELETE FROM `Location_5` WHERE id = {$this->id}";
            $dbObjMain->query($sql);

            if ($updateOnElasticsearch and $symfonyContainer = SymfonyCore::getContainer()) {
                $symfonyContainer->get("location.synchronization")->addLocation5Delete($this->id);
            }
        }
    }

    function isRepeated($father_level, &$error_message)
    {
        $dbObj = db_getDBObject(DEFAULT_DB, true);
        $sql = "SELECT * FROM Location_5 WHERE name = " . db_formatString($this->name);
        if ($father_level !== false) {
            $father_level_value = "location_" . $father_level;
            if ($this->$father_level_value) {
                $father_level_value = $this->$father_level_value;
                $sql .= " AND location_" . $father_level . " = " . $father_level_value;
            }
        }
        if ($this->id) {
            $sql .= " AND id != $this->id";
        }
        $result = $dbObj->query($sql);
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            $error_message = string_ucwords(LOCATION_TITLE) . " " . $this->name . " " . system_showText(LANG_SITEMGR_LOCATION_ALREADYEXISTS);

            return true;
        }

        return false;
    }

    function retrievedIfRepeated($_locations)
    {

        $sql = "SELECT * FROM Location_5 WHERE (friendly_url = " . db_formatString($this->friendly_url) . " OR name = " . db_formatString($this->name) . ") ";

        foreach ($_locations as $each_location) {
            if ($each_location < 5) {
                $attribute = "location_" . $each_location;
                $sql .= " AND location_" . $each_location . " = " . $this->$attribute;
            }
        }

        if ($this->id) {
            $sql .= " AND id != $this->id";
        }

        $dbObj = db_getDBObject(DEFAULT_DB, true);
        $result = $dbObj->query($sql);
        $row = mysqli_fetch_assoc($result);

        if ($row["id"]) {
            return $row["id"];
        } else {
            return false;
        }
    }

    function retrieveAllLocation($ids = false, $fields = "*")
    {
        $dbObj = db_getDBObject(DEFAULT_DB, true);
        $sql = "SELECT $fields FROM Location_5 " . (is_array($ids) ? "WHERE id IN (" . implode(",",
                    $ids) . ")" : "") . " ORDER BY name";
        $result = $dbObj->query($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        if ($data) {
            return $data;
        } else {
            return false;
        }
    }

    function retrieveLocationById()
    {
        $dbObj = db_getDBObject(DEFAULT_DB, true);
        $sql = "SELECT * FROM Location_5 WHERE id = $this->id";
        $result = $dbObj->query($sql);
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            return $row;
        } else {
            return false;
        }
    }

    function retrieveLocationByLocation($father_level, $allLocations = false, $ids = false)
    {
        $father_level_value = "location_" . $father_level;
        $father_level_value = $this->$father_level_value;
        if (!$father_level_value) {
            return false;
        }
        $dbObj = db_getDBObject(DEFAULT_DB, true);

        if ($allLocations) {

            /**
             * Get locations on domain per module
             */
            $locations_id = LocationCounter::getLocationIDByLocationLevel(5);

            if (is_array($locations_id)) {
                $sql = "SELECT * FROM Location_5 WHERE location_" . $father_level . " = " . $father_level_value . " AND id IN (" . implode(",",
                        $locations_id) . ") ORDER BY NAME";
            } else {
                return false;
            }
        } else {
            $sql = "SELECT * FROM Location_5 WHERE location_" . $father_level . " = " . $father_level_value . " " . (is_array($ids) ? " AND id IN (" . implode(",",
                        $ids) . ")" : "") . " ORDER BY NAME";
        }
        $result = $dbObj->query($sql);
        if (mysqli_num_rows($result)) {
            unset($data);
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }

            if ($data) {
                return $data;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    function isValidFriendlyUrl($father_level, &$error_message)
    {

        if (!$this->getString("friendly_url")) {
            $error_message = "&#149;&nbsp; Friendly Title is required, please do not leave it blank.";

            return false;
        }
        $dbObj = db_getDBObject(DEFAULT_DB, true);

        for ($i = 1; $i <= 5; $i++) {

            $sql = "SELECT friendly_url FROM Location_$i WHERE friendly_url = '" . $this->getString("friendly_url") . "'";
            if ($father_level !== false) {
                $father_level_value = "location_" . $father_level;
                if ($this->$father_level_value) {
                    $father_level_value = $this->$father_level_value;
                    //$sql .= " AND location_" . $father_level . " = " . $father_level_value;
                }
            }
            if ($this->getString("id") && $i == 5) {
                $sql .= " AND id != " . $this->getString("id");
            }
            $sql .= " LIMIT 1";
            $rs = $dbObj->query($sql);
            if (mysqli_num_rows($rs) > 0) {
                $error_message = "&#149;&nbsp; Friendly Title already in use, please choose another Friendly Title";

                return false;
            }
        }
        if (!preg_match(FRIENDLYURL_REGULAREXPRESSION, $this->getString("friendly_url"))) {
            $error_message = "&#149;&nbsp; Friendly Url contain invalid chars";

            return false;
        }

        return true;
    }

    function updateFullTextItems()
    {

        if ($this->id) {

            $dbObj = db_getDBObject(DEFAULT_DB, true);

            //Listing
            if (LISTING_SCALABILITY_OPTIMIZATION != 'on') {
                $sql = "SELECT * FROM Listing WHERE location_5_id = $this->id";
                $result = $dbObj->query($sql);
                while ($row = mysqli_fetch_array($result)) {
                    $itemObj = new Listing($row['id']);
                    $itemObj->setFullTextSearch();
                    unset($itemObj);
                }
            }

            //Event
            if (EVENT_FEATURE == 'on' && EVENT_SCALABILITY_OPTIMIZATION != 'on') {
                $sql = "SELECT * FROM Event WHERE location_5_id = $this->id";
                $result = $dbObj->query($sql);
                while ($row = mysqli_fetch_array($result)) {
                    $itemObj = new Event($row['id']);
                    $itemObj->setFullTextSearch();
                    unset($itemObj);
                }
            }

            //Classified
            if (CLASSIFIED_FEATURE == 'on' && CLASSIFIED_SCALABILITY_OPTIMIZATION != 'on') {
                $sql = "SELECT * FROM Classified WHERE location_5_id = $this->id";
                $result = $dbObj->query($sql);
                while ($row = mysqli_fetch_array($result)) {
                    $itemObj = new Classified($row['id']);
                    $itemObj->setFullTextSearch();
                    unset($itemObj);
                }
            }

            return true;

        }

        return false;

    }

}
