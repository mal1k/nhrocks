<?php
namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\CoreBundle\Services\Modules;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\MultiDomainBundle\Services\Settings;
use ArcaSolutions\WebBundle\Entity\SettingNavigation;
use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NavigationService
 * @package ArcaSolutions\WebBundle\Services
 */
final class NavigationService
{
    /**
     * @var Settings
     */
    private $multidomainSettings;

    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Modules
     */
    private $modules;

    public $mainHeaderNavigation     = [
        PageType::HOME_PAGE             => ['module' =>  NULL       ,'label' => 'Home'],
        PageType::LISTING_HOME_PAGE     => ['module' =>  NULL       ,'label' => 'Listings'],
        PageType::EVENT_HOME_PAGE       => ['module' => 'event'     ,'label' => 'Events'],
        PageType::CLASSIFIED_HOME_PAGE  => ['module' => 'classified','label' => 'Classifieds'],
        PageType::ARTICLE_HOME_PAGE     => ['module' => 'article'   ,'label' => 'Articles'],
        PageType::DEAL_HOME_PAGE        => ['module' => 'promotion' ,'label' => 'Deals'],
        PageType::BLOG_HOME_PAGE        => ['module' => 'blog'      ,'label' => 'Blog'],
        PageType::ADVERTISE_PAGE        => ['module' =>  NULL       ,'label' => 'Advertise'],
        PageType::CONTACT_US_PAGE       => ['module' =>  NULL       ,'label' => 'Contact Us'],
    ];
    public $mainFooterNavigation     = [
        PageType::HOME_PAGE             => ['module' =>  NULL       ,'label' => 'Home'],
        PageType::LISTING_HOME_PAGE     => ['module' =>  NULL       ,'label' => 'Listings'],
        PageType::EVENT_HOME_PAGE       => ['module' => 'event'     ,'label' => 'Events'],
        PageType::CLASSIFIED_HOME_PAGE  => ['module' => 'classified','label' => 'Classifieds'],
        PageType::ARTICLE_HOME_PAGE     => ['module' => 'article'   ,'label' => 'Articles'],
        PageType::DEAL_HOME_PAGE        => ['module' => 'promotion' ,'label' => 'Deals'],
        PageType::BLOG_HOME_PAGE        => ['module' => 'blog'      ,'label' => 'Blog'],
        PageType::ADVERTISE_PAGE        => ['module' =>  NULL       ,'label' => 'Advertise'],
        PageType::CONTACT_US_PAGE       => ['module' =>  NULL       ,'label' => 'Contact Us'],
        PageType::FAQ_PAGE              => ['module' =>  NULL       ,'label' => 'FAQ'],
        PageType::TERMS_OF_SERVICE_PAGE => ['module' =>  NULL       ,'label' => 'Terms of Use'],
        PageType::PRIVACY_POLICY_PAGE   => ['module' =>  NULL       ,'label' => 'Privacy Policy'],
    ];

    /**
     * NavigationService constructor.
     * @param Settings $multidomainSettings
     * @param DoctrineRegistry $doctrine
     * @param ContainerInterface $container
     * @param Modules $modules
     */
    public function __construct(Settings $multidomainSettings, DoctrineRegistry $doctrine, ContainerInterface $container, Modules $modules)
    {
        $this->multidomainSettings = $multidomainSettings;
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->modules = $modules;

        /* ModStorpagees Hooks */
        HookFire('navigationservice_construct', [
            'mainHeaderNavigation'                  => &$this->mainHeaderNavigation
        ]);
    }

    /**
     * Gets header menu
     *
     * @return array
     * @throws \Exception
     */
    public function getHeader()
    {
        $settingNavigation = $this->doctrine->getRepository('WebBundle:SettingNavigation');
        $settingNavigation->clear();
        $menu = $settingNavigation->getMenuByArea('header');

        $this->removesDisabledModules('Header', $menu);

        /* ModStores Hooks */
        HookFire("classnavigation_before_return_navbar", [
            "navBarOptions" => &$menu
        ]);

        /* ModStores Hooks */
        HookFire( "navigationhandler_before_returnheader", [
            "menu" => &$menu,
        ]);

        return $menu;
    }

