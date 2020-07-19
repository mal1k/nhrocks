<?php

namespace ArcaSolutions\DealBundle\Controller;

use ArcaSolutions\CoreBundle\Entity\Account;
use ArcaSolutions\CoreBundle\Entity\Contact;
use ArcaSolutions\CoreBundle\Exception\ItemNotFoundException;
use ArcaSolutions\CoreBundle\Exception\UnavailableItemException;
use ArcaSolutions\CoreBundle\Form\Type\CaptchaType;
use ArcaSolutions\CoreBundle\Services\ValidationDetail;
use ArcaSolutions\DealBundle\DealItemDetail;
use ArcaSolutions\DealBundle\Entity\Promotion;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use ArcaSolutions\ListingBundle\ListingItemDetail;
use ArcaSolutions\ReportsBundle\Services\ReportHandler;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Category;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use ArcaSolutions\WebBundle\Form\Type\SendMailType;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Ivory\GoogleMap\Helper\Builder\ApiHelperBuilder;
use Ivory\GoogleMap\Helper\Builder\MapHelperBuilder;
use Ivory\GoogleMap\Overlay\Icon;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlay\Marker;
use Ivory\GoogleMap\Base\Coordinate;

final class DefaultController extends Controller
{
    /**
     * Homepage
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->get('widget.service')->setModule(ParameterHandler::MODULE_DEAL);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::DEAL_HOME_PAGE);

        return $this->render('::base.html.twig', [
            'title'           => 'Deal Index',
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * Detail page
     *
     * @param $friendlyUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws ItemNotFoundException
     * @throws \Exception
     * @throws \Ivory\GoogleMap\Exception\MapException
     * @throws \Ivory\GoogleMap\Exception\OverlayException
     */
    public function detailAction($friendlyUrl)
    {
        /*
         * Validation
         */
        /* @var $item Promotion For phpstorm get properties of entity Listing */
        $item = $this->get('search.engine')->itemFriendlyURL($friendlyUrl, 'deal', 'DealBundle:Promotion');
		$account = $this->container->get('user')->getAccount();
		$isUser = false;
		$canRedeem = false;
		if($account instanceof Account) {
			$isActive = $account->getActive() === 'y';
			$isSponsor = $account->getIsSponsor() === 'y';
			$isUser = !$isSponsor;
			$canRedeem = $isActive and !$isSponsor;
		}
        /* event not found by friendlyURL */
        if (is_null($item)) {
            throw new ItemNotFoundException();
        }

        /* normalizes item to validate detail */
        $dealItemDetail = new DealItemDetail($this->container, $item);

        $listingItemDetail = null;
        if ($item->getListingId()) {
            $listingItemDetail = new ListingItemDetail($this->container, $item->getListing());
            $reviewTotal = $this->get('doctrine')->getRepository('WebBundle:Review')->getReviewsPaginated($item->getListingId(), 1);
        }

        /*
         * It validates if the listing is enabled, if sponsor is accessing this page or if the sitemgr is accessing
         * It is used an OR conditional because it has rules for sponsor and sitemgr that have to overwrite all the others
         */
        if (!($listingItemDetail && ValidationDetail::isDetailAllowed($dealItemDetail) && 'A' === $listingItemDetail->getItem()->getStatus())) {
            /* error page */
            throw new UnavailableItemException();
        }

        /* ModStores Hooks */
        HookFire("deal_after_validate_itemdetail", [
            "item" => &$item,
            "that" => &$this,
        ]);

        /*
         * Report
         */
        if (false === ValidationDetail::isSponsorsOrSitemgr($dealItemDetail)) {
            /* Counts the view towards the statistics */
            $this->container->get("reporthandler")->addDealReport($item->getId(), ReportHandler::DEAL_DETAIL);
        }

        /*
         * Workaround to get item's locations
         * We did in this way for reuse the 'Utility.address'(summary) macro in view
         */
        $locations = array_filter($this->get('location.service')->getLocations($item->getListing()));
        $locations_ids = [];
        $locations_rows = [];
        foreach (array_filter($locations) as $levelLocation => $location) {
            $key = substr($levelLocation, 0, 2).':'.$location->getId();
            $locations_ids[] = $key;
            $locations_rows[$key] = $location;
        }

        $map = null;
        /* checks if item has latitude and longitude to show the map */
        if ($item->getListing()->getLatitude() && $item->getListing()->getLongitude() && $this->container->get('settings')->getDomainSetting('google_map_status') == 'on'
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

            $mapZoom = ($item->getListing()->getMapZoom() ? $item->getListing()->getMapZoom() : 15);
            $map->setMapOption('zoom', $mapZoom);

            /* sets the item's location the center of the map */
            $map->setCenter(new Coordinate((float) $item->getListing()->getLatitude(), (float) $item->getListing()->getLongitude()));

            $marker = new Marker(new Coordinate((float) $item->getListing()->getLatitude(), (float) $item->getListing()->getLongitude(), true));

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

        /* adds view phone script(listing) */
        $this->get('javascripthandler')->addJSBlock('::modules/listing/js/summary.js.twig');

        /* calculating percentage */
        $percentage = 0;
        if ($item->getRealvalue() != 0) {
            $percentage = sprintf('%d', 100 - $item->getDealvalue() * 100 / $item->getRealvalue());
        }

        if ($item->getListingId() and $listing = $item->getListing()) {
            $categoryIds = array_map(function ($item) {
                /* @var $item ListingCategory */
                return Category::create()
                    ->setId($item->getId())
                    ->setModule(ParameterHandler::MODULE_DEAL);
            }, $listing->getCategories()->toArray());
        } else {
            $categoryIds = [];
        }

        $this->get('widget.service')->setModule(ParameterHandler::MODULE_DEAL);

        /* gets item's gallery */
        $gallery = null;
        if ($listingItemDetail->getLevel()->imageCount > 0) {
            $gallery = $this->get('doctrine')->getRepository('ListingBundle:Listing')
                ->getGallery($item->getListing(), $listingItemDetail->getLevel()->imageCount);
        }

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

            $formSendMail->add('sendEmailCaptcha', CaptchaType::class, $options);
        }

        $item->getListing()->setCoverImage($item->getCoverImage());

        $twig = $this->container->get("twig");

        /* ModStores Hooks */
        HookFire("deal_before_add_globalvars", [
            "item" => &$item,
            "that" => &$this,
        ]);

        $twig->addGlobal('bannerCategories', $categoryIds);
        $twig->addGlobal('item', $item);
        $twig->addGlobal('map', $map);
        $twig->addGlobal('gallery', $gallery);
        $twig->addGlobal('percentage', $percentage);
        $listingItemDetail and $twig->addGlobal('listingLevel', $listingItemDetail->getLevel());
        !empty($reviewTotal) and $twig->addGlobal('listingReviewsTotal', $reviewTotal['total']);
        $twig->addGlobal('locationsIDs', $locations_ids);
        $twig->addGlobal('locationsObjs', $locations_rows);
        $twig->addGlobal('isUser', $isUser);
        $twig->addGlobal('canRedeem', $canRedeem);
        $formSendMail and $twig->addGlobal('formSendMail', $formSendMail->createView());

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::DEAL_DETAIL_PAGE);

