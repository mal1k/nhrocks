<?php

namespace ArcaSolutions\CoreBundle\Controller;

use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MaintenanceController extends Controller
{
    /**
     * Handle maintenance mode
     * Get content in Content table
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->get('widget.service')->setModule('');

        $twig = $this->container->get("twig");
        $twig->addGlobal('contentType', PageType::MAINTENANCE_PAGE);

        $page = $this->container->get('doctrine')
            ->getRepository('WysiwygBundle:Page')
            ->getPageByType(PageType::MAINTENANCE_PAGE);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle' => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords' => $page->getMetaKey(),
            'customTag' => $page->getCustomTag()
        ]);
    }
}
