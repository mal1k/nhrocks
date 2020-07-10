<?php

namespace ArcaSolutions\ImportBundle\Logic;


use ArcaSolutions\ListingBundle\Entity\ListingTemplate;
use Doctrine\ORM\EntityManager;

/**
 * Class ListingTypeLogic
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Logic
 * @since 11.3.00
 */
class ListingTypeLogic
{
    /**
     * @var EntityManager
     */
    private $domainManager;

    /**
     * ListingTypeLogic constructor.
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param EntityManager $domainManager
     */
    public function __construct(EntityManager $domainManager)
    {
        $this->domainManager = $domainManager;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $name The listing type name
     * @return ListingTemplate
     */
    public function getListingType($name, $listingType)
    {
        return $this->findOrCreateListingType($name, $listingType);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string|null $name The listing type name
     * @return ListingTemplate
     */
    public function findOrCreateListingType($name, $listingType)
    {
        if (strlen(trim($name)) === 0 or is_null($name)) {
            return $listingType ?: $this->findListingTypeDefault();
        }

        if (!$listingType = $this->findListingTypeByName($name)) {
            $listingType = $this->createListingType($name);
        }

        return $listingType;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @return mixed
     */
    private function findListingTypeDefault()
    {
        $listingType = $this->domainManager->getRepository(ListingTemplate::class)->findOneByEditable('n');

        return $listingType;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $name
     * @return ListingTemplate
     */
    protected function findListingTypeByName($name)
    {
        /* @var ListingTemplate $listingType */
        $listingType = $this->domainManager->getRepository(ListingTemplate::class)->findOneByTitle($name);

        return $listingType;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param $name
     * @return ListingTemplate
     */
    protected function createListingType($name)
    {
        $listingType = new ListingTemplate();
        $listingType->setTitle($name);

        $this->domainManager->persist($listingType);

        return $listingType;
    }
}
