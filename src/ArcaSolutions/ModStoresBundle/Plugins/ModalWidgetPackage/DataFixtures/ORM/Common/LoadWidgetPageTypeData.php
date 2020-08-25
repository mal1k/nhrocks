<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\ModalWidgetPackage\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\WidgetPageType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadWidgetPageTypeData
 *
 * This class is responsible for inserting at the Database the standard Widget_PageType of the system
 *
 */
class LoadWidgetPageTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $widgetRepository = $manager->getRepository('WysiwygBundle:Widget');

        $standardWidgetPageTypes = [
            [
                'widget'   => $this->hasReference('Pop-up Modal') ?
                    $this->getReference('Pop-up Modal') :
                    $widgetRepository->findOneBy(['title' => 'Pop-up Modal']),
                'pageType' => null,
            ],
            [
                'widget'   => $this->hasReference('Left Drawer') ?
                    $this->getReference('Left Drawer') :
                    $widgetRepository->findOneBy(['title' => 'Left Drawer']),
                'pageType' => null,
            ],
            [
                'widget'   => $this->hasReference('Right Drawer') ?
                    $this->getReference('Right Drawer') :
                    $widgetRepository->findOneBy(['title' => 'Right Drawer']),
                'pageType' => null,
            ],
            [
                'widget'   => $this->hasReference('Top Drawer') ?
                    $this->getReference('Top Drawer') :
                    $widgetRepository->findOneBy(['title' => 'Top Drawer']),
                'pageType' => null,
            ],
            [
                'widget'   => $this->hasReference('Bottom Drawer') ?
                    $this->getReference('Bottom Drawer') :
                    $widgetRepository->findOneBy(['title' => 'Bottom Drawer']),
                'pageType' => null,
            ],

        ];

        foreach ($standardWidgetPageTypes as $sWidgetPageType) {

            $widgetPageType = new WidgetPageType();

            $query = $manager->getRepository('WysiwygBundle:WidgetPageType')->findOneBy([
                'pageType' => $sWidgetPageType['pageType'],
                'widget'   => $sWidgetPageType['widget'],
            ]);

            $query and $widgetPageType = $query;

            $widgetPageType->setWidget($sWidgetPageType['widget']);
            $widgetPageType->setPageType($sWidgetPageType['pageType']);

            $manager->persist($widgetPageType);
            $manager->flush();
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
        return 4;
    }
}
