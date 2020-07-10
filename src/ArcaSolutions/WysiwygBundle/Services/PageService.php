<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Wysiwyg
 *
 * This service handles everything but RENDERING that has something to do with Wysiwyg
 * Create, Edit, Delete pages and their widgets
 * Retrieving the data from DB, saving data in DB.
 *
 */
class PageService
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getAllPages()
    {
        return $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->findAll();
    }

    /**
     * @param integer $id
     */
    public function deletePage($id)
    {
        try {
            $doctrine = $this->container->get('doctrine');
            $em = $doctrine->getManager();
            $page = $doctrine->getRepository('WysiwygBundle:Page')->find($id);

            $em->remove($page);
            $em->flush($page);
        } catch (Exception $e) {
            $this->container->get('logger')->addError($e->getMessage());
        }
    }

    /**
     * @param integer $id
     *
     * @return Page
     */
    public function getPage($id)
    {
        return $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($id);
    }

    /**
     * @param $page Page
     *
     * @return string
     */
    public function getFinalPageUrl($page)
    {
        $pageTypeTitle = $page->getPageType()->getTitle();

        $uri = $this->container->get('pagetype.service')->getModuleUri($pageTypeTitle);

        $domainUrl = $this->container->get('request_stack')->getCurrentRequest()->getSchemeAndHttpHost();

        $pageUrl = $domainUrl.($uri ? '/'. $uri : '').'/'.$page->getUrl();

        $pageUrl .= $pageTypeTitle === PageType::CUSTOM_PAGE ? '.html' : '';

        return $pageUrl;
    }

    /**
     * @param $page Page
     *
     * @return string
     */
    public function getActiveHostFinalPageUrl($page)
    {
        $pageTypeTitle = $page->getPageType()->getTitle();

        $uri = $this->container->get('pagetype.service')->getModuleUri($pageTypeTitle);

        $domainUrl = str_replace('_','-',$this->container->get('multi_domain.information')->getActiveHost());

        $scheme = $this->container->get('request_stack')->getCurrentRequest()->getScheme().'://';

        $pageUrl = $scheme.$domainUrl.($uri ? '/'. $uri : '').'/'.$page->getUrl();

        $pageUrl .= $pageTypeTitle === PageType::CUSTOM_PAGE ? '.html' : '';

        return $pageUrl;
    }
}