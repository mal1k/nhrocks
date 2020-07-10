<?php

namespace ArcaSolutions\MultiDomainBundle\EventListener;



use ArcaSolutions\MultiDomainBundle\Services\Settings;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ConsoleCommandListener
 * @package ArcaSolutions\MultiDomainBundle\EventListener
 */
class ConsoleCommandListener implements EventSubscriberInterface
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var Settings
     */
    private $settings;

    /**
     * ConsoleCommandListener constructor.
     *
     * @param Container $container
     * @param Settings $settings
     */
    public function __construct(Container $container, Settings $settings)
    {
        $this->container = $container;
        $this->settings = $settings;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'console.command' => 'setTranslatorLocale',
        ];
    }

    public function setTranslatorLocale()
    {
        $translator = $this->container->get("translator");
        $translator->setLocale($this->settings->getLocale());
    }

}