        /* ModStores Hooks */
        HookFire("deal_before_render", [
            "page" => &$page,
            "that" => &$this,
        ]);

        return $this->render('::modules/deal/detail.html.twig', [
            'pageId'    => $page->getId(),
            'customTag' => $page->getCustomTag(),
        ]);
    }

    /**
     * All categories page
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function allcategoriesAction()
    {
        /* Loading and setting wysiwyg */
        $this->get('widget.service')->setModule(ParameterHandler::MODULE_DEAL);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::DEAL_CATEGORIES_PAGE);

        $categories = $this->get('search.repository.category')
            ->findCategoriesWithItens(ParameterHandler::MODULE_DEAL);

        $twig = $this->get('twig');

        $twig->addGlobal('categories', $categories);
        $twig->addGlobal('routing', ParameterHandler::MODULE_DEAL);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * Make a redeem of a deal. Send notification and get the code.
     * If it was already redeemed, just get the code and show it again.
     * If it was not, generate a new code, save it and show.
     *
     * @param Request $request
     * @param         $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function redeemAction(Request $request)
    {
        /* gets user Id using profile credentials */
        $userId = $request->getSession()->get('SESS_ACCOUNT_ID');

        if ($userId === null) {
            return new JsonResponse([
                'status'  => 'login',
            ]);
        }

        if($id = $request->query->get('id')) {
            $deal = $this->container->get('doctrine')->getRepository('DealBundle:Promotion')->find($id);

            $dealInfo['endDate'] = $deal->getEndDate()->format($this->container->get('translator')->trans('date.format', [], 'units'));

            $dealInfo['dealName'] = $deal->getName();

            $dealInfo['dealValue'] = $this->container->getParameter('payment.payment_currency_symbol') . $deal->getDealValue();

            $dealInfo['realValue'] = $this->container->getParameter('payment.payment_currency_symbol') . $deal->getRealValue();

            $dealInfo['listingTitle'] = $deal->getListing()->getTitle();

            $dealInfo['download'] = $deal->getFriendlyUrl() . '.png';

            if ($deal->getCoverImage()) {
                if(!empty($deal->getCoverImage()->getUnsplash())) {
                    $dealInfo['coverImage'] = $deal->getCoverImage()->getUnsplash();
                } else {
                    $dealInfo['coverImage'] = $this->container->get('templating.helper.assets')
                        ->getUrl($this->container->get('imagehandler')->getPath($deal->getCoverImage()), 'domain_images');
                }
            }

            if (!empty($deal->getListing()->getLogoImage())) {
                $imagine_filter = $this->container->get('liip_imagine.cache.manager');

                $imagePath = $this->container->get('templating.helper.assets')
                    ->getUrl($this->container->get('imagehandler')->getPath($deal->getListing()->getLogoImage()), 'domain_images');

                $dealInfo['logoImage'] = $imagine_filter->getBrowserPath($imagePath, 'logo_icon_3');
            }

            $userAccountInfo = [];

            $redeemInfo = [];

            /* @var Contact $userAccount */
            $userAccount = $this->container->get('doctrine')->getManager('main')->getRepository(
                'CoreBundle:Contact')
                ->findOneBy(['account' => $userId]);

            if($userAccount !== null) {
                $redeem = $this->container->get('redeem.handler')->makeRedeem($deal, $userAccount);
                $redeemInfo['redeemCode'] = $redeem->getRedeemCode();
                $userAccountInfo['name'] = $userAccount->getFirstName() . ' ' . $userAccount->getLastName();
            }

            return new JsonResponse([
                'item' => $dealInfo,
                'redeem' => $redeemInfo,
                'user' => $userAccountInfo
            ]);
        }

        return new JsonResponse([
            'status'  => 'redeem'
        ]);
    }


    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function alllocationsAction()
    {
        $locations_enable = $this->get('doctrine')->getRepository('WebBundle:SettingLocation')->getLocationsEnabledID();
        $locations = $this->get('helper.location')->getAllLocations($locations_enable, ParameterHandler::MODULE_DEAL);

        $this->get('widget.service')->setModule(ParameterHandler::MODULE_DEAL);

        $twig = $this->container->get("twig");

        $twig->addGlobal('locations', $locations);
        $twig->addGlobal('routing', ParameterHandler::MODULE_DEAL);

        $page = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::DEAL_ALL_LOCATIONS);

        return $this->render('::base.html.twig', [
            'pageId'          => $page->getId(),
            'pageTitle'       => $page->getTitle(),
            'metaDescription' => $page->getMetaDescription(),
            'metaKeywords'    => $page->getMetaKey(),
            'customTag'       => $page->getCustomTag(),
        ]);
    }

    /**
     * Returns children locations on ajax call
     *
     * @return Response JsonResponse
     */
    public function locationsAction(Request $request)
    {
        return $this->container->get('location.service')->getChildrenLocations($request);
    }
}
