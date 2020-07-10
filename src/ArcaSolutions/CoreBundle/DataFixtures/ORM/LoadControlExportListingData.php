<?php

namespace ArcaSolutions\CoreBundle\DataFixtures\ORM;

use ArcaSolutions\CoreBundle\Entity\ControlExportListing;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadControlExportListingData
 * @package ArcaSolutions\CoreBundle\DataFixtures\ORM
 */
class LoadControlExportListingData extends AbstractFixture implements OrderedFixtureInterface
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
                'id'                   => 1,
                'domainId'             => 1,
                'lastRunDate'          => new \DateTime(),
                'totalListingExported' => 0,
                'lastListingId'        => 0,
                'block'                => 50000,
                'finished'             => 'Y',
                'filename'             => '',
                'type'                 => 'csv',
                'runningCron'          => 'N',
                'scheduled'            => 'N',
            ],
            [
                'id'                   => 2,
                'domainId'             => 1,
                'lastRunDate'          => new \DateTime(),
                'totalListingExported' => 0,
                'lastListingId'        => 0,
                'block'                => 10000,
                'finished'             => 'Y',
                'filename'             => '',
                'type'                 => 'csv - data',
                'runningCron'          => 'N',
                'scheduled'            => 'N',
            ],
        ];

        $repository = $manager->getRepository('CoreBundle:ControlExportListing');

        foreach ($standardInserts as $controlExportListingInsert) {
            $query = $repository->findOneBy([
                'domainId' => $controlExportListingInsert['domainId'],
                'type'     => $controlExportListingInsert['type'],
            ]);

            $controlCron = new ControlExportListing();

            /* checks if the control_export_listing already exist so they can be updated or added */
            if ($query) {
                $controlCron = $query;
            }

            $controlCron->setId($controlExportListingInsert['id']);
            $controlCron->setDomainId($controlExportListingInsert['domainId']);
            $controlCron->setLastRunDate($controlExportListingInsert['lastRunDate']);
            $controlCron->setTotalListingExported($controlExportListingInsert['totalListingExported']);
            $controlCron->setLastListingId($controlExportListingInsert['lastListingId']);
            $controlCron->setBlock($controlExportListingInsert['block']);
            $controlCron->setFinished($controlExportListingInsert['finished']);
            $controlCron->setFilename($controlExportListingInsert['filename']);
            $controlCron->setType($controlExportListingInsert['type']);
            $controlCron->setRunningCron($controlExportListingInsert['runningCron']);
            $controlCron->setScheduled($controlExportListingInsert['scheduled']);

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
