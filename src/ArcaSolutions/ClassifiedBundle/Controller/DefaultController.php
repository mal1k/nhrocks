<?php

namespace ArcaSolutions\ClassifiedBundle\Controller;

use ArcaSolutions\ClassifiedBundle\ClassifiedItemDetail;
use ArcaSolutions\ClassifiedBundle\Entity\Classified;
use ArcaSolutions\ClassifiedBundle\Entity\Classifiedcategory;
use ArcaSolutions\ClassifiedBundle\Sample\ClassifiedSample;
use ArcaSolutions\CoreBundle\Exception\ItemNotFoundException;
use ArcaSolutions\CoreBundle\Exception\UnavailableItemException;
use ArcaSolutions\CoreBundle\Services\ValidationDetail;
use ArcaSolutions\ListingBundle\ListingItemDetail;
use ArcaSolutions\ReportsBundle\Services\ReportHandler;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Category;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use ArcaSolutions\WebBundle\Form\Type\SendMailType;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlay\Marker;
use Ivory\GoogleMap\Overlay\Icon;
use Ivory\GoogleMap\Base\Coordinate;

class DefaultController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->get('widget.service')->setModule(ParameterHandler::MODULE_CLASSIFIED);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::CLASSIFIED_HOME_PAGE);

        return $this->render('::base.html.twig', [
            'title' => 'Classified',
            'pageId' => $page->getId(),
            'pageTitle' => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords' => $page->getMetaKey(),
            'customTag' => $page->getCustomTag()
        ]);
    }

    /**
     * @param $friendlyUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws UnavailableItemException
     * @throws \Exception
     * @throws \Ivory\GoogleMap\Exception\MapException
     * @throws \Ivory\GoogleMap\Exception\OverlayException
     */
    public function detailAction($friendlyUrl)
    {
        /*
         * Validation
         */
        /* @var $item Classified For phpstorm get properties of entity Listing */
        $item = $this->get('search.engine')->itemFriendlyURL($friendlyUrl, 'classified', 'ClassifiedBundle:Classified');
        /* event not found by friendlyURL */
        if (is_null($item)) {
            throw new ItemNotFoundException();
        }

        $listingItemDetail = null;
        if ($item->getListingId()) {
            $listingItemDetail = new ListingItemDetail($this->container, $item->getListing());
            $reviewTotal = $this->get('doctrine')->getRepository('WebBundle:Review')->getReviewsPaginated($item->getListingId(), 1);
        }

        /* normalizes item to validate detail */
        $classifiedItemDetail = new ClassifiedItemDetail($this->container, $item);

        /* validating if listing is enabled, if listing's level is active and if level allows detail */
        if (!ValidationDetail::isDetailAllowed($classifiedItemDetail)) {
            $parameterHandler = new ParameterHandler($this->container, false);
            $parameterHandler->addModule(ParameterHandler::MODULE_CLASSIFIED);
            $parameterHandler->addKeyword($friendlyUrl);

            $this->get('request_stack')->getCurrentRequest()->cookies->set('edirectory_results_viewmode', 'item');

            return $this->redirect($parameterHandler->buildUrl());
        }

        /* ModStores Hooks */
        HookFire("classified_after_validate_itemdetail", [
            "item" => &$item,
            "that" => &$this,
        ]);

        /*
         * Report
         */
        if (false === ValidationDetail::isSponsorsOrSitemgr($classifiedItemDetail)) {
            /* Counts the view towards the statistics */
            $this->container->get('reporthandler')->addClassifiedReport($item->getId(), ReportHandler::CLASSIFIED_DETAIL);
        }

        /*
         * Workaround to get item's locations
         * We did in this way for reuse the 'Utility.address'(summary) macro in view
         */
        $locations = $this->get('location.service')->getLocations($item);
        $locations_ids = [];
        $locations_rows = [];
        foreach (array_filter($locations) as $levelLocation => $location) {
            $key = substr($levelLocation, 0, 2).':'.$location->getId();
            $locations_ids[] = $key;
            $locations_rows[$key] = $location;
        }

        /* gets item's gallery */
        $gallery = null;
        if ($classifiedItemDetail->getLevel()->imageCount > 0) {
            $gallery = $this->get('doctrine')->getRepository('ClassifiedBundle:Classified')
                ->getGallery($item, $classifiedItemDetail->getLevel()->imageCount);
        }

        $map = null;
        /* checks if item has latitude and longitude to show the map */
        if ($item->getLatitude() && $item->getLongitude() && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')) {
            /* sets map */
            $map = new Map();
            $map->setMapOption("scrollwheel", false);
            $map->setStylesheetOptions([
                'width'  => '100%',
                'height' => '255px',
            ]);
            $domain = $this->get('multi_domain.information')->getId();
            $theme = lcfirst($this->get('theme.service')->getSelectedTheme()->getTitle());
            $defaultIconPath = '/assets/' . $theme . '/icons/listing.svg';
            $customIconPath = 'custom/domain_' . $domain . '/theme/' . $theme . '/icons/listing.svg';

            $mapZoom = ($item->getMapZoom() ? $item->getMapZoom() : 15);
            $map->setMapOption('zoom', $mapZoom);

            /* sets the item's location the center of the map */
            $map->setCenter(new Coordinate((float) $item->getLatitude(), (float) $item->getLongitude()));

            $marker = new Marker(new Coordinate((float) $item->getLatitude(), (float) $item->getLongitude(), true));

            /* mark item in map */
            $marker->setOptions([
                'clickable' => false,
                'flat'      => true,
            ]);

            if (file_exists($customIconPath)) {
                $iconPath = '/' . $customIconPath;
            } else {
                $iconPath = $defaultIconPath;
            }

            $marker->setIcon(new Icon($this->container->get('request')->getSchemeAndHttpHost() . '/' . $iconPath));

            $map->getOverlayManager()->addMarker($marker);

            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
        }

        $categoryIds = array_map(function ($item) {
            /* @var $item ClassifiedCategory */
            return Category::create()
                ->setId($item->getId())
                ->setModule(ParameterHandler::MODULE_CLASSIFIED);
        }, $item->getCategories());

        $listing[] = $this->get('doctrine')->getRepository('ListingBundle:Listing')->getClassifiedAssociatedListing($item->getListingId());

        $this->get('widget.service')->setModule(ParameterHandler::MODULE_CLASSIFIED);

        $userId = $this->container->get('request')->getSession()->get('SESS_ACCOUNT_ID');
        $memberAccount = null;

        if($userId) {
            $memberAccount = $this->container->get('doctrine')->getRepository('WebBundle:Accountprofilecontact')->find($userId);
        }

        $formSendMail = $this->createForm(new SendMailType(), null, ['member' => $memberAccount]);

        if (!$userId) {

            if ($this->container->get('settings')->getDomainSetting('google_recaptcha_status') === 'on') {
                $options = [];
            } else {
                $options = [
                    'reload' => true,
                    'as_url' => true,
                ];
            }

            $formSendMail->add('sendEmailCaptcha', 'edirectory_captcha', $options);
        }

        $twig = $this->container->get('twig');

        /* ModStores Hooks */
        HookFire("classified_before_add_globalvars", [
            "item" => &$item,
            "that" => &$this,
        ]);

        $twig->addGlobal('bannerCategories', $categoryIds);
        $twig->addGlobal('item', $item);
        $twig->addGlobal('level', $classifiedItemDetail->getLevel());
        $twig->addGlobal('categories', $item->getCategories());
        $twig->addGlobal('gallery', $gallery);
        $twig->addGlobal('map', $map);
        $twig->addGlobal('locationsIDs', $locations_ids);
        $twig->addGlobal('locationsObjs', $locations_rows);
        $twig->addGlobal('country', $locations['country']);
        $twig->addGlobal('region', $locations['region']);
        $twig->addGlobal('state', $locations['state']);
        $twig->addGlobal('city', $locations['city']);
        $twig->addGlobal('neighborhood', $locations['neighborhood']);
        $twig->addGlobal('listing', $listing);
        $listingItemDetail and $twig->addGlobal('listingLevel', $listingItemDetail->getLevel());
        !empty($reviewTotal) and $twig->addGlobal('listingReviewsTotal', $reviewTotal['total']);
        $twig->addGlobal('formSendMail', $formSendMail->createView());

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::CLASSIFIED_DETAIL_PAGE);

        /* ModStores Hooks */
        HookFire("classified_before_render", [
            "page" => &$page,
            "that" => &$this,
        ]);

        return $this->render('::modules/classified/detail.html.twig', [
            'pageId' => $page->getId(),
            'customTag' => $page->getCustomTag()
        ]);
    }

    public function sampleDetailAction($level = 0)
    {
        $item = new ClassifiedSample($level, $this->get('translator'), $this->get('doctrine'));

        /* normalizes item to validate detail */
        $classifiedItemDetail = new ClassifiedItemDetail($this->container, $item);

        $map = null;
        /* checks if item has latitude and longitude to show the map */
        if ($item->getLatitude() && $item->getLongitude() && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
            and $googleMapsKey = $this->container->get('settings')->getDomainSetting('google_api_key')) {
            /* sets map */
            $map = new Map();
            $map->setMapOption("scrollwheel", false);
            $map->setStylesheetOptions([
                'width'  => '100%',
                'height' => '255px',
            ]);
            $domain = $this->get('multi_domain.information')->getId();
            $theme = lcfirst($this->get('theme.service')->getSelectedTheme()->getTitle());
            $defaultIconPath = '/assets/' . $theme . '/icons/listing.svg';
            $customIconPath = 'custom/domain_' . $domain . '/theme/' . $theme . '/icons/listing.svg';

            $mapZoom = ($item->getMapZoom() ? $item->getMapZoom() : 15);
            $map->setMapOption('zoom', $mapZoom);

            /* sets the item's location the center of the map */
            $map->setCenter(new Coordinate((float) $item->getLatitude(), (float) $item->getLongitude()));

            $marker = new Marker(new Coordinate((float) $item->getLatitude(), (float) $item->getLongitude(), true));

            /* mark item in map */
            $marker->setOptions([
                'clickable' => false,
                'flat'      => true,
            ]);

            if (file_exists($customIconPath)) {
                $iconPath = '/' . $customIconPath;
            } else {
                $iconPath = $defaultIconPath;
            }

            $marker->setIcon(new Icon($this->container->get('request')->getSchemeAndHttpHost() . '/' . $iconPath));

            $map->getOverlayManager()->addMarker($marker);

            $mapJSHelper = MapHelperBuilder::create()->build()->renderJavascript($map);
            $apiHelper = ApiHelperBuilder::create()->setKey($googleMapsKey)->build()->render([$map]);

            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSBlock('::js/summary/map.html.twig');
            $jsHandler->addTwigParameter('mapJSHelper', $mapJSHelper);
            $jsHandler->addTwigParameter('apiHelper', $apiHelper);
        }

        $twig = $this->container->get('twig');

        /* ModStores Hooks */
        HookFire("classifiedsample_before_add_globalvars", [
            "item" => &$item,
            "that" => &$this,
        ]);

        $twig->addGlobal('item', $item);
        $twig->addGlobal('level', $classifiedItemDetail->getLevel());
        $twig->addGlobal('map', $map);
        $twig->addGlobal('gallery', $item->getGallery(--$classifiedItemDetail->getLevel()->imageCount));
        $twig->addGlobal('categories', $item->getCategories());
        $twig->addGlobal('locationsIDs', $item->getFakeLocationsIds());
        $twig->addGlobal('locationsObjs', $item->getLocationObjects());
        $twig->addGlobal('isSample', true);

        $this->get('widget.service')->setModule(ParameterHandler::MODULE_CLASSIFIED);
        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::CLASSIFIED_DETAIL_PAGE);

        /* ModStores Hooks */
        HookFire("classifiedsample_before_render", [
            "page" => &$page,
            "that" => &$this,
        ]);

        return $this->render('::modules/classified/detail.html.twig', [
            'pageId' => $page->getId(),
            'customTag' => $page->getCustomTag()
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allcategoriesAction()
    {
        /* Loading and setting wysiwyg */
        $this->get('widget.service')->setModule(ParameterHandler::MODULE_CLASSIFIED);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::CLASSIFIED_CATEGORIES_PAGE);

        $categories = $this->get('search.repository.category')
            ->findCategoriesWithItens(ParameterHandler::MODULE_CLASSIFIED);

        $twig = $this->get('twig');

        $twig->addGlobal('categories', $categories);
        $twig->addGlobal('routing', ParameterHandler::MODULE_CLASSIFIED);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function alllocationsAction()
    {
        $locations_enable = $this->get('doctrine')->getRepository('WebBundle:SettingLocation')->getLocationsEnabledID();
        $locations = $this->get('helper.location')->getAllLocations($locations_enable, ParameterHandler::MODULE_CLASSIFIED);

        $this->get('widget.service')->setModule(ParameterHandler::MODULE_CLASSIFIED);

        $twig = $this->container->get('twig');

        $twig->addGlobal('locations', $locations);
        $twig->addGlobal('routing', ParameterHandler::MODULE_CLASSIFIED);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::CLASSIFIED_ALL_LOCATIONS);

        return $this->render('::base.html.twig',[
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * Returns locations on ajax call
     *
     * @param Request $request
     * @return Response JsonResponse
     */
    public function locationsAction(Request $request)
    {
        return $this->container->get('location.service')->getChildrenLocations($request);
    }
}
