<?php

namespace ArcaSolutions\ListingBundle\Repository;

use ArcaSolutions\CoreBundle\Doctrine\ORM\LevelRepository;

class ListingLevelRepository extends LevelRepository
{
    public function getDealsCount($level) {
        $qb = $this->createQueryBuilder('l')
            ->select('l')
            ->where('l.value = :value')
            ->setParameter('value', $level)
            ->andWhere('l.deals > 0');

        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
