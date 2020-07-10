<?php

namespace ArcaSolutions\ImportBundle\Services;

use ArcaSolutions\CoreBundle\Entity\Location1;
use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Entity\Location3;
use ArcaSolutions\CoreBundle\Entity\Location4;
use ArcaSolutions\CoreBundle\Entity\Location5;
use ArcaSolutions\CoreBundle\Str;
use ArcaSolutions\ImportBundle\Constants;
use ArcaSolutions\ImportBundle\Entity\ListingImport;
use ArcaSolutions\ImportBundle\Logic\ListingTypeLogic;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class ListingImportService
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Services
 * @since 11.3.00
 */
class ListingImportService extends ModuleImportService
{

    /**
     * @var ListingTypeLogic
     */
    private $listingType;

    /**
     * ListingImportService constructor.
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $container
     */
    public function __construct($container)
    {
        parent::__construct($container);

        $this->listingType = new ListingTypeLogic($this->domainManager);
    }

    /**
     *
     * @param ListingImport $listingImport
     * @return Listing
     */
    protected function buildModule($listingImport)
    {
        $listing = $this->findOneModuleById($listingImport->getListingId(), Listing::class);

        if ($listing == null) {
            $listing = new Listing();
            $listingImport->setListingId(0);
        } else {
            $listing->setUpdated(new \DateTime());
        }

        $listing->setTitle($listingImport->getListingTitle());

        $listing->setStatus($this->getStatus($listingImport->getListingStatus(), $listing->getStatus()));

        $listing->setSeoTitle($listingImport->getListingSeoTitle() ?: ($listing->getSeoTitle() ?: $listingImport->getListingTitle()));

        if ($listingImport->getListingSeoDescription() !== null) {
            $listing->setSeoDescription($listingImport->getListingSeoDescription());
        }

        if ($listingImport->getListingKeywords() !== null) {
            $listing->setSeoKeywords(str_replace(' || ', ',', $listingImport->getListingKeywords()));
        }

        if ($listingImport->getListingThirdPartyId() !== null) {
            $listing->setCustomId($listingImport->getListingThirdPartyId());
        }

        if ($listingImport->getListingAddress() !== null) {
            $listing->setAddress($listingImport->getListingAddress(120));
        }

        if ($listingImport->getListingAddress2() !== null) {
            $listing->setAddress2($listingImport->getListingAddress2(120));
        }

        if ($listingImport->getListingRenewalDate()) {
            $listing->setRenewalDate(\Datetime::createFromFormat($this->dateFormat,
                $listingImport->getListingRenewalDate()));
        }

        if ($categories = $this->getCategories($listingImport)) {
            $listing->setCategories(new ArrayCollection());
            foreach ($categories as $category) {
                if (!$listing->getCategories()->contains($category)) {
                    $listing->getCategories()->add($category);
                }
            }
        }

        if ($friendlyUrl = $this->getFriendlyUrl($listingImport->getListingTitle(), $listingImport->getListingId(),
            Listing::class)) {
            $listing->setFriendlyUrl($friendlyUrl);
        }

        if ($listingImport->getListingEmail() !== null){
            $listing->setEmail($listingImport->getListingEmail());
        }

        if ($listingImport->getListingKeywords() !== null) {
            $listing->setKeywords($listingImport->getListingKeywords());
        }

        if ($listingImport->getListingLatitude() !== null) {
            $listing->setLatitude($listingImport->getListingLatitude());
        }

        if ($listingImport->getListingLongitude() !== null) {
            $listing->setLongitude($listingImport->getListingLongitude());
        }

        $listing->setLevelObj($this->getLevel($listingImport->getListingLevel()));

        if ($listingImport->getListingShortDescription() !== null) {
            $listing->setDescription($listingImport->getListingShortDescription());
        }

        if ($listingImport->getListingLongDescription() !== null) {
            $listing->setLongDescription($listingImport->getListingLongDescription());
        }

        if ($listingImport->getListingPhone() !== null) {
            $listing->setPhone($listingImport->getListingPhone());
        }

        if ($listingImport->getListingUrl() !== null) {
            $listing->setUrl($listingImport->getListingUrl());
        }

        if ($listingImport->getListingZipCode() !== null) {
            $listing->setZipCode(trim($listingImport->getListingZipCode()));
        }

        $listing->setImport($this->import);

        //
        // IMPORTANT: Must follow location sequence to guarantee location logic.
        //
        $activeLocations = $this->container->get("location.service")->getLocationsEnabled();

        $parentLocation = null;
        $location1 = $this->locationLogic->getLocation($listingImport->getListingCountry(),
            $listingImport->getListingCountryAbbreviation(), Location1::class, $parentLocation, $activeLocations);
        $parentLocation = $location1 ?: $parentLocation;
        $location2 = $this->locationLogic->getLocation($listingImport->getListingRegion(),
            $listingImport->getListingRegionAbbreviation(), Location2::class, $parentLocation, $activeLocations);
        $parentLocation = $location2 ?: $parentLocation;
        $location3 = $this->locationLogic->getLocation($listingImport->getListingState(),
            $listingImport->getListingStateAbbreviation(), Location3::class, $parentLocation, $activeLocations);
        $parentLocation = $location3 ?: $parentLocation;
        $location4 = $this->locationLogic->getLocation($listingImport->getListingCity(),
            $listingImport->getListingCityAbbreviation(), Location4::class, $parentLocation, $activeLocations);
        $parentLocation = $location4 ?: $parentLocation;
        $location5 = $this->locationLogic->getLocation($listingImport->getListingNeighborhood(),
            $listingImport->getListingNeighborhoodAbbreviation(), Location5::class, $parentLocation, $activeLocations);
        $this->mainManager->flush();

        if ($listingImport->getListingCountry()) {
            $listing->setLocation1($location1 != null ? $location1->getId() : null);
        }

        if ($listingImport->getListingRegion()) {
            $listing->setLocation2($location2 != null ? $location2->getId() : null);
        }

        if ($listingImport->getListingState()) {
            $listing->setLocation3($location3 != null ? $location3->getId() : null);
        }

        if ($listingImport->getListingCity()) {
            $listing->setLocation4($location4 != null ? $location4->getId() : null);
        }

        if ($listingImport->getListingNeighborhood()) {
            $listing->setLocation5($location5 != null ? $location5->getId() : null);
        }

        if ($fullSearchKeyword = $this->getFullTextSearchKeyword($listingImport, $categories)) {
            $listing->setFulltextsearchKeyword(implode(' ', $fullSearchKeyword));
        }

        if ($fullSearchWhere = $this->getFullTextSearchWhere($listingImport, $listing)) {
            $listing->setFulltextsearchWhere(implode(' ', $fullSearchWhere));
        }

        if ($location1) {
            $this->mainManager->detach($location1);
        }
        if ($location2) {
            $this->mainManager->detach($location2);
        }
        if ($location3) {
            $this->mainManager->detach($location3);
        }
        if ($location4) {
            $this->mainManager->detach($location4);
        }
        if ($location5) {
            $this->mainManager->detach($location5);
        }
        unset($location1, $location2, $location3, $location4, $location5);

        if ($listingImport->getAccountUsername()) {
            /* Saves the account */
            $listing->setAccount($this->getAccount($listingImport));
        }

        /* Saves the listing type */
        $listing->setTemplate($this->listingType->getListingType($listingImport->getListingListingTypeName(), $listing->getTemplate()));

        return $listing;
    }

