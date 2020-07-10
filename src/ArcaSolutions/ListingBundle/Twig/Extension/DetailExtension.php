<?php

namespace ArcaSolutions\ListingBundle\Twig\Extension;

use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BlocksExtension
 *
 * @package ArcaSolutions\ListingBundle\Twig\Extension
 */
class DetailExtension extends \Twig_Extension
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
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('listingContent', [$this, 'listingContent']),
        ];
    }

    public function listingContent(Listing $listing,ListingLevelFeatures $listingLevel, $reviewsPaginated, $address, $map, $gallery, $deals, $classifieds, $isSample)
    {
        $contentCount = 0;
        $overviewCount = 0;
        $activeTab = 0;
        $listingTemplateValue = 0;

        if(!empty($isSample)) {
            return [
                'content'   => 1,
                'overview'  => 1,
                'activeTab' => 1
            ];
        }

        $reviews_active = $this->container->get('doctrine')->getRepository('WebBundle:Setting')->getSetting('review_listing_enabled');

        HookFire('detailextension_overwrite_activetab', [
            'contentCount'  => &$contentCount,
            'activeTab'     => &$activeTab,
            'listing'       => &$listing
        ]);

        //Classified
        !empty($classifieds) and $contentCount++ and $activeTab = 5;

        //Deals
        !empty($deals) and $contentCount++ and $activeTab = 4;

        //Review
        if (!HookFire('detailextension_overwrite_hasreview', [
            'activeTab'        => &$activeTab,
            'contentCount'     => &$contentCount,
            'reviewsPaginated' => $reviewsPaginated,
            'listingLevel'     => $listingLevel,
            'reviews_active'   => $reviews_active
        ])) {
            if ($listingLevel->hasReview && $reviews_active && !empty($reviewsPaginated['reviews']->count())){
                $contentCount++;
                $activeTab = 3;
            }
        }

        //Photos
        !empty($gallery) and $contentCount++ and $activeTab = 2;

        //Overview
        HookFire('detailextension_before_increaseoverviewcount', [
            'listingLevel'  => $listingLevel,
            'listing'       => $listing,
            'contentCount'  => &$contentCount,
            'overviewCount' => &$overviewCount,
        ]);
        if ($listingLevel->hasSummaryDescription && !empty($listing->getDescription())) {
            $contentCount++;
            $overviewCount++;
        }
        if ($listingLevel->hasLongDescription && !empty($listing->getLongDescription())) {
            $contentCount++;
            $overviewCount++;
        }
        if ($listingLevel->hasFeatureInformation && !empty($listing->getFeatures())) {
            $contentCount++;
            $overviewCount++;
        }
        if ($listing->getTemplate() !== null && !empty($listing->getTemplate()->getFields()->count())) {
            foreach($listing->getTemplate()->getFields() as $field) {
                $listingTemplate = explode('_',$field->getField());
                if(!empty($listingTemplate)) {
                    $listingTemplateField = 'get';
                    foreach($listingTemplate as $templateField) {
                        $listingTemplateField .= ucfirst($templateField);
                    }

                    if (strpos($listingTemplateField, 'Custom') && !empty($listing->$listingTemplateField())) {
                        $listingTemplateValue++;
                        $contentCount++;
                        $overviewCount++;
                        break;
                    }
                }
            }
        }
        if ($listingLevel->hasAdditionalFiles && !empty($listing->getAttachmentFile())) {
            $contentCount++;
            $overviewCount++;
        }
        if ($listingLevel->hasHoursOfWork && !empty($listing->getHoursWork())) {
            $contentCount++;
            $overviewCount++;
        }
        if (!HookFire('detailextension_overwrite_hasvideo', [
            'listingLevel'  => $listingLevel,
            'listing'       => $listing,
            'contentCount'  => &$contentCount,
            'overviewCount' => &$overviewCount,
        ])) {
            if ($listingLevel->hasVideo && !empty($listing->getVideoSnippet())){
                $contentCount++;
                $overviewCount++;
            }
        }

        !empty($listing->getMainImage()) and $contentCount++ and $overviewCount++;
        if (!empty($address) && !empty($map)) {
            $contentCount++;
            $overviewCount++;
        }
        if (!HookFire('detailextension_overwrite_hassocialnetworking', [
            'listingLevel'  => $listingLevel,
            'listing'       => $listing,
            'contentCount'  => &$contentCount,
            'overviewCount' => &$overviewCount,
        ])) {
            if ($listingLevel->hasSocialNetworking && !empty($listing->getSocialNetwork()))  {
                $contentCount++;
                $overviewCount++;
            }
        }
        !empty($overviewCount) and $activeTab = 1;

        /* Active Tab Value

         * Overview   1
         * Photos     2
         * Review     3
         * Deal       4
         * Classified 5
         * */

        HookFire('detailextension_before_return', [
            'listingLevel' => $listingLevel,
            'listing' => $listing
        ]);

        return [
            'content'              => $contentCount,
            'overview'             => $overviewCount,
            'activeTab'            => $activeTab,
            'listingTemplateValue' => $listingTemplateValue
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'detail_listing';
    }
}
