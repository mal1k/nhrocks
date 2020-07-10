<?php

namespace ArcaSolutions\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\Container;

/**
 * Class LocalizedDateExtension
 *
 * Adds a filter as a shortcut to twig's localized function
 *
 * @package ArcaSolutions\CoreBundle\Twig\Extension
 */
class LocalizedDateExtension extends \Twig_Extension
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('localized_date', [$this, 'localized_date'], ['needs_environment' => true]),
        ];
    }

    /**
     * Shortcut to the twig's localized function
     *
     * @param \Twig_Environment $twig_Environment
     * @param \DateTime $date
     * @param null $format
     *
     * @return bool|string
     * @throws \Exception
     */
    public function localized_date(\Twig_Environment $twig_Environment, \DateTime $date, $format = null)
    {
        $sitemgrDateFormat = $this->container->get('languagehandler')->getDateFormat();

        /*
         * we decided that pt, for language(in symfony), represents Brazilian portuguese
         * but in unix systems the Brazilian portuguese representation is pt_BR
         * so for use it in date, we have to convert it to pt_BR
        */
        $localized = $this->container->get('request_stack')->getCurrentRequest()->getLocale();
        if ($localized === 'pt') {
            $localized = 'pt_BR';
        }

        if ($localized === 'en_us' && $sitemgrDateFormat === 'd/m/Y') {
            if ($format === 'MMMM d') {
                $format = 'd MMMM';
            } elseif ($format === 'MMMM d, y') {
                $format = 'd MMMM, y';
            }
        }

        return twig_localized_date_filter($twig_Environment, $date, 'none', 'none', $localized,
            date_default_timezone_get(), $format);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'localized_date';
    }
}
