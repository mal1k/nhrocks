<?php

class EventLevel
{

    public $default;
    public $value;
    public $name;
    public $detail;
    public $images;
    public $has_cover_image;
    public $price;
    public $price_yearly;
    public $trial;
    public $active;
    public $popular;
    public $featured;

    public function __construct($listAll = false, $domain_id = false)
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
        }

        unset($dbMain);

        $sql = '';

        if (!defined('ALL_EVENTLEVEL_INFORMATION') || !defined('ACTIVE_EVENTLEVEL_INFORMATION')) {
            $sql = 'SELECT * FROM EventLevel ORDER BY value DESC';
        }

        if (!empty($sql)) {
            $result = $dbObj->query($sql);
            $eventLevelAux = $eventLevelAuxAll = [];
            $i = 0;
            $j = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                foreach ($row as $key => $value) {
                    if ($row['active'] === 'y') {
                        if ($key === 'defaultlevel' && $value === 'y') {
                            $eventLevelAuxAll[$j]['default'] = $row['value'];
                        }
                        $eventLevelAuxAll[$j][$key] = $value;

                    }
                    if ($key === 'defaultlevel' && $value === 'y') {
                        $eventLevelAux[$i]['default'] = $row['value'];
                    }
                    $eventLevelAux[$i][$key] = $value;
                }
                $i++;
                $j++;
            }
        }

        if (is_array($eventLevelAux)) {
            if (!defined('ALL_EVENTLEVEL_INFORMATION')) {
                define('ALL_EVENTLEVEL_INFORMATION', serialize($eventLevelAux));
            }
        }

        if (is_array($eventLevelAuxAll)) {
            if (!defined('ACTIVE_EVENTLEVEL_INFORMATION')) {
                define('ACTIVE_EVENTLEVEL_INFORMATION', serialize($eventLevelAuxAll));
            }
        }

        if ($listAll) {
            $eventLevelAux = unserialize(ALL_EVENTLEVEL_INFORMATION);
        } else {
            $eventLevelAux = unserialize(ACTIVE_EVENTLEVEL_INFORMATION);
        }

        if (is_array($eventLevelAux)) {
            foreach ($eventLevelAux as $eventLevel) {
                if ($eventLevel['defaultlevel'] === 'y') {
                    $this->default = $eventLevel['value'];
                }

                $this->value[] = $eventLevel['value'];
                $this->name[] = $eventLevel['name'];
                $this->detail[] = $eventLevel['detail'];
                $this->images[] = $eventLevel['images'];
                $this->has_cover_image[] = $eventLevel['has_cover_image'];
                $this->price[] = $eventLevel['price'];
                $this->price_yearly[] = $eventLevel['price_yearly'];
                $this->trial[] = $eventLevel['trial'];
                $this->active[] = $eventLevel['active'];
                $this->popular[] = $eventLevel['popular'];
                $this->featured[] = $eventLevel['featured'];

            }
        }

        /* ModStores Hooks */
        HookFire("classeventlevel_contruct", [
            "that" => &$this
        ]);
    }

    public function getHasCoverImage($value)
    {
        $coverImageArray = $this->union($this->value, $this->has_cover_image);
        if (isset($coverImageArray[$value])) {
            return $coverImageArray[$value];
        }

        return $coverImageArray[$this->default];
    }

    public function getDetail($value)
    {
        $detailArray = $this->union($this->value, $this->detail);
        if (isset($detailArray[$value])) {
            return $detailArray[$value];
        }

        return $detailArray[$this->default];
    }

    public function getImages($value)
    {
        $imagesArray = $this->union($this->value, $this->images);
        if (isset($imagesArray[$value])) {
            return $imagesArray[$value];
        }

        return $imagesArray[$this->default];
    }

    public function getPrice($value, $period = '')
    {
        $priceArray = $this->union($this->value, $this->{'price'.($period ? '_'.$period : $period)});
        if (isset($priceArray[$value])) {
            return $priceArray[$value];
        }

        return $priceArray[$this->default];
    }

    public function getTrial($value)
    {
        $trialArray = $this->union($this->value, $this->trial);
        if (isset($trialArray[$value])) {
            return $trialArray[$value];
        }

        return $trialArray[$this->default];
    }

    public function getLevelValues()
    {
        return $this->getValues();
    }

    public function getValues()
    {
        return $this->value;
    }

    public function getLevelNames()
    {
        return $this->getNames();
    }

    public function getNames()
    {
        return $this->name;
    }

    public function showLevel($value)
    {
        if ($this->getName($value)) {
            return string_ucwords($this->getName($value));
        }

        return string_ucwords($this->getLevel($this->getDefaultLevel()));
    }

    public function getName($value)
    {
        if (is_numeric($value)) {
            $value_name = $this->getValueName();

            return $value_name[$value];
        }
    }

    public function getValueName()
    {
        return $this->union($this->getValues(), $this->getNames());
    }

    public function getLevel($value)
    {
        if ($this->getName($value)) {
            return $this->getName($value);
        }

        return $this->getLevel($this->getDefaultLevel());
    }

    public function getDefaultLevel()
    {
        return $this->getDefault();
    }

    public function getDefault()
    {
        $activeArray = array_filter($this->union($this->value, $this->active), 'validateActive');
        if (array_key_exists($this->default, $activeArray)) {
            return $this->default;
        }

        krsort($activeArray);
        $newActiveArray = array_keys($activeArray);

        return $newActiveArray[0];
    }

    public function union($key, $value)
    {
        for ($i = 0; $i < count($key); $i++) {
            $aux[$key[$i]] = $value[$i];
        }

        return $aux;
    }

    public function showLevelNames()
    {
        $names = $this->getNames();
        foreach ($names as $name) {
            $array[] = string_ucwords($name);
        }

        return $array;
    }

    public function getLevelActive($value)
    {
        if ($this->getActive($value) === 'y') {
            return $value;
        }

        return $this->getDefaultLevel();
    }

    public function getActive($value)
    {
        $activeArray = $this->union($this->value, $this->active);

        return $activeArray[$value];
    }

    public function getPopular($value)
    {
        $popularArray = $this->union($this->value, $this->popular);

        return $popularArray[$value];
    }

    public function getFeatured($value)
    {
        $popularArray = $this->union($this->value, $this->featured);

        return $popularArray[$value];
    }

    public function getLevelOrdering($value)
    {
        switch ($value) {
            case 10:
                return system_showText(LANG_SITEMGR_FIRST);
                break;
            case 30:
                return system_showText(LANG_SITEMGR_SECOND);
                break;
            case 50:
                return system_showText(LANG_SITEMGR_THIRD);
                break;
            default:
                return null;
        }
    }

    public function updateValues(
        $name,
        $active,
        $detail = '',
        $images = '',
        $hasCoverImage = '',
        $levelValue,
        $type = 'names',
        $popular = ''
    ) {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        if ($type === 'names') {
            $sql = sprintf(
                "UPDATE EventLevel SET name = '%s', active = '%s', popular = '%s' WHERE value = '%s'",
                $name, $active, $popular, $levelValue
            );
        } elseif ($type === 'fields') {
            $sql = sprintf(
                "UPDATE EventLevel SET detail = '%s', images = '%s', has_cover_image = '%s' WHERE value = '%s'",
                $detail, $images, $hasCoverImage, $levelValue
            );
        }

        $dbObj->query($sql);
    }

    public function updatePricing($field, $fieldValue, $level)
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        $sql = "UPDATE EventLevel SET $field = ".$fieldValue.' WHERE value = '.$level;
        $dbObj->query($sql);
    }

    public function updateFeatured($newValue, $level)
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        $sql = "UPDATE EventLevel SET featured = '{$newValue}' WHERE value = ".$level;
        $dbObj->query($sql);
    }
}
