<?php

namespace ArcaSolutions\WebBundle\Services;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TagPictureService
 */
class TagPictureService
{
    public const DEFAULT_FILTER = 'small';
    public const AVAILABLE_FILTERS = ['small', 'medium', 'large'];
    public const DEFAULT_WIDTHS = [
        'desktop' => ['min' => 1025],
        'laptop'  => ['min' => 769, 'max' => 1024],
        'tablet'  => ['min' => 426, 'max' => 768],
        'mobile'  => ['max' => 425]
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * TagPictureService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $imagePath
     * @param string $title
     * @param array $deviceFilter
     * @return array
     */
    public function getImageSource(string $imagePath, string $title, array $deviceFilter = [])
    {
        $defaultFilters = [
            'desktop' => 'small',
            'laptop'  => 'small',
            'tablet'  => 'small',
            'mobile'  => 'small'
        ];

        $deviceFilter = array_replace($defaultFilters, $deviceFilter);

        $fallbackFilter = self::DEFAULT_FILTER;

        $chooseDeviceFilter = [];
        foreach (self::DEFAULT_WIDTHS as $device => $size) {
            $devices = array_keys($deviceFilter);
            if (in_array($device, $devices)) {
                $filter = $deviceFilter[$device];
            } else {
                $filter = self::DEFAULT_FILTER;
            }
            $chooseDeviceFilter[$device] = $filter;
            $filterIndex = array_search($filter, self::AVAILABLE_FILTERS);
            if ($filterIndex && $filterIndex > 0) {
                $fallbackFilter =  $filter;
            }
        }

        $sources = [];
        foreach ($chooseDeviceFilter as $device => $filter) {
            $size = self::DEFAULT_WIDTHS[$device];
            $widths = [];

            if (isset($size['min'])) {
                $widths['min'] = $size['min'];
            }

            if (isset($size['max'])) {
                $widths['max'] = $size['max'];
            }

            $media = '('.implode(') and (', array_map(function ($k, $v) {
                    return $k . '-width:' . $v . 'px';
                }, array_keys($widths), $widths)) . ')';

            $this->appendSources($sources, $imagePath, $filter, $media);
        }

        $fallbackImageUrl = $this->resolveUrlApplyingFilter($imagePath, $fallbackFilter);

        // Puts webp image sources on top
        usort($sources, static function ($l, $r) {
            if (!isset($l['type'])) {
                return 1;
            }
            if (!isset($r['type'])) {
                return -1;
            }
            return strcmp($l['type'], $r['type']);
        });

        return [
            'sources'     => $sources,
            'imgFallback' => $fallbackImageUrl,
            'title'       => $title
        ];
    }

    /**
     * @param $sources
     * @param $path
     * @param $filter
     * @param null $media
     */
    private function appendSources(&$sources, $path, $filter, $media = null)
    {
        $url = $this->resolveUrlApplyingFilter($path, $filter);
        $webpUrl = preg_replace('/^((.+)(\.[a-z]+))$/', '$2.webp', $url);
        $sources[] = ['url' => $url, 'media' => $media];
        if ($this->imageUrlExistsOnFS($webpUrl)) {
            $sources[] = ['url' => $webpUrl, 'media' => $media, 'type' => 'image/webp'];
        }
    }

    /**
     * @param string $string - Image URL.
     * @return bool - True is that image exists on file system.
     */
    private function imageUrlExistsOnFS($imageUrl) {
        $path = preg_replace('/^(.+\/\/(.+?\/)(.+))$/', '$3', $imageUrl);
        return file_exists($this->container->getParameter('kernel.root_dir').'/../web/'.$path);
    }

    /**
     * @param $path
     * @return string|string[]|null
     */
    private function resolveWebpPath($path)
    {
        return preg_replace('/^((.+)(\.[a-z]+))$/', '$2.webp', $path);
    }

    /**
     * Resolves the imageUrl and apply filter to them if necessary.
     *
     * @param String $path
     * @param String $filter
     *
     * @return String image url .
     */
    private function resolveUrlApplyingFilter($path, $filter)
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = $this->container->get('liip_imagine.cache.manager');
        /** @var DataManager $dataManager */
        $dataManager = $this->container->get('liip_imagine.data.manager');
        /** @var FilterManager $filterManager */
        $filterManager = $this->container->get('liip_imagine.filter.manager');

        if(empty($path) || !file_exists($this->container->getParameter('kernel.root_dir').'/../web'.$path)) {
            $path = $this->container->get('utility')->getNoImagePath();
            $filter = 'noImage_'.$filter;
        }

        if(!empty($path) && file_exists($this->container->getParameter('kernel.root_dir').'/../web'.$path)) {
            $webpPath = $this->resolveWebpPath($path);
            $shouldApplyFilter = !$cacheManager->isStored($path, $filter)
                || !$cacheManager->isStored($webpPath, $filter);

            if ($shouldApplyFilter) {
                $binary = $dataManager->find($filter, $path);
                $cacheManager->store(
                    $filterManager->applyFilter($binary, $filter),
                    $path,
                    $filter
                );
            }

            return $cacheManager->resolve($path, $filter);
        }
    }
}
