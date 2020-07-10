<?php

namespace ArcaSolutions\SearchBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('searchSummary', [$this, 'searchSummary'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * Render footer navigation view
     *
     * @param \Twig_Environment $twig_Environment
     * @param $item
     * @param $pageCategories
     * @param $pageLocations
     * @param $pageBadges
     * @param $levelFeatures
     * @param $twigFile
     *
     * @return string
     */
    public function searchSummary(\Twig_Environment $twig_Environment, $item, $pageCategories, $pageLocations, $pageBadges, $levelFeatures, $twigFile = 'summary.html.twig' )
    {
        if (!$item) {
            return '';
        }

        $twigFile = '::modules/' . $item->getType() . '/' . $twigFile;

        $data = [
            'item' => $item,
            'pageCategories' => $pageCategories,
            'pageLocations' => $pageLocations,
            'pageBadges' => $pageBadges,
            'levelFeatures' => $levelFeatures,
        ];

        /* ModStores Hooks */
        HookFire( "summaryextension_before_render_summary", [
            "twigFile" => &$twigFile,
            "data"     => &$data,
        ]);

        return $twig_Environment->render($twigFile, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'search';
    }
}