<?php

/**
 * Class ListingLevel
 */
class ListingLevel
{

    public $default;
    public $value;
    public $name;
    public $detail;
    public $images;
    public $has_review;
    public $has_cover_image;
    public $has_logo_image;
    public $price;
    public $price_yearly;
    public $trial;
    public $free_category;
    public $deals;
    public $category_price;
    public $active;
    public $popular;
    public $featured;
    public $classified_quantity_association;

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

        if (!defined('ALL_LISTINGLEVEL_INFORMATION') || !defined('ACTIVE_LISTINGLEVEL_INFORMATION')) {
            $sql = 'SELECT * FROM ListingLevel ORDER BY value DESC';
        }

        if (!empty($sql)) {
            $result = $dbObj->query($sql);
            $listingLevelAux = $listingLevelAuxAll = [];

            $i = 0;
            $j = 0;
            while ($row = mysqli_fetch_assoc($result)) {
                foreach ($row as $key => $value) {
                    if ($row['active'] === 'y') {
                        if ($key === 'defaultlevel' && $value === 'y') {
                            $listingLevelAuxAll[$j]['default'] = $row['value'];
                        }
                        $listingLevelAuxAll[$j][$key] = $value;

                    }
                    if ($key === 'defaultlevel' && $value === 'y') {
                        $listingLevelAux[$i]['default'] = $row['value'];
                    }
                    $listingLevelAux[$i][$key] = $value;
                }
                $i++;
                $j++;
            }
        }

        if (is_array($listingLevelAux) && !defined('ALL_LISTINGLEVEL_INFORMATION')) {
            define('ALL_LISTINGLEVEL_INFORMATION', serialize($listingLevelAux));
        }

        if (is_array($listingLevelAuxAll) && !defined('ACTIVE_LISTINGLEVEL_INFORMATION')) {
            define('ACTIVE_LISTINGLEVEL_INFORMATION', serialize($listingLevelAuxAll));
        }

        if ($listAll) {
            $listingLevelAux = unserialize(ALL_LISTINGLEVEL_INFORMATION);
        } else {
            $listingLevelAux = unserialize(ACTIVE_LISTINGLEVEL_INFORMATION);
        }

        if (is_array($listingLevelAux)) {
            foreach ($listingLevelAux as $listingLevel) {
                if ($listingLevel['defaultlevel'] === 'y') {
                    $this->default = $listingLevel['value'];
                }

                $this->value[] = $listingLevel['value'];
                $this->name[] = $listingLevel['name'];
                $this->detail[] = $listingLevel['detail'];
                $this->images[] = $listingLevel['images'];
                $this->has_cover_image[] = $listingLevel['has_cover_image'];
                $this->has_logo_image[] = $listingLevel['has_logo_image'];
                $this->has_review[] = $listingLevel['has_review'];
                $this->price[] = $listingLevel['price'];
                $this->price_yearly[] = $listingLevel['price_yearly'];
                $this->trial[] = $listingLevel['trial'];
                $this->free_category[] = $listingLevel['free_category'];
                $this->deals[] = $listingLevel['deals'];
                $this->category_price[] = $listingLevel['category_price'];
                $this->active[] = $listingLevel['active'];
                $this->popular[] = $listingLevel['popular'];
                $this->featured[] = $listingLevel['featured'];
                $this->classified_quantity_association[] = $listingLevel['classified_quantity_association'];

            }
        }

        /* ModStores Hooks */
        HookFire("classlistinglevel_contruct", [
            "that" => &$this
        ]);
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

    public function getClassifiedQuantityAssociation($value)
    {
        $classified_quantity_association = $this->union($this->value, $this->classified_quantity_association);
        if (isset($classified_quantity_association[$value])) {
            return $classified_quantity_association[$value];
        }

        return $classified_quantity_association[$this->default];
    }

    public function getHasReview($value)
    {
        $hasreviewArray = $this->union($this->value, $this->has_review);
        if (isset($hasreviewArray[$value])) {
            return $hasreviewArray[$value];
        }

        return $hasreviewArray[$this->default];
    }

