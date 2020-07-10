<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use ArcaSolutions\ArticleBundle\Entity\Internal\ArticleLevelFeatures;
use ArcaSolutions\BannersBundle\Entity\Internal\BannerLevelFeatures;
use ArcaSolutions\ClassifiedBundle\Entity\Internal\ClassifiedLevelFeatures;
use ArcaSolutions\CoreBundle\Services\CurrencyHandler;
use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\WebBundle\Services\AdvertiseHandler;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

final class AdvertiseExtension extends \Twig_Extension
{
    /**
     * @var AdvertiseHandler
     */
    private $advertiseHandler;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param AdvertiseHandler $advertiseHandler
     * @param CurrencyHandler $currencyHandler
     * @param Translator|TranslatorInterface $translator
     */
    public function __construct(
        AdvertiseHandler $advertiseHandler,
        TranslatorInterface $translator
    ) {
        $this->advertiseHandler = $advertiseHandler;
        $this->translator = $translator;
    }

    /**
     * Returns extension function's names
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('advertisePlans', [$this, 'getAdvertisePlans'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('advertisePrice', [$this, 'getAdvertisePrice'], [
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('getPlansByModule', [$this, 'getPlansByModule'], [
                'needs_environment' => false,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('getPlanFrequency', [$this, 'getPlanFrequency'], [
                'needs_environment' => false,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('getNonFeatures', [$this, 'getNonFeatures'], [
                'needs_environment' => false,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * Returns filters function's names
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('cast_to_array', [$this, 'objectFilter']),
        ];
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param $module string Module Name
     * @param $widgetId
     * @return bool
     * @throws \ArcaSolutions\CoreBundle\Exception\LevelInvalidException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function getAdvertisePlans(\Twig_Environment $twig_Environment, $module, $widgetId)
    {
        $plans = $this->getPlansByModule($module);

        return $twig_Environment->render('::blocks/advertise/plans.html.twig', [
            'plans'       => $plans,
            'module'      => $module,
            'widget_id'   => $widgetId
        ]);
    }

    /**
     * @param ListingLevelFeatures|EventLevelFeatures|ClassifiedLevelFeatures|ArticleLevelFeatures|BannerLevelFeatures $plan
     * @param bool $sufix
     * @param $content
     * @return array
     */
    public function getAdvertisePrice($plan, $sufix = false, $content = null)
    {
        return $this->advertiseHandler->getAdvertisePrice($plan, $sufix, $content);
    }

    /**
     * Change the typecast to an array
     *
     * @param object $stdClassObject
     * @return array
     */
    public function objectFilter($stdClassObject)
    {
        return (array)$stdClassObject;
    }

    /**
     * Returns extension name
     */
    public function getName()
    {
        return 'edirectory_advertise_extension';
    }

    /**
     * @param $plans
     * @return array
     */
    public function getPlanFrequency($plans)
    {
        $countMonthlyPlan = 0;
        $countYearlyPlan = 0;
        $countTotalPlan = 0;
        foreach ($plans as $plan) {
            $plan->price > 0 and $countMonthlyPlan++;
            $plan->price_yearly > 0 and $countYearlyPlan++;
            $plan->price > 0 || $plan->price_yearly > 0 and $countTotalPlan++;
        }

        return [
            'monthly'    => $countMonthlyPlan,
            'yearly'     => $countYearlyPlan,
            'total_plan' => $countTotalPlan
        ];
    }

    /**
     * @param $module
     * @return mixed|string
     * @throws \ArcaSolutions\CoreBundle\Exception\LevelInvalidException
     */
    public function getPlansByModule($module)
    {
        $plans = $this->advertiseHandler->getLevels($module);

        return $plans;
    }

    /**
     * @return array
     */
    public function getNonFeatures()
    {
        return $this->advertiseHandler->getNonFeatures();
    }
}
