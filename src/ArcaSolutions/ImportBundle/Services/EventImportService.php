<?php

namespace ArcaSolutions\ImportBundle\Services;

use ArcaSolutions\CoreBundle\Entity\Location1;
use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Entity\Location3;
use ArcaSolutions\CoreBundle\Entity\Location4;
use ArcaSolutions\CoreBundle\Entity\Location5;
use ArcaSolutions\CoreBundle\Str;
use ArcaSolutions\ImportBundle\Constants;
use ArcaSolutions\ImportBundle\Entity\EventImport;
use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\EventBundle\Entity\Eventcategory;
use ArcaSolutions\EventBundle\Entity\EventLevel;
use ArcaSolutions\ImportBundle\Exception\InvalidModuleException;

/**
 * Class EventImportService
 * @package ArcaSolutions\ImportBundle\Services
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @since 11.3.00
 */
class EventImportService extends ModuleImportService
{

    /**
     * @param EventImport $eventImport
     * @return Event
     * @throws InvalidModuleException
     * @throws \ArcaSolutions\CoreBundle\Exception\EmailNotificationServicesException
     * @throws \ArcaSolutions\ImportBundle\Exception\InvalidLocationNameException
     */
    protected function buildModule($eventImport)
    {
        $event = $this->findOneModuleById($eventImport->getEventId(), Event::class);

        if ($event == null) {
            $event = new Event();
            $eventImport->setEventId(0);
        } else {
            $event->setUpdated(new \DateTime());
        }

        $event->setTitle($eventImport->getEventTitle());

        $event->setStatus($this->getStatus($eventImport->getEventStatus(), $event->getStatus()));

        $event->setSeoTitle($eventImport->getEventSeoTitle() ?: ($event->getSeoTitle() ?: $eventImport->getEventTitle()));

        if ($eventImport->getEventSeoDescription() !== null) {
            $event->setSeoDescription($eventImport->getEventSeoDescription());
        }

        if ($eventImport->getEventKeywords() !== null) {
            $event->setSeoKeywords(str_replace(" || ", ",", $eventImport->getEventKeywords()));
        }

        if ($eventImport->getEventThirdPartyId() !== null) {
            $event->setCustomId($eventImport->getEventThirdPartyId());
        }

        if ($eventImport->getEventAddress(255) !== null) {
            $event->setAddress($eventImport->getEventAddress(255));
        }

        if ($eventImport->getEventContactName() !== null) {
            $event->setContactName($eventImport->getEventContactName());
        }

        if ($eventImport->getEventRenewalDate()) {
            $event->setRenewalDate(\Datetime::createFromFormat($this->dateFormat,
                $eventImport->getEventRenewalDate()));
        }

        /* Venue */
        if ($eventImport->getEventLocation() !== null) {
            $event->setLocation($eventImport->getEventLocation());
        }

        if ($categories = $this->getCategories($eventImport)) {
            $this->setEventCategories($event, $categories);
        }

        if ($eventImport->getEventStartDate() !== null) {
            $event->setStartDate(\Datetime::createFromFormat($this->dateFormat,
                $eventImport->getEventStartDate()));
        }

        if ($eventImport->getEventEndDate() !== null) {
            $event->setEndDate(\Datetime::createFromFormat($this->dateFormat,
                $eventImport->getEventEndDate()));
        }

        if ($eventImport->getEventStartTime() !== null) {
            $event->setStartTime(
                \Datetime::createFromFormat(
                    $this->dateFormat. " H:i:s",
                    $eventImport->getEventStartDate() ." ". $this->getEventTimeWithClockType($eventImport->getEventStartTime())
                )
            );
        }
        if ($eventImport->getEventEndTime() !== null) {
            $event->setEndTime(
                \Datetime::createFromFormat(
                    $this->dateFormat. " H:i:s",
                    $eventImport->getEventStartDate() ." ".$this->getEventTimeWithClockType($eventImport->getEventEndTime())
                )
            );
        }

        if ($friendlyUrl = $this->getFriendlyUrl($eventImport->getEventTitle(), $eventImport->getEventId() ,Event::class)) {
            $event->setFriendlyUrl($friendlyUrl);
        }

        if ($eventImport->getEventEmail() !== null) {
            $event->setEmail($eventImport->getEventEmail());
        }

        if ($eventImport->getEventKeywords() !== null) {
            $event->setKeywords($eventImport->getEventKeywords());
        }

        if ($eventImport->getEventLatitude() !== null) {
            $event->setLatitude($eventImport->getEventLatitude());
        }

        if ($eventImport->getEventLongitude() !== null) {
            $event->setLongitude($eventImport->getEventLongitude());
        }

        $event->setLevelObj($this->getLevel($eventImport->getEventLevel()));

        if ($eventImport->getEventShortDescription() !== null) {
            $event->setDescription($eventImport->getEventShortDescription());
        }

        if ($eventImport->getEventLongDescription() !== null) {
            $event->setLongDescription($eventImport->getEventLongDescription());
        }

        if ($eventImport->getEventPhone() !== null) {
            $event->setPhone($eventImport->getEventPhone());
        }

        if ($eventImport->getEventUrl() !== null) {
            $event->setUrl($eventImport->getEventUrl());
        }

        if ($eventImport->getEventZipCode() !== null) {
            $event->setZipCode(trim($eventImport->getEventZipCode()));
        }
        $event->setImport($this->import);

        //
        // IMPORTANT: Must follow location sequence to guarantee location logic.
        //
        $activeLocations = $this->container->get("location.service")->getLocationsEnabled();

        $parentLocation = null;
        $location1 = $this->locationLogic->getLocation($eventImport->getEventCountry(),
            $eventImport->getEventCountryAbbreviation(), Location1::class, $parentLocation, $activeLocations);
        $parentLocation = $location1?: $parentLocation;
        $location2 = $this->locationLogic->getLocation($eventImport->getEventRegion(),
            $eventImport->getEventRegionAbbreviation(), Location2::class, $parentLocation, $activeLocations);
        $parentLocation = $location2?: $parentLocation;
        $location3 = $this->locationLogic->getLocation($eventImport->getEventState(),
            $eventImport->getEventStateAbbreviation(), Location3::class, $parentLocation, $activeLocations);
        $parentLocation = $location3?: $parentLocation;
        $location4 = $this->locationLogic->getLocation($eventImport->getEventCity(),
            $eventImport->getEventCityAbbreviation(), Location4::class, $parentLocation, $activeLocations);
        $parentLocation = $location4?: $parentLocation;
        $location5 = $this->locationLogic->getLocation($eventImport->getEventNeighborhood(),
            $eventImport->getEventNeighborhoodAbbreviation(), Location5::class, $parentLocation, $activeLocations);
        $this->mainManager->flush();

        if ($eventImport->getEventCountry()) {
            $event->setLocation1($location1 != null ? $location1->getId() : null);
        }
        if ($eventImport->getEventRegion()) {
            $event->setLocation2($location2 != null ? $location2->getId() : null);
        }
        if ($eventImport->getEventState()) {
            $event->setLocation3($location3 != null ? $location3->getId() : null);
        }
        if ($eventImport->getEventCity()) {
            $event->setLocation4($location4 != null ? $location4->getId() : null);
        }
        if ($eventImport->getEventNeighborhood()) {
            $event->setLocation5($location5 != null ? $location5->getId() : null);
        }

        if ($fullSearchKeyword = $this->getFullTextSearchKeyword($eventImport, $categories)) {
            $event->setFulltextsearchKeyword(implode(" ", $fullSearchKeyword));
        }

        if ($fullSearchWhere = $this->getFullTextSearchWhere($eventImport, $event)) {
            $event->setFulltextsearchWhere(implode(" ", $fullSearchWhere));
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

        if ($eventImport->getAccountUsername()) {
            /* Saves the account */
            $event->setAccountId($this->getAccount($eventImport)->getAccountId());
        }

        return $event;
    }

    /**
     * @param EventImport $eventImport
     * @return Eventcategory[]
     */
    private function getCategories($eventImport)
    {
        $categoriesTitles = $this->getCategoriesTitles($eventImport);

        $category = null;
        $categories = [];

        foreach ($categoriesTitles as $titles) {
            foreach ($titles as $title) {
                $category = $this->findCategoryByTitleWithParentId($title, $category);
            }

            $categories[] = $category;
            $category = null;
        }

        unset($title, $titles, $category, $categoriesTitles, $eventImport);

        return $categories;
    }

    /**
     * @param EventImport $eventImport
     * @return array
     */
    private function getCategoriesTitles($eventImport)
    {
        $reflectionClass = new \ReflectionClass(EventImport::class);
        $properties = $reflectionClass->getProperties();
        $categoriesTitles = [];
        foreach ($properties as $property) {
            if (strpos($property->name, "eventCategory") === 0) {
                $property->setAccessible(true);
                $value = $property->getValue($eventImport);
                if ($value) {
                    $categoriesTitles[] = array_map('trim', explode(Constants::CATEGORY_SEPARATOR, $value));
                }
                unset($value);
            }
        }
        unset($eventImport);
        unset($properties);
        unset($reflectionClass);

        return $categoriesTitles;
    }

    /**
     * @param $title
     * @param Eventcategory|null $parent
     * @return Eventcategory
     */
    private function findCategoryByTitleWithParentId($title, $parent)
    {
        $eventCategory = null;

        $eventCategoryRepository = $this->domainManager->getRepository(Eventcategory::class);
        $eventCategory = $eventCategoryRepository->findOneBy([
            "title"  => $title,
            "parent" => $parent != null ? ["id" => $parent->getId()] : null,
        ]);

        if (!$eventCategory) {
            $eventCategory = new Eventcategory();
            $eventCategory->setTitle($title);
            $eventCategory->setPageTitle($title);
            $eventCategory->setFriendlyUrl($this->friendlyUrlLogic->buildUniqueFriendlyUrl($title));
            $eventCategory->setFeatured($this->import->isNewCategoriesAsFeatured());
            $eventCategory->setEnabled('y');
            $eventCategory->setImport($this->import);

            if ($parent != null) {
                $eventCategory->setParent($parent);
            }

            $this->domainManager->persist($eventCategory);
            $this->domainManager->flush($eventCategory);
        }

        unset($title, $parent);

        return $eventCategory;
    }

    /**
     * @param Event $event
     * @param $categories
     */
    private function setEventCategories(&$event, $categories)
    {
        $categoryCount = 1;
        foreach ($categories as $category) {
            /* @var Eventcategory $category */
            $categoryId = $category->getId();
            $parentCategory = $category;
            $i = 0;
            $parents = [];
            while ($categoryId != 0) {
                $categoryId = 0;
                if ($parentCategory->getParent()) {
                    /* @var Eventcategory $parentCategory */
                    $parentCategory = $parentCategory->getParent();
                    $categoryId = $parentCategory->getId();
                    $parents[$i++] = $categoryId;
                }

            }
            for ($j = count($parents); $j < 4; $j++) {
                $parents[$j] = 0;
            }
            $event->{"setCategory".$categoryCount}($category);
            $event->{"setParcat".$categoryCount."Level1Id"}($parents[0]);
            $event->{"setParcat".$categoryCount."Level2Id"}($parents[1]);
            $event->{"setParcat".$categoryCount."Level3Id"}($parents[2]);
            $event->{"setParcat".$categoryCount."Level4Id"}($parents[3]);
            $categoryCount++;
        }
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param EventImport $moduleImport
     * @param array $moduleCategories
     * @return array
     * @throws InvalidModuleException
     */
    protected function getFullTextSearchKeyword($moduleImport, array $moduleCategories)
    {
        if (!$moduleImport instanceof EventImport) {
            throw new InvalidModuleException();
        }

        $fullText = [];

        /* Title */
        $fullText[] = $moduleImport->getEventTitle();

        /* Title without apostle */
        if ($titleApostle = Str::replaceApostleWords($moduleImport->getEventTitle())) {
            $fullText[] = implode(" ", $titleApostle);
            unset($titleApostle);
        }

        /* Keywords */
        if ($keywords = $moduleImport->getEventKeywords()) {
            $keywords = str_replace(" || ", " ", $keywords);
            $fullText[] = $keywords;
            /* Keyword without apostle */
            if ($keywordsApostle = Str::replaceApostleWords($keywords)) {
                $fullText[] = implode(" ", $keywordsApostle);
                unset($keywordsApostle);
            }
            unset($keywords);
        }

        /* Description */
        if ($moduleImport->getEventShortDescription()) {
            $fullText[] = $moduleImport->getEventShortDescription();
            unset($shortDescription);
        }

        /* Categories */
        if ($moduleCategories) {
            /* @var Eventcategory $category */
            foreach ($moduleCategories as $category) {
                while (!is_null($category)) {
                    /* Category title */
                    if ($category->getTitle()) {
                        $fullText[] = $category->getTitle();
                    }

                    /* Category keyword */
                    if ($categoryKeywords = $category->getKeywords()) {
                        $fullText[] = str_replace(["\r\n", "\n"], " ", $categoryKeywords);
                    }

                    $category = $category->getParent();
                }
            }
        }

        return array_unique($fullText);
    }

    /**
     * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
     * @since 11.3.00
     *
     * @param EventImport $moduleImport
     * @param Event $module
     * @return array
     * @throws InvalidModuleException
     * @throws \ArcaSolutions\ImportBundle\Exception\LocationNotFoundException
     */
    protected function getFullTextSearchWhere($moduleImport, $module)
    {
        if (!$moduleImport instanceof EventImport) {
            throw new InvalidModuleException();
        }

        $fullText = [];

        /* Address */
        if ($moduleImport->getEventAddress()) {
            $fullText[] = $moduleImport->getEventAddress(255);
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
     * Return all the levels of the module to be imported
     *
     * @return EventLevel[]
     */
    protected function getModuleLevels()
    {
        return $this->domainManager->getRepository(EventLevel::class)->findAll();
    }

    /**
     * Retrieve the time in the H:i format
     *
     * @param $time
     * @return string
     */
    public function getEventTimeWithClockType($time)
    {
        $time = str_replace(" ", "", $time);
        $timeSplited = explode(":", $time);

        /* hours part */
        $timeSplited[0] = (int)$timeSplited[0];
        /* hours part */
        $timeSplited[1] = (int)$timeSplited[1];

        if ($this->timeFormat == 12) {
            /* ["pm", "am"] */
            $timeMode = substr(trim($time), -2);

            if (strtolower($timeMode) == "pm") {
                $hourPart = $timeSplited[0];

                $timeSplited[0] = $hourPart + 12;
            }
        }

        if ($timeSplited[0] < 10){
            $timeSplited[0] = "0".(string)$timeSplited[0];
        }
        if ($timeSplited[1] < 10){
            $timeSplited[1] = "0".(string)$timeSplited[1];
        }

        $time = implode(":", [$timeSplited[0], $timeSplited[1], "00"]);
        return $time;
    }
}
