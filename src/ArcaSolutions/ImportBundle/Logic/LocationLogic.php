<?php

namespace ArcaSolutions\ImportBundle\Logic;


use ArcaSolutions\CoreBundle\Entity\Location1;
use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Entity\Location3;
use ArcaSolutions\CoreBundle\Entity\Location4;
use ArcaSolutions\CoreBundle\Entity\Location5;
use ArcaSolutions\CoreBundle\Logic\FriendlyUrlLogic;
use ArcaSolutions\ImportBundle\Entity\ImportLog;
use ArcaSolutions\ImportBundle\Exception\InvalidLocationNameException;
use ArcaSolutions\ImportBundle\Exception\LocationNotFoundException;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WebBundle\Entity\SettingLocation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class LocationLogic
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Logic
 * @since 11.3.00
 */
class LocationLogic
{
    /**
     * @var EntityManager
     */
    private $mainManager;

    /**
     * @var FriendlyUrlLogic
     */
    private $friendlyUrlLogic;

    /**
     * @var ImportLog
     */
    private $import;

    /**
     * @var SettingLocation[]
     */
    private $enableLocations;

    /**
     * @var bool
     */
    private $usingCountry = false;

    /**
     * @var bool
     */
    private $usingRegion = false;

    /**
     * @var bool
     */
    private $usingState = false;

    /**
     * @var bool
     */
    private $usingCity = false;

    /**
     * @var bool
     */
    private $usingNeighborhood = false;

    /**
     * @var Location1
     */
    private $defaultCountry = null;

    /**
     * @var Location2
     */
    private $defaultRegion = null;

    /**
     * @var Location3
     */
    private $defaultState = null;

    /**
     * @var Location4
     */
    private $defaultCity = null;

