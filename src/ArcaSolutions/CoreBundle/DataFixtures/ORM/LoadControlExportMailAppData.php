<?php

namespace ArcaSolutions\CoreBundle\DataFixtures\ORM;


use ArcaSolutions\CoreBundle\Entity\ControlExportMailapp;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadControlExportMailAppData
 * @package ArcaSolutions\CoreBundle\DataFixtures\ORM
 */
class LoadControlExportMailAppData extends AbstractFixture implements OrderedFixtureInterface
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
                'scheduled' => 'N',
                'running' => 'N',
                'lastRunDate' => new \DateTime(),
                'lastExportlog' => 0
            ]
        ];

        $repository = $manager->getRepository('CoreBundle:ControlExportMailapp');

        foreach ($standardInserts as $controlExportMailAppInsert) {
            $query = $repository->findOneBy([
                'domainId' => $controlExportMailAppInsert['domainId'],
            ]);

            $controlCron = new ControlExportMailapp();

            /* checks if the control_Export_Mailapp already exist so they can be updated or added */
            if ($query) {
                $controlCron = $query;
            }

            $controlCron->setDomainId($controlExportMailAppInsert['domainId']);
            $controlCron->setScheduled($controlExportMailAppInsert['scheduled']);
            $controlCron->setRunning($controlExportMailAppInsert['running']);
            $controlCron->setLastRunDate($controlExportMailAppInsert['lastRunDate']);
            $controlCron->setLastExportlog($controlExportMailAppInsert['lastExportlog']);

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
