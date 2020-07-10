<?php
namespace ArcaSolutions\ArticleBundle\Twig\Extension;

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
            new \Twig_SimpleFunction(
                'recentArticle',
                [$this, 'recentArticle']
            ),
            new \Twig_SimpleFunction(
                'popularArticle',
                [$this, 'popularArticle']
            ),
        ];
    }

    /**
     * @param int $quantity
     *
     * @return array
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function recentArticle($quantity = 2)
    {
        if (!$this->container->get('modules')->isModuleAvailable('article')) {
            return [];
        }

        $recentArticles = $this->container->get('search.block')->getRecent('article', $quantity);

        if (!$recentArticles) {
            return [];
        }

        return $recentArticles;
    }

    public function popularArticle($quantity = 5)
    {
        $content = new \stdClass();
        $content->custom = new \stdClass();
        $content->custom->order1 = 'popular';
        $content->custom->order2 = 'random';
        $popularArticles = $this->container->get('search.block')->getCards(ParameterHandler::MODULE_ARTICLE, $quantity, $content);

        if (!$popularArticles) {
            return [];
        }

        return $popularArticles;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'blocks_article';
    }
}
