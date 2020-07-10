<?php

namespace ArcaSolutions\CoreBundle\EventListener;

use ArcaSolutions\CoreBundle\Controller\MaintenanceController;
use ArcaSolutions\CoreBundle\Exception\ItemNotFoundException;
use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\WebBundle\Controller\LeadGenController;
use FOS\JsRoutingBundle\Controller\Controller as JsRoutingController;
use Symfony\Bundle\AsseticBundle\Controller\AsseticController;
use Symfony\Bundle\WebProfilerBundle\Controller\ProfilerController;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class BeforeControllerListener
 *
 * @package ArcaSolutions\CoreBundle\EventListener
 */
class BeforeControllerListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * BeforeControllerListener constructor.
     *
     * @param ContainerInterface $container
     * @param Settings $settings
     */
    public function __construct(ContainerInterface $container, Settings $settings)
    {
        $this->container = $container;
        $this->settings = $settings;
    }

    /**
     * Call it before a controller in every request
     *
     * @param FilterControllerEvent $event
     * @return Response|void
     * @throws \Exception
     */
    public function onKernelController(FilterControllerEvent $event)
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

        $this->container->get("utility")->setPackages();

        $this->validateRoute($event);

        $this->maintenanceMode($controller, $event);

        /*
         * Sets project locale
         */
        $this->setsTimeLocale();

        /*
         * Exits if it is a ajax request
         */
        if ($event->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($request->getSession()->get('modal')) {
            $request->getSession()->remove('modal');
            $this->container->get('twig')->addGlobal('openModal', true);
        }

        /* ModStores Hooks */
        HookFire('beforecontroller_after_onkernel', [
            'that'  => $this,
            'event' => $event
        ]);
    }

    /**
     * @param FilterControllerEvent $event
     * @throws \Exception
     */
    private function validateRoute(FilterControllerEvent $event)
    {
        /* gets current route's name */
        $possibly_module_name = explode('_', $event->getRequest()->get('_route'));
        $possibly_module_name = current($possibly_module_name);

        /*
         * If it is not a module, just continue
         * If it is a module, then validate it
         */
        if ($this->container->get('modules')->isModule($possibly_module_name)
            && !$this->container->get('modules')->isModuleAvailable($possibly_module_name)
        ) {
            throw new ItemNotFoundException();
        }
    }

    /**
     * Redirects if maintenance mode is true or sends code 503 if a ajax
     * request or request was NOT from api, else if the user is in the maintenance
     * screen it is redirected to the home page
     *
     * @param array $controller
     * @param FilterControllerEvent $event
     */
    private function maintenanceMode($controller, $event)
    {
        // Gets the parameter of the maintenance
        $maintenance = $this->settings->getDomainSetting('maintenance_mode');
        $redirectUrl = null;

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if(preg_match('/\/api\/v\d+/', $request->getRequestUri())) {
            return;
        }

        if ($maintenance == 'on' and !(
                $controller[0] instanceof LeadGenController or
                $controller[0] instanceof MaintenanceController or
                $controller[0] instanceof AsseticController or
                $controller[0] instanceof JsRoutingController or
                $controller[0] instanceof ProfilerController or // Only Dev Mode
                (strpos($_SERVER["REQUEST_URI"], "media/cache") !== false) or
                $request->getSession()->get('SM_LOGGEDIN')
            )
        ) {
            if ($event->getRequest()->isXmlHttpRequest() or
                ("api" == substr($event->getRequest()->attributes->get('_route'), 0, 3))
            ) {
                $response = new Response();
                $response->setStatusCode(503);
                $response->headers->set('content-type', $event->getRequest()->attributes->get('media_type'));

                $response->setContent(json_encode([
                    "code"    => "503",
                    "message" => "Service Unavailable",
                ]));

                $response->send();
                exit;
            }

            $redirectUrl = $this->container->get('router')->generate('web_maintenance');
        } elseif ($maintenance == 'off' and $controller[0] instanceof MaintenanceController) {
            $redirectUrl = $this->container->get('router')->generate('web_homepage');
        }
        if (!is_null($redirectUrl)) {
            $event->setController(function () use ($redirectUrl) {
                return new RedirectResponse($redirectUrl);
            });
        }
    }

    /**
     * Sets project locale
     *
     * @return void
     */
    private function setsTimeLocale()
    {
        $settings = $this->container->get('settings')->getDomainSetting('date_timezone');

        date_default_timezone_set($settings ?: 'America/Los_Angeles');

        setlocale(LC_TIME,
            $this->container->get('multi_domain.information')->getLocale().'.'.$this->container->getParameter('charset'));
    }
}
