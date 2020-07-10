<?php

namespace ArcaSolutions\AdminBundle\EventListener;

use ArcaSolutions\AdminBundle\Controller\AbstractAdminController;
use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\MultiDomainBundle\Exception\MultiDomainException;
use ArcaSolutions\MultiDomainBundle\Services\Settings as MultiDomain;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * Class BeforeControllerListener
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.3.00
 * @package ArcaSolutions\AdminBundle\EventListener
 */
class BeforeControllerListener
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var MultiDomain
     */
    private $multiDomain;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * BeforeControllerListener constructor.
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param TranslatorInterface $translator
     * @param Settings $settings
     * @param MultiDomain $multiDomain
     * @param Logger $logger
     */
    public function __construct(TranslatorInterface $translator, Settings $settings, MultiDomain $multiDomain, Logger $logger)
    {
        $this->translator = $translator;
        $this->settings = $settings;
        $this->multiDomain = $multiDomain;
        $this->logger = $logger;
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param FilterControllerEvent $event
     */
    public function onKernerController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure.
         * This is not usual in Symfony but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if (!$controller[0] instanceof AbstractAdminController) {
            return;
        }

        /* Sets active Domain */
        $request = $event->getRequest();
        $domainId = $request->get('domainId');
        if ($domainId && $this->multiDomain->getId() != $domainId) {
            try {
                $this->multiDomain->setActiveHostById($domainId, true);
            }catch (MultiDomainException $exception) {
                $this->logger->error('Error performing domain switching', ['Import']);
            }
        }

        /* Change Locale */
        if ($locale = $this->settings->getSetting('sitemgr_language')) {
            /* Workaround for the German language */
            $locale = $locale == 'ge_ge' ? 'de_de' : $locale;

            $this->translator->setLocale($locale);
            $request->setLocale($request->getPreferredLanguage([$locale]));
        }
    }
}
