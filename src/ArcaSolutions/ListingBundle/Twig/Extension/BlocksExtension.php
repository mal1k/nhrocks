<?php

namespace ArcaSolutions\ListingBundle\Twig\Extension;

use ArcaSolutions\CoreBundle\Inflector;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Category;
use ArcaSolutions\WebBundle\Entity\Review;
use function is_array;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BlocksExtension
 *
 * @package ArcaSolutions\ListingBundle\Twig\Extension
 */
class BlocksExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
            new \Twig_SimpleFunction('reviewListing', [$this, 'reviewListing'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('statusTimeText', [$this, 'statusTimeText'], []),
        ];
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param int $quantity
     * @param string $class
     * @param string $grid
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function reviewListing(\Twig_Environment $twig_Environment, $quantity = 4)
    {
        if ($this->container->get('settings')->getDomainSetting('review_listing_enabled') !== 'on') {
            return false;
        }

        $info = [];

        $doctrine = $this->container->get('doctrine');

        $items = $doctrine->getRepository('WebBundle:Review')->findBy([
            'itemType' => 'listing',
            'approved' => 1,
        ], ['added' => 'DESC'], $quantity);

        /* @var $review Review */
        foreach ($items as $review) {
            $listing = $doctrine->getRepository('ListingBundle:Listing')->findOneBy(
                [
                    'id'     => $review->getItemId(),
                    'status' => 'A'
                ]);

            !empty($listing) and $info[] = [
                'review' => $review,
                'module' => $listing,
            ];
        }

        return $info ? $twig_Environment->render('::blocks/recent-reviews.html.twig', ['reviews' => $info]) : '';
    }

    /**
     * @param array $hoursWork
     * @return array
     * @throws \Exception
     */
    public function statusTimeText($hoursWork = [])
    {
        $text = $this->container->get('translator')->trans('Closed today');
        $class = 'closed-today';
        $displayDate = null;
        $now = date('H:i');
        foreach ($hoursWork as $dayWeek => $hourWork) {
            if (!empty($hourWork) && $dayWeek == date('w')) {
                $displayDate = null;

                if (is_array($hourWork)) {
                    foreach ($hourWork as $hour) {
                        if ($hour['hours_start'] >= $now || ($hour['hours_end'] >= $now)) {
                            $displayDate = $hour;
                            break;
                        }
                    }
                    if(empty($displayDate)) {
                        $displayDate = end($hourWork);
                    }
                }
            }
        }

        if (!empty($displayDate)) {
            $dateHoursEnd = new \DateTime($displayDate['hours_end']);
            $dateTimeNow = new \DateTime();
            $dateInterval = $dateTimeNow->diff($dateHoursEnd);

            $hours = (int)$dateInterval->format('%h');
            $minutes = (int)$dateInterval->format('%i');
            $totalMinutes = $hours * 60 + $minutes;

            if(($displayDate['hours_end'] === '00:00' || $displayDate['hours_end'] >= $now) && ($displayDate['hours_start'] <= $now || $now === '00:00')) {
                if($totalMinutes <= 60) {
                    $text = $this->container->get('translator')->trans('Closing soon');
                    $class = 'closing-soon';
                } else {
                    $text = $this->container->get('translator')->trans('Open Now');
                    $class = 'open-now';
                }
            } elseif ($displayDate['hours_start'] > $now) {
                $timeFormat = $this->container->get('languagehandler')->getTimeFormat();
                $hoursStart = new \DateTime($displayDate['hours_start']);
                $text = $this->container->get('translator')->trans('Closed. Opens at') . ' <span>' . $hoursStart->format($timeFormat) . '</span>';
                $class = 'open-at';
            } else {
                $text = $this->container->get('translator')->trans('Closed Now');
                $class = 'closed-now';
            }
        }

        return ['text' => $text, 'class' => $class];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'blocks_listing';
    }
}
