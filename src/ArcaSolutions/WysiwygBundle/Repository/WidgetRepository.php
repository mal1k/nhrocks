<?php

namespace ArcaSolutions\WysiwygBundle\Repository;

use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Doctrine\ORM\EntityRepository;

class WidgetRepository extends EntityRepository
{
    public function findTypes()
    {
        return $this->createQueryBuilder('p')
            ->select('p.type')
            ->groupBy('p.type')
            ->getQuery()
            ->getResult();
    }

    public function findAllGrouped($pageTypeId, $themeId)
    {
        $groupedResult = [];
        $results = $this->createQueryBuilder('p')
            ->select('p.id', 'p.title', 'p.type', 'p.content')
            ->leftJoin('p.themes', 't')
            ->andWhere('t.themeId = :themeId')
            ->leftJoin('p.pageTypes', 'wpt')
            ->andWhere('wpt.pageTypeId IS NULL OR wpt.pageTypeId = :pageTypeId')
            ->setParameter('pageTypeId', $pageTypeId)
            ->setParameter('themeId', $themeId)
            ->orderBy('p.type')
            ->getQuery()
            ->getResult();

        foreach ($results as $result) {
            if (!in_array($result['type'], [Widget::CARDS_TYPE, Widget::NEWSLETTER_TYPE], true)) {
                $groupedResult['all'][] = $result;
            }

            $groupedResult[$result['type']][] = $result;
        }

        return $groupedResult;
    }

    /**
     * @param $widgetType
     * @param $themeId
     * @return array
     */
    public function getWidgetsMostUsedByType($widgetType, $themeId)
    {
        return $this->createQueryBuilder('w')
            ->select('w', 'count(pw.widgetId) as HIDDEN widgetCount', 'pw.content')
            ->leftJoin('w.pageWidgets', 'pw')
            ->where('w.type = :widgetType')
            ->andWhere('pw.themeId = :themeId')
            ->setParameter('widgetType', $widgetType)
            ->setParameter('themeId', $themeId)
            ->groupBy('w, pw.content')
            ->orderBy('widgetCount', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