    /**
     * @param ListingImport $listingImport
     * @return ListingCategory[]
     */
    private function getCategories($listingImport)
    {
        $categoriesTitles = $this->getCategoriesTitles($listingImport);

        $category = null;
        $categories = [];

        foreach ($categoriesTitles as $titles) {
            foreach ($titles as $title) {
                $category = $this->findCategoryByTitleWithParentId($title, $category);
            }

            $categories[] = $category;
            $category = null;
        }

        unset($title, $titles, $category, $categoriesTitles, $listingImport);

        return $categories;
    }

    /**
     * @param ListingImport $listingImport
     * @return array
     * @throws \ReflectionException
     */
    private function getCategoriesTitles($listingImport)
    {
        $reflectionClass = new \ReflectionClass(ListingImport::class);
        $properties = $reflectionClass->getProperties();
        $categories = [];

        foreach ($properties as $i => $property) {
            if (strpos($property->name, 'listingCategory') !== 0) {
                continue;
            }

            $property->setAccessible(true);
            $value = $property->getValue($listingImport);

            if (!$value) {
                continue;
            }

            $titles = array_map('trim', explode(Constants::CATEGORY_SEPARATOR, $value));

            foreach ($titles as $title) {
                if (!empty($title)) {
                    $categories[$i][] = $title;
                }
            }

            unset($value);
        }

        unset($properties, $reflectionClass);

        return $categories;
    }

    /**
     * @param $title
     * @param ListingCategory|null $parent
     * @return ListingCategory
     */
    private function findCategoryByTitleWithParentId($title, $parent = null)
    {
        $listingCategory = null;

        $listingCategoryRepository = $this->domainManager->getRepository(ListingCategory::class);
        $listingCategory = $listingCategoryRepository->findOneBy([
            'title'  => $title,
            'parent' => $parent != null ? ['id' => $parent->getId()] : null,
        ]);

        if (!$listingCategory) {
            $listingCategory = new ListingCategory();
            $listingCategory->setTitle($title);
            $listingCategory->setPageTitle($title);
            $listingCategory->setFriendlyUrl($this->friendlyUrlLogic->buildUniqueFriendlyUrl($title));
            $listingCategory->setFeatured($this->import->isNewCategoriesAsFeatured() ? 'y' : 'n');
            $listingCategory->setEnabled('y');
            $listingCategory->setImport($this->import);

            if ($parent != null) {
                $listingCategory->setParent($parent);
                $listingCategory->setCategoryId($parent->getId());
            }

            $this->domainManager->persist($listingCategory);
            $this->domainManager->flush($listingCategory);

            $this->domainManager->flush($listingCategory);

        }

        unset($title, $parent);

        return $listingCategory;
    }

