<?php

namespace ArcaSolutions\WebBundle\Imagine\Cache;

use ArcaSolutions\WebBundle\Imagine\WebpGenerator;
use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

class CustomWebPathResolver extends WebPathResolver {

    private $webpGenerator;

    /**
     * @param Filesystem     $filesystem
     * @param RequestContext $requestContext
     * @param String $webRoot
     * @param String $cachePrefix
     */
    public function __construct(Filesystem $filesystem, RequestContext $requestContext, String $webRoot, String $cachePrefix) {
        parent::__construct($filesystem, $requestContext, $webRoot, $cachePrefix);
        $this->webpGenerator = new WebpGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter) {
        parent::store($binary, $path, $filter);

        $imagePath = $this->getFilePath($path, $filter);
        $webpPath = $this->webpGenerator->getWebpPath($imagePath);

        // If webp file not exits let's create it
        if (!is_file($webpPath)) {
            try {
                $this->webpGenerator->createWebpFromImagePath($imagePath, $webpPath);
            } catch (\Exception $e) {
            }
        }
    }
}
