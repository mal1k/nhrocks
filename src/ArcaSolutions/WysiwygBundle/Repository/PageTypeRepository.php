<?php

namespace ArcaSolutions\WysiwygBundle\Repository;


use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

/**
 * Class PageTypeRepository
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since v11.4.00
 * @package ArcaSolutions\WysiwygBundle\Repository
 */
class PageTypeRepository extends EntityRepository
{
    /**
     * @var array
     */
    protected $customPagesTypes = [
        PageType::CUSTOM_PAGE,
    ];

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since v11.4.00
     *
     * @return PageType[]
     */
    public function getTypesPageIdLessCustomPage()
    {
        $queryBuilder = $this->createQueryBuilder('page_type');

        $query = $queryBuilder
            ->select('page_type.title')
            ->where(
                $queryBuilder
                    ->expr()
                    ->notIn('page_type.title', $this->customPagesTypes)
            )
            ->getQuery();

        return $query->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * Return the Page by it's type title
     *
     * @return mixed
     */
    public function getAllPageByType()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select(['pt.title, p.id'])
            ->from('WysiwygBundle:PageType', 'pt', 'pt.title')
            ->innerJoin('WysiwygBundle:Page', 'p', Expr\Join::WITH, 'p.pageType = pt.id')
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }
}
