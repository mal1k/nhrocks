<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ArcaSolutions\WebBundle\Entity\Slider;

class SliderExtension extends \Twig_Extension
{
    /**
     * ContainerInterface
     *
     * @var object
     */
    protected $container;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('contentSlider', [$this, 'contentSlider'], [
                'needs_environment' => true,
                'is_safe' => ['html']
            ])
        ];
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param $content
     * @param $type string
     * @param $heroClass
     * @return mixed|string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function contentSlider(\Twig_Environment $twig_Environment, $content, $type = 'default', $heroClass)
    {
        $arrayContent = (array)$content;
        $imageBackground = [];

        if (!isset($arrayContent['contentSlider'])) {
            return '';
        }

        $slidersArray = $arrayContent['contentSlider'];

        if (!$slidersArray) {
            return '';
        }

        $pageRepository = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page');

        $sliderFilters = [
            'desktop' => 'large',
            'laptop'  => 'large',
            'tablet'  => 'large',
            'mobile'  => 'large'
        ];

        $sliders = [];
        foreach($slidersArray as $key => $sliderArr) {
            $sliderArr = (array)$sliderArr;

            $sliders[$key] = new Slider();

            $sliders[$key]->setTitle($sliderArr['title']);
            $sliders[$key]->setSummary($sliderArr['summary']);
            $sliders[$key]->setSlideOrder($key);
            $sliders[$key]->setTarget(isset($sliderArr['openWindow']) ? 'blank' : 'self');

            if ($sliderArr['imageId']) {
                $imgObj = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($sliderArr['imageId']);

                $sliders[$key]->setImage($imgObj);

                if(empty($imgObj->getUnsplash())) {
                    $imageUrl = $this->container->get('templating.helper.assets')
                        ->getUrl($this->container->get('imagehandler')->getPath($imgObj), 'domain_images');

                    $imageData = $this->container->get('tag.picture.service')->getImageSource($imageUrl, $sliderArr['title'], $sliderFilters);

                    $imageData['webPSupport'] = in_array('image/webp', array_column($imageData['sources'], 'type'), true);

                    $imageData['itemId'] = 'slider-' . $key;
                    $imageBackground[] = $this->container->get('templating')->render('@Web/images/background.html.twig', $imageData);
                }
            }

            if ($sliderArr['sliderCustomLink']) {
                $pageUrl = strpos( $sliderArr['sliderCustomLink'], '://') ? $sliderArr['sliderCustomLink'] : $this->container->get('pagetype.service')->getBaseUrl(PageType::HOME_PAGE) . '/' . $sliderArr['sliderCustomLink'];
                $sliders[$key]->setPageUrl($pageUrl);
                continue;
            }
            if (!empty($sliderArr['navLink']) && $sliderArr['navLink'] !== 'custom') {
                $pageUrl = $this->container->get('page.service')->getFinalPageUrl($pageRepository->find($sliderArr['navLink']));
                $sliders[$key]->setPageUrl($pageUrl);
                continue;
            }
        }

        $sliderBlock = $twig_Environment->render('::blocks/slider.html.twig', array(
            'sliders'         => $sliders,
            'type'            => $type,
            'imageBackground' => $imageBackground,
            'heroClass'       => $heroClass
        ));

        return [
            'sliderBlock' => $sliderBlock,
            'sliderCount' => \count($sliders),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'slider';
    }
}
