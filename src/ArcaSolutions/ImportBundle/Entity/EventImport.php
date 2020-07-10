<?php

namespace ArcaSolutions\ImportBundle\Entity;

use ArcaSolutions\ImportBundle\Annotation as Edirectory;
use ArcaSolutions\ImportBundle\Validator\Constraints as EdirectoryAssert;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class EventImport
 *
 * @author Marcos Sartori <marcos.sartori@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Entity
 * @since 11.3.00
 *
 * @EdirectoryAssert\AccountPassword(message="Account Password cannot be empty and must between 4 and 50 characters if an Account Username is provided.")
 * @EdirectoryAssert\AccountFirstName(message="Account First Name cannot be empty if an Account Username is provided.")
 * @EdirectoryAssert\AccountLastName(message="Account Last Name cannot be empty if an Account Username is provided.")
 * @EdirectoryAssert\EventEndDate(message="End date should be a date greater than the start date.")
 * @EdirectoryAssert\LocationHierarchy(message="Location hierarchy doesn't match the system settings. One or more locations are missing (for instance, city without state).", module="event")
 */
class EventImport
{

    /**
     * @since 11.3.00
     * @var string
     */
    private $id;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Title", mappingRequired=true)
     * @EdirectoryAssert\EventTitle(message="Title cannot be empty.")
     *
     */
    private $eventTitle;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event SEO Title")
     *
     */
    private $eventSeoTitle;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Email")
     * @Assert\Email(message="The event email must be a valid email address")
     *
     */
    private $eventEmail;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Url")
     *
     */
    private $eventUrl;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Address")
     *
     */
    private $eventAddress;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Venue")
     *
     */
    private $eventLocation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Contact Name")
     */
    private $eventContactName;

    /**
     * @since 11.3.00
     * @var \DateTime
     *
     * @Edirectory\Import(name="Event Start Date", mappingRequired=true, isDate=true)
     * @EdirectoryAssert\DateFormat(message="Start date should be a valid date format and cannot be empty.", empty=false)
     */
    private $eventStartDate;

    /**
     * @since 11.3.00
     * @var \DateTime
     *
     * @Edirectory\Import(name="Event End Date", mappingRequired=true, isDate=true)
     * @EdirectoryAssert\DateFormat(message="End date should be a valid date format and cannot be empty.", empty=false)
     * @EdirectoryAssert\FutureDate(message="End date should be a valid date in the future.")
     */
    private $eventEndDate;

    /**
     * @since 11.3.00
     * @var \DateTime
     *
     * @Edirectory\Import(name="Event Start Time")
     * @EdirectoryAssert\TimeFormat(message="Time format must be the same as your domain configuration.")
     */
    private $eventStartTime;

    /**
     * @since 11.3.00
     * @var \DateTime
     *
     * @Edirectory\Import(name="Event End Time")
     * @EdirectoryAssert\TimeFormat(message="Time format must be the same as your domain configuration.")
     */
    private $eventEndTime;


    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Country")
     *
     */
    private $eventCountry;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Country Abbreviation")
     *
     */
    private $eventCountryAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Region")
     *
     */
    private $eventRegion;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Region Abbreviation")
     *
     */
    private $eventRegionAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event State")
     *
     */
    private $eventState;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event State Abbreviation")
     *
     */
    private $eventStateAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event City")
     *
     */
    private $eventCity;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event City Abbreviation")
     *
     */
    private $eventCityAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Neighborhood")
     *
     */
    private $eventNeighborhood;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Neighborhood Abbreviation")
     *
     */
    private $eventNeighborhoodAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Postal Code")
     *
     */
    private $eventZipCode;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Latitude")
     * @EdirectoryAssert\Latitude(message="Latitude should be a number between -90 and 90.")
     *
     */
    private $eventLatitude;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Longitude")
     * @EdirectoryAssert\Longitude(message="Longitude should be a number between -180 and 180.")
     *
     */
    private $eventLongitude;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Phone")
     *
     */
    private $eventPhone;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Short Description")
     *
     */
    private $eventShortDescription;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Long Description")
     *
     */
    private $eventLongDescription;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Seo Description")
     *
     */
    private $eventSeoDescription;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Keywords")
     * @EdirectoryAssert\Keyword(message="Maximum of {{ maxNumberOfWords }} keywords allowed. Keywords should be separated by {{ separator }} and can have up to {{ maxNumberOfCharsPerWord }} characters.")
     *
     */
    private $eventKeywords;

