<?php

namespace ArcaSolutions\WysiwygBundle\Twig\Extension;


use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\PageWidget;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WysiwygExtension
 *
 * @package ArcaSolutions\WysiwygBundle\Twig\Extension
 */
class WysiwygExtension extends \Twig_Extension
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
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
            new \Twig_SimpleFunction('renderPage', [$this, 'renderPage'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('getModule', [$this, 'getModule']),
            new \Twig_SimpleFunction('getModuleBanner', [$this, 'getModuleBanner']),
            new \Twig_SimpleFunction('getModuleSearch', [$this, 'getModuleSearch']),
            new \Twig_SimpleFunction('isSitemgrSession', [$this, 'isSitemgrSession']),
        ];
    }

    /**
     * This function renders the whole page using the information of the DB
     * Ordering the widgets of the page
     *
     * @param \Twig_Environment $twig_Environment
     * @param null $pageId
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderPage(\Twig_Environment $twig_Environment, $pageId = null)
    {
        $return = '';

        $theme = $this->container->get('theme.service')->getSelectedTheme();

        $pageWidgets = $this->container->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->findBy([
            'pageId'  => $pageId,
            'themeId' => $theme->getId(),
        ], ['order' => 'ASC']);

        /* ModStores Hooks */
        HookFire('wysiwyg_extension_renderpage_after_retrieve_page_widgets', [
            'page_id'      => &$pageId,
            'page_widgets' => &$pageWidgets,
            'html_return'  => &$return
        ]);

        /* @var PageWidget $pageWidget */
        foreach ($pageWidgets as $k => $pageWidget) {
            /* @var Widget $widget */
            $widget = $pageWidget->getWidget();

            if(in_array($widget->getTitle(), $this->container->get('widget.service')->thinStrip) && !empty($pageWidgets[$k+1]) && $pageWidgets[$k+1]->getWidget()->getType() == Widget::FOOTER_TYPE) {
                $widgetContent = json_decode($pageWidget->getContent(), true);
                $footerContent = json_decode($pageWidgets[$k+1]->getContent(), true);

                if($widgetContent['backgroundColor'] === $footerContent['backgroundColor']) {
                    $widgetContent['lastItem'] = 'true';
                    $pageWidget->setContent(json_encode($widgetContent));
                }
            }

            $widgetFile = '::widgets' . $widget->getTwigFile();

            /* ModStores Hooks */
            HookFire('wysiwygextension_before_validate_widget', [
                'pageWidget' => &$pageWidget,
                'widgetFile' => &$widgetFile,
            ]);

            if ($twig_Environment->getLoader()->exists($widgetFile)) {

                $content = json_decode($pageWidget->getContent());

                $widgetLink = null;

                if(isset($content->widgetLink) && !empty($content->widgetLink->label)) {
                    if (!empty($content->widgetLink->link)) {
                        $widgetLink = strpos( $content->widgetLink->link, '://') ? $content->widgetLink->link : $this->container->get('pagetype.service')->getBaseUrl(PageType::HOME_PAGE) . '/' . $content->widgetLink->link;
                    } elseif(!empty($content->widgetLink->page_id)) {
                        /** @var Page $page */
                        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->find($content->widgetLink->page_id);
                        $widgetLink = $this->container->get('page.service')->getFinalPageUrl($page);
                    }
                }

                $data = [
                    'content'    => $content,
                    'widgetLink' => $widgetLink,
                    'widget_id' => $pageWidget->getId(),
                ];

                /* ModStores Hooks */
                HookFire('wysiwygextension_before_render_widget', [
                    'pageWidget' => &$pageWidget,
                    'widgetFile' => &$widgetFile,
                    'data'       => &$data,
                ]);

                $renderedHtml = $twig_Environment->render($widgetFile, $data);

                /* ModStores Hooks */
                HookFire('wysiwygextension_after_render_widget', [
                    'pageWidget'   => &$pageWidget,
                    'widgetFile'   => &$widgetFile,
                    'renderedHtml' => &$renderedHtml
                ]);

                $return .= $renderedHtml;
            } else {
                $this->container->get('logger')->addError('Twig file not found: ' . $widget->getTwigFile());
            }
        }

        /* ModStores Hooks */
        HookFire('wysiwyg_extension_renderpage_after_render_widgets', [
            'page_id'      => &$pageId,
            'page_widgets' => &$pageWidgets,
            'html_return'  => &$return
        ]);

        return $return;
    }

    /**
     * @return mixed
     */
    public function isSitemgrSession()
    {
        return $this->container->get('request_stack')->getCurrentRequest()->getSession()->get('SM_LOGGEDIN');
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->container->get('widget.service')->getModule();
    }

    /**
     * @return string
     */
    public function getModuleSearch()
    {
        return $this->container->get('widget.service')->getModuleSearch();
    }

    /**
     * @return string
     */
    public function getModuleBanner()
    {
        return $this->container->get('widget.service')->getModuleBanner();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'wysiwyg';
    }
}