    public function getHasCoverImage($value) {
        $hasCoverImageArray = $this->union($this->value, $this->has_cover_image);

        if(isset($hasCoverImageArray[$value])) {
            return $hasCoverImageArray[$value];
        }

        return $hasCoverImageArray[$this->default];
    }

    public function getHasLogoImage($value) {
        $hasLogoImageArray = $this->union($this->value, $this->has_logo_image);

        if(isset($hasLogoImageArray[$value])) {
            return $hasLogoImageArray[$value];
        }

        return $hasLogoImageArray[$this->default];
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

    public function getFreeCategory($value)
    {
        $freeCategoryArray = $this->union($this->value, $this->free_category);
        if (isset($freeCategoryArray[$value])) {
            return $freeCategoryArray[$value];
        }

        return $freeCategoryArray[$this->default];
    }

    public function getDeals($value)
    {
        $dealsArray = $this->union($this->value, $this->deals);
        if (isset($dealsArray[$value])) {
            return $dealsArray[$value];
        }

        return $dealsArray[$this->default];
    }

    public function getCategoryPrice($value)
    {
        $categoryPriceArray = $this->union($this->value, $this->category_price);
        if (isset($categoryPriceArray[$value])) {
            return $categoryPriceArray[$value];
        }

        return $categoryPriceArray[$this->default];
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

    public function getLevelOrdering($value)
    {
        switch ($value) {
            case 10:
                return system_showText(LANG_SITEMGR_FIRST);
            case 30:
                return system_showText(LANG_SITEMGR_SECOND);
            case 50:
                return system_showText(LANG_SITEMGR_THIRD);
            case 70:
                return system_showText(LANG_SITEMGR_FOURTH);
            default:
                return null;
        }
    }

    public function convertTableToArray()
    {
        $array_fields = get_object_vars($this);

        $level_values = [];

        $size = count($array_fields['value']);
        for ($i = 0; $i < $size; $i++) {
            $level_values[] = $array_fields['value'][$i];
        }

        if (count($level_values) && is_array($array_fields)) {
            $aux_new_array_fields = [];
            foreach ($array_fields as $key => $value) {
                if (is_array($value)) {
                    $size = count($level_values);
                    for ($i = 0; $i < $size; $i++) {
                        $aux_new_array_fields[$key][$level_values[$i]] = $value[$i];
                    }
                }

            }

            return $aux_new_array_fields;

        }

        return false;

    }

    public function updateValues(
        $name = '',
        $active = '',
        $has_review = '',
        $detail = '',
        $images = '',
        $has_cover_image = '',
        $has_logo_image = '',
        $levelValue,
        $type = 'names',
        $popular = '',
        $classified_association_quantity = ''
    ) {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        if ($type === 'names') {
            $sql = sprintf(
                "UPDATE ListingLevel SET name='%s', active='%s', popular='%s' WHERE value = '%s'",
                $name, $active, $popular, $levelValue
            );
        } elseif ($type === 'fields') {
            $sql = sprintf(
                "UPDATE ListingLevel SET detail = '%s', has_review = '%s', images = '%s', has_cover_image = '%s', has_logo_image = '%s',classified_quantity_association = '%s' WHERE value = '%d'",
                $detail, $has_review, $images, $has_cover_image, $has_logo_image, $classified_association_quantity,
                $levelValue
            );
        }

        $dbObj->query($sql);
    }

    public function updatePricing($field, $fieldValue, $level)
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        $sql = "UPDATE ListingLevel SET $field = ".$fieldValue.' WHERE value = '.$level;
        $dbObj->query($sql);
    }

    public function updateFeatured($newValue, $level)
    {

        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $dbObj = db_getDBObjectByDomainID(SELECTED_DOMAIN_ID, $dbMain);

        $sql = "UPDATE ListingLevel SET featured = '{$newValue}' WHERE value = ".$level;
        $dbObj->query($sql);
    }
}
	