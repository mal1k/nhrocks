<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VideoGalleryExtension
 * @package ArcaSolutions\WebBundle\Twig\Extension
 */
class VideoGalleryExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('getVideoImageUrl', [$this, 'getVideoImageUrl'])
        ];
    }

    /**
     * @param $videoUrl
     * @return mixed|string
     */
    public function getVideoImageUrl($videoUrl)
    {
        $thumbnailUrl = null;

        if(preg_match('/vimeo\.com/', $videoUrl)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://vimeo.com/api/oembed.json?url=' . $videoUrl . '&width=640');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = json_decode(curl_exec($ch), true);

            $thumbnailUrl = str_replace('.webp', '.jpg', $data['thumbnail_url']);
        }

        if(empty($thumbnailUrl)) {
            return $this->container->get('templating.helper.assets')->getUrl('assets/images/video-placeholder.png');
        }

        return $thumbnailUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'video-gallery';
    }
}
