<?php

namespace ArcaSolutions\ImportBundle\Entity;

use ArcaSolutions\ImportBundle\Annotation as Edirectory;
use ArcaSolutions\ImportBundle\Validator\Constraints as EdirectoryAssert;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Class Listing
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Entity
 * @since 11.3.00
 *
 * @EdirectoryAssert\AccountPassword(message="Account Password cannot be empty and must between 4 and 50 characters if an Account Username is provided.")
 * @EdirectoryAssert\AccountFirstName(message="Account First Name cannot be empty if an Account Username is provided.")
 * @EdirectoryAssert\AccountLastName(message="Account Last Name cannot be empty if an Account Username is provided.")
 * @EdirectoryAssert\LocationHierarchy(message="Location hierarchy doesn't match the system settings. One or more locations are missing (for instance, city without state).", module="listing")
 */
class ListingImport
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
     * @Edirectory\Import(name="Listing Title", mappingRequired=true)
     * @EdirectoryAssert\ListingTitle(message="Title cannot be empty.")
     *
     */
    private $listingTitle;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing SEO Title")
     */
    private $listingSeoTitle;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Email")
     * @Assert\Email(message="The listing email must be a valid email address")
     *
     */
    private $listingEmail;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Url")
     *
     */
    private $listingUrl;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Address")
     *
     */
    private $listingAddress;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Address2")
     *
     */
    private $listingAddress2;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Country")
     *
     */
    private $listingCountry;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Country Abbreviation")
     *
     */
    private $listingCountryAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Region")
     *
     */
    private $listingRegion;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Region Abbreviation")
     *
     */
    private $listingRegionAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing State")
     *
     */
    private $listingState;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing State Abbreviation")
     *
     */
    private $listingStateAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing City")
     *
     */
    private $listingCity;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing City Abbreviation")
     *
     */
    private $listingCityAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Neighborhood")
     *
     */
    private $listingNeighborhood;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Neighborhood Abbreviation")
     *
     */
    private $listingNeighborhoodAbbreviation;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Postal Code")
     *
     */
    private $listingZipCode;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Latitude")
     * @EdirectoryAssert\Latitude(message="Latitude should be a number between -90 and 90.")
     *
     */
    private $listingLatitude;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Longitude")
     * @EdirectoryAssert\Longitude(message="Longitude should be a number between -180 and 180.")
     *
     */
    private $listingLongitude;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Phone")
     *
     */
    private $listingPhone;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Short Description")
     *
     */
    private $listingShortDescription;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Long Description")
     *
     */
    private $listingLongDescription;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Seo Description")
     *
     */
    private $listingSeoDescription;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Keywords")
     * @EdirectoryAssert\Keyword(message="Maximum of {{ maxNumberOfWords }} keywords allowed. Keywords should be separated by {{ separator }} and can have up to {{ maxNumberOfCharsPerWord }} characters.")
     *
     */
    private $listingKeywords;

    /**
     * @since 11.3.00
     * @var \DateTime
     *
     * @Edirectory\Import(name="Listing Renewal Date", isDate=true)
     * @EdirectoryAssert\DateFormat(message="Renewal date should be a valid date.")
     * @EdirectoryAssert\FutureDate(message="Renewal date should be a valid date in the future.")
     *
     */
    private $listingRenewalDate;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Status")
     */
    private $listingStatus;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Level")
     *
     */
    private $listingLevel;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 1")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     *
     */
    private $listingCategory1;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 2")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     *
     */
    private $listingCategory2;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 3")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     *
     */
    private $listingCategory3;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 4")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory4;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 5")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory5;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 6")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory6;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 7")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory7;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 8")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory8;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 9")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory9;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 10")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory10;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 11")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory11;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 12")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory12;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 13")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory13;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 14")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory14;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 15")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory15;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 16")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory16;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 17")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory17;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 18")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory18;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 19")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory19;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Category 20")
     * @EdirectoryAssert\Category(message="Limit of {{ maxNumberOfLevels }} categories in the category hierarchy has been exceeded.")
     */
    private $listingCategory20;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Template")
     * @EdirectoryAssert\Category
     *
     */
    private $listingListingTypeName;

    /**
     * @since 11.3.00
     * @var integer
     *
     * @Edirectory\Import(name="Listing DB Id")
     *
     */
    private $listingId;

    /**
     * @since 11.3.00
     * @var string
     *
     * @Edirectory\Import(name="Listing Custom Id")
     *
     */
    private $listingThirdPartyId;

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
    public function getListingTitle()
    {
        return $this->listingTitle;
    }

    /**
     * @param string $listingTitle
     * @return $this
     */
    public function setListingTitle($listingTitle)
    {
        $this->listingTitle = $listingTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingSeoTitle()
    {
        return $this->listingSeoTitle;
    }

    /**
     * @param string $listingSeoTitle
     * @return $this
     */
    public function setListingSeoTitle($listingSeoTitle)
    {
        $this->listingSeoTitle = $listingSeoTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingEmail()
    {
        return $this->listingEmail;
    }

    /**
     * @param string $listingEmail
     * @return $this
     */
    public function setListingEmail($listingEmail)
    {
        $this->listingEmail = $listingEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingUrl()
    {
        return $this->listingUrl;
    }

    /**
     * @param string $listingUrl
     * @return $this
     */
    public function setListingUrl($listingUrl)
    {
        $this->listingUrl = $listingUrl;

        return $this;
    }

    /**
     * @param int|null $length
     * @return string
     */
    public function getListingAddress($length = null)
    {
        $listingAddress = $this->listingAddress;

        if ($length and $length > 0) {
            $listingAddress = (strlen($listingAddress) >= $length) ? substr($listingAddress, 0, $length) : $listingAddress;
        }

        return $listingAddress;
    }

    /**
     * @param string $listingAddress
     * @return $this
     */
    public function setListingAddress($listingAddress)
    {
        $this->listingAddress = $listingAddress;

        return $this;
    }

    /**
     * @param int|null $length
     * @return string
     */
    public function getListingAddress2($length = null)
    {
        $listingAddress2 = $this->listingAddress2;

        if ($length and $length > 0) {
            $listingAddress2 = (strlen($listingAddress2) >= $length) ? substr($listingAddress2, 0, $length) : $listingAddress2;
        }

        return $listingAddress2;
    }

    /**
     * @param string $listingAddress2
     * @return $this
     */
    public function setListingAddress2($listingAddress2)
    {
        $this->listingAddress2 = $listingAddress2;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCountry()
    {
        return $this->listingCountry;
    }

    /**
     * @param string $listingCountry
     * @return $this
     */
    public function setListingCountry($listingCountry)
    {
        $this->listingCountry = $listingCountry;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCountryAbbreviation()
    {
        return $this->listingCountryAbbreviation;
    }

    /**
     * @param string $listingCountryAbbreviation
     * @return $this
     */
    public function setListingCountryAbbreviation($listingCountryAbbreviation)
    {
        $this->listingCountryAbbreviation = $listingCountryAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingRegion()
    {
        return $this->listingRegion;
    }

    /**
     * @param string $listingRegion
     * @return $this
     */
    public function setListingRegion($listingRegion)
    {
        $this->listingRegion = $listingRegion;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingRegionAbbreviation()
    {
        return $this->listingRegionAbbreviation;
    }

    /**
     * @param string $listingRegionAbbreviation
     * @return $this
     */
    public function setListingRegionAbbreviation($listingRegionAbbreviation)
    {
        $this->listingRegionAbbreviation = $listingRegionAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingState()
    {
        return $this->listingState;
    }

    /**
     * @param string $listingState
     * @return $this
     */
    public function setListingState($listingState)
    {
        $this->listingState = $listingState;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingStateAbbreviation()
    {
        return $this->listingStateAbbreviation;
    }

    /**
     * @param string $listingStateAbbreviation
     * @return $this
     */
    public function setListingStateAbbreviation($listingStateAbbreviation)
    {
        $this->listingStateAbbreviation = $listingStateAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCity()
    {
        return $this->listingCity;
    }

    /**
     * @param string $listingCity
     * @return $this
     */
    public function setListingCity($listingCity)
    {
        $this->listingCity = $listingCity;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCityAbbreviation()
    {
        return $this->listingCityAbbreviation;
    }

    /**
     * @param string $listingCityAbbreviation
     * @return $this
     */
    public function setListingCityAbbreviation($listingCityAbbreviation)
    {
        $this->listingCityAbbreviation = $listingCityAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingNeighborhood()
    {
        return $this->listingNeighborhood;
    }

    /**
     * @param string $listingNeighborhood
     * @return $this
     */
    public function setListingNeighborhood($listingNeighborhood)
    {
        $this->listingNeighborhood = $listingNeighborhood;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingNeighborhoodAbbreviation()
    {
        return $this->listingNeighborhoodAbbreviation;
    }

    /**
     * @param string $listingNeighborhoodAbbreviation
     * @return $this
     */
    public function setListingNeighborhoodAbbreviation($listingNeighborhoodAbbreviation)
    {
        $this->listingNeighborhoodAbbreviation = $listingNeighborhoodAbbreviation;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingZipCode()
    {
        return $this->listingZipCode;
    }

    /**
     * @param string $listingZipCode
     * @return $this
     */
    public function setListingZipCode($listingZipCode)
    {
        $this->listingZipCode = $listingZipCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingLatitude()
    {
        return $this->listingLatitude;
    }

    /**
     * @param string $listingLatitude
     * @return $this
     */
    public function setListingLatitude($listingLatitude)
    {
        $this->listingLatitude = $listingLatitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingLongitude()
    {
        return $this->listingLongitude;
    }

    /**
     * @param string $listingLongitude
     * @return $this
     */
    public function setListingLongitude($listingLongitude)
    {
        $this->listingLongitude = $listingLongitude;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingPhone()
    {
        return $this->listingPhone;
    }

    /**
     * @param string $listingPhone
     * @return $this
     */
    public function setListingPhone($listingPhone)
    {
        $this->listingPhone = $listingPhone;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingShortDescription()
    {
        return $this->listingShortDescription;
    }

    /**
     * @param string $listingShortDescription
     * @return $this
     */
    public function setListingShortDescription($listingShortDescription)
    {
        $this->listingShortDescription = $listingShortDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingLongDescription()
    {
        return $this->listingLongDescription;
    }

    /**
     * @param string $listingLongDescription
     * @return $this
     */
    public function setListingLongDescription($listingLongDescription)
    {
        $this->listingLongDescription = $listingLongDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingSeoDescription()
    {
        return $this->listingSeoDescription;
    }

    /**
     * @param string $listingSeoDescription
     * @return $this
     */
    public function setListingSeoDescription($listingSeoDescription)
    {
        $this->listingSeoDescription = $listingSeoDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingKeywords()
    {
        if ($this->listingKeywords !== null) {
            return preg_replace("/\s*\|\|\s*/", " || ", $this->listingKeywords);
        }

        return null;
    }

    /**
     * @param string $listingKeywords
     * @return $this
     */
    public function setListingKeywords($listingKeywords)
    {
        $this->listingKeywords = $listingKeywords;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getListingRenewalDate()
    {
        return $this->listingRenewalDate;
    }

    /**
     * @param string $listingRenewalDate
     * @return $this
     */
    public function setListingRenewalDate($listingRenewalDate)
    {
        $this->listingRenewalDate = $listingRenewalDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingStatus()
    {
        return $this->listingStatus;
    }

    /**
     * @param string $listingStatus
     * @return $this
     */
    public function setListingStatus($listingStatus)
    {
        $this->listingStatus = $listingStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingLevel()
    {
        return $this->listingLevel;
    }

    /**
     * @param string $listingLevel
     * @return $this
     */
    public function setListingLevel($listingLevel)
    {
        $this->listingLevel = $listingLevel;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory1()
    {
        return $this->listingCategory1;
    }

    /**
     * @param string $listingCategory1
     * @return $this
     */
    public function setListingCategory1($listingCategory1)
    {
        $this->listingCategory1 = $listingCategory1;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory2()
    {
        return $this->listingCategory2;
    }

    /**
     * @param string $listingCategory2
     * @return $this
     */
    public function setListingCategory2($listingCategory2)
    {
        $this->listingCategory2 = $listingCategory2;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory3()
    {
        return $this->listingCategory3;
    }

    /**
     * @param string $listingCategory3
     * @return $this
     */
    public function setListingCategory3($listingCategory3)
    {
        $this->listingCategory3 = $listingCategory3;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory4()
    {
        return $this->listingCategory4;
    }

    /**
     * @param string $listingCategory4
     * @return $this
     */
    public function setListingCategory4($listingCategory4)
    {
        $this->listingCategory4 = $listingCategory4;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory5()
    {
        return $this->listingCategory5;
    }

    /**
     * @param string $listingCategory5
     * @return $this
     */
    public function setListingCategory5($listingCategory5)
    {
        $this->listingCategory5 = $listingCategory5;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory6()
    {
        return $this->listingCategory6;
    }

    /**
     * @param string $listingCategory6
     * @return $this
     */
    public function setListingCategory6($listingCategory6)
    {
        $this->listingCategory6 = $listingCategory6;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory7()
    {
        return $this->listingCategory7;
    }

    /**
     * @param string $listingCategory7
     * @return $this
     */
    public function setListingCategory7($listingCategory7)
    {
        $this->listingCategory7 = $listingCategory7;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory8()
    {
        return $this->listingCategory8;
    }

    /*
     * @param string $listingCategory8
     * @return $this
     */
    public function setListingCategory8($listingCategory8)
    {
        $this->listingCategory8 = $listingCategory8;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory9()
    {
        return $this->listingCategory9;
    }

    /**
     * @param string $listingCategory9
     * @return $this
     */
    public function setListingCategory9($listingCategory9)
    {
        $this->listingCategory9 = $listingCategory9;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory10()
    {
        return $this->listingCategory10;
    }

    /**
     * @param string $listingCategory10
     * @return $this
     */
    public function setListingCategory10($listingCategory10)
    {
        $this->listingCategory10 = $listingCategory10;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory11()
    {
        return $this->listingCategory11;
    }

    /**
     * @param string $listingCategory11
     * @return $this
     */
    public function setListingCategory11($listingCategory11)
    {
        $this->listingCategory11 = $listingCategory11;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory12()
    {
        return $this->listingCategory12;
    }

    /**
     * @param string $listingCategory12
     * @return $this
     */
    public function setListingCategory12($listingCategory12)
    {
        $this->listingCategory12 = $listingCategory12;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory13()
    {
        return $this->listingCategory13;
    }

    /**
     * @param string $listingCategory13
     * @return $this
     */
    public function setListingCategory13($listingCategory13)
    {
        $this->listingCategory13 = $listingCategory13;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory14()
    {
        return $this->listingCategory14;
    }

    /**
     * @param string $listingCategory14
     * @return $this
     */
    public function setListingCategory14($listingCategory14)
    {
        $this->listingCategory14 = $listingCategory14;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory15()
    {
        return $this->listingCategory15;
    }

    /**
     * @param string $listingCategory15
     * @return $this
     */
    public function setListingCategory15($listingCategory15)
    {
        $this->listingCategory15 = $listingCategory15;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory16()
    {
        return $this->listingCategory16;
    }

    /**
     * @param string $listingCategory16
     * @return $this
     */
    public function setListingCategory16($listingCategory16)
    {
        $this->listingCategory16 = $listingCategory16;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory17()
    {
        return $this->listingCategory17;
    }

    /**
     * @param string $listingCategory17
     * @return $this
     */
    public function setListingCategory17($listingCategory17)
    {
        $this->listingCategory17 = $listingCategory17;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory18()
    {
        return $this->listingCategory18;
    }

    /**
     * @param string $listingCategory18
     * @return $this
     */
    public function setListingCategory18($listingCategory18)
    {
        $this->listingCategory18 = $listingCategory18;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory19()
    {
        return $this->listingCategory19;
    }

    /**
     * @param string $listingCategory19
     * @return $this
     */
    public function setListingCategory19($listingCategory19)
    {
        $this->listingCategory19 = $listingCategory19;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingCategory20()
    {
        return $this->listingCategory20;
    }

    /**
     * @param string $listingCategory20
     * @return $this
     */
    public function setListingCategory20($listingCategory20)
    {
        $this->listingCategory20 = $listingCategory20;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingListingTypeName()
    {
        return $this->listingListingTypeName;
    }

    /**
     * @param string $listingListingTypeName
     * @return $this
     */
    public function setListingListingTypeName($listingListingTypeName)
    {
        $this->listingListingTypeName = $listingListingTypeName;

        return $this;
    }

    /**
     * @return int
     */
    public function getListingId()
    {
        return $this->listingId;
    }

    /**
     * @param int $listingId
     * @return $this
     */
    public function setListingId($listingId)
    {
        $this->listingId = $listingId;

        return $this;
    }

    /**
     * @return string
     */
    public function getListingThirdPartyId()
    {
        return $this->listingThirdPartyId;
    }

    /**
     * @param string $listingThirdPartyId
     * @return $this
     */
    public function setListingThirdPartyId($listingThirdPartyId)
    {
        $this->listingThirdPartyId = $listingThirdPartyId;

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
