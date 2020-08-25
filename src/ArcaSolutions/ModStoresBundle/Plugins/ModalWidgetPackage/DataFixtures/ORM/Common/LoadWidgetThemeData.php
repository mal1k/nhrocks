<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\ModalWidgetPackage\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\WidgetTheme;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadWidgetThemeData
 *
 */
class LoadWidgetThemeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $standardWidgetThemes = [
            $this->getReference('Pop-up Modal'),
            $this->getReference('Left Drawer'),
            $this->getReference('Right Drawer'),
            $this->getReference('Top Drawer'),
            $this->getReference('Bottom Drawer'),
        ];

        foreach ($manager->getRepository('WysiwygBundle:Theme')->findAll() as $theme) {

            foreach ($standardWidgetThemes as $standardWidgetTheme) {

                $widgetTheme = new WidgetTheme();

                $query = $manager->getRepository('WysiwygBundle:WidgetTheme')->findOneBy([
                    'widgetId' => $standardWidgetTheme->getId(),
                    'themeId'  => $theme->getId(),
                ]);

                $query and $widgetTheme = $query;

                $widgetTheme->setWidget($standardWidgetTheme);
                $widgetTheme->setTheme($theme);

                $manager->persist($widgetTheme);
                $manager->flush();
            }

        }
    }

    /**
     * the order in which fixtures will be loaded
     * the lower the number, the sooner that this fixture is loaded
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
