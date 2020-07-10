<?php

namespace ArcaSolutions\CoreBundle\Twig\Extension;

/**
 * Class UrlExtension
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.3.04
 * @package ArcaSolutions\CoreBundle\Twig\Extension
 */
class UrlExtension extends \Twig_Extension
{

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'url_scheme',
                [$this, 'urlSchemeFilter'],
                ['is_safe' => ['html']]
            )
        ];
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.04
     *
     * @param string $url
     * @return string
     */
    public function urlSchemeFilter($url)
    {
        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'url_build';
    }
}
