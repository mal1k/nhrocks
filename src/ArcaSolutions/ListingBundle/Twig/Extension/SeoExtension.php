<?php

namespace ArcaSolutions\ListingBundle\Twig\Extension;

use ArcaSolutions\CoreBundle\Entity\Location2;
use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SeoExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * SeoExtension constructor.
     *
     * @param ContainerInterface $container
     * @param Settings $settings
     */
    public function __construct(ContainerInterface $container, Settings $settings)
    {
        $this->container = $container;
        $this->settings = $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'seo.listing';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'generateListingSEO',
                [$this, 'generateListingSEO'],
                ['is_safe' => ['all']]
            ),
            new \Twig_SimpleFunction(
                'generateListingReviewsSEO',
                [$this, 'generateReviewsSEO'],
                ['is_safe' => ['all']]
            ),
        ];
    }

    public function generateListingSEO(Listing $item)
    {
        $keywords[] = $item->getSeoKeywords();
        $description = $item->getSeoDescription();

        $titlePart[] = $item->getSeoTitle() ? $item->getSeoTitle() : $item->getTitle();

        $locations = $this->container->get('location.service')->getLocations($item);
        $doctrine = $this->container->get('doctrine');

        $countryName = $locations['country'] ? $locations['country']->getName() : null;
        $region = $locations['state'] ? $locations['state']->getName() : null;
        $locality = $locations['city'] ? $locations['city']->getName() : null;

        while ($locations) {
            /* @var $location Location2 */
            if ($location = array_pop($locations)) {
                $titlePart[] = $location->getName();
                $keyword[] = $location->getSeoKeywords();
            }
        }

        foreach ($item->getCategories() as $category) {
            /* @var $category ListingCategory */
            $keywords[] = $category->getSeoKeywords();
        }

        $title = $this->container->get('translator')->trans(
            '%pageTitle% | %directoryTitle%',
            [
                '%pageTitle%'      => implode(', ', $titlePart),
                '%directoryTitle%' => $this->container->get('multi_domain.information')->getTitle(),
            ]
        );

        if ($item->getImageId()) {
            $img = $doctrine->getRepository('ImageBundle:Image')->find($item->getImageId());
            $image = $this->container->get('templating.helper.assets')
                ->getUrl($this->container->get('imagehandler')->getPath($img), 'domain_images');
        } else {
            $image = $this->container->get('utility')->getLogoImage();
        }

        $url = $this->container->get('router')->generate(
            'listing_detail',
            [
                'friendlyUrl' => $item->getFriendlyUrl(),
                '_format'     => 'html',
            ],
            true
        );

        $totalByItemId = (array)$doctrine->getRepository('WebBundle:Review')->getTotalByItemId($item->getId(),
            'listing');
        $schema = [
            '@context' => 'http://schema.org',
            '@type'    => 'LocalBusiness',
            'name'     => $item->getTitle(),
            'url'      => $url,
        ];

        if ($item->getAvgReview() && $totalByItemId) {
            $schema['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => $item->getAvgReview(),
                'reviewCount' => array_pop($totalByItemId) ?: 0,
            ];
        }

        if ($item->getLatitude() or $item->getLongitude()) {
            $schema['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $item->getLatitude(),
                'longitude' => $item->getLongitude(),
            ];
        }

        $contact = [
            'streetAddress' => $item->getAddress(),
            'countryName'   => $countryName,
            'region'        => $region,
            'locality'      => $locality,
            'postalCode'    => $item->getZipCode(),
            'email'         => $item->getEmail(),
            'phoneNumber'   => $item->getPhone(),
            'website'       => $item->getUrl()
        ];

        $item->getDescription() and $schema['description'] = $item->getDescription();
        $item->getPhone() and $schema['telephone'] = $item->getPhone();
        if($item->getAdditionalPhone() && strtolower($item->getLabelAdditionalPhone()) === 'fax') {
            $schema['faxNumber'] = $item->getAdditionalPhone();
            $contact['faxNumber'] = $item->getAdditionalPhone();
        }
        $item->getEmail() and $schema['email'] = $item->getEmail();
        $image and $schema['image'] = $this->container->get('request_stack')->getCurrentRequest()->getSchemeAndHttpHost().$image;

        $address = [];

        $locality and $address['addressLocality'] = $locality;
        $region and $address['addressRegion'] = $region;
        $countryName and $address['addressCountry'] = $countryName;

        $item->getZipCode() and $address['postalCode'] = $item->getZipCode();
        $item->getAddress() and $address['streetAddress'] = $item->getAddress();

        if ($address) {
            $address['@type'] = 'PostalAddress';
            $schema['address'] = $address;
        }

        return $this->container->get('twig')->render(
            '::blocks/seo/business.og.html.twig',
            [
                'title'       => $title,
                'description' => $description,
                'keywords'    => preg_replace('/,+/', ',', implode(', ', $keywords)),
                'author'      => $this->container->get('settings')->getDomainSetting('header_author'),
                'schema'      => json_encode($schema),
                'og'          => [
                    'url'         => $url,
                    'type'        => 'business.business',
                    'title'       => $title,
                    'description' => $description,
                    'image'       => $image,
                    'business'    => [
                        'contact' => $contact,
                    ],
                ],
            ]
        );
    }

    public function generateReviewsSEO(Listing $item)
    {
        return $this->generateGenericListingSEO(
            $item,
            $this->container->get('translator')->trans('Reviews of "%title%"', ['%title%' => $item->getTitle()])
        );
    }

    public function generateGenericListingSEO(Listing $item, $titlePart)
    {
        $keywords[] = trim($item->getSeoKeywords());
        $description = $item->getSeoDescription();

        $doctrine = $this->container->get('doctrine');
        $locations = $this->container->get('location.service')->getLocations($item);

        $countryName = $locations['country'] ? $locations['country']->getName() : null;
        $region = $locations['state'] ? $locations['state']->getName() : null;
        $locality = $locations['city'] ? $locations['city']->getName() : null;

        while ($locations) {
            /* @var $location Location2 */
            if ($location = array_pop($locations)) {
                $keywords[] = trim($location->getSeoKeywords());
            }
        }

        foreach ($item->getCategories() as $category) {
            /* @var $category ListingCategory */
            $keywords[] = trim($category->getSeoKeywords());
        }

        $title = $this->container->get('translator')->trans(
            '%pageTitle% | %directoryTitle%',
            [
                '%pageTitle%'      => $titlePart,
                '%directoryTitle%' => $this->container->get('multi_domain.information')->getTitle(),
            ]
        );

        if ($item->getImageId()) {
            $img = $doctrine->getRepository('ImageBundle:Image')->find($item->getImageId());
            $image = $this->container->get('templating.helper.assets')->getUrl(
                $this->container->get('imagehandler')->getPath($img),
                'domain_images'
            );
        } else {
            $image = $this->container->get('utility')->getLogoImage(true);
        }

        $url = $this->container->get('router')->generate(
            'listing_detail',
            [
                'friendlyUrl' => $item->getFriendlyUrl(),
                '_format'     => 'html',
            ],
            true
        );

        $schema = [
            '@context'        => 'http://schema.org',
            '@type'           => 'LocalBusiness',
            'name'            => $item->getTitle(),
            'url'             => $url,
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => $item->getAvgReview(),
                'reviewCount' => $doctrine->getRepository('WebBundle:Review')->getTotalByItemId($item->getId(),
                    'listing'),
            ],
        ];

        if ($item->getLatitude() or $item->getLongitude()) {
            $schema['geo'] = [
                '@type'     => 'GeoCoordinates',
                'latitude'  => $item->getLatitude(),
                'longitude' => $item->getLongitude(),
            ];
        }

        $contact = [
            'streetAddress' => $item->getAddress(),
            'countryName'   => $countryName,
            'region'        => $region,
            'locality'      => $locality,
            'postalCode'    => $item->getZipCode(),
            'email'         => $item->getEmail(),
            'phoneNumber'   => $item->getPhone(),
            'website'       => $item->getUrl()
        ];

        $item->getDescription() and $schema['description'] = $item->getDescription();
        $item->getPhone() and $schema['telephone'] = $item->getPhone();
        if($item->getAdditionalPhone() && strtolower($item->getLabelAdditionalPhone()) === 'fax') {
            $schema['faxNumber'] = $item->getAdditionalPhone();
            $contact['faxNumber'] = $item->getAdditionalPhone();
        }
        $item->getEmail() and $schema['email'] = $item->getEmail();
        $image and $schema['image'] = $image;

        $address = [];

        $locality and $address['addressLocality'] = $locality;
        $region and $address['addressRegion'] = $region;
        $item->getZipCode() and $address['postalCode'] = $item->getZipCode();
        $item->getAddress() and $address['streetAddress'] = $item->getAddress();

        if ($address) {
            $address['@type'] = 'PostalAddress';
            $schema['address'] = $address;
        }

        return $this->container->get('twig')->render(
            '::blocks/seo/business.og.html.twig',
            [
                'title'       => $title,
                'description' => $description,
                'keywords'    => preg_replace('/,+/', ',', implode(', ', $keywords)),
                'author'      => $this->container->get('settings')->getDomainSetting('header_author'),
                'schema'      => json_encode($schema),
                'og'          => [
                    'url'         => $url,
                    'type'        => 'business.business',
                    'title'       => $title,
                    'description' => $description,
                    'image'       => $image,
                    'business'    => [
                        'contact' => $contact,
                    ],
                ],
            ]
        );
    }
}
