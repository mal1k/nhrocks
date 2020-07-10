<?php

namespace ArcaSolutions\WysiwygBundle\DataFixtures\ORM\Common;

use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadWidgetData
 *
 * This class is responsible for inserting at the DataBase the standard widgets of the system
 *
 * @package ArcaSolutions\WysiwygBundle\DataFixtures\ORM\Common
 */
class LoadWidgetData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function load(ObjectManager $manager)
    {

        /* These are the standard widgets of the system.
         *
         * Widget title is used as reference in LoadPageWidgetData,
         * so if you change here don't forget to change there.
         *
         * Widget's title will be translated in sitemgr,
         * so if you change here don't forget to change there.
         **/

        $standardWidgets = $this->container->get('widget.service')->getWidgets();

        /* ModStores Hooks */
        HookFire("loadwidget_after_add_standardwidget", [
            "standardWidgets" => &$standardWidgets,
        ]);

        $repository = $manager->getRepository('WysiwygBundle:Widget');

        foreach ($standardWidgets as $sWidget) {
            $query = $repository->findOneBy([
                'twigFile' => $sWidget['twigFile'],
                'title'    => $sWidget['title'],
            ]);

            $widget = new Widget();
            /* checks if the widget already exist so they can be updated or added */
            if ($query) {
                $widget = $query;
            }

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

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
