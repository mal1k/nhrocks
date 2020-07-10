<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NavigationExtension extends \Twig_Extension
{
    /**
     * ContainerInterface
     *
     * @var object
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
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('navigationHeader', [$this, 'navigationHeader'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('navigationFooter', [$this, 'navigationFooter'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * Render header navigation view
     *
     * @param \Twig_Environment $twig_Environment
     * @param $content
     *
     * @param null $widget
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function navigationHeader(\Twig_Environment $twig_Environment, $content = null, $widget = null)
    {
        $items = $this->container->get('navigation.service')->getHeader();

        // doesn't have items
        if (false === $items) {
            return '';
        }

        $twigFile = '::blocks/navigation/header-navigation';

        $twigFile .= $widget ? $widget.'.html.twig' : '.html.twig';

        $itemsToConsider = array();
        for ($k = 0, $kMax = \count($items); $k < $kMax; $k++) {
            if ($items[$k]['custom']) {
                $items[$k]['pageUrl'] = strpos($items[$k]['link'], '://') ? $items[$k]['link'] : $this->container->get('pagetype.service')->getBaseUrl(PageType::HOME_PAGE).'/'.$items[$k]['link'];
                $itemsToConsider[] = $items[$k];
            } else if (!HookFire( 'navigationextension_before_build_itempageid', ['item' => &$items[$k]])) {
                if(array_key_exists('pageId',$items[$k]) && !empty($items[$k]['pageId'])) {
                    $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($items[$k]['pageId']);
                    $items[$k]['pageUrl'] = $this->container->get('page.service')->getFinalPageUrl($page);
                    $itemsToConsider[] = $items[$k];
                }
            } else {
                $itemsToConsider = $items;
            }
        }

        $data = [
            'items'   => $itemsToConsider,
            'content' => $content,
        ];

        /* ModStores Hooks */
        HookFire( 'navigationextension_before_render_navigationheader', [
            'twigFile' => &$twigFile,
            'items'    => &$itemsToConsider,
            'data'     => &$data,
        ]);

        return $twig_Environment->render($twigFile, $data);
    }

    /**
     * Render footer navigation view
     *
     * @param \Twig_Environment $twig_Environment
     * @param string $content
     *
     * @param null $widget
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function navigationFooter(\Twig_Environment $twig_Environment, $content = null, $widget = null)
    {
        $items = $this->container->get('navigation.service')->getFooter();

        // doesn't have items
        if (false === $items) {
            return '';
        }

        $twigFile = '::blocks/navigation/footer-navigation';

        $twigFile .= $widget ? $widget.'.html.twig' : '.html.twig';

        for($k = 0, $kMax = count($items); $k < $kMax; $k ++) {
            if ($items[$k]['custom']) {
                $items[$k]['pageUrl'] = strpos( $items[$k]['link'], '://') ? $items[$k]['link'] : $this->container->get('pagetype.service')->getBaseUrl(PageType::HOME_PAGE) . '/' . $items[$k]['link'];
            } else {
                /** @var Page $page */
                $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($items[$k]['pageId']);
                $items[$k]['pageUrl'] = $this->container->get('page.service')->getFinalPageUrl($page);
            }
        }

        $data = [
            'items'   => $items,
            'content' => $content,
        ];

        return $twig_Environment->render($twigFile, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'navigation_front';
    }
}
