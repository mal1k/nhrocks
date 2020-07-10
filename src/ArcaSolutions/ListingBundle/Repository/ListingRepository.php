<?php

namespace ArcaSolutions\ListingBundle\Repository;

use ArcaSolutions\CoreBundle\Interfaces\EntityModulesRowInterface;
use ArcaSolutions\CoreBundle\Repository\EntityModulesRowRepository;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\ORM\Mapping\PreRemove;

/**
 * ListingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
final class ListingRepository extends EntityModulesRowRepository implements EntityModulesRowInterface
{
    /**
     * Returns module name in lowercase
     *
     * @return string
     */
    function getModuleName()
    {
        return 'listing';
    }

    /**
     * Return a valid classified associated listing if Listing is active and Listing level classified quantity association is bigger than 0
     *
     * @param $listingId
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getClassifiedAssociatedListing($listingId)
    {
        $qb = $this->createQueryBuilder('l')
            ->select('l')
            ->join('ListingBundle:ListingLevel', 'll', 'WITH', 'l.level = ll.value')
            ->where('l.id = :id')
            ->andWhere('ll.classifiedQuantityAssociation > 0')
            ->andWhere('l.status = :status')
            ->setParameter('id', $listingId)
            ->setParameter('status', 'A');

        return $qb->getQuery()->getOneOrNullResult();
    }
}
