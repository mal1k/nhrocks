<?php

namespace ArcaSolutions\CoreBundle\DataFixtures\ORM;

use ArcaSolutions\CoreBundle\Entity\Setting;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadSettingData
 * @package ArcaSolutions\CoreBundle\DataFixtures\ORM
 */
class LoadSettingData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /* These are the standard data of the system */
        $standardInserts = [
            ['sitemgr_username','sitemgr@demodirectory.com'],
            ['sitemgr_password','e99a18c428cb38d5f260853678922e03'],
            ['sitemgr_faillogin_count','0'],
            ['sitemgr_faillogin_datetime','0000-00-00 00:00:00'],
            ['sitemgr_first_login','yes'],
            ['sitemgr_language','en_us'],
            ['complementary_info','e3d35c82b32a48c1496241bc482f90a5']
        ];

        $repository = $manager->getRepository('CoreBundle:Setting');

        foreach ($standardInserts as list($name, $value)) {
            $query = $repository->findOneBy([
                'name' => $name,
            ]);

            $setting = new Setting();

            /* checks if the setting already exist so they can be updated or added */
            if ($query) {
                $setting = $query;
            }

            $setting->setName($name);
            $setting->setValue($value);

            $manager->persist($setting);
        }

        $manager->flush();
    }
}
