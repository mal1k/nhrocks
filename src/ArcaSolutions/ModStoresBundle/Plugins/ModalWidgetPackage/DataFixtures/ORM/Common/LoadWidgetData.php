<?php

namespace ArcaSolutions\ModStoresBundle\Plugins\ModalWidgetPackage\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LoadWidgetData
 *
 * This class is responsible for inserting at the DataBase the standard widgets of the system
 *
 */
class LoadWidgetData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $standardWidgets = [
            [
                'title'    => 'Pop-up Modal',
                'twigFile' => '/modal/popup.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'drawerType' => 'popup',
                ],
                'modal'    => 'edit-custom-popup-content-modal',
            ],
            [
                'title'    => 'Left Drawer',
                'twigFile' => '/modal/left-drawer.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'drawerType' => 'left',
                ],
                'modal'    => 'edit-left-drawer-content-modal',
            ],
            [
                'title'    => 'Right Drawer',
                'twigFile' => '/modal/right-drawer.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'drawerType' => 'right',
                ],
                'modal'    => 'edit-right-drawer-content-modal',
            ],
            [
                'title'    => 'Top Drawer',
                'twigFile' => '/modal/top-drawer.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'drawerType' => 'top',
                ],
                'modal'    => 'edit-top-drawer-content-modal',
            ],
            [
                'title'    => 'Bottom Drawer',
                'twigFile' => '/modal/bottom-drawer.html.twig',
                'type'     => Widget::COMMON_TYPE,
                'content'  => [
                    'drawerType' => 'bottom',
                ],
                'modal'    => 'edit-bottom-drawer-content-modal',
            ],
        ];

        foreach ($standardWidgets as $sWidget) {

            $widget = new Widget();

            $query = $manager->getRepository('WysiwygBundle:Widget')->findOneBy([
                'twigFile' => $sWidget['twigFile'],
                'title'    => $sWidget['title'],
            ]);

            $query and $widget = $query;

            $widget->setTitle($sWidget['title']);
            $widget->setTwigFile($sWidget['twigFile']);
            $widget->setType($sWidget['type']);
            $widget->setContent(json_encode($sWidget['content']));
            $widget->setModal($sWidget['modal']);

            $manager->persist($widget);
            $manager->flush();

            $this->addReference($widget->getTitle(), $widget);
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
        return 1;
    }
}
