<?php

namespace ArcaSolutions\CoreBundle\DataFixtures\ORM;

use ArcaSolutions\CoreBundle\Entity\ControlExportEvent;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadControlExportEventData
 *
 * This class is responsible for inserting at the DataBase the standard Control_Export_Event data
 *
 * @package ArcaSolutions\CoreBundle\DataFixtures\ORM
 */
class LoadControlExportEventData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* These are the standard data of the system */
        $standardInserts = [
            [
                'id'                 => 1,
                'domainId'           => 1,
                'lastRunDate'        => new \DateTime(),
                'totalEventExported' => 0,
                'lastEventId'        => 0,
                'block'              => 50000,
                'finished'           => 'Y',
                'filename'           => '',
                'type'               => 'csv',
                'runningCron'        => 'N',
                'scheduled'          => 'N',
            ],
            [
                'id'                 => 2,
                'domainId'           => 1,
                'lastRunDate'        => new \DateTime(),
                'totalEventExported' => 0,
                'lastEventId'        => 0,
                'block'              => 10000,
                'finished'           => 'Y',
                'filename'           => '',
                'type'               => 'csv - data',
                'runningCron'        => 'N',
                'scheduled'          => 'N',
            ],
        ];

        $repository = $manager->getRepository('CoreBundle:ControlExportEvent');

        foreach ($standardInserts as $controlExportEventInsert) {
            $query = $repository->findOneBy([
                'domainId' => $controlExportEventInsert['domainId'],
                'type'     => $controlExportEventInsert['type'],
            ]);

            $controlCron = new ControlExportEvent();

            /* checks if the control_export_event already exist so they can be updated or added */
            if ($query) {
                $controlCron = $query;
            }

            $controlCron->setId($controlExportEventInsert['id']);
            $controlCron->setDomainId($controlExportEventInsert['domainId']);
            $controlCron->setLastRunDate($controlExportEventInsert['lastRunDate']);
            $controlCron->setTotalEventExported($controlExportEventInsert['totalEventExported']);
            $controlCron->setLastEventId($controlExportEventInsert['lastEventId']);
            $controlCron->setBlock($controlExportEventInsert['block']);
            $controlCron->setFinished($controlExportEventInsert['finished']);
            $controlCron->setFilename($controlExportEventInsert['filename']);
            $controlCron->setType($controlExportEventInsert['type']);
            $controlCron->setRunningCron($controlExportEventInsert['runningCron']);
            $controlCron->setScheduled($controlExportEventInsert['scheduled']);

            $manager->persist($controlCron);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }


}
