<?php

namespace ArcaSolutions\ListingBundle\Entity\Internal;

use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use Doctrine\Bundle\DoctrineBundle\Registry;

class ListingLevelFeatures
{
    /**
     * @var boolean
     */
    public $hasDetail = false;
    /**
     * @var boolean
     */
    public $hasReview = false;
    /**
     * @var boolean
     */
    public $hasEmail = false;
    /**
     * @var boolean
     */
    public $hasURL = false;
    /**
     * @var boolean
     */
    public $hasPhone = false;
    /**
     * @var boolean
     */
    public $hasAdditionalPhone = false;
    /**
     * @var boolean
     */
    public $hasVideo = false;
    /**
     * @var boolean
     */
    public $hasAdditionalFiles = false;
    /**
     * @var boolean
     */
    public $hasSummaryDescription = false;
    /**
     * @var boolean
     */
    public $hasLongDescription = false;
    /**
     * @var boolean
     */
    public $hasHoursOfWork = false;
    /**
     * @var boolean
     */
    public $hasLocationReference = false;
    /**
     * @var boolean
     */
    public $hasBadges = false;
    /**
     * @var boolean
     */
    public $hasSocialNetworking = false;
    /**
     * @var boolean
     */
    public $hasFeatureInformation = false;
    /**
     * @var boolean
     */
    public $isActive = false;
    /**
     * @var boolean
     */
    public $isFeatured = false;
    /**
     * @var boolean
     */
    public $isPopular = false;
    /**
     * @var boolean
     */
    public $isDefault = false;
    /**
     * @var double
     */
    public $categoryPrice = 0;
    /**
     * @var double
     */
    public $price = 0;
    /**
     * @var int
     */
    public $freeCategoryCount = 0;
    /**
     * @var int
     */
    public $imageCount = 0;
    /**
     * @var int
     */
    public $level = 0;
    /**
     * @var string
     */
    public $name = null;
    /**
     * @var int
     */
    public $dealCount = 0;
    /**
     * @var int
     */
    public $classifiedQuantityAssociation = 0;
    /**
     * @var double
     */
    public $price_yearly = 0;
    /**
     * @var integer
     */
    public  $trial = 0;
    /**
     * @var boolean
     */
    public $hasCoverImage;
    /**
     * @var boolean
     */
    public $hasLogoImage;

    public function __construct()
    {
        /* ModStores Hooks */
        HookFire('listinglevel_construct', [
            'that' => &$this
        ]);
    }

    /**
     * @param Registry $doctrine
     * @param array $settings
     * @return array
     */
    public static function getAllLevelsAndNormalize($doctrine, array $settings = [])
    {
        $return = [];

        $levels = $doctrine->getRepository('ListingBundle:ListingLevel')->findBy(['active' => 'y'], ['value' => 'DESC']);

        /* @var $level ListingLevel */
        foreach ($levels as $level) {
            $listingLevel = static::normalizeLevel($level, $doctrine, $settings);

            $return[$level->getValue()] = $listingLevel;
        }

        return $return;
    }

    /**
     * @param ListingLevel $level
     * @param $doctrine
     * @param array $settings
     * @return ListingLevelFeatures
     */
    public static function normalizeLevel(ListingLevel $level, $doctrine, array $settings = [])
    {
        $fields = $doctrine->getRepository('ListingBundle:ListingLevelField')->findBy([
            'level' => $level->getValue()
        ]);

        $listingLevel = new self();

        $listingLevel->name = (string)$level->getName();

        $listingLevel->isActive = $level->getActive() === 'y';
        $listingLevel->isDefault = $level->getDefaultlevel() === 'y';
        $listingLevel->isPopular = $level->getPopular() === 'y';

        $listingLevel->level = (int)$level->getValue();
        $listingLevel->imageCount = (int)$level->getImages();
        $listingLevel->hasCoverImage = $level->getHasCoverImage() === 'y';
        $listingLevel->hasLogoImage = $level->getHasLogoImage() === 'y';
        $listingLevel->dealCount = (int)$level->getDeals();
        $listingLevel->freeCategoryCount = (int)$level->getFreeCategory();

        $listingLevel->categoryPrice = (double)$level->getCategoryPrice();
        $listingLevel->price = (double)$level->getPrice();
        $listingLevel->price_yearly = (double)$level->getPriceYearly();
        $listingLevel->trial = (int)$level->getTrial();

        $listingLevel->hasDetail = $level->getDetail() === 'y';
        $listingLevel->hasReview = $level->getHasReview() === 'y';

        $listingLevel->isFeatured = $level->getFeatured() === 'y';
        $listingLevel->classifiedQuantityAssociation = $level->getClassifiedQuantityAssociation();

        /* Validate if Reviews are enabled for listings */
        if (isset($settings['review']) && $settings['review'] !== 'on') {
            unset($listingLevel->hasReview);
        }

        /* Validate if Deal is enabled for listings */
        if (isset($settings['deal']) && $settings['deal'] !== 'on') {
            unset($listingLevel->dealCount);
        }

        /* Validate if classified module is enabled */
        if (isset($settings['classified']) && $settings['classified'] !== 'on') {
            unset($listingLevel->classifiedQuantityAssociation);
        }

        foreach ($fields as $field) {
            switch ($field->getField()) {
                case 'email' :
                    $listingLevel->hasEmail = true;
                    break;
                case 'url' :
                    $listingLevel->hasURL = true;
                    break;
                case 'phone' :
                    $listingLevel->hasPhone = true;
                    break;
                case 'additional_phone' :
                    $listingLevel->hasAdditionalPhone = true;
                    break;
                case 'video' :
                    $listingLevel->hasVideo = true;
                    break;
                case 'attachment_file' :
                    $listingLevel->hasAdditionalFiles = true;
                    break;
                case 'summary_description' :
                    $listingLevel->hasSummaryDescription = true;
                    break;
                case 'long_description' :
                    $listingLevel->hasLongDescription = true;
                    break;
                case 'hours_of_work' :
                    $listingLevel->hasHoursOfWork = true;
                    break;
                case 'locations' :
                    $listingLevel->hasLocationReference = true;
                    break;
                case 'badges' :
                    $listingLevel->hasBadges = true;
                    break;
                case 'social_network' :
                    $listingLevel->hasSocialNetworking = true;
                    break;
                case 'features' :
                    $listingLevel->hasFeatureInformation = true;
                    break;
            }
        }

        /* ModStores Hooks */
        HookFire('listinglevelfeature_before_return', [
            'listingLevel' => &$listingLevel,
            'level'        => &$level,
            'fields'       => &$fields,
        ]);

        return $listingLevel;
    }

}
