<?php

namespace ArcaSolutions\ClassifiedBundle\Entity\Internal;

use ArcaSolutions\ClassifiedBundle\Entity\ClassifiedLevel;
use Doctrine\Bundle\DoctrineBundle\Registry;

class ClassifiedLevelFeatures
{
    /**
     * @var boolean
     */
    public $hasDetail = false;
    /**
     * @var boolean
     */
    public $hasURL = false;
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
    public $hasContactName = false;
    /**
     * @var boolean
     */
    public $hasPhone = false;
    /**
     * @var boolean
     */
    public $hasEmail = false;
    /**
     * @var boolean
     */
    public $hasClassifiedPrice = false;
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
    public $price = 0;
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
     * @var double
     */
    public $price_yearly = 0;
    /**
     * @var integer
     */
    public $trial = 0;
    /**
     * @var boolean
     */
    public $hasCoverImage;

    public function __construct()
    {
        /* ModStores Hooks */
        HookFire("classifiedlevel_construct", [
            "that" => &$this
        ]);
    }

    /**
     * @param Registry $doctrine
     *
     * @return array
     */
    public static function getAllLevelsAndNormalize($doctrine)
    {
        $return = [];

        $levels = $doctrine->getRepository('ClassifiedBundle:ClassifiedLevel')->findBy(['active' => 'y'], ['value' => 'DESC']);

        foreach ($levels as $level) {
            $classifiedLevel = static::normalizeLevel($level, $doctrine);

            $return[$level->getValue()] = $classifiedLevel;
        }

        return $return;
    }

    public static function normalizeLevel(ClassifiedLevel $level, $doctrine)
    {
        $fields = $doctrine->getRepository('ClassifiedBundle:ClassifiedLevelField')->findBy([
            'level' => $level->getValue()
        ]);

        $classifiedLevel = new self();

        $classifiedLevel->name = (string)$level->getName();

        $classifiedLevel->isActive = $level->getActive() === 'y';
        $classifiedLevel->isDefault = $level->getDefaultlevel() === 'y';
        $classifiedLevel->isPopular = $level->getPopular() === 'y';
        $classifiedLevel->hasCoverImage = $level->getHasCoverImage() === 'y';

        $classifiedLevel->level = (int)$level->getValue();
        $classifiedLevel->imageCount = (int)$level->getImages();

        $classifiedLevel->price = (double)$level->getPrice();
        $classifiedLevel->price_yearly = (double)$level->getPriceYearly();
        $classifiedLevel->trial = (int)$level->getTrial();

        /* Setting all the has */

        $classifiedLevel->hasDetail = $level->getDetail() === 'y';
        $classifiedLevel->hasVideo = $level->getVideo() === 'y';
        $classifiedLevel->hasAdditionalFiles = $level->getAdditionalFiles() === 'y';

        $classifiedLevel->isFeatured = $level->getFeatured() === 'y';

        foreach ($fields as $field) {
            switch ($field->getField()) {
                case 'contact_name' :
                    $classifiedLevel->hasContactName = true;
                    break;
                case 'contact_phone' :
                    $classifiedLevel->hasPhone = true;
                    break;
                case 'contact_email' :
                    $classifiedLevel->hasEmail = true;
                    break;
                case 'url' :
                    $classifiedLevel->hasURL = true;
                    break;
                case 'long_description' :
                    $classifiedLevel->hasLongDescription = true;
                    break;
                case 'summary_description' :
                    $classifiedLevel->hasSummaryDescription = true;
                    break;
                case 'price' :
                    $classifiedLevel->hasClassifiedPrice = true;
                    break;
            }
        }

        /* ModStores Hooks */
        HookFire("classifiedlevelfeature_before_return", [
            "classifiedLevel" => &$classifiedLevel,
            "level"           => &$level,
            "fields"          => &$fields,
        ]);

        return $classifiedLevel;
    }

}
