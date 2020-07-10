<?php
namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\WebBundle\Entity\Slider;
use ArcaSolutions\WebBundle\Repository\SliderRepository;
use ArcaSolutions\WysiwygBundle\Entity\Page;
use ArcaSolutions\WysiwygBundle\Entity\PageType;
use ArcaSolutions\WysiwygBundle\Repository\PageRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SliderService
 * @package ArcaSolutions\WebBundle\Services
 */
final class SliderService
{
    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * NavigationService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function reloadContentSlider($pageWidget)
    {
        $content = json_decode($pageWidget->getContent(), true);
        $sliderContent = $content['contentSlider'];
        $sliderHtml = '';
        $sliderInfoHtml = '';
        $navigationService = $this->container->get('navigation.service');

        $pageRepository = $this->container->get('doctrine')->getRepository('WysiwygBundle:Page');

        $sliders = [];
        if($sliderContent) {
            foreach ($sliderContent as $key => $sliderArr) {
                $sliders[$key] = new Slider();

                $sliders[$key]->setId($sliderArr['slideId']);
                $sliders[$key]->setTitle($sliderArr['title']);
                $sliders[$key]->setSummary($sliderArr['summary']);
                $sliders[$key]->setPage($sliderArr['navLink'] !== 'custom' ? $pageRepository->find($sliderArr['navLink']) : null);
                $sliders[$key]->setLink($sliderArr['navLink'] === 'custom' && $sliderArr['sliderCustomLink'] ? trim($sliderArr['sliderCustomLink']) : null);
                $sliders[$key]->setSlideOrder($key);
                $sliders[$key]->setArea('web');
                $sliders[$key]->setTarget(isset($sliderArr['openWindow']) ? 'blank' : 'self');

                if ($sliderArr['imageId']) {
                    $img = $this->container->get('doctrine')->getRepository('ImageBundle:Image')->find($sliderArr['imageId']);
                    $sliders[$key]->setImage($img);
                    $sliders[$key]->setImageId($img->getId());
                    $fileName = $this->container->get('imagehandler')->getPath($img);
                    $sliders[$key]->setImagePath(IMAGE_URL.'/'.$fileName);
                }
            }
        }

        /* The translator and pages are being used on the forms */
        $translator = $this->container->get('translator');
        $auxArrayPages = $navigationService->getNavigationPages('Header');
        $customLink = array('name' => LANG_SITEMGR_NAVIGATION_CUSTOM_LINK, 'url' => 'custom');
        $array_main_pages = $auxArrayPages['mainPages'];
        $navigationService->removesDisabledModules($array_main_pages);
        $array_custom_pages = $auxArrayPages['customPages'];
        $baseUrl = $this->container->get('pagetype.service')->getBaseUrl(PageType::HOME_PAGE).'/';
        for ($i = 0; $i < TOTAL_SLIDER_ITEMS; $i++) {
            if ($sliders[$i]) {
                include $this->container->getParameter('kernel.root_dir').'/../web/includes/forms/form-slider-structure.php';
                include $this->container->getParameter('kernel.root_dir').'/../web/includes/forms/form-slider-info-structure.php';
            }
        }

        return ['sliderHtml' => $sliderHtml, 'sliderInfoHtml' => $sliderInfoHtml];
    }

    public function saveSocialLinks($socialLinks)
    {
        $settings = $this->container->get('settings');
        foreach ($socialLinks as $socialLink) {
            $settings->setSetting($socialLink['name'], $socialLink['value']);
        }
    }
}
