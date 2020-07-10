<?php

namespace ArcaSolutions\ApiBundle\Controller;

use ArcaSolutions\ApiBundle\Documents\ConfigDocument;
use ArcaSolutions\ApiBundle\Documents\GeneralDocument;
use ArcaSolutions\ApiBundle\Documents\ModuleLevelDocument;
use ArcaSolutions\ApiBundle\Entity\Result;
use ArcaSolutions\ApiBundle\Helper\CategoryHelper;
use ArcaSolutions\ArticleBundle\ArticleItemDetail;
use ArcaSolutions\ArticleBundle\Entity\Article;
use ArcaSolutions\ArticleBundle\Entity\Articlecategory;
use ArcaSolutions\BlogBundle\BlogItemDetail;
use ArcaSolutions\BlogBundle\Entity\Blogcategory;
use ArcaSolutions\BlogBundle\Entity\BlogCategory1;
use ArcaSolutions\BlogBundle\Entity\Post as Blog;
use ArcaSolutions\ClassifiedBundle\ClassifiedItemDetail;
use ArcaSolutions\ClassifiedBundle\Entity\Classified;
use ArcaSolutions\ClassifiedBundle\Entity\Classifiedcategory;
use ArcaSolutions\CoreBundle\Entity\Account;
use ArcaSolutions\CoreBundle\Entity\Contact;
use ArcaSolutions\CoreBundle\Exception\InvalidFormException;
use ArcaSolutions\CoreBundle\Exception\ItemNotFoundException;
use ArcaSolutions\CoreBundle\Exception\UnavailableItemException;
use ArcaSolutions\CoreBundle\Services\ValidationDetail;
use ArcaSolutions\DealBundle\DealItemDetail;
use ArcaSolutions\DealBundle\Entity\Promotion;
use ArcaSolutions\DealBundle\Entity\PromotionRedeem;
use ArcaSolutions\EventBundle\Entity\Event;
use ArcaSolutions\EventBundle\Entity\Eventcategory;
use ArcaSolutions\EventBundle\EventItemDetail;
use ArcaSolutions\EventBundle\Services\Recurring;
use ArcaSolutions\EventBundle\Twig\Extension\RecurringExtension;
use ArcaSolutions\ListingBundle\Entity\Listing;
use ArcaSolutions\ListingBundle\Entity\ListingCategory;
use ArcaSolutions\ListingBundle\ListingItemDetail;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Category;
use ArcaSolutions\WebBundle\Entity\Accountprofilecontact;
use ArcaSolutions\WebBundle\Entity\Appcustompages;
use ArcaSolutions\WebBundle\Entity\Review;
use ArcaSolutions\WebBundle\Form\Type\ReviewsType;
use ArcaSolutions\WebBundle\Form\Type\SendMailType;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityNotFoundException;
use Elastica\Suggest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;

