<?php

namespace ArcaSolutions\WysiwygBundle\DataFixtures\ORM\ThemeDoctor;

use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
use ArcaSolutions\WysiwygBundle\Entity\Theme;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadPageWidgetData
 *
 * This class is responsible for inserting at the DataBase the standard PageWidgets of the system
 * The table PageWidgets has the information of which widgets a page has and in which order they are allocated.
 *
 * @package ArcaSolutions\WysiwygBundle\DataFixtures\ORM
 */
class LoadPageWidgetData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $repository = $manager->getRepository('WysiwygBundle:PageWidget');
        $this->container->get('theme.service')->setTheme(Theme::DOCTOR_THEME);
        $pageWidgetService = $this->container->get('pagewidget.service');
        $pagesDefault = $pageWidgetService->getAllPageDefaultWidgets();
        /** @var Theme $theme */
        $theme = $this->getReference(Theme::DOCTOR_THEME);

        foreach ($pagesDefault as $pageType => $pageDefaults) {
            /** @var Page $page */
            $page = $this->getReference($pageType.'_REFERENCE');

            if ($repository->hasWidgetOnPage($page->getId(), $theme->getId())) {
                continue;
            }

            foreach ($pageDefaults as $i => $pageDefault) {
                $content = null;
                if(is_array($pageDefault)) {
                    $content = json_encode(current($pageDefault)['content']);
                    $pageDefault = key($pageDefault);
                }
                $pageWidget = new PageWidget();
                $pageWidget->setTheme($theme);
                $pageWidget->setPage($page);
                $pageWidget->setWidget($this->getReference($pageDefault));
                $pageWidget->setOrder($i);
                $pageWidget->setContent(
                    $content ?: $this->getReference($pageDefault)->getContent()
                );

                $manager->persist($pageWidget);
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
        return 3;
    }
}