    /**
     * @var Location5
     */
    private $defaultNeighborhood = null;

    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * @param ContainerInterface $container
     * @param ImportLog $import
     */
    public function __construct(ContainerInterface $container, ImportLog $import = null)
    {
        $doctrine = $container->get('doctrine');
        $this->friendlyUrlLogic = new FriendlyUrlLogic($container);
        $this->mainManager = $doctrine->getManager("main");
        $this->import = $import;
        $this->doctrine = $doctrine;

        $this->enableLocations = $doctrine->getRepository(SettingLocation::class)->findByEnabled('y');

        foreach ($this->enableLocations as $enableLocation) {
            switch ($enableLocation->getName()) {
                case "COUNTRY":
                    $this->usingCountry = true;
                    $this->defaultCountry = $this->mainManager->getRepository(Location1::class)->find($enableLocation->getDefaultId());
                    break;
                case "REGION":
                    $this->usingRegion = true;
                    $this->defaultRegion = $this->mainManager->getRepository(Location2::class)->find($enableLocation->getDefaultId());
                    break;
                case "STATE":
                    $this->usingState = true;
                    $this->defaultState = $this->mainManager->getRepository(Location3::class)->find($enableLocation->getDefaultId());
                    break;
                case "CITY":
                    $this->usingCity = true;
                    $this->defaultCity = $this->mainManager->getRepository(Location4::class)->find($enableLocation->getDefaultId());
                    break;
                case "NEIGHBORHOOD":
                    $this->usingNeighborhood = true;
                    $this->defaultNeighborhood = $this->mainManager->getRepository(Location5::class)->find($enableLocation->getDefaultId());
                    break;
            }
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $name
     * @param $abbreviation
     * @param $class
     * @param Location1|Location2|Location3|Location4 $parentLocation
     * @param array|null $activeLocations
     * @return mixed|null|object
     * @throws InvalidLocationNameException
     */
    public function getLocation($name, $abbreviation, $class, $parentLocation = null, $activeLocations = [])
    {
        return empty(trim($name)) ? null : $this->findOrCreateLocation($name, $abbreviation, $class, $parentLocation, $activeLocations);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $name
     * @param $abbreviation
     * @param $class
     * @param $parentLocation
     * @param array|null $activeLocations
     * @return mixed|null|object
     * @throws InvalidLocationNameException
     */
    public function findOrCreateLocation($name, $abbreviation, $class, $parentLocation = null, $activeLocations = [])
    {
        $defaultLocation = null;
        switch ($class) {
            case Location1::class:
                $isUsingLocation = $this->isUsingCountry();
                $defaultLocation = $this->getDefaultCountry();
                break;
            case Location2::class;
                $isUsingLocation = $this->isUsingRegion();
                $defaultLocation = $this->getDefaultRegion();
                break;
            case Location3::class;
                $isUsingLocation = $this->isUsingState();
                $defaultLocation = $this->getDefaultState();
                break;
            case Location4::class;
                $isUsingLocation = $this->isUsingCity();
                $defaultLocation = $this->getDefaultCity();
                break;
            case Location5::class;
                $isUsingLocation = $this->isUsingNeighborhood();
                $defaultLocation = $this->getDefaultNeighborhood();
                break;
            default:
                throw new InvalidLocationNameException();
        }

        $locationLevel = (int)substr($class, -1);
        /* checks if any location level was skipped */
        if(($parentLocation == null) && ($this->enableLocations[0]->getId() != $locationLevel)){
            return null;
        }

        if ($isUsingLocation) {
            if ($defaultLocation && $this->locationMatchWithNameOrAbbreviation($defaultLocation, $name,
                    $abbreviation)) {
                return $defaultLocation;
            }

            if (!$defaultLocation) {
                $location = $this->findLocationByNameOrAbbreviation($name, $abbreviation, $class, $parentLocation, $activeLocations);
                if ($location == null) {
                    $location = $this->createLocation($name, $abbreviation, $class, $parentLocation);
                }

                return $location;
            }

            throw new InvalidLocationNameException();
        }

        return null;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return bool
     */
    public function isUsingCountry()
    {
        return $this->usingCountry;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return Location1
     */
    public function getDefaultCountry()
    {
        return $this->defaultCountry;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return bool
     */
    public function isUsingRegion()
    {
        return $this->usingRegion;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return Location2
     */
    public function getDefaultRegion()
    {
        return $this->defaultRegion;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return bool
     */
    public function isUsingState()
    {
        return $this->usingState;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return Location3
     */
    public function getDefaultState()
    {
        return $this->defaultState;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return bool
     */
    public function isUsingCity()
    {
        return $this->usingCity;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return Location4
     */
    public function getDefaultCity()
    {
        return $this->defaultCity;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @return bool
     */
    public function isUsingNeighborhood()
    {
        return $this->usingNeighborhood;
    }

    /**
     * @return Location5
     */
    public function getDefaultNeighborhood()
    {
        return $this->defaultNeighborhood;
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param Location1|Location2|Location3|Location4|Location5 $location
     * @param $name
     * @param $abbreviation
     * @return bool
     */
    private function locationMatchWithNameOrAbbreviation($location, $name, $abbreviation)
    {
        return strtolower($location->getName()) == strtolower($name)
            || strtolower($location->getAbbreviation()) == strtolower($abbreviation);
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $name
     * @param $abbreviation
     * @param $class
     * @param $parentLocation
     * @param array|null $activeLocations
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function findLocationByNameOrAbbreviation($name, $abbreviation, $class, $parentLocation = null, $activeLocations = [])
    {
        /* @var $repository EntityRepository */
        $repository = $this->mainManager->getRepository($class);
        $query = $repository->createQueryBuilder("l")
            ->andWhere('l.name = :name')
            ->setParameter('name', $name);

        if($abbreviation !== null && $abbreviation){
            $query->orWhere("l.abbreviation = :abbreviation")->setParameter("abbreviation", $abbreviation);
        }


        if ($parentLocation) {
            if ($parentLocation instanceof Location1) {
                if (is_array($activeLocations) and array_key_exists(1, $activeLocations)) {
                    $query->andWhere('l.location1 = :location1')->setParameter('location1', $parentLocation->getId());
                }
            }

            if ($parentLocation instanceof Location2) {
                if (is_array($activeLocations) and array_key_exists(1, $activeLocations)) {
                    $query->andWhere('l.location1 = :location1')->setParameter('location1', $parentLocation->getLocation1());
                }
                if (is_array($activeLocations) and array_key_exists(2, $activeLocations)) {
                    $query->andWhere('l.location2 = :location2')->setParameter('location2', $parentLocation->getId());
                }
            }

            if ($parentLocation instanceof Location3) {
                if (is_array($activeLocations) and array_key_exists(1, $activeLocations)) {
                    $query->andWhere('l.location1 = :location1')->setParameter('location1', $parentLocation->getLocation1());
                }
                if (is_array($activeLocations) and array_key_exists(2, $activeLocations)) {
                    $query->andWhere('l.location2 = :location2')->setParameter('location2', $parentLocation->getLocation2());
                }
                if (is_array($activeLocations) and array_key_exists(3, $activeLocations)) {
                    $query->andWhere('l.location3 = :location3')->setParameter('location3', $parentLocation->getId());
                }
            }

            if ($parentLocation instanceof Location4) {

                if (is_array($activeLocations) and array_key_exists(1, $activeLocations)) {
                    $query->andWhere('l.location1 = :location1')->setParameter('location1', $parentLocation->getLocation1());
                }
                if (is_array($activeLocations) and array_key_exists(2, $activeLocations)) {
                    $query->andWhere('l.location2 = :location2')->setParameter('location2', $parentLocation->getLocation2());
                }
                if (is_array($activeLocations) and array_key_exists(3, $activeLocations)) {
                    $query->andWhere('l.location3 = :location3')->setParameter('location3', $parentLocation->getLocation3());
                }
                if (is_array($activeLocations) and array_key_exists(4, $activeLocations)) {
                    $query->andWhere('l.location4 = :location4')->setParameter('location4', $parentLocation->getId());
                }
            }
        }

        $query = $query->setMaxResults(1)
            ->getQuery();
        try {
            return $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @author Roberto Silva <roberto.silva@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $name
     * @param $abbreviation
     * @param $class
     * @param Location1|Location2|Location3|Location4 $parentLocation
     * @return object
     * @throws InvalidLocationNameException
     */
    private function createLocation($name, $abbreviation, $class, $parentLocation = null)
    {
        if (strlen(trim($name)) == 0) {
            throw new InvalidLocationNameException();
        }

        $reflectionClass = new \ReflectionClass($class);

        $location = $reflectionClass->newInstance();

        $propName = $reflectionClass->getProperty("name");
        $propName->setAccessible(true);
        $propName->setValue($location, $name);

        $propAbbr = $reflectionClass->getProperty("abbreviation");
        $propAbbr->setAccessible(true);
        $propAbbr->setValue($location, $abbreviation);

        $propUrl = $reflectionClass->getProperty("friendlyUrl");
        $propUrl->setAccessible(true);
        $propUrl->setValue($location, $this->friendlyUrlLogic->buildUniqueFriendlyUrl($name));

        $propImport = $reflectionClass->getProperty('import');
        $propImport->setAccessible(true);
        $propImport->setValue($location, $this->import->getId());

        if ($parentLocation) {

            $settingLocationRepository = $this->doctrine->getRepository("WebBundle:SettingLocation");
            $enabledLocations = $settingLocationRepository->getLocationsEnabledID(false);

            $parentLevel = (int)substr(get_class($parentLocation), -1);
            $propParentLocation = $reflectionClass->getProperty("location{$parentLevel}");
            $propParentLocation->setAccessible(true);
            $propParentLocation->setValue($location, $parentLocation->getId());

            for ($i = $parentLevel; $i > 1; $i--) {
                $parentLevel = $i - 1;
                if (!in_array($parentLevel, $enabledLocations)) {
                    continue;
                }
                $parentLocation = $this->mainManager->getRepository("CoreBundle:Location{$parentLevel}")->find($parentLocation->{"getLocation".(string)$parentLevel}());

                $propParentLocation = $reflectionClass->getProperty("location{$parentLevel}");
                $propParentLocation->setAccessible(true);
                $propParentLocation->setValue($location, $parentLocation->getId());
            }
        }

        $this->mainManager->persist($location);
        $this->mainManager->flush();

        return $location;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param int $id
     * @param string $class
     * @return object
     * @throws LocationNotFoundException
     */
    public function getLocationId($id, $class)
    {
        if (!$location = $this->mainManager->getRepository($class)->find($id)) {
            throw new LocationNotFoundException();
        }

        return $location;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param ImportLog $import
     */
    public function setImport(ImportLog $import)
    {
        $this->import = $import;
    }
}