class DefaultController extends FOSRestController
{
    /**
     * Return the general configuration of app
     *
     * @ApiDoc(
     *     resource = true,
     *     description = "Return the General Configuration of the App",
     *     statusCodes = {
     *       200 = "Returned when successful",
     *       500 = "Colors Unselected|Internal Server Error"
     *     }
     * )
     *
     * @View(serializerGroups={"generalConfigs"})
     * @param Request $request
     * @return array
     */
    public function getConfigAction(Request $request)
    {
        $configDocument = new ConfigDocument(
            $request,
            $this->get('settings'),
            $this->get('translator'),
            $this->getDoctrine(),
            $this->get('templating.helper.assets'),
            $this->get('modules')
        );

        return [
            'content'     => $configDocument->getContent(),
            'theme'       => $configDocument->getTheme(),
            'menuOptions' => $configDocument->getMenu(),
            'about'       => $configDocument->getAbout(),
        ];
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get Home resource. It combines featured boxes",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Return featured boxes",
     *       Codes::HTTP_INTERNAL_SERVER_ERROR = "Internal error",
     *     },
     * )
     */
    public function getHomeAction()
    {
        $sliders = [];
        $listings = [];
        $articles = [];
        $classifieds = [];
        $events = [];
        $posts = [];
        $deals = [];

        $slidersResponse = $this->forward('ApiBundle:Default:getSliders', [], ['area' => 'app_home']);
        if ($slidersResponse->getStatusCode() == Codes::HTTP_OK && $slidersResponse->headers->get('content_type') === 'application/json') {
            $sliders = json_decode($slidersResponse->getContent(), true);
        }
        $sliders = isset($sliders['data']) ? $sliders['data'] : [];

        /* gets listings using an internal request for its action */
        $listingResponse = $this->forward('ApiBundle:Default:getFeatured', ['module' => 'listing'], ['quantity' => 10]);
        if ($listingResponse->getStatusCode() == Codes::HTTP_OK && $listingResponse->headers->get('content_type') === 'application/json') {
            $listings = json_decode($listingResponse->getContent(), true);
        }
        $listings = isset($listings['data']) ? $listings['data'] : [];

        /* gets articles using an internal request for its action */
        $articleResponse = $this->forward('ApiBundle:Default:getRecent', ['module' => 'article'], ['quantity' => 10]);
        if ($articleResponse->getStatusCode() == Codes::HTTP_OK && $articleResponse->headers->get('content_type') === 'application/json') {
            $articles = json_decode($articleResponse->getContent(), true);
        }
        $articles = isset($articles['data']) ? $articles['data'] : [];

        /* gets classifieds using an internal request for its action */
        $classifiedsResponse = $this->forward('ApiBundle:Default:getFeatured', ['module' => 'classified'],
            ['quantity' => 10]);
        if ($classifiedsResponse->getStatusCode() == Codes::HTTP_OK && $classifiedsResponse->headers->get('content_type') === 'application/json') {
            $classifieds = json_decode($classifiedsResponse->getContent(), true);
        }
        $classifieds = isset($classifieds['data']) ? $classifieds['data'] : [];

        /* gets events using an internal request for its action */
        $eventsResponse = $this->forward('ApiBundle:Default:getFeatured', ['module' => 'event'], ['quantity' => 10]);
        if ($eventsResponse->getStatusCode() == Codes::HTTP_OK && $eventsResponse->headers->get('content_type') === 'application/json') {
            $events = json_decode($eventsResponse->getContent(), true);
        }
        $events = isset($events['data']) ? $events['data'] : [];

        /* gets deals using an internal request for its action */
        $dealsResponse = $this->forward('ApiBundle:Default:getPopular', ['module' => 'deal', ['quantity' => 10]]);
        if ($dealsResponse->getStatusCode() == Codes::HTTP_OK && $dealsResponse->headers->get('content_type') === 'application/json') {
            $deals = json_decode($dealsResponse->getContent(), true);
        }
        $deals = isset($deals['data']) ? $deals['data'] : [];

        /* gets posts using an internal request for its action */
        $postsResponse = $this->forward('ApiBundle:Default:getPopular', ['module' => 'blog', ['quantity' => 10]]);
        if ($postsResponse->getStatusCode() == Codes::HTTP_OK && $postsResponse->headers->get('content_type') === 'application/json') {
            $posts = json_decode($postsResponse->getContent(), true);
        }
        $posts = isset($posts['data']) ? $posts['data'] : [];

        /* ModStores Hooks */
        HookFire("api_before_returnhome", [
            'sliders'     => &$sliders,
            'listings'    => &$listings,
            'articles'    => &$articles,
            'classifieds' => &$classifieds,
            'events'      => &$events,
            'deals'       => &$deals,
            'posts'       => &$posts,
        ]);

        return [
            'data' => [
                'sliders'     => $sliders,
                'listings'    => $listings,
                'articles'    => $articles,
                'classifieds' => $classifieds,
                'events'      => $events,
                'deals'       => $deals,
                'posts'       => $posts,
            ],
        ];
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get elasticsearch results and the possible filters.",
     *     method="GET",
     *     statusCodes={
     *       Codes::HTTP_OK = "Returned Result",
     *     },
     *     output={
     *       "class"="ArcaSolutions\ApiBundle\Entity\Result",
     *       "groups"={"Result"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     parameters={
     *       {"name" = "q", "dataType" = "string", "required" = false, "description" = "Keyword to search", "format" = "([\w]+\s)+"},
     *       {"name" = "where", "dataType" = "string", "required" = false, "description" = "The location to search", "format" = "\w+"},
     *       {"name" = "page", "dataType" = "integer", "required" = false, "description" = "To paginate. Default value = 1", "format" = "\d+"},
     *       {"name" = "sort", "dataType" = "string", "required" = false, "description" = "To sort the results.  The
     *         sort parameter changes with the WebSite language. When sorting by distance it needs the user geo location(lat, lng)
     *         as extra parameters", "format" = "\w+"},
     *       {"name" = "account_id", "dataType" = "integer", "required" = false, "description" = "Account id of user", "format" = "\d+"},
     *     },
     *     filters={
     *       {"name" = "module", "dataType" = "array", "description" = "To filter by module", "multiple" = true, "delimiter" = ",", "format" = "((\w+)((,+\w+)+))|(\w+)"},
     *       {"name" = "location", "dataType" = "array", "description" = "To filter by location", "multiple" = true, "delimiter" = ","},
     *       {"name" = "category", "dataType" = "array", "description" = "To filter by category", "multiple" = true, "delimiter" = ","},
     *       {"name" = "rating", "dataType" = "array", "description" = "To filter by rating. The rating filter parameter changes with the WebSite language.", "multiple" = true, "delimiter" = "-"},
     *       {"name" = "date", "dataType" = "array", "description" = "To filter by date range (only with event module items). Ex: 2016-05-13,2016-05-13", "multiple" = false, "format" = "Y-m-d"},
     *       {"name" = "topLeft", "dataType" = "string", "description" = "To filter by geo bounding box (only works if sent together with bottomRight filter). Ex: 41.675083,-92.813354", "multiple" = false},
     *       {"name" = "bottomRight", "dataType" = "string", "description" = "To filter by geo bounding box (only works if sent together with topLeft filter). Ex: 33.528746,-79.563963", "multiple" = false},
     *     }
     * )
     *
     *
     * @param Request $request
     * @return Result
     * @View(serializerGroups={"Result"})
     */
    public function getResultsAction(Request $request)
    {
        $searchEngine = $this->get('search.engine');
        $parameterHandler = $this->get('search.parameters');

        /* get all the parameters of the request */
        $keyword = strtolower($request->get('q'));
        $where = strtolower($request->get('where'));
        $page = $request->get('page') ? (int)$request->get('page') : 1;
        $modules = $request->get('module');
        $locations = $request->get('location');
        $categories = $request->get('category');
        $date = $request->get('date');

        /* setting the parameters at the handler */
        if (!empty($keyword)) {
            $parameterHandler->setKeywords($keyword);
        }

        /* setting the parameters at the handler */
        if (!empty($where)) {
            $parameterHandler->setKeywords($where);
        }

        /* add module filter to $parameterHandler */
        if (!empty($modules)) {
            $activeModules = $searchEngine->getActiveModules();

            $modulesArray = array_map(function ($module) {
                return $module === 'deal' ? 'promotion' : $module;
            }, GeneralDocument::convertStringToArray($modules));

            $modules = array_intersect(array_keys($activeModules), $modulesArray);

            foreach ($modules as $key => $value) {
                $parameterHandler->addModule($value);
            }
        }

        /* add categories filter to $parameterHandler */
        if (!empty($categories)) {
            $parameterHandler->setCategories($searchEngine->categoryFriendlyURLSearch(GeneralDocument::convertStringToArray($categories)));
        }

        /* add locations filter to $parameterHandler */
        if (!empty($locations)) {
            $locations = GeneralDocument::convertStringToArray($locations);
            $parameterHandler->setLocations($searchEngine->locationFriendlyURLSearch($locations));
        }

        /* add date filter to $parameterHandler */
        if (!empty($date)) {
            $dateFormat = 'Y-m-d';
            $date = GeneralDocument::convertStringToArray($date);

            $parameterHandler->setStartDate(\DateTime::createFromFormat($dateFormat, $date[0]));
            $parameterHandler->setEndDate(\DateTime::createFromFormat($dateFormat, $date[1]));
        }

        $searchEvent = $searchEngine->globalSearch($keyword, $where);
        $search = $searchEngine->search($searchEvent);

        $pagination = $this->get('knp_paginator')->paginate($search, $page);
        $searchEvent->processAggregationResults($pagination);

        return new Result($pagination, $searchEvent, $searchEngine, $this->container);
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get elasticsearch suggestions for the given keyword.",
     *     method="GET",
     *     statusCodes={
     *       Codes::HTTP_OK = "Returned Suggestions",
     *     },
     *     output={
     *       "class"="\ArcaSolutions\ApiBundle\Entity\Suggest",
     *       "groups"={"Suggest"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     parameters={
     *       {"name" = "q", "dataType" = "string", "required" = false, "description" = "Keyword to search for suggestions", "format" = "([\w]+\s)+"},
     *     }
     * )
     * @param Request $request
     * @return array
     * @View(serializerGroups={"Suggest"})
     */
    public function getSuggestAction(Request $request)
    {
        $data = [];

        $keyword = $request->get('q', false);

        if (!$keyword) {
            return $data;
        }

        $suggestionName = 'search';

        $searchEngine = $this->get('search.engine');

        $elasticaClient = $searchEngine->getElasticaClient();
        $indexName = $this->get('search.engine')->getElasticIndexName();
        $elasticaIndex = $elasticaClient->getIndex($indexName);

        $suggest = new Suggest();
        $suggestion = new Suggest\Completion($suggestionName, 'suggest.what');
        $suggestion->setText($keyword);

        $suggest->addSuggestion($suggestion);

        $result = $elasticaIndex->search($suggest);

        if ($matches = $result->getSuggests()) {
            foreach ($matches[$suggestionName] as $match) {
                foreach ($match['options'] as $option) {
                    $data[] = new \ArcaSolutions\ApiBundle\Entity\Suggest($option);
                }
            }
        }

        return ['data' => $data];
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get listing  detail",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Return the listing detail",
     *       Codes::HTTP_NOT_FOUND = "Listing not found",
     *     },
     *     output={
     *       "class"="\ArcaSolutions\ListingBundle\Entity\Listing",
     *       "groups"={"listingDetail"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     parameters={
     *       {"name" = "account_id", "dataType" = "integer", "required" = false, "description" = "Account id of user", "format" = "\d+"},
     *     },
     *     requirements={
     *       {"name" = "listing", "dataType" = "integer", "description" = "Listing id", "requirement" = "\d+"},
     *     }
     * )
     *
     * @param Listing $listing
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     * @View(serializerGroups={"listingDetail"})
     * @ParamConverter("listing", class="ListingBundle:Listing")
     */
    public function getListingAction(Listing $listing)
    {
        /* listing not found */
        if (null === $listing) {
            throw new ItemNotFoundException();
        }

        /* normalizes item to validate detail */
        $listingItemDetail = new ListingItemDetail($this->container, $listing);

        if ('A' !== $listingItemDetail->getItem()->getStatus()) {
            throw new UnavailableItemException();
        }

        $generalDocument = new GeneralDocument($this->container);

        /* Replace newline or \r\n to <br/> */
        $listing->setLongDescription(nl2br($listing->getLongDescription()));

        /* set the icon path for each badge */
        $listing->setChoices($generalDocument->getBadgesImagePath($listing));
        /* set choices as an array */
        $badges = [];
        foreach ($listing->getChoices() as $listingChoice) {
            $badges[] = $listingChoice->getEditorChoice();
        }
        $listing->setChoices($badges);

        /* insert all the locations at the address */
        $listing->setAddress($generalDocument->getItemLocationsImploded($listing));

        $accountId = $this->get('request')->get('account_id');

        $favorite = $this->get('doctrine')
            ->getRepository('WebBundle:Quicklist')
            ->getQuicklistByAccountItemModule($accountId, $listing->getId(), 'listing');

        /* checks if the user has already marked this listing as favorite */
        if ($favorite) {
            $listing->setFavoriteId($favorite->getId());
        }

        /* set the full path of the file */
        $listing->setAttachmentFile($generalDocument->getFilePath($listing->getAttachmentFile()));

        /*
        * get listing gallery
        * and set the image path for each image
        */
        $listing->setGalleryAPI($generalDocument->getGallery($listing, $listingItemDetail));

        $reviews_enable = $this->getDoctrine()
            ->getRepository('WebBundle:Setting')
            ->getSetting('review_listing_enabled');

        if ($reviews_enable) {
            /* Gets total of reviews */
            $reviews_total = $this->get('doctrine')
                ->getRepository('WebBundle:Review')
                ->getTotalByItemId($listing->getId(), 'listing');

            $listing->setReviewsTotal((int)array_values($reviews_total)[0]);
        } else {
            $listing->setAvgReview(null)
                ->setReviewsTotal(null);
        }

        $moduleLevel = new ModuleLevelDocument('listing');

        $hoursArray = $this->container->get('listing.service')->formatHoursWork($listing->getHoursWork());
        $hoursWork = '';

        if (!empty($hoursArray)) {
            $hoursArrayStr = [];
            foreach ($hoursArray as $dayWeek => $hours) {
                $hoursStr = '';
                $weekDay = $this->container->get('translator')->transChoice('week.days', (int)$dayWeek + 1, [], 'units');
                if(!empty($hours)){
                    foreach ($hours as $key => $hour) {
                        if (count($hours) > 1 and $key != 0) {
                            $hoursStr .= ' / ';
                        }
                        $startTime = new DateTime($hour['hours_start']);
                        $startTime = $startTime->format($this->container->get('translator')->trans('time.format', [], 'units'));
                        $endTime = new DateTime($hour['hours_end']);
                        $endTime = $endTime->format($this->container->get('translator')->trans('time.format', [], 'units'));
                        $hoursStr .= $startTime . ' - ' . $endTime;
                    }
                } else {
                    $hoursStr = $this->container->get('translator')->trans('Closed');
                }

                $hoursArrayStr[] = $weekDay . ': ' . $hoursStr;
            }

            $hoursWork = implode(PHP_EOL, $hoursArrayStr);
        }

        $listing->setHoursWork($hoursWork);

        $features = '';

        if (!empty($listing->getFeatures())) {
            $featuresArray = json_decode($listing->getFeatures(), true);
            if (is_array($featuresArray)) {
                foreach ($featuresArray as $feature) {
                    $features .= $feature['title'] . PHP_EOL;
                }
            }
        }

        $listing->setFeatures($features);

        $listing->setFax($listing->getAdditionalPhone());

        /* set null the fields that the listing level doesn't allow */
        /* @var $listing Listing */
        $listing = $moduleLevel->applyModuleLevel($listing, $listingItemDetail->getLevel());

        /* set categories as an array */
        $categories = [];
        foreach ($listing->getCategories() as $category) {
            /* @var $category ListingCategory */
            if ($category->getEnabled() === 'n') {
                continue;
            }

            $categories[] = $category;
        }

        $listing->setCategories($categories);

        /* gets listing's deals */
        $deals = [];
        foreach ($listing->getDeals() as $deal) {
            /* @var $deal Promotion */
            if ($this->container->get('deal.handler')->isValid($deal)) {
                $deal->setImageUrl($generalDocument->getImagePath($deal->getMainImage()));

                $deal->cleanRedeem(true);
                if ($accountId) {
                    $redeem = $this->getDoctrine()->getRepository('DealBundle:PromotionRedeem')->existUserCodeForDeal($deal,
                        $accountId);
                    $redeem and $deal->setRedeem($redeem);
                }

                $deals[] = $deal;
            }
        }
        /* limit deals by listing level */
        $listing->setDeals(array_slice($deals, 0, $listingItemDetail->getLevel()->dealCount));

        /* get the extra fields of the listing */
        if ($listing->getTemplate()) {
            $extra_fields = $listing->getTemplate()->getFields();

            /* apply the view rules for the extra fields */
            $amenities = null;
            foreach ($extra_fields as $field) {
                if (strpos($field->getField(), 'custom_') !== false) {
                    $getter = 'get'.GeneralDocument::convertToCamel($field->getField());
                    $value = $listing->$getter();
                    if ($value) {
                        if ($value === 'y' || $value === 'n') {
                            $value = $value === 'y' ? $this->container->get('translator')->trans('Yes', [],
                                'messages') : $this->container->get('translator')->trans('No', [], 'messages');
                        }
                        $amenities[] = [
                            'label' => /** @Ignore */
                                $field->getLabel(),
                            'value' => $value,
                        ];
                    }
                }
            }
            $listing->setExtraFields($amenities);
        }

        /* gets listing's classifieds */
        $classifieds = [];
        foreach ($listing->getClassifieds() as $classified) {
            /* @var $classified Classified */
            if ($this->container->get('classified.handler')->isValid($classified)) {
                $classifiedItemDetail = new ClassifiedItemDetail($this->container, $classified);
                if ($classifiedItemDetail->getLevel()->imageCount > 0) {
                    $classified->setImageUrl($generalDocument->getImagePath($classified->getImage()));
                }

                $classifieds[] = $classified;
            }
        }

        /* limit classifieds by listing level */
        $listing->setClassifieds(array_slice($classifieds, 0,
            $listingItemDetail->getLevel()->classifiedQuantityAssociation));

        $listing->setDetailUrl(
            $this->container->get('router')->generate(
                'listing_detail',
                ['friendlyUrl' => $listing->getFriendlyUrl(), '_format' => 'html'],
                true
            )
        );

        /* ModStores Hooks */
        HookFire("api_before_returnlisting", [
            "listing"           => &$listing,
            "listingItemDetail" => &$listingItemDetail
        ]);

        return ['data' => $listing];
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get deal(Promotion) detail",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Return the deal detail",
     *       Codes::HTTP_NOT_FOUND = "Deal not found",
     *     },
     *     output={
     *       "class"="\ArcaSolutions\DealBundle\Entity\Promotion",
     *       "groups"={"dealDetail", "listingDetail"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     parameters={
     *       {"name" = "account_id", "dataType" = "integer", "required" = false, "description" = "Account id of user", "format" = "\d+"},
     *     },
     *     requirements={
     *       {"name" = "deal", "dataType" = "integer", "description" = "Deal id", "requirement" = "\d+"},
     *     }
     * )
     *
     * @param Promotion $deal
     * @return array
     * @throws \Exception
     * @View(serializerGroups={"dealDetail"})
     * @ParamConverter("deal", class="DealBundle:Promotion")
     */
    public function getDealAction(Promotion $deal)
    {
        /* deal not found */
        if (null === $deal) {
            throw new ItemNotFoundException();
        }

        /* normalizes item to validate detail */
        $dealItemDetail = new DealItemDetail($this->container, $deal);

        if ('A' !== $dealItemDetail->getItem()->getStatus()) {
            throw new UnavailableItemException();
        }

        $listingItemDetail = null;
        $listing = $deal->getListing();
        if ($deal->getListingId()) {
            $listingItemDetail = new ListingItemDetail($this->container, $listing);
        }

        /*
         * It validates if the listing is enabled, if sponsor is accessing this page or if the sitemgr is accessing
         * It is used an OR conditional because it has rules for sponsor and sitemgr that have to overwrite all the others
         */
        if (!($listingItemDetail && ValidationDetail::isDetailAllowed($dealItemDetail) && 'A' === $listingItemDetail->getItem()->getStatus())) {
            throw new UnavailableItemException();
        }
        if (!$this->container->get('deal.handler')->isValid($deal)) {
            throw new UnavailableItemException();
        }

        $today = new \DateTime('now');
        /* workaround to fix edirectory behavior */
        $endDate = clone $deal->getEndDate();
        if ($endDate->modify('+1 day') < $today) {
            throw new UnavailableItemException;
        }

        $generalDocument = new GeneralDocument($this->container);

        /* Replace newline or \r\n to <br/> */
        $deal->setLongDescription(nl2br($deal->getLongDescription()));

        /* set the complete image path url */
        $deal->setImageUrl($generalDocument->getImagePath($deal->getMainImage()));

        /* insert all the listing locations at the address */
        $listing->setAddress($generalDocument->getItemLocationsImploded($listing));

        /* set the complete image path url */
        $listing->setImageUrl($generalDocument->getImagePath($listing->getMainImage()));

        /* set categories as an array */
        $categories = [];
        foreach ($listing->getCategories() as $category) {
            /* @var $category ListingCategory */
            if ($category->getEnabled() === 'n') {
                continue;
            }

            $categories[] = $category;
        }

        $listing->setCategories($categories);

        $deal->setListing($listing);

        $redeems = clone $deal->getRedeem();
        $deal->cleanRedeem(true);
        /* @var $item PromotionRedeem */
        foreach ($redeems as $item) {
            $item->getAccountId() === (int)$this->get('request')->get('account_id')
            and $deal->setRedeem($item);
        }

        $deal->setDetailUrl(
            $this->container->get('router')->generate(
                'deal_detail',
                ['friendlyUrl' => $deal->getFriendlyUrl(), '_format' => 'html'],
                true
            )
        );

        $deal->getEndDate()
            ->add(\DateInterval::createFromDateString('+ 23 hours'))
            ->add(\DateInterval::createFromDateString('+ 59 minutes'))
            ->add(\DateInterval::createFromDateString('+ 59 seconds'));

        /* ModStores Hooks */
        HookFire("api_before_returndeal", [
            "deal"           => &$deal,
            "dealItemDetail" => &$dealItemDetail
        ]);

        return ['data' => $deal];
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get event detail",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Return the Event detail",
     *       Codes::HTTP_NOT_FOUND = "Event not found",
     *     },
     *     output={
     *       "class"="\ArcaSolutions\EventBundle\Entity\Event",
     *       "groups"={"eventDetail"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     parameters={
     *       {"name" = "account_id", "dataType" = "integer", "required" = false, "description" = "Account id of user", "format" = "\d+"},
     *     },
     *     requirements={
     *       {"name" = "event", "dataType" = "integer", "description" = "Event id", "requirement" = "\d+"},
     *     }
     * )
     * @param Event $event
     * @return array
     * @throws \Exception * @View(serializerGroups={"eventDetail"})
     * @ParamConverter("event", class="EventBundle:Event")
     */
    public function getEventAction(Event $event)
    {

        /* event not found */
        if (null === $event) {
            throw new ItemNotFoundException();
        }

        /* normalizes item to validate detail */
        $eventItemDetail = new EventItemDetail($this->container, $event);

        if ('A' !== $eventItemDetail->getItem()->getStatus()) {
            throw new UnavailableItemException();
        }

        $generalDocument = new GeneralDocument($this->container);

        /* Replace newline or \r\n to <br/> */
        $event->setLongDescription(nl2br($event->getLongDescription()));

        /* insert all the event locations at the address */
        $event->setAddress($generalDocument->getItemLocationsImploded($event));

        /* checks if the user has already marked this event as favorite */
        if ($favorite = $this->get('doctrine')->getRepository('WebBundle:Quicklist')->getQuicklistByAccountItemModule($this->get('request')->get('account_id'),
            $event->getId(), 'event')
        ) {
            $event->setFavoriteId($favorite->getId());
        }

        /*
         * get event gallery
         * and set the image path for each image
         */
        $event->setGalleryAPI($generalDocument->getGallery($event, $eventItemDetail));

        /* set the recurring date of the event */
        if ($event->getRecurring() === 'Y') {
            $recurring = new RecurringExtension($this->container);
            $event->setRecurringPhrase($recurring->recurringPhrase($event));

            $generalDocument->setStartDateToNextOccurrence($event);
        }

        $event = $generalDocument->setBadDateValueToNull($event);

        $moduleLevel = new ModuleLevelDocument('event');

        /* set null the fields that the event level doesn't allow */
        $event = $moduleLevel->applyModuleLevel($event, $eventItemDetail->getLevel());

        $event->setDetailUrl(
            $this->container->get('router')->generate(
                'event_detail',
                ['friendlyUrl' => $event->getFriendlyUrl(), '_format' => 'html'],
                true
            )
        );

        /* ModStores Hooks */
        HookFire("api_before_returnevent", [
            "event"           => &$event,
            "eventItemDetail" => &$eventItemDetail
        ]);

        return ['data' => $event];
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get Custom page detail",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Return the Custom page detail",
     *       Codes::HTTP_NOT_FOUND = "Custom page not found",
     *     },
     *     output={
     *       "class"="\ArcaSolutions\WebBundle\Entity\Appcustompages",
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     requirements={
     *       {"name" = "custom", "dataType" = "integer", "description" = "Custom page id", "requirement" = "\d+"},
     *     }
     * )
     * @param Appcustompages $custom
     * @return array
     * @View()
     * @ParamConverter("custom", class="WebBundle:Appcustompages")
     */
    public function getCustompagesAction(Appcustompages $custom)
    {

        /* custom not found */
        if (null === $custom) {
            throw new ItemNotFoundException();
        }

        $generalDocument = new GeneralDocument($this->container);

        $customJson = json_decode($custom->getJson());

        $customData = $generalDocument->setStdClassFieldsToArray($customJson->data);

        /* ModStores Hooks */
        HookFire("api_before_returncustompage", [
            "customData" => &$customData,
        ]);

        return ['data' => $customData];
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get classified detail",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Return the Classified detail",
     *       Codes::HTTP_NOT_FOUND = "Classified not found",
     *     },
     *     output={
     *       "class"="\ArcaSolutions\ClassifiedBundle\Entity\Classified",
     *       "groups"={"classifiedDetail"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     parameters={
     *       {"name" = "account_id", "dataType" = "integer", "required" = false, "description" = "Account id of user", "format" = "\d+"},
     *     },
     *     requirements={
     *       {"name" = "classified", "dataType" = "integer", "description" = "Classified id", "requirement" = "\d+"},
     *     }
     * )
     * @param Classified $classified
     * @return array
     * @throws \Exception* @View(serializerGroups={"classifiedDetail"})
     * @ParamConverter("classified", class="ClassifiedBundle:Classified")
     */
    public function getClassifiedAction(Classified $classified)
    {
        /* classified not found by friendlyURL */
        if (null === $classified) {
            throw new ItemNotFoundException();
        }

        /* normalizes item to validate detail */
        $classifiedItemDetail = new ClassifiedItemDetail($this->container, $classified);

        if ('A' !== $classifiedItemDetail->getItem()->getStatus()) {
            throw new UnavailableItemException();
        }

        $generalDocument = new GeneralDocument($this->container);

        /* Replace newline or \r\n to <br/> */
        $classified->setDetaildesc(nl2br($classified->getDetaildesc()));

        /* insert all the classified locations at the address */
        $classified->setAddress($generalDocument->getItemLocationsImploded($classified));

        /* checks if the user has already marked this classified as favorite */
        if ($favorite = $this->get('doctrine')->getRepository('WebBundle:Quicklist')->getQuicklistByAccountItemModule($this->get('request')->get('account_id'),
            $classified->getId(), 'classified')
        ) {
            $classified->setFavoriteId($favorite->getId());
        }

        /*
        * get classified gallery
        * and set the image path for each image
        */
        $classified->setGalleryAPI($generalDocument->getGallery($classified, $classifiedItemDetail));

        /* set the full path of the file */
        $classified->setAttachmentFile($generalDocument->getFilePath($classified->getAttachmentFile()));

        $moduleLevel = new ModuleLevelDocument('classified');

        /* set null the fields that the classified level doesn't allow */
        $classified = $moduleLevel->applyModuleLevel($classified, $classifiedItemDetail->getLevel());

        /* validate listing */
        $listing = $this->get('doctrine')->getRepository('ListingBundle:Listing')->getClassifiedAssociatedListing($classified->getListingId());
        if ($listing) {
            $listingItemDetail = new ListingItemDetail($this->container, $listing);
            if ($listingItemDetail->getLevel()->imageCount > 0) {
                $listing->setImageUrl($generalDocument->getImagePath($listing->getMainImage()));
            }
        } else {
            $classified->removeListing();
        }

        $classified->setDetailUrl(
            $this->container->get('router')->generate(
                'classified_detail',
                ['friendlyUrl' => $classified->getFriendlyUrl(), '_format' => 'html'],
                true
            )
        );

        /* ModStores Hooks */
        HookFire("api_before_returnclassified", [
            "classified"           => &$classified,
            "classifiedItemDetail" => &$classifiedItemDetail
        ]);

        return ['data' => $classified];
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get Article detail",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Return the Article detail",
     *       Codes::HTTP_NOT_FOUND = "Article not found",
     *     },
     *     output={
     *       "class"="\ArcaSolutions\ArticleBundle\Entity\Article",
     *       "groups"={"articleDetail"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     parameters={
     *       {"name" = "account_id", "dataType" = "integer", "required" = false, "description" = "Account id of user", "format" = "\d+"},
     *     },
     *     requirements={
     *       {"name" = "article", "dataType" = "integer", "description" = "Article id", "requirement" = "\d+"},
     *     }
     * )
     * @param Article $article
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @View(serializerGroups={"articleDetail"})
     * @ParamConverter("article", class="ArticleBundle:Article")
     */
    public function getArticleAction(Article $article)
    {
        /* article not found by friendlyURL */
        if (null === $article) {
            throw new ItemNotFoundException();
        }

        $favorite = $this->get('doctrine')
            ->getRepository('WebBundle:Quicklist')
            ->getQuicklistByAccountItemModule(
                $this->get('request')->get('account_id'),
                $article->getId(), 'article'
            );

        /* checks if the user has already marked this article as favorite */
        if ($favorite) {
            $article->setFavoriteId($favorite->getId());
        }

        /* normalizes item to validate detail */
        $articleItemDetail = new ArticleItemDetail($this->container, $article);

        if ('A' !== $articleItemDetail->getItem()->getStatus()) {
            throw new UnavailableItemException();
        }

        $generalDocument = new GeneralDocument($this->container);

        /* Replace newline or \r\n to <br/> */
        $article->setAbstract(nl2br($article->getAbstract()));

        /*
        * get event gallery
        * and set the image path for each image
        */
        $article->setGalleryAPI($generalDocument->getGallery($article, $articleItemDetail));

        $article->setDetailUrl(
            $this->container->get('router')->generate(
                'article_detail',
                ['friendlyUrl' => $article->getFriendlyUrl(), '_format' => 'html'],
                true
            )
        );

        /* ModStores Hooks */
        HookFire("api_before_returnarticle", [
            "article"           => &$article,
            "articleItemDetail" => &$articleItemDetail
        ]);

        return ['data' => $article];
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get Blog detail",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Return the Blog detail",
     *       Codes::HTTP_NOT_FOUND = "Blog not found",
     *     },
     *     output={
     *       "class"="\ArcaSolutions\BlogBundle\Entity\Post",
     *       "groups"={"blogDetail"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     *     requirements={
     *       {"name" = "blog", "dataType" = "integer", "description" = "Blog id", "requirement" = "\d+"},
     *     }
     * )
     * @param Blog $blog
     * @return array
     * @throws \Exception
     * @View(serializerGroups={"blogDetail"})
     * @ParamConverter("blog", class="BlogBundle:Post")
     */
    public function getBlogAction(Blog $blog)
    {
        /* blog not found by friendlyURL */
        if (null === $blog) {
            throw new ItemNotFoundException();
        }

        /* normalizes item to validate detail */
        $blogItemDetail = new BlogItemDetail($this->container, $blog);

        if ('A' !== $blogItemDetail->getItem()->getStatus()) {
            throw new UnavailableItemException();
        }

        $generalDocument = new GeneralDocument($this->container);

        /* set image complete url */
        $blog->setImageUrl($generalDocument->getImagePath($blog->getImage()));

        /* set categories as an array */
        $categories = [];
        foreach ($blog->getCategories() as $blogCategory1) {
            /* @var $cat Blogcategory */
            $cat = $blogCategory1->getCategory();
            if ($cat->getEnabled() === 'n') {
                continue;
            }

            $categories[] = $cat;
        }

        $blog->setCategories($categories);

        $blog->setDetailUrl(
            $this->container->get('router')->generate(
                'blog_detail',
                ['friendlyUrl' => $blog->getFriendlyUrl(), '_format' => 'html'],
                true
            )
        );

        /* ModStores Hooks */
        HookFire("api_before_returnblog", [
            "blog"           => &$blog,
            "blogItemDetail" => &$blogItemDetail
        ]);

        return ['data' => $blog];
    }

    /**
     * @ApiDoc(
     *     resource = true,
     *     description = "Return the Terms of use",
     *     statusCodes = {
     *       200 = "Returned when successful",
     *       500 = "Internal Server Error"
     *     }
     * )
     *
     * @View()
     */
    public function getTermsAction()
    {
        $termsOfUse = $this->container->get('pagetype.service')->getCustomContentAndTitlePerPageType(PageType::TERMS_OF_SERVICE_PAGE);

        return [
            'data' => [
                'title' => $termsOfUse['title'],
                'body'  => $termsOfUse['body'],
            ],
        ];
    }

    /**
     * @ApiDoc(
     *     resource = true,
     *     description = "Return the Privacy policy",
     *     statusCodes = {
     *       200 = "Returned when successful",
     *       500 = "Internal Server Error"
     *     }
     * )
     *
     * @View()
     *
     * @return array
     */
    public function getPrivacyAction()
    {
        $privacy = $this->container->get('pagetype.service')->getCustomContentAndTitlePerPageType(PageType::PRIVACY_POLICY_PAGE);

        return [
            'data' => [
                'title' => $privacy['title'],
                'body'  => $privacy['body'],
            ],
        ];
    }

    /**
     * @Get("/reviews/{accountId}", methods={"GET"})
     * @ApiDoc(
     *     resource=true,
     *     description="Get reviews from an user",
     *     method="GET",
     *     statusCodes={
     *       Codes::HTTP_OK = "Return Reviews",
     *       Codes::HTTP_NOT_FOUND = "Error in find the reviews",
     *     },
     *     output={
     *       "class"="ArcaSolutions\WebBundle\Entity\Review",
     *       "groups"={"ReviewByAccount"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     }
     * )
     * @ParamConverter("accountId", class="WebBundle:Accountprofilecontact")
     *
     * @param Accountprofilecontact $accountId
     * @return array
     * @throws \Exception
     * @View(serializerGroups={"ReviewByAccount", "reviewItem"})
     */
    public function getReviewsAccountAction(Accountprofilecontact $accountId)
    {
        $reviews = $this->get('review.handler')->getReviewsByAccountId($accountId);

        foreach ($reviews as $key => $review) {
            $repositoryName = $this->container->get('helper.module')->getModuleRepositoryName($review->getItemType());
            $item = $this->getDoctrine()->getRepository($repositoryName)->findOneBy([
                'id'     => $review->getItemId(),
                'status' => 'A',
            ]);
            if ($item) {
                $item->setType($review->getItemType());
                $review->setModule($item);
            } else {
                unset($reviews[$key]);

            }
        }

        return ['data' => array_values($reviews)];
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Delete review from an user",
     *     method="DELETE",
     *     statusCodes={
     *       Codes::HTTP_OK = "Review Deleted, return account.",
     *       Codes::HTTP_NOT_ACCEPTABLE = "Error in delete the review",
     *     }
     * )
     * @ParamConverter("accountId", class="WebBundle:Accountprofilecontact")
     * @RequestParam(name="reviewIds", requirements="((\d+)((,+\d+)+))|(\d+)", description="The array of review ids", allowBlank=false)
     *
     * @param Accountprofilecontact $accountId
     * @param ParamFetcher $paramFetcher
     * @return array
     */
    public function deleteReviewsAction(Accountprofilecontact $accountId, ParamFetcher $paramFetcher)
    {
        $reviewIds = explode(',', $paramFetcher->get('reviewIds', true));
        try {
            foreach ($reviewIds as $reviewId) {
                $account = $this->get('review.handler')->deleteReviewById(
                    $accountId,
                    $reviewId
                );
            }
        } catch (\Exception $e) {
            throw new HttpException(Codes::HTTP_NOT_ACCEPTABLE, $e->getMessage());
        }

        return [
            'data' => $account,
        ];
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Get reviews from a module",
     *     method="GET",
     *     statusCodes={
     *       Codes::HTTP_OK = "Returned Reviews",
     *       Codes::HTTP_BAD_REQUEST = {"Review not enabled for module"},
     *     },
     *     output={
     *       "class"="ArcaSolutions\WebBundle\Entity\Review",
     *       "groups"={"Review"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     }
     * )
     * @QueryParam(name="module", requirements="^(listing|article)$", description="Review's module", allowBlank=false)
     * @QueryParam(name="itemId", requirements="\d+", description="The item Id of the review", allowBlank=false)
     * @QueryParam(name="page", requirements="\d+", description="To paginate", allowBlank=true, default="1")
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     * @return array
     * @throws \Exception
     * @throws \Twig_Error_Runtime
     * @View(serializerGroups={"Review"})
     */
    public function getReviewsAction(ParamFetcher $paramFetcher, Request $request)
    {
        /* get parameters and throw an error if it needed */
        $module = $paramFetcher->get('module', true);
        $itemId = $paramFetcher->get('itemId', true);
        $page = $paramFetcher->get('page', false);

        $reviewHandler = $this->get('review.handler');
        /* Check if review is enabled for that module */
        if ($reviewHandler->isModuleEnabled($module) === false) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST,
                sprintf('Review for %s module is not enabled.', ucfirst($module)));
        }

        $return = [];

        /* criteria used to filter reviews */
        $criteria = [];
        $criteria['itemType'] = $module;
        $criteria['itemId'] = $itemId;
        $criteria['approved'] = 1;

        $query = $this->getDoctrine()->getRepository('WebBundle:Review')->findBy($criteria, ['added' => 'DESC']);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page
        );

        /* I did not want do this, but I have to */
        $items = $pagination->getItems();
        /** @var Review $item */
        foreach ($items as &$item) {
            $image = $request->getSchemeAndHttpHost().$this->get('templating.helper.assets')->getUrl('assets/images/user-image.png');

            $profile = $item->getProfile();
            $contact = null;
            if ($profile) {
                /* @var Contact $contact */
                $contact = $this->getDoctrine()->getRepository('CoreBundle:Contact',
                    'main')->findOneBy(['account' => $profile->getAccountId()]);

                /* Add user image in review */
                if ($profile->getAccountId()) {
                    $imageName = $this->get('twig')->getExtension('image_extension')->getProfileImage($profile);
                    if ($imageName) {
                        $image = $request->getSchemeAndHttpHost().$this->get('templating.helper.assets')->getUrl($imageName,
                                'profile_images');
                    }
                    $image = $profile->getFacebookImage() ?: $image;
                }
            }

            $item->setReviewer($contact, $image);
        }

        $return['data'] = $items;

        $return['paging'] = [
            'page'  => (int)$pagination->getCurrentPageNumber(),
            'pages' => $pagination->getPageCount(),
            'total' => $pagination->getTotalItemCount(),
        ];

        return $return;
    }

    /**
     * @Post("/reviews", methods={"POST"})
     * @ApiDoc(
     *     resource= true,
     *     description = "Insert a new review",
     *     method = "POST",
     *     input="ArcaSolutions\WebBundle\Form\Type\ReviewsType",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Insert done",
     *       Codes::HTTP_BAD_REQUEST = {"Error in form", "Review not enabled for module", "Account with id does not exist", "Item with id does not exist."},
     *       Codes::HTTP_CONFLICT = {"User already reviewed this one"}
     *     },
     *     output={
     *       "class"="ArcaSolutions\WebBundle\Entity\Review",
     *       "groups"={"Review"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     }
     * )
     *
     * @RequestParam(name="module", requirements="^(listing|article)$", description="Review's module")
     * @RequestParam(name="itemId", requirements="\d+", description="The item Id of the review", allowBlank=false)
     * @RequestParam(name="accountId", requirements="\d+", description="Account Id of the client", allowBlank=true)
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     * @return array|\Symfony\Component\Form\Form
     * @throws \Exception
     */
    public function postReviewAction(ParamFetcher $paramFetcher, Request $request)
    {
        /* get parameters and throw an error if it needed */
        $module = $paramFetcher->get('module', true);
        $itemId = $paramFetcher->get('itemId', true);

        $reviewHandler = $this->get('review.handler');

        /* Check if review is enabled for that module */
        if ($reviewHandler->isModuleEnabled($module) === false) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST,
                sprintf('Review for %s module is not enabled.', ucfirst($module)));
        }

        /* Get form fields */
        $requestFields = $request->request->all();

        /* Get account field */
        $account = null;
        if ($accountId = $paramFetcher->get('accountId', false)) {
            /* gets account if it is given */
            if (!($account = $this->getDoctrine()->getRepository('WebBundle:Accountprofilecontact')->find($accountId)) || !($contactMain = $this->getDoctrine()->getRepository('CoreBundle:Contact',
                    'main')->findOneBy(['account' => $account->getAccountId()]))
            ) {
                throw new HttpException(Codes::HTTP_BAD_REQUEST,
                    $this->get('translator')->trans('We could not find your account. Please log in again and come back later.')
                );
            } else {
                $requestFields['name'] = $account->getFirstName().' '.$account->getLastName();
                $requestFields['email'] = $contactMain->getEmail();
            }
        }

        /* Check if forcelogin is enabled */
        if ($reviewHandler->forceLogin($module) and !$accountId) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST,
                $this->get('translator')->trans('Please log in to send your review.')
            );
        }

        if (!($item = $this->getDoctrine()->getRepository($this->container->get('helper.module')->getModuleRepositoryName($module))->find($itemId))) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST,
                $this->get('translator')->trans('This article is not available for review anymore.')
            );
        }

        /* creates form following reviews form */
        $form = $this->createForm(ReviewsType::class, ['member' => $account]);
        /* populate form using parameters */
        $form->submit($requestFields);

        if ($form->isValid()) {
            try {
                /* save review */
                $review = $reviewHandler->save($module, $itemId, $account, $form->getData());

                return [
                    'message' => $reviewHandler->successMessage(),
                    'data'    => $review,
                ];
            } catch (\Exception $e) {
                /* usually database error */
                $message = $e->getCode() == Codes::HTTP_CONFLICT ?
                    $e->getMessage() :
                    sprintf('An error occurred: %s', $e->getMessage());
                throw new HttpException($e->getCode(), $message);
            }
        }

        /* returns form error */

        return $form;
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get all sliders",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Returned all sliders",
     *     },
     *     output={
     *       "class"="ArcaSolutions\WebBundle\Entity\Slider",
     *       "groups"={"Slider"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     }
     * )
     *
     * @QueryParam(name="area", requirements="^(web|app_home|app_listing)$", description="Slider's area", allowBlank=false)
     *
     * @View(serializerGroups={"Slider"})
     * @param ParamFetcher $paramFetcher
     * @return array
     */
    public function getSlidersAction(ParamFetcher $paramFetcher)
    {
        $repo = $this->container->get('doctrine')
            ->getRepository('WebBundle:Slider');

        $sliders = $repo->getSlidersByArea($paramFetcher->get('area', true));

        $generalDocument = new GeneralDocument($this->container);

        $validSliders = [];

        foreach ($sliders as &$slider) {
            if (!$repo->isValid($slider)) {
                continue;
            }

            $validSliders[] = $slider;
            $slider->setImagePath($generalDocument->getImagePath($slider->getImage()));
        }

        return ['data' => $validSliders];
    }

    /**
     * @Get("/{module}/recent", methods={"GET"}, requirements={"module"="article|blog|classified|deal"})
     * @ApiDoc(
     *     resource= true,
     *     description = "Get recent Articles|Blog|Classified|Deal",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Get recent items from a respective module",
     *       Codes::HTTP_FORBIDDEN = "The module is not available",
     *     },
     * )
     *
     * @QueryParam(name="quantity", requirements="\d+", description="Quantity of items", allowBlank=true, default="4")
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     * @return array
     * @throws \Exception
     * @View(serializerGroups={"Result"})
     */
    public function getRecentAction(ParamFetcher $paramFetcher, Request $request)
    {
        $module = $request->get('module');
        if (!$this->container->get('modules')->isModuleAvailable($module)) {
            throw new HttpException(Codes::HTTP_FORBIDDEN, sprintf('The %s module is not available', ucfirst($module)));
        }

        $generalDocument = new GeneralDocument($this->get('service_container'));
        $results = $this->container->get('search.block')->getRecent($module, $paramFetcher->get('quantity', true));

        $data = $generalDocument->getElasticResultsData($results) ?: [];

        /* ModStores Hooks */
        HookFire("api_before_returnrecent", [
            "data" => &$data
        ]);

        return ['data' => $data];
    }

    /**
     * @Get("/{module}/popular", methods={"GET"}, requirements={"module"="article|blog|deal|event"})
     * @ApiDoc(
     *     resource= true,
     *     description = "Get popular item from a respective module",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Returned popular items",
     *       Codes::HTTP_FORBIDDEN = "The module is not available",
     *     },
     * )
     *
     * @QueryParam(name="quantity", requirements="\d+", description="Quantity of items", allowBlank=true, default="4")
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     * @return array
     * @throws \Exception
     * @View(serializerGroups={"Result"})
     */
    public function getPopularAction(ParamFetcher $paramFetcher, Request $request)
    {
        $module = $request->get('module');
        if (!$this->container->get('modules')->isModuleAvailable($module)) {
            throw new HttpException(Codes::HTTP_FORBIDDEN, sprintf('The %s module is not available', ucfirst($module)));
        }

        $generalDocument = new GeneralDocument($this->get('service_container'));
        $results = $this->container->get('search.block')->getPopular($module, $paramFetcher->get('quantity', true));

        $data = $generalDocument->getElasticResultsData($results);

        /* ModStores Hooks */
        HookFire("api_before_returnrpopular", [
            "data" => &$data
        ]);

        return ['data' => $data];
    }

    /**
     * @Get("/{module}/featured", methods={"GET"}, requirements={"module"="classified|event|listing"})
     * @ApiDoc(
     *     resource= true,
     *     description = "Get featured item from a respective module",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Returned featured items",
     *       Codes::HTTP_FORBIDDEN = "The module is not available",
     *     },
     * )
     *
     * @QueryParam(name="quantity", requirements="\d+", description="Quantity of items", allowBlank=true, default="4")
     *
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     * @return array
     * @throws \Exception
     * @View(serializerGroups={"Result"})
     */
    public function getFeaturedAction(ParamFetcher $paramFetcher, Request $request)
    {
        $module = $request->get('module');
        if (!$this->container->get('modules')->isModuleAvailable($module)) {
            throw new HttpException(Codes::HTTP_FORBIDDEN, sprintf('The %s module is not available', ucfirst($module)));
        }

        $generalDocument = new GeneralDocument($this->get('service_container'));
        $results = $this->container->get('search.block')->getFeatured($module, $paramFetcher->get('quantity', true));

        $data = $generalDocument->getElasticResultsData($results);

        /* ModStores Hooks */
        HookFire("api_before_returnrfeatured", [
            "data" => &$data
        ]);

        return ['data' => $data];
    }

    /**
     * @Get("/{module}/categories/featured", methods={"GET"}, requirements={"module"="classified|event|listing|blog|deal|article"})
     * @ApiDoc(
     *     resource= true,
     *     description = "Get featured categories from a respective module",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Returned featured categories",
     *       Codes::HTTP_FORBIDDEN = "The module is not available",
     *     }
     * )
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     *
     * @View(serializerGroups={"featuredCategory"})
     */
    public function getFeaturedCategoryAction(Request $request)
    {
        $module = $request->get('module');
        if (!$this->container->get('modules')->isModuleAvailable($module)) {
            throw new HttpException(Codes::HTTP_FORBIDDEN, sprintf('The %s module is not available', ucfirst($module)));
        }

        $categoriesFeatured = $this->container->get('search.repository.category')->findCategoriesWithItens($module,
            true);
        $categoriesEnabled = $this->container->get('search.repository.category')->findCategoriesWithItens($module);

        $generalDocument = new GeneralDocument($this->container);

        /* adds image url */
        /* @var Category $category */
        foreach ($categoriesFeatured as &$category) {
            $category->setId((int)substr($category->getId(), 2));
            $category->setThumbnail($generalDocument->getImagePathByName($category->getThumbnail()));
        }

        $featuredCount = count($categoriesFeatured);
        $categoriesCount = count($categoriesEnabled);

        return [
            'data'            => array_values($categoriesFeatured),
            'more_categories' => (($categoriesCount - $featuredCount) > 0),
        ];
    }

    /**
     * @Get("/{module}/categories", methods={"GET"}, requirements={"module"="classified|event|listing|blog|deal|article"})
     * @ApiDoc(
     *     resource= true,
     *     description = "Get categories from a respective module",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Returned categories",
     *       Codes::HTTP_FORBIDDEN = "The module is not available",
     *     }
     * )
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     *
     * @View(serializerGroups={"API", "API_WITH_CHILDREN"})
     */
    public function getCategoriesAction(Request $request)
    {
        $module = $request->get('module');
        if (!$this->container->get('modules')->isModuleAvailable($module)) {
            throw new HttpException(Codes::HTTP_FORBIDDEN, sprintf('The %s module is not available', ucfirst($module)));
        }

        $categoryHelper = new CategoryHelper($this->getDoctrine());

        $repository = $categoryHelper::getRepositoryNameByModule($module);

        /* @var $categories ListingCategory|Classifiedcategory|Articlecategory|BlogCategory1|Eventcategory */
        $categories = $this->getDoctrine()->getRepository($repository)->getAllParent();

        return ['data' => $categories];
    }

    /**
     * @Get("/{module}/categories/count", methods={"GET"}, requirements={"module"="classified|event|listing|blog|deal|article"})
     * @ApiDoc(
     *     resource= true,
     *     description = "Get the count of categories from a respective module",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Returned the count categories",
     *       Codes::HTTP_FORBIDDEN = "The module is not available",
     *     }
     * )
     *
     * @param Request $request
     * @return array
     * @throws \Exception
     *
     * @View(serializerGroups={"API", "API_WITH_CHILDREN"})
     */
    public function getCountCategoriesAction(Request $request)
    {
        $module = $request->get('module');
        if (!$this->container->get('modules')->isModuleAvailable($module)) {
            throw new HttpException(Codes::HTTP_FORBIDDEN, sprintf('The %s module is not available', ucfirst($module)));
        }

        $categoryHelper = new CategoryHelper($this->getDoctrine());

        $repository = $categoryHelper::getRepositoryNameByModule($module);

        /* @var $categories ListingCategory|Classifiedcategory|Articlecategory|BlogCategory1|Eventcategory */
        $total = $this->getDoctrine()->getRepository($repository)->getCategoriesCount();
        $featured = $this->getDoctrine()->getRepository($repository)->getCategoriesFeaturedCount();

        return [
            'data' => [
                'total'    => $total,
                'featured' => $featured,
            ],
        ];
    }

    /**
     * Create a Account from the submitted data.
     *
     * @ApiDoc(
     *     resource = true,
     *     description = "Creates a new account from the submitted data",
     *     input = "ArcaSolutions\WebBundle\Form\Type\AccountType",
     *     statusCodes = {
     *       200 = "Returned when successful",
     *       400 = "Returned when the form has errors",
     *       409 = "Returned when the account has errors: eg. email already registered"
     *     }
     * )
     *
     * @View(
     *     templateVar = "form"
     * )
     *
     * @param Request $request
     * @return array
     * @throws \ArcaSolutions\CoreBundle\Exception\EmailNotificationServicesException
     */
    public function postAccountAction(Request $request)
    {
        try {
            /** @var Account $newAccount */
            $accountHandler = $this->get('account.handler')
                ->setEmailNotification($this->get('email.notification.service'));

            $newAccount = $accountHandler->post($request);

            $routeOptions = [
                'username' => $newAccount->getUsername(),
                'password' => $request->get('password'),
            ];

            return $this->get('account.handler')->login($routeOptions);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        } catch (DBALException $exception) {
            throw new HttpException(
                Codes::HTTP_CONFLICT,
                $this->get('translator')->trans('Email already registered.'),
                $exception
            );
        }
    }

    /**
     *
     * @ApiDoc(
     *     resource = true,
     *     description = "Login a user",
     *     input = "ArcaSolutions\WebBundle\Form\Type\LoginType",
     *     statusCodes = {
     *       200 = "Returned when successful",
     *       400 = "Returned when the form has errors",
     *       401 = "Returned when the information is wrong"
     *     }
     * )
     *
     * @Post("/login")
     * @View(
     *     templateVar = "form"
     * )
     *
     * @param Request $request
     * @return array
     */
    public function postLoginAction(Request $request)
    {
        try {
            return $this->get('account.handler')
                ->setEmailNotification($this->get('email.notification.service'))
                ->login($request);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        } catch (UnauthorizedHttpException $exception) {
            throw new UnauthorizedHttpException(null, $exception->getMessage());
        }
    }

    /**
     * @ApiDoc(
     *     resource = true,
     *     description = "Login a user with a social network",
     *     input = "ArcaSolutions\ApiBundle\Form\Type\SocialType",
     *     statusCodes = {
     *       200 = "Returned when successful",
     *       400 = "Returned when the form has errors",
     *       401 = "Returned when the information is wrong"
     *     }
     * )
     *
     * @Post("/login/social")
     * @View()
     *
     * @param Request $request
     * @return null
     * @throws \ArcaSolutions\CoreBundle\Exception\EmailNotificationServicesException
     * @throws \Twig_Error_Runtime
     */
    public function postSocialLoginAction(Request $request)
    {
        try {
            return $this->get('account.handler')
                ->setEmailNotification($this->get('email.notification.service'))
                ->loginSocial($request);
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        } catch (ProviderNotFoundException $exception) {
            throw new  HttpException(Codes::HTTP_UNAUTHORIZED, $exception->getMessage());
        }
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Gets the items which were bookmarked",
     *     statusCodes={
     *       Codes::HTTP_OK = "Returns all data",
     *       Codes::HTTP_NOT_FOUND = "Account not found"
     *     }
     * )
     * @ParamConverter("accountId", class="WebBundle:Accountprofilecontact")
     *
     * @View(serializerGroups={"Result"})
     * @param Accountprofilecontact $accountId
     * @return array
     * @throws \Exception
     */
    public function getFavoriteAction(Accountprofilecontact $accountId)
    {
        $quicklist = $this->get('quicklist.handler')->getAllItemsByAccountId($accountId);

        $generalDocument = new GeneralDocument($this->container);
        $favoriteList = [];
        foreach ($quicklist as $key => $items) {
            $itemDetail = '';
            switch ($key) {
                case 'listing':
                    $itemDetail = 'ArcaSolutions\ListingBundle\ListingItemDetail';
                    break;
                case 'article':
                    /* the level does not control it */
                    $itemDetail = '';
                    break;
                case 'classified':
                    $itemDetail = 'ArcaSolutions\ClassifiedBundle\ClassifiedItemDetail';
                    break;
                case 'event':
                    $itemDetail = 'ArcaSolutions\EventBundle\EventItemDetail';
                    break;
            }
            foreach ($items as $item) {
                /* articles doesn't have itemDetail */
                if ($key !== 'article') {
                    $itemDetailObject = new $itemDetail($this->container, $item);
                    if ($itemDetailObject->getLevel()->imageCount > 0) {
                        /* events, classifieds and listings reach this, but listing has another function for image */
                        if ($key === 'listing') {
                            $image = $item->getMainImage();
                            $item->cleanClassifieds(true);
                        } else {
                            $image = $item->getImage();
                        }
                        $item->setImageUrl($generalDocument->getImagePath($image));
                    }
                } else {
                    $item->setImageUrl($generalDocument->getImagePath($item->getImage()));
                }

                if ($key === 'event') {
                    /* set the recurring date of the event */
                    if ($item->getRecurring() === 'Y') {
                        $recurring = new RecurringExtension($this->container);
                        $item->setRecurringPhrase($recurring->recurringPhrase($item));

                        $generalDocument->setStartDateToNextOccurrence($item);
                    }
                }

                $favoriteList[] = $item;
            }

        }

        return ['data' => $favoriteList];
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Saves the items which were bookmarked",
     *     statusCodes={
     *       Codes::HTTP_OK = "Save item",
     *       Codes::HTTP_NOT_ACCEPTABLE = "Error in save the item"
     *     },
     *     output="ArcaSolutions\WebBundle\Entity\Quicklist",
     * )
     * @ParamConverter("accountId", class="WebBundle:Accountprofilecontact")
     * @RequestParam(name="module", requirements="^(listing|article|classified|event|deal)$", description="Item's type", allowBlank=false)
     * @RequestParam(name="itemId", requirements="\d+", description="The item Id", allowBlank=false)
     *
     * @param Accountprofilecontact $accountId
     * @param ParamFetcher $paramFetcher
     * @return array
     */
    public function postFavoriteAction(Accountprofilecontact $accountId, ParamFetcher $paramFetcher)
    {
        try {
            $quicklist = $this->get('quicklist.handler')->saveItem(
                $accountId,
                $paramFetcher->get('module', true),
                $paramFetcher->get('itemId', true)
            );

            return [
                'data' => $quicklist,
            ];
        } catch (\Exception $e) {
            throw new HttpException(Codes::HTTP_NOT_ACCEPTABLE, $e->getMessage());
        }
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Deletes the items which were bookmarked",
     *     method = "DELETE",
     *     statusCodes={
     *       Codes::HTTP_OK = "Delete bookmark, and return the account",
     *       Codes::HTTP_NOT_ACCEPTABLE = "Error in remove the bookmark"
     *     }
     * )
     * @ParamConverter("accountId", class="WebBundle:Accountprofilecontact")
     * @RequestParam(name="favoriteIds", requirements="((\d+)((,+\d+)+))|(\d+)", description="The array of favorite ids", allowBlank=false)
     *
     * @param Accountprofilecontact $accountId
     * @param ParamFetcher $paramFetcher
     * @return array
     */
    public function deleteFavoriteAction(Accountprofilecontact $accountId, ParamFetcher $paramFetcher)
    {
        $favoriteIds = explode(',', $paramFetcher->get('favoriteIds', true));
        try {
            foreach ($favoriteIds as $favoriteId) {
                $account = $this->get('quicklist.handler')->deleteItemById(
                    $accountId,
                    $favoriteId
                );
            }
        } catch (\Exception $e) {
            throw new HttpException(Codes::HTTP_NOT_ACCEPTABLE, $e->getMessage());
        }

        return [
            'data' => $account,
        ];
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Gets redeemed items",
     *     statusCodes={
     *       Codes::HTTP_NO_CONTENT = "Gets redeems",
     *       Codes::HTTP_INTERNAL_SERVER_ERROR = "Error in get the redeems"
     *     },
     *     output={
     *       "class"="ArcaSolutions\DealBundle\Entity\PromotionRedeem",
     *       "groups"={"API"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     * )
     * @ParamConverter("accountId", class="WebBundle:Accountprofilecontact")
     *
     * @param Accountprofilecontact $accountId
     * @return array
     *
     * @throws \Doctrine\ORM\ORMException
     * @View(serializerGroups={"API", "dealRedeem"})
     */
    public function getDealsRedeemAction(Accountprofilecontact $accountId)
    {
        $redeems = $this->getDoctrine()->getRepository('DealBundle:PromotionRedeem')->findByAccountId(
            $accountId->getAccountId()
        );

        $generalDocument = new GeneralDocument($this->container);

        /* @var $redeem PromotionRedeem */
        foreach ($redeems as &$redeem) {
            /* @var $deal Promotion */
            $deal = $redeem->getDeal();
            $deal->setImageUrl($generalDocument->getImagePath($deal->getMainImage()));
            $redeem->setDeal($deal);
        }

        return [
            'data' => $redeems,
        ];
    }

    /**
     * @Post("/deals/{accountId}/redeem", methods={"POST"})
     * It was put the @ post because the routing was not same as above. It was putting a 's' at the end
     * @ApiDoc(
     *     resource=true,
     *     description="Save redeem",
     *     statusCodes={
     *       Codes::HTTP_NO_CONTENT = "Saved",
     *       Codes::HTTP_INTERNAL_SERVER_ERROR = "Error in save the redeems"
     *     },
     *     output={
     *       "class"="ArcaSolutions\DealBundle\Entity\PromotionRedeem",
     *       "groups"={"API"},
     *       "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *     },
     * )
     *
     * @RequestParam(name="itemId", requirements="\d+", description="The item Id", allowBlank=false)
     *
     * @param $accountId
     * @param ParamFetcher $paramFetcher
     * @return array
     * @throws \Exception
     * @View(serializerGroups={"API", "Result", "DetailRedeem"})
     */
    public function postDealsRedeemAction($accountId, ParamFetcher $paramFetcher)
    {
        $deal = $this->get('doctrine')->getRepository('DealBundle:Promotion')->find($paramFetcher->get('itemId', true));

        $contact = $this->get('doctrine')->getRepository('CoreBundle:Contact',
            'main')->findOneBy(['account' => $accountId]);

        $redeem = $this->get('redeem.handler')->makeRedeem($deal, $contact);

        return ['data' => $redeem];
    }

    /**
     * @ApiDoc(
     *     resource=true,
     *     description="Send a email to the user with a link to change his password",
     *     statusCodes={
     *       Codes::HTTP_OK = "Email Sent with change password link",
     *       Codes::HTTP_NOT_ACCEPTABLE = "Error in save the item"
     *     },
     *     output="ArcaSolutions\CoreBundle\Entity\ForgotPassword",
     * )
     *
     * @RequestParam(name="username", requirements="string", description="The username used for login", allowBlank=false)
     *
     * @param Request $request
     * @return array|EntityNotFoundException|\Exception
     * @throws \Exception
     */
    public function postForgotpasswordAction(Request $request)
    {
        return $this->get('account.handler')
            ->setEmailNotification($this->get('email.notification.service'))
            ->forgotPassword($request);
    }

    /**
     * @Post("/{module}/sendmail", methods={"POST"}, requirements={"module"="listing|classified|event"})
     * @ApiDoc(
     *     resource= true,
     *     description = "Send an Email",
     *     method = "POST",
     *     input="ArcaSolutions\WebBundle\Form\Type\SendMailType",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Email sent",
     *       Codes::HTTP_BAD_REQUEST = {"Error in form", "It was not possible send the email."}
     *     }
     * )
     *
     * @RequestParam(name="itemId", requirements="\d+", description="The item Id", allowBlank=false)
     * @param ParamFetcher $paramFetcher
     * @param Request $request
     * @return array|\Symfony\Component\Form\Form
     */
    public function postSendmailAction(ParamFetcher $paramFetcher, Request $request)
    {
        $doctrine = $this->get('doctrine');
        $itemId = $paramFetcher->get('itemId', true);

        switch ($request->get('module')) {
            case 'listing':
                $item = $doctrine->getRepository('ListingBundle:Listing')->find($itemId);
                break;
            case 'event':
                $item = $doctrine->getRepository('EventBundle:Event')->find($itemId);
                break;
            case 'classified':
                $item = $doctrine->getRepository('ClassifiedBundle:Classified')->find($itemId);
                break;
        }

        if (!$item) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST, 'Item does not exist.');
        }

        /* creates form following reviews form */
        $form = $this->createForm(new SendMailType());
        /* populate form using parameters */
        /* It makes the api accept the fields without it be an array */
        $requestAll = $request->request->all();
        /* It should send just the form's fields */
        unset($requestAll['itemId']);
        $form->submit($requestAll);

        if ($form->isValid()) {
            try {

                $send = $this->get('sendmail.module')->send($item, $form);

                if (!$send) {
                    throw new HttpException(Codes::HTTP_BAD_REQUEST, 'It was not possible send the email.');
                }

                return [
                    'message' => $this->get('translator')->trans('Your e-mail has been sent. Thank you.'),
                ];
            } catch (\Exception $e) {
                /* usually database error */
                throw new HttpException(Codes::HTTP_BAD_REQUEST, sprintf('An error occurred: %s', $e->getMessage()));
            }
        }

        /* returns form error */

        return $form;
    }

    /**
     * @ApiDoc(
     *     resource= true,
     *     description = "Get Upcoming Events",
     *     method = "GET",
     *     statusCodes = {
     *       Codes::HTTP_OK = "Events",
     *     },
     * )
     *
     * @View(serializerGroups={"Result", "EventWithDay"})
     * @return array
     * @throws \Exception
     */
    public function getUpcomingeventsAction()
    {
        $generalDocument = new GeneralDocument($this->container);

        $startDate = new \DateTime(date('Y-m-d'));
        $startDate->setTime(00, 00, 00);

        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P30D'))->setTime(23, 59, 59);

        $events = $this->get('event.recurring.service')->getUpcomingEvents($startDate, $endDate, true);

        $recurringService = $this->get('event.recurring.service');

        foreach ($events as $event) {
            $eventStartDate = $event->getStartDate();
            $eventUntilDate = $event->getUntilDate();

            if ($event->getRecurring() === 'Y') {
                $eventStartDate = $recurringService->getNextOccurrence(
                    $eventStartDate, $eventUntilDate, $recurringService->getRRule_rfc2445($event)
                );
            }

            $eventItemDetail = new EventItemDetail($this->container, $event);

            $event->setGalleryAPI($generalDocument->getGallery($event, $eventItemDetail));
            $event->setImageUrl($generalDocument->getImagePath($event->getImage()));
            $event->setAddress($generalDocument->getItemLocationsImploded($event));
            $event->setStartDate($eventStartDate);
            $generalDocument->setBadDateValueToNull($event);
        }

        /* ModStores Hooks */
        HookFire("api_before_returnrupcomingevents", [
            "events" => &$events
        ]);

        return ['data' => $events];
    }
}