    /**
     * @inheritdoc
     */
    protected function getFullTextSearchKeyword($moduleImport, array $moduleCategories)
    {
        $fullText = [];

        /* Title */
        $fullText[] = $moduleImport->getListingTitle();

        /* Title without apostle */
        if ($titleApostle = Str::replaceApostleWords($moduleImport->getListingTitle())) {
            $fullText[] = implode(' ', $titleApostle);
            unset($titleApostle);
        }

        /* Keywords */
        if ($keywords = $moduleImport->getListingKeywords()) {
            $keywords = str_replace(' || ', ' ', $keywords);
            $fullText[] = $keywords;
            /* Keyword without apostle */
            if ($keywordsApostle = Str::replaceApostleWords($keywords)) {
                $fullText[] = implode(' ', $keywordsApostle);
                unset($keywordsApostle);
            }
            unset($keywords);
        }

        /* Description */
        if ($moduleImport->getListingShortDescription()) {
            $fullText[] = $moduleImport->getListingShortDescription();
            unset($shortDescription);
        }

        /* Categories */
        if ($moduleCategories) {
            /* @var ListingCategory $category */
            foreach ($moduleCategories as $category) {
                while (!is_null($category)) {
                    /* Category title */
                    if ($category->getTitle()) {
                        $fullText[] = $category->getTitle();
                    }

                    /* Category keyword */
                    if ($categoryKeywords = $category->getKeywords()) {
                        $fullText[] = str_replace(["\r\n", "\n"], ' ', $categoryKeywords);
                    }

                    $category = $category->getParent();
                }
            }
        }

        return array_unique($fullText);
    }

    /**
     * @inheritdoc
     */
    protected function getFullTextSearchWhere($moduleImport, $module)
    {
        $fullText = [];

        /* Address */
        if ($moduleImport->getListingAddress()) {
            $fullText[] = $moduleImport->getListingAddress(120);
        }

        /* ZipCode */
        if ($moduleImport->getAccountZipCode()) {
            $fullText[] = $moduleImport->getAccountZipCode();
        }

        /* Locations */

        if ($module->getLocation1()) {
            /* @var Location1 $location */
            $location = $this->locationLogic->getLocationId($module->getLocation1(), Location1::class);
            if ($location) {
                /* Location Title */
                $fullText[] = $location->getName();
                /* Location Abbreviation */
                if ($location->getAbbreviation()) {
                    $fullText[] = $location->getAbbreviation();
                }
            }
        }

        if ($module->getLocation2()) {
            /* @var Location2 $location */
            $location = $this->locationLogic->getLocationId($module->getLocation2(), Location2::class);
            if ($location) {
                /* Location Title */
                $fullText[] = $location->getName();
                /* Location Abbreviation */
                if ($location->getAbbreviation()) {
                    $fullText[] = $location->getAbbreviation();
                }
            }
        }

        if ($module->getLocation3()) {
            /* @var Location3 $location */
            $location = $this->locationLogic->getLocationId($module->getLocation3(), Location3::class);
            if ($location) {
                /* Location Title */
                $fullText[] = $location->getName();
                /* Location Abbreviation */
                if ($location->getAbbreviation()) {
                    $fullText[] = $location->getAbbreviation();
                }
            }
        }

        if ($module->getLocation4()) {
            /* @var Location4 $location */
            $location = $this->locationLogic->getLocationId($module->getLocation4(), Location4::class);
            if ($location) {
                /* Location Title */
                $fullText[] = $location->getName();
                /* Location Abbreviation */
                if ($location->getAbbreviation()) {
                    $fullText[] = $location->getAbbreviation();
                }
            }
        }

        if ($module->getLocation5()) {
            /* @var Location5 $location */
            $location = $this->locationLogic->getLocationId($module->getLocation5(), Location5::class);
            if ($location) {
                /* Location Title */
                $fullText[] = $location->getName();
                /* Location Abbreviation */
                if ($location->getAbbreviation()) {
                    $fullText[] = $location->getAbbreviation();
                }
            }
        }

        return array_unique($fullText);

    }

    /**
     * @return ListingLevel[]
     */
    protected function getModuleLevels()
    {
        return $this->domainManager->getRepository(ListingLevel::class)->findAll();
    }
}
