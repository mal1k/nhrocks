<?php

namespace ArcaSolutions\SearchBundle\Controller;

use ArcaSolutions\ArticleBundle\Search\ArticleConfiguration;
use ArcaSolutions\BlogBundle\Search\BlogConfiguration;
use ArcaSolutions\ClassifiedBundle\Entity\Internal\ClassifiedLevelFeatures;
use ArcaSolutions\ClassifiedBundle\Search\ClassifiedConfiguration;
use ArcaSolutions\CoreBundle\Exception\ItemNotFoundException;
use ArcaSolutions\CoreBundle\Form\Type\CaptchaType;
use ArcaSolutions\CoreBundle\Services\Utility;
use ArcaSolutions\DealBundle\Search\DealConfiguration;
use ArcaSolutions\ElasticsearchBundle\Elastica\Suggest\Completion;
use ArcaSolutions\ElasticsearchBundle\Elastica\Suggest\Context;
use ArcaSolutions\EventBundle\Entity\Internal\EventLevelFeatures;
use ArcaSolutions\EventBundle\Search\EventConfiguration;
use ArcaSolutions\ListingBundle\Entity\Internal\ListingLevelFeatures;
use ArcaSolutions\ListingBundle\Search\ListingConfiguration;
use ArcaSolutions\ReportsBundle\Services\ReportHandler;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Category;
use ArcaSolutions\SearchBundle\Entity\Elasticsearch\Location;
use ArcaSolutions\SearchBundle\Entity\Filters\CategoryFilter;
use ArcaSolutions\SearchBundle\Entity\Filters\LocationFilter;
use ArcaSolutions\SearchBundle\Entity\Summary\SummaryTitle;
use ArcaSolutions\SearchBundle\Events\SearchEvent;
use ArcaSolutions\SearchBundle\Services\ParameterHandler;
use ArcaSolutions\WebBundle\Form\Type\ReviewsType;
use ArcaSolutions\WebBundle\Form\Type\SendMailType;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Entity\Widget;
use Elastica\Exception\ElasticsearchException;
use Elastica\Query;
use Elastica\Search;
use Elastica\Suggest;
use Ivory\GoogleMap\Exception\Exception;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @param $page
     * @return Response
     * @throws ElasticsearchException
     */
    public function searchAction($page)
    {
        $searchEngine = $this->get('search.engine');
        $parameterHandler = $this->get('search.parameters');
        $reportHandler = $this->get('reporthandler');
        $JSHandler = $this->get('javascripthandler');
        $settings = $this->container->get('settings');

        /* ModStores Hooks */
        HookFire("search_init_controller", [
            "that" => &$this,
        ]);

        $JSHandler->addTwigParameter('geolocationCookieName', $searchEngine->getGeoLocationCookieName());

        $page = $searchEngine->convertFromPaginationFormat($page);

        $keyword = implode(' ', $parameterHandler->getKeywords());
        $where = implode(' ', $parameterHandler->getWheres());

        /* ModStores Hooks */
        HookFire("search_before_after_searchparams", [
            "where"   => &$where,
            "keyword" => &$keyword
        ]);

        if ($keyword || $where) {
            $modules = $parameterHandler->hasModules() ? $parameterHandler->getModules() : ['global'];

            foreach ($modules as $module) {
                $reportHandler->addKeywordSearchReport($reportHandler->getReportModule($module), $keyword, $where);
            }
        }

        /* Returns a SearchEvent instance filled with information collected by a search.global event cast */
        /* The Global search event will collect query and filter information for all available modules */
        $searchEvent = $searchEngine->globalSearch($keyword, $where);

        /* Retrieves the Elastica Search Query assembled with information retrieved by the SearchEvent instance */
        $search = $searchEngine->search($searchEvent);

        $pageObject = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page')->getPageByType(PageType::RESULTS_PAGE);

        $searchConfig = $this->container->getParameter('search.config');

        $settingsResultSize = $settings->getDomainSetting('result_size');

        if (empty($settingsResultSize)) {
            $theme = $this->container->get('theme.service')->getSelectedTheme();
            $resultsWidget = $this->get('doctrine')->getRepository('WysiwygBundle:PageWidget')->getPageWidgetByWidgetName($pageObject->getId(),
                Widget::RESULTS, $theme->getId());

            $resultContent = json_decode($resultsWidget->getContent(), true);

            $settingsResultSize = $resultContent['resultView'] === 'list' ? 'defaultSearchResultSize' : 'defaultSearchResultGridSize';

            $settings->setSetting('result_size', $settingsResultSize);
        }

        $resultsPerPage = $searchConfig['settings'][$settingsResultSize];

        if(($modules = $parameterHandler->getModules()) && \count($modules) === 1) {
            $this->container->get('widget.service')->setModuleSearch(reset($modules));
        }

        /* ElasticSearch only supports 10000 results */
        if($page * $resultsPerPage > 10000 || $search->count() === 0) {
            return $this->render('::results.html.twig', [
                'pageId'    => $pageObject->getId(),
                'customTag' => $pageObject->getCustomTag(),
            ]);
        }


        /* Retrieves information which will be used while rendering the current page */
        /* @var SlidingPagination $pagination */
        $pagination = $this->get('knp_paginator')->paginate($search, $page, $resultsPerPage);

        /* ModStore Hooks */
        HookFire("search_after_searchpagination", [
            "pagination" => &$pagination
        ]);

        /* Processes results aggregations in order to show while rendering the filters */
        $searchEvent->processAggregationResults($pagination);

        /* Sets module level information to be used while rendering the summary templates */
        $levels = $searchEvent->getModuleLevelFeatures();

        /* Adds the required Javascript to enable module results interaction */
        foreach ($searchEvent->getResultsJSTwigs() as $pathToTwig) {
            $JSHandler->addJSBlock($pathToTwig);
        }

        /* Prepares information for SEO pagination meta tags */
        $parameterHandlerCanonical = clone $parameterHandler;
        $parameterHandlerCanonical->clearAllQueryParameters();
        $previousPage = $page > 1 ? $parameterHandler->buildUrl($page - 1) : null;
        $nextPage = $page < $pagination->getPageCount() ? $parameterHandler->buildUrl($page + 1) : null;

        $map = $pagination->getTotalItemCount() < 1000 ? $searchEngine->buildMap($search) : null;

        $summaryTitle = SummaryTitle::extract($parameterHandler, $this->container);

        /*
         * Report Summary
         */
        $pagination->getItems() and $this->get('reporthandler')->addSummaryReport($pagination->getItems());

        $userId = $this->container->get('request')->getSession()->get('SESS_ACCOUNT_ID');
        $memberAccount = null;

        if($userId) {
            $memberAccount = $this->container->get('doctrine')->getRepository('WebBundle:Accountprofilecontact')->find($userId);
        }

        $formSendMail = $this->createForm(new SendMailType(), null, ['member' => $memberAccount]);

        $formReview = $this->createForm(ReviewsType::class, null, ['member' => $memberAccount ? true : false]);

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
            $formReview->add('reviewCaptcha', CaptchaType::class, $options);
        }

        $twig = $this->container->get('twig');

        /* ModStores Hooks */
        HookFire("search_before_add_globalvars", [
            "that"        => &$this,
            "searchEvent" => &$searchEvent,
            "pagination"  => &$pagination,
        ]);

        $twig->addGlobal('isDistanceSorterEnabled', ($parameterHandler->getSort() === 'distance' ? true : false));
        $twig->addGlobal('previousPage', $previousPage);
        $twig->addGlobal('nextPage', $nextPage);
        $twig->addGlobal('canonical', $parameterHandlerCanonical->buildUrl());
        $twig->addGlobal('levels', $levels);
        $twig->addGlobal('pagination', $pagination);
        $twig->addGlobal('searchEvent', $searchEvent);
        $twig->addGlobal('map', $map);
        $twig->addGlobal('summaryTitle', $summaryTitle);
        $twig->addGlobal('dateFilter', $this->get('filter.date'));
        $formReview and $twig->addGlobal('formReview', $formReview->createView());
        $formSendMail and $twig->addGlobal('formSendMail', $formSendMail->createView());

        /* ModStores Hooks */
        HookFire("search_before_render", [
            "that"       => &$this,
            "pageObject" => &$pageObject,
        ]);

        return $this->render('::results.html.twig', [
            'pageId'    => $pageObject->getId(),
            'customTag' => $pageObject->getCustomTag(),
        ]);
    }

    public function suggestCardItemAction()
    {
        $response = [];

        if ($input = addslashes($this->get('request')->query->get('key'))) {
            $suggestionName = 'search';

            $searchEngine = $this->get('search.engine');

            $elasticaClient = $searchEngine->getElasticaClient();
            $indexName = $this->get('search.engine')->getElasticIndexName();
            $elasticaIndex = $elasticaClient->getIndex($indexName);

            $suggest = new Suggest();

            $module = $this->get('request')->query->get('module') === 'promotion' ? 'deal' : $this->get('request')->query->get('module');

            $suggestion = new Completion($suggestionName, 'suggest.' . $module);
            $suggestion->setText($input);
            $suggest->addSuggestion($suggestion);

            $result = $elasticaIndex->search($suggest);

            if ($matches = $result->getSuggests()) {
                foreach ($matches[$suggestionName] as $match) {
                    $response = $match;
                }
            }
        }

        return new JsonResponse($response);
    }

    public function suggestWhatAction()
    {
        $response = [];

        if ($input = addslashes($this->get('request')->query->get('key'))) {
            $suggestionName = 'search';

            $searchEngine = $this->get('search.engine');

            $elasticaClient = $searchEngine->getElasticaClient();
            $indexName = $this->get('search.engine')->getElasticIndexName();
            $elasticaIndex = $elasticaClient->getIndex($indexName);

            $suggest = new Suggest();
            $context = new Context();
            $modules = [];

            switch ($this->get('request')->query->get('module')) {
                case 'blog' :
                    $modules['blog'] = 'blog';
                    break;
                default :
                    $modules = $searchEngine->getActiveModules();

                    if(isset($modules['promotion'])) {
                        $val = $modules['promotion'];
                        unset($modules['promotion']);
                        $modules['deal'] = $val;
                    }

                    break;
            }

            $context->setParam('module', array_keys($modules));

            $suggestion = new Completion($suggestionName, 'suggest.what');
            $suggestion->setText($input);
            $suggestion->addContext($context);
            $suggest->addSuggestion($suggestion);

            $result = $elasticaIndex->search($suggest);

            if ($matches = $result->getSuggests()) {
                foreach ($matches[$suggestionName] as $match) {
                    $response = $match;
                }
            }
        }

        return new JsonResponse($response);
    }

    public function suggestWhereAction()
    {
        $response = [];

        if ($input = addslashes($this->get('request')->query->get('key'))) {
            $suggestionName = 'search';

            $searchEngine = $this->get('search.engine');

            $elasticaClient = $searchEngine->getElasticaClient();
            $indexName = $this->get('search.engine')->getElasticIndexName();
            $elasticaIndex = $elasticaClient->getIndex($indexName);

            $suggest = new Suggest();
            $suggestion = new Suggest\Completion($suggestionName, 'suggest.where');
            $suggestion->setText($input);

            $suggest->addSuggestion($suggestion);

            $result = $elasticaIndex->search($suggest);

            if ($matches = $result->getSuggests()) {
                foreach ($matches[$suggestionName] as $match) {
                    $response = $match;
                }
            }
        }

        return new JsonResponse($response);
    }

    public function advancedCategoryAction(Request $request)
    {
        $searchEngine = $this->get('search.engine');
        $eventDispatcher = $this->get('event_dispatcher');

        $keyword = null;

        if ($locationFriendlyUrl = $request->get('data')) {
            if ($results = $searchEngine->locationFriendlyURLSearch($locationFriendlyUrl)) {
                /* @var $location Location */
                $location = array_pop($results);

                $keyword = $location->getId();
            }
        }

        $event = new SearchEvent($keyword);
        $eventDispatcher->dispatch('search.suggest.category', $event);

        $results = $searchEngine->search($event)->search()->getAggregations();

        $categoryDocumentCount = [];

        foreach ($results[CategoryFilter::getName()]['buckets'] as $result) {
            $categoryDocumentCount[$result['key']] = $result['doc_count'];
        }

        if ($categoryDocumentCount) {
            $categoryInfo = $searchEngine->categoryIdSearch(array_keys($categoryDocumentCount));
        } else {
            $categoryInfo = $searchEngine->getAllCategories();
        }

        $moduleCategories = [];
        foreach ($categoryInfo as $category) {
            if ($category->getParentId() == null) {
                $moduleCategories[$category->getModule()][$category->getId()] = [
                    'item'          => $category,
                    'documentCount' => !empty($categoryDocumentCount[$category->getId()]) ? $categoryDocumentCount[$category->getId()] : 0,
                ];
            }
        }

        /* Sorts each module's categories in order to show larger ones first */
        foreach ($moduleCategories as &$category) {
            uasort($category, function ($a, $b) {
                return $a['documentCount'] < $b['documentCount'];
            });
        }

        return $this->render('::blocks/search/advanced-category.html.twig', [
            'modules' => $moduleCategories,
        ]);
    }

    public function advancedLocationAction()
    {
        $response = null;

        $locationDocumentCount = [];
        $locationInfo = [];
        $moduleLocations = [];

        $searchEngine = $this->get('search.engine');
        $eventDispatcher = $this->get('event_dispatcher');
        $request = $this->get('request');

        $keyword = null;

        if ($categoryFriendlyUrl = $request->get('data')) {

            if ($results = $searchEngine->categoryFriendlyURLSearch($categoryFriendlyUrl)) {
                /* @var $category Category */
                $category = array_pop($results);

                $keyword = $category->getId();
            }
        }

        if ($keyword) {

            $event = new SearchEvent($keyword);
            $eventDispatcher->dispatch('search.suggest.location', $event);

            $results = $searchEngine->search($event)->search()->getAggregations();

            foreach ($results[LocationFilter::getName()]['buckets'] as $result) {
                $locationDocumentCount[$result['key']] = $result['doc_count'];
            }

            if ($locationDocumentCount) {
                $locationInfo = $searchEngine->locationIdSearch(array_keys($locationDocumentCount));
            }

        } else {
            $locationInfo = $searchEngine->getAllLocations(1);
        }

        foreach ($locationInfo as $location) {

            switch ($location->getLevel()) {
                case 1:
                    $label = 'country';
                    break;
                case 2:
                    $label = 'region';
                    break;
                case 3:
                    $label = 'state';
                    break;
                case 4:
                    $label = 'city';
                    break;
                case 5:
                    $label = 'neighborhood';
                    break;
                default:
                    $label = 'location';
                    break;
            }

            $moduleLocations[$label][$location->getId()] = [
                'item'          => $location,
                'documentCount' => empty($locationDocumentCount[$location->getId()]) ? 0 : $locationDocumentCount[$location->getId()],
            ];
        }

        foreach ($moduleLocations as &$location) {
            uasort($location, function ($a, $b) {
                return $a['documentCount'] < $b['documentCount'];
            });
        }


        $response = $this->render(
            '::blocks/search/advanced-location.html.twig',
            ['locations' => $moduleLocations,]
        );

        return $response;
    }

    public function buildUrlAction()
    {
        $data = [
            'status' => false,
        ];

        $request = $this->get('request_stack')->getCurrentRequest();
        $parameters = new ParameterHandler($this->container, false);

        /* These are all available parameters for this action */

        /* These will lead to the results page if present */
        $module = $request->get('module');
        $location = $request->get('location');
        $category = $request->get('category');
        $keyword = $request->get('keyword');
        $where = $request->get('where');
        $startDate = $request->get('startDate');

        /* These will lead to the detail page if present */
        $item = $request->get('item');
        $itemType = $request->get('itemtype');

        if ($location || $category || $keyword || $startDate || $where) {
            $overrideParameters = [];

            $location and $overrideParameters[ParameterHandler::SLUG_LOCATION] = $location;
            $category and $overrideParameters[ParameterHandler::SLUG_CATEGORY] = $category;
            $keyword and $parameters->addKeyword($keyword);
            $where and $parameters->addWhere($where);

            /* Date filters will enforce Event module. If categories are present, no module filter can be applied
             * since it's not guaranteed the category will belong to the selected module. */
            if ($startDate) {
                $parameters->addModule(ParameterHandler::MODULE_EVENT);
                $overrideParameters[ParameterHandler::SLUG_STARTDATE] = $startDate;
            } elseif (!$category and $module) {
                $parameters->addModule($module);
            }

            $data = [
                'status' => true,
                'url'    => $parameters->buildUrl(1, $overrideParameters),
            ];
        } elseif ($item and $itemType) {

            switch ($itemType) {
                case 'article':
                    $type = ArticleConfiguration::$elasticType;
                    $route = 'article_detail';
                    $repository = 'ArticleBundle:Article';
                    break;
                case 'blog':
                    $type = BlogConfiguration::$elasticType;
                    $route = 'blog_detail';
                    $repository = 'BlogBundle:Post';
                    break;
                case 'classified':
                    $type = ClassifiedConfiguration::$elasticType;
                    $route = 'classified_detail';
                    $repository = 'ClassifiedBundle:Classified';
                    break;
                case 'deal':
                    $type = DealConfiguration::$elasticType;
                    $route = 'deal_detail';
                    $repository = 'DealBundle:Promotion';
                    break;
                case 'event':
                    $type = EventConfiguration::$elasticType;
                    $route = 'event_detail';
                    $repository = 'EventBundle:Event';
                    break;
                case 'listing':
                    $type = ListingConfiguration::$elasticType;
                    $route = 'listing_detail';
                    $repository = 'ListingBundle:Listing';
                    break;
                default:
                    $type = null;
                    $route = '';
                    $repository = null;
                    break;
            }

            if ($type) {
                if ($result = $this->get('search.engine')->itemFriendlyURL($item, $type, $repository)) {
                    $data = [
                        'status' => true,
                        'url'    => $this->get('router')->generate(
                            $route,
                            [
                                'friendlyUrl' => $result->getFriendlyUrl(),
                                '_format'     => 'html',
                            ]
                        ),
                    ];
                }
            }
        }

        $response = new JsonResponse($data);

        return $response;
    }

    public function mapSummaryAction()
    {
        $twig = $this->get('twig');
        $response = [];

        $data = $this->get('request_stack')->getCurrentRequest()->get('data');

        while (is_array($data) && $item = array_pop($data)) {
            $itemId = $item['item'];
            $type = $item['itemtype'];

            switch ($type) {
                case ClassifiedConfiguration::$elasticType :
                    $levels[ClassifiedConfiguration::$elasticType] = ClassifiedLevelFeatures::getAllLevelsAndNormalize($this->get('doctrine'));
                    break;
                case DealConfiguration::$elasticType :
                    $levels = true;
                    break;
                case EventConfiguration::$elasticType :
                    $levels[EventConfiguration::$elasticType] = EventLevelFeatures::getAllLevelsAndNormalize($this->get('doctrine'));
                    break;
                case ListingConfiguration::$elasticType :
                    $levels[ListingConfiguration::$elasticType] = ListingLevelFeatures::getAllLevelsAndNormalize($this->get('doctrine'));
                    break;
                default:
                    $levels = null;
                    break;
            }
            
		    $conn = $this->get('doctrine')->getEntityManager()
		    ->getConnection();
            
            	if($type == 'deal'){
            		$sql = 'SELECT Listing.map_info FROM Promotion LEFT JOIN Listing on Promotion.listing_id = Listing.id WHERE Promotion.id = ' . $itemId;
            	} else {
			$sql = 'SELECT * FROM Listing WHERE id = ' . $itemId;
		}
		$stmt = $conn->prepare($sql);
		$stmt->execute();
		$map_info = $stmt->fetch()['map_info'];
        	        	

            /* ModStores Hooks */
            HookFire("mapsummary_before_set_data", [
                "data" => &$item,
                "itemId" => &$itemId,
                "type" => &$type,
            ]);

            if ($itemId && $levels) {
                $searchEngine = $this->get('search.engine');

                $elasticaClient = $searchEngine->getElasticaClient();
                $indexName = $this->get('search.engine')->getElasticIndexName();

                $elasticaIndex = $elasticaClient->getIndex($indexName);
                $elasticaType = $elasticaIndex->getType($type);
                $item = $elasticaType->getDocument($itemId);
                
                //item->map_info = $map_info->map_info;
		
                $itemData = $item->getData();

                $categories = empty($itemData['categoryId']) ? [] : $searchEngine->categoryIdSearch(Utility::convertStringToArray($itemData['categoryId'],
                    ' '));
                $locations = empty($itemData['locationId']) ? [] : $searchEngine->locationIdSearch(Utility::convertStringToArray($itemData['locationId'],
                    ' '));
                $badges = empty($itemData['badgeId']) ? [] : $searchEngine->badgeIdSearch(Utility::convertStringToArray($itemData['badgeId'],
                    ' '));

                $hours = $this->container->get('listing.service')->formatHoursWork($itemData['hoursWork']);
               
		$item->map_info = $map_info;
                $response[$itemData['title']] = $twig->render(
                    "::modules/{$type}/map-summary.html.twig",
                    [
                        'item'           => $item,
                        'pageCategories' => $categories,
                        'pageLocations'  => $locations,
                        'pageBadges'     => $badges,
                        'levelFeatures'  => $levels,
                        'hoursWork'  => $hours,
                    ]
                );
            }
        }

        if ($response) {
            if ((count($response) == 1)) {
                $response = array_pop($response);
            } else {
                $response = $twig->render(
                    '::blocks/search/map.multiple.summary.html.twig',
                    [
                        'titles'    => array_keys($response),
                        'summaries' => $response,
                    ]
                );
            }
        }

        return new Response($response?: '');
    }
}
