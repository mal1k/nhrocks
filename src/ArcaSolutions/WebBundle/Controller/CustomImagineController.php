<?php

namespace ArcaSolutions\WebBundle\Controller;

use Liip\ImagineBundle\Controller\ImagineController;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomImagineController extends ImagineController {

    const SUPPORTED_IMAGE_EXT = ['.png', '.jpg', '.gif', '.jpeg'];

    /**
     * Checks if `$path` ends with `.webp`
     * 
     * @param String $path It's the webp image cache path passed in url path.
     * @return Boolean Returs `true` is `$path` ends with `.webp`.
     */
    private function isWebp($path) {
        $ext = 'webp';
        $start = strlen($ext) * -1;
        return substr($path, $start) === $ext;
    }

    /**
     * Look up for the origin file for given webp file `$path`.
     * 
     * @param mixed $filter The name of the imagine filter in effect.
     * @param String $path It's the webp image cache path passed in url path.
     * 
     * @throws NotFoundHttpException if no origin file found.
     * 
     * @return String The origin file of webp, it can ends with `.jpg`, `.png`, `.jpeg` and `.gif`.
     */
    private function getWebpOriginPath($filter, $path) {
        
        $loader = $this->dataManager->getLoader($filter);
        
        foreach (self::SUPPORTED_IMAGE_EXT as $ext) {
            $supposedPath = str_replace('.webp', $ext, $path); 
            try {
                $binary = $loader->find($supposedPath);
                if (null != $binary->getContent()) {
                    return $supposedPath;
                }
            } catch(NotLoadableException $e) {                
            }
        }

        throw NotFoundHttpException();
    }

    private function applyFilter($filter, $path) {
        try {
            $binary = $this->dataManager->find($filter, $path);
        } catch (NotLoadableException $e) {
            if ($defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter)) {
                return new RedirectResponse($defaultImageUrl);
            }

            throw new NotFoundHttpException('Source image could not be found', $e);
        }

        $this->cacheManager->store(
            $this->filterManager->applyFilter($binary, $filter),
            $path,
            $filter
        );
    }

    /**
     * {@inheritdoc}
     */
    function filterAction(\Symfony\Component\HttpFoundation\Request $request, $path, $filter)
    {
        // decoding special characters and whitespaces from path obtained from url
        $path = urldecode($path);

        try {

            if (!$this->cacheManager->isStored($path, $filter)) {
                // If requested image is webp, should find the origined file then create cash 
                // for both origin image and the requested webp image.
                if ($this->isWebp($path)) {
                    $originPath = $this->getWebpOriginPath($filter, $path);
                    $this->applyFilter($filter, $originPath);
                } else {
                    $this->applyFilter($filter, $path);
                }
            }

            return new RedirectResponse($this->cacheManager->resolve($path, $filter), 301);
        } catch (NonExistingFilterException $e) {
            $message = sprintf('Could not locate filter "%s" for path "%s". Message was "%s"', $filter, $path, $e->getMessage());

            if (null !== $this->logger) {
                $this->logger->debug($message);
            }

            throw new NotFoundHttpException($message, $e);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
        }
    }

}