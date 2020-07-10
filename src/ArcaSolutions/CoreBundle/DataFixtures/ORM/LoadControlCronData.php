<?php

namespace ArcaSolutions\CoreBundle\DataFixtures\ORM;

use ArcaSolutions\CoreBundle\Entity\ControlCron;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Class LoadControlCronData
 *
 * This class is responsible for inserting at the DataBase the standard Control_Cron data
 *
 * @package ArcaSolutions\CoreBundle\DataFixtures\ORM
 */
class LoadControlCronData extends AbstractFixture implements OrderedFixtureInterface
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
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'daily_maintenance'
            ],
            [
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'randomizer'
            ],
            [
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'renewal_reminder'
            ],
            [
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'report_rollup'
            ],
            [
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'sitemap'
            ],
            [
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'statisticreport'
            ],
            [
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'location_update'
            ],
            [
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'email_traffic'
            ],
            [
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'rollback_import'
            ],
            [
                'domainId' => 1,
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'type' => 'rollback_import_events'
            ],
        ];

        $repository = $manager->getRepository('CoreBundle:ControlCron');

        foreach ($standardInserts as $controlCronInsert) {
            $query = $repository->findOneBy([
                'domainId' => $controlCronInsert['domainId'],
                'type' => $controlCronInsert['type']
            ]);

            $controlCron = new ControlCron();
            /* checks if the control_cron already exist so they can be updated or added */

            if ($query) {
                $controlCron = $query;
            }

            $controlCron->setDomainId($controlCronInsert['domainId']);
            $controlCron->setRunning($controlCronInsert['running']);
            $controlCron->setLastRunDate($controlCronInsert['lastRunDate']);
            $controlCron->setType($controlCronInsert['type']);

            $manager->persist($controlCron);
        }

        $manager->flush();
    }

    /**
     * the order in which fixtures will be loaded
     * the lower the number, the sooner that this fixture is loaded
     *
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
