<?php

namespace ArcaSolutions\EventBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UpcomingEventsExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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
            new \Twig_SimpleFunction('upcomingEvents',[$this,'upcomingEvents'],[
                'needs_environment' => true,
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('upcomingEventsCarousel',[$this,'upcomingEventsCarousel'],[
                'needs_environment' => true,
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('getDateFilter', [$this, 'getDateFilter']),
            new \Twig_SimpleFunction('getDaysWithEvents', [$this, 'getDaysWithEvents']),
            new \Twig_SimpleFunction('checkValidEvent', [$this, 'checkValidEvent']),
        ];
    }

    /**
     * creating a alias for upcoming Events in front, just render event calendar view
     *
     * @param \Twig_Environment $twig_Environment
     * @param int $limit Limit of success
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function upcomingEvents(\Twig_Environment $twig_Environment, $limit = 5)
    {
        if (!$this->container->get('modules')->isModuleAvailable('event')) {
            return '';
        }

        $this->container->get('javascripthandler')
            ->addJSExternalFile('assets/js/lib/flickity.pkgd.min.js')
            ->addJSExternalFile('assets/js/modules/event/upcoming/event.upcoming.js')
            ->addJSExternalFile('assets/js/modules/event/upcoming/upcoming.auto.js');

        return $twig_Environment->render('::blocks/event-upcoming-widget.html.twig', [
            'limit'      => $limit,
            'dateFilter' => $this->container->get('filter.date')
        ]);
    }

    /**
     * @return \ArcaSolutions\SearchBundle\Entity\Filters\DateFilter|object
     */
    public function getDateFilter()
    {
        return $this->container->get('filter.date');
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param int $days_limit Total of days in carousel
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function upcomingEventsCarousel(\Twig_Environment $twig_Environment, $days_limit = 10)
    {
        if (!$this->container->get('modules')->isModuleAvailable('event')) {
            return '';
        }

        $today = new \DateTime('now');


        $days = $this->container->get('event.api.service')->getDaysWithEventsInAnYear($today->format('Y'), $days_limit);


        /* if none event were found, returns nothing. This usually happens in the beginning */
        if (0 == count($days)) {
            return '';
        }

        /* adds js */
        $this->container->get('javascripthandler')
            ->addJSExternalFile('assets/js/lib/flickity.pkgd.min.js')
            ->addJSExternalFile('assets/js/modules/event/upcoming/event.upcoming.js')
            ->addJSExternalFile('assets/js/modules/event/upcoming/upcoming.carousel.js');

        return $twig_Environment->render('::blocks/event-upcoming-widget-carousel.html.twig', [
            'days' => $days
        ]);
    }

    /**
     * @param int $days_limit
     * @return array
     * @throws \Exception
     */
    public function getDaysWithEvents($days_limit = 10)
    {
        $today = new \DateTime('now');

        return $this->container->get('event.api.service')->getDaysWithEventsInAnYear($today->format('Y'), $days_limit);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkValidEvent()
    {
        return $this->container->get('doctrine')->getRepository('EventBundle:Event')->existValidEvent();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'event_upcoming';
    }
}
