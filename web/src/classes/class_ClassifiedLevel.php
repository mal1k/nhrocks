<?php

class ClassifiedLevel
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
    public $video;
    public $additional_files;

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

        $sql = '';

        if (!defined('ALL_CLASSIFIEDLEVEL_INFORMATION') || !defined('ACTIVE_CLASSIFIEDLEVEL_INFORMATION')) {
            $sql = 'SELECT * FROM ClassifiedLevel ORDER BY value DESC';
        }

        if (!empty($sql)) {
            $result = $dbObj->query($sql);
            $classifiedLevelAux = $classifiedLevelAuxAll = [];

            $i = 0;
            $j = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                foreach ($row as $key => $value) {
                    if ($row['active'] === 'y') {
                        if ($key === 'defaultlevel' && $value === 'y') {
                            $classifiedLevelAuxAll[$j]['default'] = $row['value'];
                        }
                        $classifiedLevelAuxAll[$j][$key] = $value;

                    }
                    if ($key === 'defaultlevel' && $value === 'y') {
                        $classifiedLevelAux[$i]['default'] = $row['value'];
                    }
                    $classifiedLevelAux[$i][$key] = $value;
                }
                $i++;
                $j++;
            }
        }

        if (is_array($classifiedLevelAux) && !defined('ALL_CLASSIFIEDLEVEL_INFORMATION')) {
            define('ALL_CLASSIFIEDLEVEL_INFORMATION', serialize($classifiedLevelAux));
        }

        if (is_array($classifiedLevelAuxAll) && !defined('ACTIVE_CLASSIFIEDLEVEL_INFORMATION')) {
            define('ACTIVE_CLASSIFIEDLEVEL_INFORMATION', serialize($classifiedLevelAuxAll));
        }

        if ($listAll) {
            $classifiedLevelAux = unserialize(ALL_CLASSIFIEDLEVEL_INFORMATION);
        } else {
            $classifiedLevelAux = unserialize(ACTIVE_CLASSIFIEDLEVEL_INFORMATION);
        }

        if (is_array($classifiedLevelAux)) {
            foreach ($classifiedLevelAux as $classifiedLevel) {
                if ($classifiedLevel['defaultlevel'] === 'y') {
                    $this->default = $classifiedLevel['value'];
                }

                $this->value[] = $classifiedLevel['value'];
                $this->name[] = $classifiedLevel['name'];
                $this->detail[] = $classifiedLevel['detail'];
                $this->images[] = $classifiedLevel['images'];
                $this->has_cover_image[] = $classifiedLevel['has_cover_image'];
                $this->price[] = $classifiedLevel['price'];
                $this->price_yearly[] = $classifiedLevel['price_yearly'];
                $this->trial[] = $classifiedLevel['trial'];
                $this->active[] = $classifiedLevel['active'];
                $this->popular[] = $classifiedLevel['popular'];
                $this->featured[] = $classifiedLevel['featured'];
                $this->video[] = $classifiedLevel['video'];
                $this->additional_files[] = $classifiedLevel['additional_files'];
            }
        }

        /* ModStores Hooks */
        HookFire("classclassifiedlevel_contruct", [
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

    public function union($key, $value)
    {
        $aux = [];
        $size = count($key);

        for ($i = 0; $i < $size; $i++) {
            $aux[$key[$i]] = $value[$i];
        }

        return $aux;
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
        if (!is_numeric($value)) {
            return null;
        }

        $value_name = $this->getValueName();

        return $value_name[$value];
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

    public function getVideo($value)
    {
        $videoArray = $this->union($this->value, $this->video);
        if (isset($videoArray[$value])) {
            return $videoArray[$value];
        }

        return $videoArray[$this->default];
    }

    public function getAdditionalFiles($value)
    {
        $additionalFilesArray = $this->union($this->value, $this->additional_files);
        if (isset($additionalFilesArray[$value])) {
            return $additionalFilesArray[$value];
        }

        return $additionalFilesArray[$this->default];
    }

    public function getLevelOrdering($value)
    {
        switch ($value) {
            case 10:
                return system_showText(LANG_SITEMGR_FIRST);

            case 30:
                return system_showText(LANG_SITEMGR_SECOND);

            case 50:
                return system_showText(LANG_SITEMGR_THIRD);
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
        $video = '',
        $additional_files = '',
        $type = 'names',
        $popular = ''
    ) {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        if ($type === 'names') {
            $sql = sprintf(
                "UPDATE ClassifiedLevel SET name = '%s', active = '%s', popular = '%s' WHERE value = '%s'",
                $name, $active, $popular, $levelValue
            );
        } elseif ($type === 'fields') {
            $sql = sprintf(
                "UPDATE ClassifiedLevel SET detail = '%s', images = '%s', has_cover_image = '%s',video = '%s', additional_files = '%s' WHERE value = '%s'",
                $detail, $images, $hasCoverImage, $video, $additional_files, $levelValue
            );
        }

        $dbObj->query($sql);
    }

    public function updatePricing($field, $fieldValue, $level)
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        $sql = "UPDATE ClassifiedLevel SET $field = ".$fieldValue.' WHERE value = '.$level;
        $dbObj->query($sql);
    }

    public function updateFeatured($newValue, $level)
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        $sql = "UPDATE ClassifiedLevel SET featured = '{$newValue}' WHERE value = ".$level;
        $dbObj->query($sql);
    }
}