    /**
     * @since 11.3.00
     * @var \DateTime
     *
     * @Edirectory\Import(name="Event Renewal Date", isDate=true)
     * @EdirectoryAssert\DateFormat(message="Renewal date should be a valid date.")
     * @EdirectoryAssert\FutureDate(message="Renewal date should be a valid date in the future.")
     */
    private $eventRenewalDate;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Status")
     */
    private $eventStatus;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Level")
     */
    private $eventLevel;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Category 1")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $eventCategory1;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Category 2")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $eventCategory2;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Category 3")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $eventCategory3;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Category 4")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $eventCategory4;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Category 5")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $eventCategory5;

    /**
     * @since 11.3.00
     * @var integer
     *
     * @Edirectory\Import(name="Event DB Id")
     *
     */
    private $eventId;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Event Custom Id")
     *
     */
    private $eventThirdPartyId;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Username")
     * @Assert\Email(message="The username must be a valid email address")
     *
     */
    private $accountUsername;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Password")
     *
     */
    private $accountPassword;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact First Name")
     *
     */
    private $accountFirstName;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact Last Name")
     *
     */
    private $accountLastName;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact Company")
     *
     */
    private $accountCompany;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact Address")
     *
     */
    private $accountAddress;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact Address2")
     *
     */
    private $accountAddress2;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact Country")
     *
     */
    private $accountCountry;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact State")
     *
     */
    private $accountState;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact City")
     *
     */
    private $accountCity;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact Postal Code")
     *
     */
    private $accountZipCode;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact Phone")
     *
     */
    private $accountPhone;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact Email")
     * @Assert\Email(message="The account email must be a valid email address")
     *
     */
    private $accountEmail;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Account Contact URL")
     *
     */
    private $accountUrl;

    /**
     * @since 11.3.00
     * @var integer
     *
     */
    private $fileLineNumber;

    /**
     * @since 11.3.00
     * @var string
     *
     */
    private $inserted = 'n';

    /**
     * @since 11.3.00
     * @var string
     *
     */
    private $error = 'n';

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventTitle()
    {
        return $this->eventTitle;
    }

    /**
     * @param string $eventTitle
     * @return $this
     */
    public function setEventTitle($eventTitle)
    {
        $this->eventTitle = $eventTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventSeoTitle()
    {
        return $this->eventSeoTitle;
    }

    /**
     * @param string $eventSeoTitle
     * @return $this
     */
    public function setEventSeoTitle($eventSeoTitle)
    {
        $this->eventSeoTitle = $eventSeoTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventEmail()
    {
        return $this->eventEmail;
    }

    /**
     * @param string $eventEmail
     * @return $this
     */
    public function setEventEmail($eventEmail)
    {
        $this->eventEmail = $eventEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventUrl()
    {
        return $this->eventUrl;
    }

    /**
     * @param string $eventUrl
     * @return $this
     */
    public function setEventUrl($eventUrl)
    {
        $this->eventUrl = $eventUrl;

        return $this;
    }

    /**
     * @param int|null $length
     * @return string
     */
    public function getEventAddress($length = null)
    {
        $eventAddress = $this->eventAddress;

        if ($length and $length > 0) {
            $eventAddress = (strlen($eventAddress) >= $length) ? substr($eventAddress, 0, $length) : $eventAddress;
        }

        return $eventAddress;
    }

    /**
     * @param string $eventAddress
     * @return $this
     */
    public function setEventAddress($eventAddress)
    {
        $this->eventAddress = $eventAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventLocation()
    {
        return $this->eventLocation;
    }

    /**
     * @param string $eventLocation
     */
    public function setEventLocation($eventLocation)
    {
        $this->eventLocation = $eventLocation;
    }

    /**
     * @return string
     */
    public function getEventContactName()
    {
        return $this->eventContactName;
    }

    /**
     * @param string $eventContactName
     */
    public function setEventContactName($eventContactName)
    {
        $this->eventContactName = $eventContactName;
    }

    /**
     * @return string
     */
    public function getEventStartDate()
    {
        return $this->eventStartDate;
    }

    /**
     * @param \DateTime $eventStartDate
     */
    public function setEventStartDate($eventStartDate)
    {
        $this->eventStartDate = $eventStartDate;
    }

    /**
     * @return \DateTime
     */
    public function getEventEndDate()
    {
        return $this->eventEndDate;
    }

    /**
     * @param \DateTime $eventEndDate
     */
    public function setEventEndDate($eventEndDate)
    {
        $this->eventEndDate = $eventEndDate;
    }

    /**
     * @return \DateTime
     */
    public function getEventStartTime()
    {
        return $this->eventStartTime;
    }

    /**
     * @param \DateTime $eventStartTime
     */
    public function setEventStartTime($eventStartTime)
    {
        $this->eventStartTime = $eventStartTime;
    }

    /**
     * @return \DateTime
     */
    public function getEventEndTime()
    {
        return $this->eventEndTime;
    }

    /**
     * @param \DateTime $eventEndTime
     */
    public function setEventEndTime($eventEndTime)
    {
        $this->eventEndTime = $eventEndTime;
    }

    /**
     * @return string
     */
    public function getEventCountry()
    {
        return $this->eventCountry;
    }

    /**
     * @param string $eventCountry
     * @return $this
     */
    public function setEventCountry($eventCountry)
    {
        $this->eventCountry = $eventCountry;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventCountryAbbreviation()
    {
        return $this->eventCountryAbbreviation;
    }

    /**
     * @param string $eventCountryAbbreviation
     * @return $this
     */
    public function setEventCountryAbbreviation($eventCountryAbbreviation)
    {
        $this->eventCountryAbbreviation = $eventCountryAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventRegion()
    {
        return $this->eventRegion;
    }

    /**
     * @param string $eventRegion
     * @return $this
     */
    public function setEventRegion($eventRegion)
    {
        $this->eventRegion = $eventRegion;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventRegionAbbreviation()
    {
        return $this->eventRegionAbbreviation;
    }

    /**
     * @param string $eventRegionAbbreviation
     * @return $this
     */
    public function setEventRegionAbbreviation($eventRegionAbbreviation)
    {
        $this->eventRegionAbbreviation = $eventRegionAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventState()
    {
        return $this->eventState;
    }

    /**
     * @param string $eventState
     * @return $this
     */
    public function setEventState($eventState)
    {
        $this->eventState = $eventState;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventStateAbbreviation()
    {
        return $this->eventStateAbbreviation;
    }

    /**
     * @param string $eventStateAbbreviation
     * @return $this
     */
    public function setEventStateAbbreviation($eventStateAbbreviation)
    {
        $this->eventStateAbbreviation = $eventStateAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventCity()
    {
        return $this->eventCity;
    }

    /**
     * @param string $eventCity
     * @return $this
     */
    public function setEventCity($eventCity)
    {
        $this->eventCity = $eventCity;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventCityAbbreviation()
    {
        return $this->eventCityAbbreviation;
    }

    /**
     * @param string $eventCityAbbreviation
     * @return $this
     */
    public function setEventCityAbbreviation($eventCityAbbreviation)
    {
        $this->eventCityAbbreviation = $eventCityAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventNeighborhood()
    {
        return $this->eventNeighborhood;
    }

    /**
     * @param string $eventNeighborhood
     * @return $this
     */
    public function setEventNeighborhood($eventNeighborhood)
    {
        $this->eventNeighborhood = $eventNeighborhood;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventNeighborhoodAbbreviation()
    {
        return $this->eventNeighborhoodAbbreviation;
    }

    /**
     * @param string $eventNeighborhoodAbbreviation
     * @return $this
     */
    public function setEventNeighborhoodAbbreviation($eventNeighborhoodAbbreviation)
    {
        $this->eventNeighborhoodAbbreviation = $eventNeighborhoodAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventZipCode()
    {
        return $this->eventZipCode;
    }

    /**
     * @param string $eventZipCode
     * @return $this
     */
    public function setEventZipCode($eventZipCode)
    {
        $this->eventZipCode = $eventZipCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventLatitude()
    {
        return $this->eventLatitude;
    }

    /**
     * @param string $eventLatitude
     * @return $this
     */
    public function setEventLatitude($eventLatitude)
    {
        $this->eventLatitude = $eventLatitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventLongitude()
    {
        return $this->eventLongitude;
    }

    /**
     * @param string $eventLongitude
     * @return $this
     */
    public function setEventLongitude($eventLongitude)
    {
        $this->eventLongitude = $eventLongitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventPhone()
    {
        return $this->eventPhone;
    }

    /**
     * @param string $eventPhone
     * @return $this
     */
    public function setEventPhone($eventPhone)
    {
        $this->eventPhone = $eventPhone;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventShortDescription()
    {
        return $this->eventShortDescription;
    }

    /**
     * @param string $eventShortDescription
     * @return $this
     */
    public function setEventShortDescription($eventShortDescription)
    {
        $this->eventShortDescription = $eventShortDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventLongDescription()
    {
        return $this->eventLongDescription;
    }

    /**
     * @param string $eventLongDescription
     * @return $this
     */
    public function setEventLongDescription($eventLongDescription)
    {
        $this->eventLongDescription = $eventLongDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventSeoDescription()
    {
        return $this->eventSeoDescription;
    }

    /**
     * @param string $eventSeoDescription
     * @return $this
     */
    public function setEventSeoDescription($eventSeoDescription)
    {
        $this->eventSeoDescription = $eventSeoDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventKeywords()
    {
        if ($this->eventKeywords !== null) {
            return preg_replace("/\s*\|\|\s*/", " || ", $this->eventKeywords);
        }

        return null;
    }

    /**
     * @param string $eventKeywords
     * @return $this
     */
    public function setEventKeywords($eventKeywords)
    {
        $this->eventKeywords = $eventKeywords;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEventRenewalDate()
    {
        return $this->eventRenewalDate;
    }

    /**
     * @param string $eventRenewalDate
     * @return $this
     */
    public function setEventRenewalDate($eventRenewalDate)
    {
        $this->eventRenewalDate = $eventRenewalDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventStatus()
    {
        return $this->eventStatus;
    }

    /**
     * @param string $eventStatus
     * @return $this
     */
    public function setEventStatus($eventStatus)
    {
        $this->eventStatus = $eventStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventLevel()
    {
        return $this->eventLevel;
    }

    /**
     * @param string $eventLevel
     * @return $this
     */
    public function setEventLevel($eventLevel)
    {
        $this->eventLevel = $eventLevel;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventCategory1()
    {
        return $this->eventCategory1;
    }

    /**
     * @param string $eventCategory1
     * @return $this
     */
    public function setEventCategory1($eventCategory1)
    {
        $this->eventCategory1 = $eventCategory1;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventCategory2()
    {
        return $this->eventCategory2;
    }

    /**
     * @param string $eventCategory2
     * @return $this
     */
    public function setEventCategory2($eventCategory2)
    {
        $this->eventCategory2 = $eventCategory2;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventCategory3()
    {
        return $this->eventCategory3;
    }

    /**
     * @param string $eventCategory3
     * @return $this
     */
    public function setEventCategory3($eventCategory3)
    {
        $this->eventCategory3 = $eventCategory3;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventCategory4()
    {
        return $this->eventCategory4;
    }

    /**
     * @param string $eventCategory4
     * @return $this
     */
    public function setEventCategory4($eventCategory4)
    {
        $this->eventCategory4 = $eventCategory4;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventCategory5()
    {
        return $this->eventCategory5;
    }

    /**
     * @param string $eventCategory5
     * @return $this
     */
    public function setEventCategory5($eventCategory5)
    {
        $this->eventCategory5 = $eventCategory5;

        return $this;
    }

    /**
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * @return string
     */
    public function getEventThirdPartyId()
    {
        return $this->eventThirdPartyId;
    }

    /**
     * @param string $eventThirdPartyId
     * @return $this
     */
    public function setEventThirdPartyId($eventThirdPartyId)
    {
        $this->eventThirdPartyId = $eventThirdPartyId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountUsername()
    {
        return $this->accountUsername;
    }

    /**
     * @param string $accountUsername
     * @return $this
     */
    public function setAccountUsername($accountUsername)
    {
        $this->accountUsername = $accountUsername;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountPassword()
    {
        return $this->accountPassword;
    }

    /**
     * @param string $accountPassword
     * @return $this
     */
    public function setAccountPassword($accountPassword)
    {
        $this->accountPassword = $accountPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountFirstName()
    {
        return $this->accountFirstName;
    }

    /**
     * @param string $accountFirstName
     * @return $this
     */
    public function setAccountFirstName($accountFirstName)
    {
        $this->accountFirstName = $accountFirstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountLastName()
    {
        return $this->accountLastName;
    }

    /**
     * @param string $accountLastName
     * @return $this
     */
    public function setAccountLastName($accountLastName)
    {
        $this->accountLastName = $accountLastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountCompany()
    {
        return $this->accountCompany;
    }

    /**
     * @param string $accountCompany
     * @return $this
     */
    public function setAccountCompany($accountCompany)
    {
        $this->accountCompany = $accountCompany;

        return $this;
    }

    /**
     * @param int|null $length
     * @return string
     */
    public function getAccountAddress($length = null)
    {
        $accountAddress = $this->accountAddress;

        if ($length and $length > 0) {
            $accountAddress = (strlen($accountAddress) >= $length) ? substr($accountAddress, 0, $length) : $accountAddress;
        }

        return $accountAddress;
    }

    /**
     * @param string $accountAddress
     * @return $this
     */
    public function setAccountAddress($accountAddress)
    {
        $this->accountAddress = $accountAddress;

        return $this;
    }

    /**
     * @param int|null $length
     * @return string
     */
    public function getAccountAddress2($length = null)
    {
        $accountAddress2 = $this->accountAddress2;

        if ($length and $length > 0) {
            $accountAddress2 = (strlen($accountAddress2) >= $length) ? substr($accountAddress2, 0, $length) : $accountAddress2;
        }

        return $accountAddress2;
    }

    /**
     * @param string $accountAddress2
     * @return $this
     */
    public function setAccountAddress2($accountAddress2)
    {
        $this->accountAddress2 = $accountAddress2;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountCountry()
    {
        return $this->accountCountry;
    }

    /**
     * @param string $accountCountry
     * @return $this
     */
    public function setAccountCountry($accountCountry)
    {
        $this->accountCountry = $accountCountry;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountState()
    {
        return $this->accountState;
    }

    /**
     * @param string $accountState
     * @return $this
     */
    public function setAccountState($accountState)
    {
        $this->accountState = $accountState;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountCity()
    {
        return $this->accountCity;
    }

    /**
     * @param string $accountCity
     * @return $this
     */
    public function setAccountCity($accountCity)
    {
        $this->accountCity = $accountCity;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountZipCode()
    {
        return $this->accountZipCode;
    }

    /**
     * @param string $accountZipCode
     * @return $this
     */
    public function setAccountZipCode($accountZipCode)
    {
        $this->accountZipCode = $accountZipCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountPhone()
    {
        return $this->accountPhone;
    }

    /**
     * @param string $accountPhone
     * @return $this
     */
    public function setAccountPhone($accountPhone)
    {
        $this->accountPhone = $accountPhone;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountEmail()
    {
        return $this->accountEmail;
    }

    /**
     * @param string $accountEmail
     * @return $this
     */
    public function setAccountEmail($accountEmail)
    {
        $this->accountEmail = $accountEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->accountUrl;
    }

    /**
     * @param string $accountUrl
     * @return $this
     */
    public function setAccountUrl($accountUrl)
    {
        $this->accountUrl = $accountUrl;

        return $this;
    }

    /**
     * @return int
     */
    public function getFileLineNumber()
    {
        return $this->fileLineNumber;
    }

    /**
     * @param int $fileLineNumber
     * @return $this
     */
    public function setFileLineNumber($fileLineNumber)
    {
        $this->fileLineNumber = $fileLineNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getInserted()
    {
        return $this->inserted;
    }

    /**
     * @param string $inserted
     * @return $this
     */
    public function setInserted($inserted)
    {
        $this->inserted = $inserted;

        return $this;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }


}
