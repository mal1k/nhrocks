<?php

namespace ArcaSolutions\WysiwygBundle\Repository;


use ArcaSolutions\WysiwygBundle\Entity\Page;
use Doctrine\ORM\EntityRepository;

/**
 * Class PageRepository
 */
class PageRepository extends EntityRepository
{
    /**
     * Return the Page by it's type title
     *
     * @param null $pageType Page type title, it will always be one of the constants of PageType
     * @return Page
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPageByType($pageType = null)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.pageType', 't')
            ->where('t.title = :pageType')
            ->setParameter('pageType', $pageType)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Return the Page by it's type title and Url
     * Used for Custom pages
     *
     * @param null $pageType
     * @param null $friendlyUrl
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getPageByTypeAndUrl($pageType = null, $friendlyUrl = null)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.pageType', 't')
            ->where('t.title = :pageType')
            ->andWhere('p.url = :friendlyUrl')
            ->setParameter('pageType', $pageType)
            ->setParameter('friendlyUrl', $friendlyUrl)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $url The page url
     * @param array $pageTypes The pageType
     * @param integer $id The page id
     *
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function uniqueUrl($url, array $pageTypes, $id)
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $whereTypeExpression = $queryBuilder
            ->expr()
            ->in('pt.title', ':pageTypes');

        if ($id !== 0) {
            $whereTypeExpression = $queryBuilder
                ->expr()
                ->andX(
                    $queryBuilder
                        ->expr()
                        ->in('pt.title', ':pageTypes')
                    ,
                    $queryBuilder
                        ->setParameter('id', $id)
                        ->expr()
                        ->neq('p.id', ':id')
                );
        }

        $query = $queryBuilder
            ->select('p')
            ->leftJoin('p.pageType', 'pt')
            ->where(
                $queryBuilder
                    ->expr()
                    ->andX(
                        $queryBuilder
                            ->expr()
                            ->eq('p.url', ':url')
                        ,
                        $whereTypeExpression
                )
            )
            ->setParameter('url', $url)
            ->setParameter('pageTypes', $pageTypes);

        return $query->getQuery()->getArrayResult();
    }

}
