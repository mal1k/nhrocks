<?php
namespace ArcaSolutions\WebBundle\Repository;

use Doctrine\ORM\EntityRepository;

class SettingNavigationRepository extends EntityRepository
{
    /**
     * @param string $area
     *
     * @return array
     * @throws \Exception
     */
    public function getMenuByArea($area = '')
    {
        if (empty($area)) {
            throw new \Exception('You must pass a area to get the menu.');
        }

        $qb = $this->createQueryBuilder('sn');

        return $qb->select('sn')
            ->where($qb->expr()->andX()->addMultiple([
                $qb->expr()->eq('sn.area', ':area'),
                $qb->expr()->orX()->addMultiple([
                    $qb->expr()->isNotNull('sn.link'),
                    $qb->expr()->isNotNull('sn.pageId')
                ])
            ]))
            ->setParameter('area', $area)
            ->orderBy('sn.order', 'ASC')
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    }
}
