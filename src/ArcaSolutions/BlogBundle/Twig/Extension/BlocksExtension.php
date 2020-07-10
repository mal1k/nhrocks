<?php
namespace ArcaSolutions\BlogBundle\Twig\Extension;

use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlocksExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $containerInterface
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->container = $containerInterface;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('recentPost', [$this, 'recentPost'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('getRecentPostsData', [$this, 'getRecentPostsData']),
        ];
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param int $quantity
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function recentPost(\Twig_Environment $twig_Environment, $quantity = 4) {
        if (!$this->container->get('modules')->isModuleAvailable('blog')) {
            return '';
        }

        $items = $this->container->get('search.block')->getRecent('blog', $quantity);

        if (!$items) {
            return '';
        }

        return $twig_Environment->render('::modules/blog/blocks/recent-big.html.twig', [
            'items'      => $items
        ]);
    }

    /**
     * @return array
     */
    public function getRecentPostsData()
    {
        // Featured Categories
        $categoriesFeatured = $this->container->get('search.repository.category')->findCategoriesWithItens(ParameterHandler::MODULE_BLOG, true);

        // Popular Posts
        $popularPosts = $this->container->get('search.block')->getPopular(ParameterHandler::MODULE_BLOG, 5, true);

        return [
            'categoriesFeatured' => $categoriesFeatured,
            'popularPosts'       => $popularPosts
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'blocks_blog';
    }
}