    /**
     * It removes disabled modules from menu directly
     *
     * @param array $menu
     * @param $area
     */
    public function removesDisabledModules($area, array &$menu = [])
    {
        $modules_available = $this->container->get('modules')->getAvailableModules();

        $newMenu = [];
        foreach ($menu as $item) {
            if (!isset($item[$area])) {
                if (empty($item['page_id'])) {
                    $newMenu[] = $item;
                    continue;
                }
                /** @var Page $page */
                $page = $this->doctrine->getRepository('WysiwygBundle:Page')->find($item['page_id']);
                $pageModule = null;
                if ($page->getPageType()->getTitle() !== 'Custom Page') {
                    $pageModule = $this->{'main'.$area.'Navigation'}[$page->getPageType()->getTitle()]['module'];
                }
            } else {
                $pageModule = $item[$area];
            }

            if (null === $pageModule || $modules_available[$pageModule] || null === $modules_available[$pageModule]) {
                $newMenu[] = $item;
            }
        }

        $menu = $newMenu;
    }

    /**
     * Gets footer menu
     *
     * @return array|null
     * @throws \Exception
     */
    public function getFooter()
    {
        $menu = $this->doctrine->getRepository('WebBundle:SettingNavigation')->getMenuByArea('footer');

        $this->removesDisabledModules('Footer', $menu);

        return $menu;
    }


    /**
     * @param $navArr
     * @param string $area
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveNavigation($navArr, $area = 'header')
    {
        $em = $this->container->get('doctrine')->getManager();

        $this->clearNavigation($area);

        $order = 0;
        $settingNav = null;
        foreach ($navArr as $itemNavbar) {
            if ($itemNavbar['name'] && strpos($itemNavbar['name'], 'navigation_text_') !== false) {
                $settingNav = new SettingNavigation();
                $settingNav->setArea($area);
                $settingNav->setOrder($order);
                $settingNav->setLabel($itemNavbar['value']);
                continue;
            }
            if ($itemNavbar['name'] && strpos($itemNavbar['name'], 'custom_') !== false) {
                $settingNav->setCustom(!empty($itemNavbar['value']) ? $itemNavbar['value'] : 0);
                continue;
            }
            if ($itemNavbar['name'] && strpos($itemNavbar['name'], 'link_') !== false) {
                $settingNav->getCustom() ? $settingNav->setLink(trim($itemNavbar['value'])) : $settingNav->setPage($this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($itemNavbar['value']));
                $order++;
                $em->persist($settingNav);
            }
        }
        $em->flush();
        $em->clear();
    }

    public function clearNavigation($area)
    {
        $em = $this->container->get('doctrine')->getManager();
        $qb = $em->createQueryBuilder();
        $qb->delete('WebBundle:SettingNavigation', 'sn')
            ->where('sn.area = :area')
            ->setParameter('area', $area)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $area
     *
     * @return string
     * @throws \Exception
     */
    public function reloadNavbar($area = 'header')
    {
        $translator = $this->container->get('translator');

        $arrayOptions = $area === 'footer' ? $this->getFooter() : $this->getHeader();
        $navbarHtml = '';

        for ($i = 0, $iMax = count($arrayOptions); $i < $iMax; $i++) {
            include $this->container->getParameter('kernel.root_dir').'/../web/includes/forms/form-navigation-structure.php';
        }

        return $navbarHtml;
    }

    /**
     * @param $area
     * @return array
     */
    public function getNavigationPages($area)
    {
        /** @var Page[] $pages */
        $pages = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->findAll();
        $translator = $this->container->get('translator');

        $sitemgrLanguage = substr($this->container->get('settings')->getSetting('sitemgr_language'), 0, 2);

        foreach($pages as $page) {
            // mainHeaderNavigation
            // mainFooterNavigation
            if (array_key_exists($page->getPageType()->getTitle(), $this->{'main'. $area .'Navigation'})) {
                $navBarOptions['mainPages'][] = [
                    'name'    => $translator->trans(/** @Ignore */ $page->getTitle(), [], 'widgets', $sitemgrLanguage),
                    'page_id' => $page->getId(),
                    'module'  => $this->{'main'.$area.'Navigation'}[$page->getPageType()->getTitle()]['module'] ? : NULL,
                    'label'   => $translator->trans(/** @Ignore */ $this->{'main'.$area.'Navigation'}[$page->getPageType()->getTitle()]['label'], [], 'messages', $sitemgrLanguage),
                ];
            } elseif ($page->getPageType()->getTitle() === PageType::CUSTOM_PAGE) {
                $navBarOptions['customPages'][] = [
                    'name'    => $page->getTitle(),
                    'page_id' => $page->getId(),
                ];
            }
        }

        $navBarOptions['customPages'] and usort($navBarOptions['customPages'], function ($a, $b) {
            return strcmp(Inflector::ascii($a['name']), Inflector::ascii($b['name']));
        });

        return $navBarOptions;
    }
}
