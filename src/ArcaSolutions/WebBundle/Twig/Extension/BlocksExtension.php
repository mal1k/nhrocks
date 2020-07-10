<?php
namespace ArcaSolutions\WebBundle\Twig\Extension;

use ArcaSolutions\ApiBundle\Entity\Result;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
            new \Twig_SimpleFunction('contentCards', [$this, 'contentCards'], [
                'needs_environment' => true,
                'is_safe' => ['html']
            ]),
            new \Twig_SimpleFunction('getCardData', [$this, 'getCardData'], [
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('generateLink', [$this, 'generateLink'], []),
        ];
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param $content
     * @param $widgetLink
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Exception
     */
    public function contentCards(\Twig_Environment $twig_Environment, $content, $widgetLink)
    {
        if(\is_array($content)) {
            $content = json_decode(json_encode($content));
        }

        $content->module = $content->module === 'promotion' ? 'deal' : $content->module;
        $modules = $this->container->get('modules');

        if (empty($content->items) && empty($content->custom)) {
            return '';
        }

        if (!$modules->isModule($content->module) || !$modules->isModuleAvailable($content->module)) {
            return '';
        }

        $quantity = !empty($content->items) ? null : $content->custom->quantity;

        if ($quantity !== null && $quantity < 1) {
            return '';
        }

        $items = $this->container->get('search.block')->getCards($content->module, $quantity, $content);

        if (!$items) {
            return '';
        }

        $flag = 0;

        $content->module === 'event' and $flag |= 1;
        !empty($content->items) and $flag |= 2;

        if($flag & 3) {
            foreach ($items as $item) {
                if ($flag & 1) {
                    $item->event = $this->container->get('doctrine')
                        ->getRepository('EventBundle:Event')
                        ->find($item->getId());
                }
                if ($flag & 2) {
                    $indexedItems[$item->getId()] = $item;
                }
            }
        }

        if($flag & 2) {
            foreach($content->items as $key => $contentItem) {
                !empty($indexedItems[$contentItem]) and $orderedItems[$key] = $indexedItems[$contentItem];
            }
            !empty($orderedItems) and $items = $orderedItems;
        }

        if(!empty($content->banner) && $content->banner !== 'empty') {
            $banner = $this->container->get('twig')->render('::widgets/banners/banner.html.twig', [
                'content' => [
                    'bannerType' => $content->banner,
                    'banners'    => $content->banner === 'skyscraper' ? $content->banner : [
                        $content->banner,
                        $content->banner
                    ],
                    'isWide'     => $content->banner === 'skyscraper' ? 'true' : 'false',
                ]
            ]);
        } else {
            $banner = '';
        }

        if ($content->cardType === 'centralized-highlighted-card') {
            $jsHandler = $this->container->get('javascripthandler');
            $jsHandler->addJSExternalFile('assets/js/widgets/cards/centralized-highlighted-card.js');
        }

        $itemsPerRow = $content->columns - ($banner ? 1 : 0);

        $filter = 'card';
        $filterNoImage = 'noimage';
        if($content->columns === '3' && $content->cardType === 'horizontal-cards') {
            $filter = 'card_3';
            $filterNoImage = 'noimage_2';
        }

        return $twig_Environment->render("::modules/$content->module/blocks/$content->cardType.html.twig", [
            'items'           => $items,
            'itemsPerRow'     => $itemsPerRow,
            'banner'          => $banner,
            'content'         => $content,
            'widgetLink'      => $widgetLink,
            'filter'          => $filter,
            'filterNoImage'   => $filterNoImage,
            'module'          => $content->module,
            'cardType'        => $content->cardType
        ]);
    }

    /**
     * @param $item
     * @param $itemType
     * @param array $imageFilter
     * @param string $noImageFilter
     * @return array
     * @throws \Exception
     */
    public function getCardData($item, $itemType, $imageFilter = [], $noImageFilter = 'noimage')
    {
        $detailLink = $this->container->get('router')->generate($itemType . '_detail',
            ['friendlyUrl' => !empty($item->friendlyUrl) ? $item->friendlyUrl : $item->getFriendlyUrl(), '_format' => 'html'],
            true
        );
        $imagine_filter = $this->container->get('liip_imagine.cache.manager');

        if ($item instanceof \Elastica\Result) {
            $thumbnail = !empty($item->thumbnail) ? $item->thumbnail : '';
        } else {
            if(!empty($item->getImageId())) {
                $thumbnail = $this->container->get('imagehandler')->getPath($this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($item->getImageId()));
            }
            if ($itemType === 'listing') {
                $logoImage = $this->container->get('imagehandler')->getPath($item->getLogoImage());
            }
        }

        if (!empty($thumbnail)) {
            $imagePath = $this->container->get('templating.helper.assets')->getUrl($thumbnail, 'domain_images', null);
        } else {
            $imagePath = '';
        }

        $data = [
            'detailLink' => $detailLink,
            'imagePath'  => $imagePath
        ];

        !empty($logoImage) and $data['logoImage'] = $this->container->get('templating.helper.assets')->getUrl($logoImage, 'domain_images');

        if ($itemType === 'event') {
            if (!HookFire('blocksextension_overwrite_recurringdata', [
                'item' => $item,
                'data' => &$data
            ], true)) {
                if ($item->recurring['enabled'] && !empty($item->date['start'])) {
                    $dateStart = $this->container->get('event.recurring.service')->getNextOccurrence(
                        new DateTime($item->date['start']),
                        new DateTime($item->recurring['until']),
                        str_replace('RRULE:', '',
                            $this->container->get('event.recurring.service')->getRRule_rfc2445($item->event))
                    );
                } else {
                    $dateStart = !empty($item->date['start']) ? new \DateTime($item->date['start']) : $item->getStartDate();
                }

                if (!empty($dateStart)) {
                    $data['weekDay'] = $dateStart->format('w') + 1;
                    $data['month'] = $dateStart->format('m');
                    $data['day'] = $dateStart->format('d');
                }
            }
        }

        if ($itemType === 'deal') {
            $data['dealvalue'] = !empty($item->value['deal']) ? $item->value['deal'] : $item->getDealValue();
            $data['realvalue'] = !empty($item->value['real']) ? $item->value['real'] : $item->getRealValue();

            if(!empty($data['dealvalue']) && !empty($data['realvalue'])) {
                $data['percentage'] = 100 - ($data['dealvalue'] * 100 / $data['realvalue']);
            }

            $endDate = !empty($item->date['end']) ? new \DateTime($item->date['end']) : $item->getEndDate();

            $data['newEndDate'] = $endDate->modify('+1 day');
            $data['interval'] = $endDate->diff(new \DateTime());
        }

        if ($itemType === 'article' && !empty($item->authorImageId)) {
            $authorImage = $this->container->get('imagehandler')->getPath($this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($item->authorImageId));
            $data['authorImage'] = $imagine_filter->getBrowserPath($this->container->get('templating.helper.assets')->getUrl($authorImage, 'domain_images', null), 'logo_icon');
        }

        $data['itemId'] = $item->getId();

        return $data;
    }

    /**
     * @param \stdClass $linkObj
     *
     * @return string $link
     * @throws \Exception
     */
    public function generateLink($linkObj)
    {
        if (!empty($linkObj->target) && !empty($linkObj->value) && !empty($linkObj->customLink) && $linkObj->value === 'custom') {
            $link = ($linkObj->target === 'external' ? $linkObj->customLink : $this->container->get('pagetype.service')->getBaseUrl(PageType::HOME_PAGE) . '/' . $linkObj->customLink);
        } else {
            $pageRepository = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page');
            $link = $this->container->get('page.service')->getFinalPageUrl($pageRepository->find($linkObj->value));
        }

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'blocks';
    }
}
