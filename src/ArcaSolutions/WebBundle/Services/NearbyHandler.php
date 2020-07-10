<?php

namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Location;
use ArcaSolutions\WebBundle\Entity\NearbySearch;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NearbyHandler
 * @package ArcaSolutions\WebBundle\Services
 */
class NearbyHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->container = $containerInterface;
    }

    /**
     * Returns the geo_distance filter info using the LOCATION to build the elastic search query
     *
     * @return array|null
     */
    public function getGeoDistanceInfoByLocation()
    {
        $geoDistanceInfo = $this->getGeoDistanceInfoByWhere();

        if ($this->isNearbyEnabled() && !empty($this->getNearbyDefaultRadius()) && !$this->container->get("utility")->isRobotUser() && !$geoDistanceInfo) {
            $latitude = null;
            $longitude = null;
            $radius = null;

            $parameterHandler = $this->container->get("search.parameters");
            $distanceUnit = $this->container->get("translator")->trans("distance.unit", [], "units");

            /* get the geo_distance info by location searched */
            if ($locationsSearched = $parameterHandler->getLocations() AND
                count($locationsSearched) == 1
            ) {
                /* @var $locationResult Location */
                $locationResult = array_pop($locationsSearched);
                $locationRepository = $this->container->get("doctrine")
                    ->getRepository("CoreBundle:Location{$locationResult->getLevel()}", "main");

                $location = $locationRepository->find(preg_replace("/L\d+:/", "", $locationResult->getId()));

                $latitude = $location->getLatitude();
                $longitude = $location->getLongitude();
                $radius = $location->getRadius();

                if (!$location->getLatitude() && !$location->getLongitude()) {
                    $googleGeocode = $this->getGoogleGeocodeLatLon($location->getName());

                    if (!$googleGeocode || !$googleGeocode['latitude'] || !$googleGeocode['longitude']) {
                        return null;
                    }

                    $nearbyToken = $this->saveNearbySearch($location->getName(), $googleGeocode['latitude'],
                        $googleGeocode['longitude']);

                    $latitude = $nearbyToken->getLatitude();
                    $longitude = $nearbyToken->getLongitude();
                    $radius = $nearbyToken->getRadius();

                    $location->setLatitude($latitude);
                    $location->setLongitude($longitude);

                    $em = $this->container->get("doctrine.orm.main_entity_manager");
                    $em->persist($location);
                    $em->flush();
                }

                $geoDistanceInfo = [
                    'latitude'  => $latitude,
                    'longitude' => $longitude,
                    'radius'    => $radius ?
                        (string)$radius.$distanceUnit :
                        $this->getRadiusDistance(),
                ];
            }
        }

        return $geoDistanceInfo;
    }

    /**
     * Returns the geo_distance filter info using the WHERE to build the elastic search query
     *
     * @return null|array
     */
    public function getGeoDistanceInfoByWhere()
    {
        if (!$this->isNearbyEnabled() || empty($this->getNearbyDefaultRadius()) || $this->container->get("utility")->isRobotUser()) {
            return null;
        }

        $latitude = null;
        $longitude = null;
        $radius = null;

        $parameterHandler = $this->container->get("search.parameters");
        $distanceUnit = $this->container->get("translator")->trans("distance.unit", [], "units");

        /* get the geo_distance info by where searched */
        $whereSearched = $parameterHandler->getWheres() AND count($whereSearched) == 1;

        if (!$whereSearched) {
            return null;
        }

        $addressToken = implode(', ', $whereSearched);

        /* @var $nearbyToken NearbySearch */
        $nearbyToken = $this->container->get("doctrine")->getRepository("WebBundle:NearbySearch")->findOneBy([
            'token' => $addressToken,
        ]);

        if (!$nearbyToken) {
            $googleGeocode = $this->getGoogleGeocodeLatLon($addressToken);

            if (!$googleGeocode || !$googleGeocode['latitude'] || !$googleGeocode['longitude']) {
                return null;
            }

            $nearbyToken = $this->saveNearbySearch($addressToken, $googleGeocode['latitude'],
                $googleGeocode['longitude']);
        }

        $latitude = $nearbyToken->getLatitude();
        $longitude = $nearbyToken->getLongitude();
        $radius = $nearbyToken->getRadius();

        if (!$latitude || !$longitude) {
            return null;
        }

        return [
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'radius'    => $radius ?
                (string)$radius.$distanceUnit :
                $this->getRadiusDistance(),
        ];
    }

    /**
     * @return bool
     */
    public function isNearbyEnabled()
    {
        return $this->container->get("settings")->getDomainSetting("nearby_feature_enabled") === "on";
    }

    /**
     * @return mixed|null|string
     */
    public function getNearbyDefaultRadius()
    {
        return $this->container->get("settings")->getDomainSetting("nearby_default_radius");
    }

    /**
     * @param $address
     * @return array
     * @throws \Exception
     */
    public function getGoogleGeocodeLatLon($address)
    {
        $googleKey = $this->container->get('settings')->getDomainSetting('google_api_serverkey');

        $urlAddress = urlencode($address);
        $googleGeocodeUrl = "https://maps.google.com/maps/api/geocode/json?address={$urlAddress}&key={$googleKey}";

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $googleGeocodeUrl);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $googleResponse = curl_exec($ch);

            if (curl_errno($ch)) {
                return null;
            }

            curl_close($ch);
        } catch (\Exception $exception) {
            throw $exception;
        }

        $response = json_decode($googleResponse, true);

        if ($response['status'] != 'OK') {
            return null;
        }

        $latitude = $response['results'][0]['geometry']['location']['lat'];
        $longitude = $response['results'][0]['geometry']['location']['lng'];

        return [
            'latitude'  => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * @param $address
     * @param $latitude
     * @param $longitude
     * @return NearbySearch|null
     */
    public function saveNearbySearch($address, $latitude, $longitude)
    {
        /* @var $nearbyToken NearbySearch */
        $nearbyToken = $this->container->get("doctrine")->getRepository("WebBundle:NearbySearch")->findOneBy([
            'token' => $address,
        ]);

        if ($nearbyToken) {
            return $nearbyToken;
        }

        if (!$latitude || !$longitude) {
            return null;
        }

        $em = $this->container->get("doctrine")->getManager();

        $nearbyToken = new NearbySearch();
        $nearbyToken->setToken($address);

        $nearbyToken->setLatitude((string)$latitude);
        $nearbyToken->setLongitude((string)$longitude);

        $em->persist($nearbyToken);
        $em->flush();

        return $nearbyToken;
    }

    /**
     * Return the default radius distance with the location unit
     *
     * @return string
     */
    public function getRadiusDistance()
    {
        $defaultRadius = $this->getNearbyDefaultRadius();
        $geoLocationUnit = $this->container->get("translator")->trans("distance.unit", [], "units");

        return $defaultRadius.$geoLocationUnit;
    }

}
