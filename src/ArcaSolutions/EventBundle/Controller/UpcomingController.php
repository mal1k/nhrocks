<?php

namespace ArcaSolutions\EventBundle\Controller;

use ArcaSolutions\CoreBundle\Twig\Extension\LocalizedDateExtension;
use ArcaSolutions\EventBundle\Entity\Eventcategory;
use ArcaSolutions\EventBundle\Services\Recurring;
use ArcaSolutions\ImageBundle\Twig\Extension\ImageExtension;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UpcomingController
 *
 * @package ArcaSolutions\EventBundle\Controller
 */
class UpcomingController extends Controller
{
    /**
     * @param null $day
     * @param null $month
     * @param null $year
     *
     * @param null $wholeMonth
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Twig_Error
     * @throws \Twig_Error_Runtime
     */
    public function upcomingAction($day = null, $month = null, $year = null, $wholeMonth = null)
    {
        if (!checkdate($month, $day, $year)) {
            throw new \Exception('You must pass a valid date');
        }

        $imagine_filter = $this->container->get('liip_imagine.cache.manager');

        $startDate = \DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-%s', $year, $month, $day));
        $startDate->setTime(00, 00, 00);

        $endDate = clone $startDate;
        $endDate->setTime(23, 59, 59);
        if ($wholeMonth) {
            $endDate->add(new \DateInterval('P30D'));
        }

        $twigEnvironment = $this->get('twig');
        /* @var $imageExtension ImageExtension */
        $imageExtension = $twigEnvironment->getExtension('image_extension');
        /* @var $localizedDate LocalizedDateExtension */
        $localizedDate = $twigEnvironment->getExtension('localized_date');

        $dateFormat = $this->container->get('filter.date')->getUrlDateFormat();

        $searchRoute = $this->container->get('router')->generate(
            'global_search_2',
            ['a0' => $this->container->getParameter('alias_event_module'), 'a1' => $startDate->format($dateFormat)]
        );

        $json = [
            'day'        => $startDate->format('j'),
            'month'      => $localizedDate->localized_date($twigEnvironment, $startDate, 'MMMM'),
            'day_name'   => ucfirst($localizedDate->localized_date($twigEnvironment, $startDate, 'EEE')),
            'events'     => [],
            'all_events' => $searchRoute
        ];

        $recurringService = $this->get('event.recurring.service');

        $events = $recurringService->getUpcomingEvents($startDate, $endDate, $wholeMonth, 10);

        if (empty($events)) {
            return new JsonResponse($json);
        }

        foreach ($events as $event) {
            $imageUrl = '';

            if ($event->getImageId() > 0) {
                $imageUrl = $this->container->get('templating.helper.assets')
                    ->getUrl($this->container->get('imagehandler')->getPath($event->getImage()), 'domain_images');

                if (file_exists($this->container->getParameter('kernel.root_dir').'/../web'.$imageUrl)) {
                    $image = $this->container->get('liip_imagine.cache.manager')
                        ->getBrowserPath($imageUrl, 'card');
                }
            }

            if(empty($image)) {
                $image = $this->container->get('liip_imagine.cache.manager')->getBrowserPath($this->container->get('utility')->getNoImagePath(), 'noimage');
            }

            $categories = [];
            foreach ($event->getCategories() as $category) {
                $categories[] = [
                    'title' => $category->getTitle(),
                    'link'  => $this->generateUrl('event_homepage').$category->getFriendlyUrl(),
                ];
            }

            $eventStartDate = $event->getStartDate();
            $eventUntilDate = $event->getUntilDate();

            if ($event->getRecurring() === 'Y') {
                $eventStartDate = $recurringService->getNextOccurrence(
                    $eventStartDate, $eventUntilDate, $recurringService->getRRule_rfc2445($event)
                );
            }

            $imageData = $this->container->get('tag.picture.service')->getImageSource($imageUrl, $event->getTitle());

            $imageData['webPSupport'] = in_array('image/webp', array_column($imageData['sources'], 'type'), true);

            $imageData['itemId'] = 'upcoming-events-' . $event->getId();

            $imageBackground = $this->container->get('templating')->render('@Web/images/background.html.twig', $imageData);

            $picture = $this->container->get('templating')->render('@Web/images/picture.html.twig', $imageData);

            $json['events'][] = [
                'id'          => $event->getId(),
                'link'        => $this->generateUrl('event_detail', [
                    'friendlyUrl' => $event->getFriendlyUrl(),
                    '_format'     => 'html',
                ]),
                'picture'         => $picture,
                'title'           => $event->getTitle(),
                'description'     => $event->getDescription(),
                'location'        => $event->getLocation(),
                'categories'      => $categories,
                'day'             => $eventStartDate->format('j'),
                'day_name'        => ucfirst($localizedDate->localized_date($twigEnvironment, $eventStartDate, 'EEE')),
                'month'           => mb_substr($localizedDate->localized_date($twigEnvironment, $eventStartDate, 'MMMM'), 0, 3, 'UTF-8'),
                'startDate'       => $eventStartDate,
                'imageBackground' => $imageBackground
            ];

            unset($image);
        }

        /* Sort Events by Occurrence date */
        if ($json['events']) {
            usort($json['events'], function ($a, $b) {
                if ($a['startDate'] == $b['startDate']) {
                    return 0;
                }

                return $a['startDate'] < $b['startDate'] ? -1 : 1;
            });
        }

        return new JsonResponse($json);
    }

}
