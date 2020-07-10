<?php

namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\ArticleBundle\Entity\Articlelevel;
use ArcaSolutions\ArticleBundle\Entity\Internal\ArticleLevelFeatures;
use ArcaSolutions\BannersBundle\Entity\Bannerlevel;
use ArcaSolutions\BannersBundle\Entity\Internal\BannerLevelFeatures;
use ArcaSolutions\ClassifiedBundle\Entity\ClassifiedLevel;
use ArcaSolutions\ClassifiedBundle\Entity\Internal\ClassifiedLevelFeatures;
use ArcaSolutions\CoreBundle\Exception\LevelInvalidException;
use ArcaSolutions\CoreBundle\Helper\ModuleHelper;
use ArcaSolutions\CoreBundle\Services\CurrencyHandler;
use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\EventBundle\Entity\EventLevel;
use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\ListingBundle\Entity\ListingLevel;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

class AdvertiseHandler
{
    const NAMESPACE_LEVEL = "ArcaSolutions\\%sBundle\\Entity\\Internal\\%s";
    const REPOSITORY_LEVEL = "%sBundle:%sLevel";

    /**
     * @var array
     */
    private $reorder = [
        'Listing',
        'Event',
        'Classified',
    ];

    /**
     * @var array
     */
    private $nonFeatures = [
        'trial',
        'name',
        'level',
        'price',
        'price_yearly',
        'categoryPrice',
        'freeCategoryCount',
        'isActive',
        'isFeatured',
        'isPopular',
        'isDefault',
    ];

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var ModuleHelper
     */
    private $moduleHelper;

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var CurrencyHandler
     */
    private $currencyHandler;

    /**
     * AdvertiseHandler constructor.
     *
     * @param RegistryInterface $doctrine
     * @param CurrencyHandler $currencyHandler
     * @param TranslatorInterface $translator
     * @param ModuleHelper $moduleHelper
     * @param Settings $settings
     */
    public function __construct(
        RegistryInterface $doctrine,
        CurrencyHandler $currencyHandler,
        TranslatorInterface $translator,
        ModuleHelper $moduleHelper,
        Settings $settings
    ) {
        $this->doctrine = $doctrine;
        $this->currencyHandler = $currencyHandler;
        $this->translator = $translator;
        $this->moduleHelper = $moduleHelper;
        $this->settings = $settings;
    }

    /**
     * @param $module
     * @return ArticleLevelFeatures[]|BannerLevelFeatures[]|ClassifiedLevelFeatures[]|EventLevelFeatures[]|ListingLevelFeatures[]
     * @throws LevelInvalidException
     */
    public function getLevels($module)
    {
        $module = ucfirst($module);
        $levelFeatures = sprintf(self::NAMESPACE_LEVEL, $module.($module == 'Banner' ? 's' : ''),
            $module.'LevelFeatures');

        if (!class_exists($levelFeatures)) {
            throw new LevelInvalidException();
        }

        if ($module == 'Article' || $module == 'Listing') {
            $settings = [
                'review'      => $this->settings->getDomainSetting(sprintf('review_%s_enabled',
                    mb_strtolower($module))),
                'classified'  => $this->settings->getDomainSetting('custom_classified_feature'),
                'deal'        => $this->settings->getDomainSetting('custom_promotion_feature'),
            ];
            $levelFeatures = call_user_func([$levelFeatures, 'getAllLevelsAndNormalize'], $this->doctrine, $settings);
        } else {
            $levelFeatures = call_user_func([$levelFeatures, 'getAllLevelsAndNormalize'], $this->doctrine);
        }

        $this->removeFeaturesDisabledInAllLevels($levelFeatures);

        return $levelFeatures;
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param $features
     */
    private function removeFeaturesDisabledInAllLevels($features)
    {
        $propertiesToKeep = [];

        foreach ($features as $feature) {
            foreach ($feature as $property => $value) {
                if (is_numeric($value)) {
                    if ($value > 0) {
                        $propertiesToKeep[$property] = true;
                    }

                    continue;
                }

                if ($value !== false) {
                    $propertiesToKeep[$property] = true;
                }
            }
        }

        foreach ($features as $feature) {
            foreach ($feature as $property => $value) {
                if (in_array($property, $this->getNonFeatures(), true)) {
                    continue;
                }

                if (!array_key_exists($property, $propertiesToKeep)) {
                    $feature->{$property} = null;
                }
            }
        }

    }

    public function getNonFeatures()
    {
        return $this->nonFeatures;
    }

    public function getPopularLevel($module)
    {
        /* @var $repository ListingLevel|Articlelevel|ClassifiedLevel|Bannerlevel|EventLevel */
        $repository = $this->moduleHelper->getModuleLevelRepositoryName($module);

        return $this->doctrine->getRepository($repository)->findOneBy(['active' => 'y', 'popular' => 'y']);
    }

    /**
     * @param ListingLevelFeatures|EventLevelFeatures|ClassifiedLevelFeatures|ArticleLevelFeatures|BannerLevelFeatures $plan
     * @param bool $sufix
     * @param null $content
     * @return array
     */
    public function getAdvertisePrice($plan, $sufix = false, $content = null)
    {
        $pricing = [];

        /* Monthly Price */
        $pricingMonthly = $plan->price;
        $pricing['monthly'] = $this->currencyHandler->formatCurrency($pricingMonthly, false,
            CurrencyHandler::RETURN_CURRENCY_ARRAY);

        /* Yearly Price */
        $pricingYearly = $plan->price_yearly;
        $pricing['yearly'] = $this->currencyHandler->formatCurrency($pricingYearly, false,
            CurrencyHandler::RETURN_CURRENCY_ARRAY);

        /* Main Price */
        $pricing['main'] = ['value' => $this->translator->trans('Free')];
        $pricing['main_renewal'] = '';
        $pricing['renewal'] = false;
        if ($pricingMonthly > 0 || $pricingYearly > 0) {
            $pricing['main'] = $pricingMonthly > 0 ? $pricing['monthly'] : $pricing['yearly'];
            $pricing['main_renewal'] = $pricingMonthly > 0 ?
                ($sufix ? $this->translator->trans('Monthly') : $this->translator->trans('Month')) :
                ($sufix ? $this->translator->trans('Yearly') : $this->translator->trans('Year'));
            $pricing['renewal'] = ($pricingMonthly > 0 && $pricingYearly > 0);
            $pricing['renewal_label'] = $sufix ? $this->translator->trans('Yearly') : $this->translator->trans('Year');
        }

        $pricing['description'] = !empty($content->{'level'.$plan->level}) ? $content->{'level'.$plan->level} : '';

        return $pricing;
    }
}
