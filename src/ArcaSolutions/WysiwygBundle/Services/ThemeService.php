<?php

namespace ArcaSolutions\WysiwygBundle\Services;

use ArcaSolutions\WysiwygBundle\Entity\Theme;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Wysiwyg
 *
 * This service handles everything but RENDERING that has something to do with Wysiwyg
 * Create, Edit, Delete pages and their widgets
 * Retrieving the data from DB, saving data in DB.
 *
 */
class ThemeService
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    private $theme = Theme::DEFAULT_THEME;

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * Return the system selected theme as an Entity
     *
     * @return Theme
     */
    public function getSelectedTheme()
    {
        $templateName = $this->container->get('multi_domain.information')->getTemplate();

        return $this->container->get('doctrine')->getRepository('WysiwygBundle:Theme')->findOneBy([
            'title' => ucfirst($templateName),
        ]);
    }

    /**
     * Returns the common widgets to all themes
     *
     * @return array
     */
    public function getCommonThemeWidgets()
    {
        $trans = $this->container->get('translator');

        $widgets = [
            $trans->trans('Search box', [], 'widgets', 'en'),
            $trans->trans('Leaderboard ad bar (728x90)', [], 'widgets', 'en'),
            $trans->trans('3 rectangle ad bar', [], 'widgets', 'en'),
            $trans->trans('Upcoming Events', [], 'widgets', 'en'),
            $trans->trans('Browse by Location', [], 'widgets', 'en'),
            $trans->trans('Banner Large Mobile, one banner Sponsored Links and one Google Ads', [], 'widgets', 'en'),
            $trans->trans('Download our apps bar', [], 'widgets', 'en'),
            $trans->trans('Search Bar', [], 'widgets', 'en'),
            $trans->trans('Horizontal Cards', [], 'widgets', 'en'),
            $trans->trans('Vertical Cards', [], 'widgets', 'en'),
            $trans->trans('Vertical Card Plus Horizontal Cards', [], 'widgets', 'en'),
            $trans->trans('2 Columns Horizontal Cards', [], 'widgets', 'en'),
            $trans->trans('Centralized Highlighted Card', [], 'widgets', 'en'),
            $trans->trans('Upcoming Events Carousel', [], 'widgets', 'en'),
            $trans->trans('Results Info', [], 'widgets', 'en'),
            $trans->trans('Results', [], 'widgets', 'en'),
            $trans->trans('Listing Detail', [], 'widgets', 'en'),
            $trans->trans('Event Detail', [], 'widgets', 'en'),
            $trans->trans('Classified Detail', [], 'widgets', 'en'),
            $trans->trans('Article Detail', [], 'widgets', 'en'),
            $trans->trans('Deal Detail', [], 'widgets', 'en'),
            $trans->trans('Blog Detail', [], 'widgets', 'en'),
            $trans->trans('Contact form', [], 'widgets', 'en'),
            $trans->trans('Faq box', [], 'widgets', 'en'),
            $trans->trans('Section header', [], 'widgets', 'en'),
            $trans->trans('Custom Content', [], 'widgets', 'en'),
            $trans->trans('Pricing & Plans', [], 'widgets', 'en'),
            $trans->trans('All Locations', [], 'widgets', 'en'),
            $trans->trans('Reviews block', [], 'widgets', 'en'),
            $trans->trans('Header', [], 'widgets', 'en'),
            $trans->trans('Header with Contact Phone', [], 'widgets', 'en'),
            $trans->trans('Navigation with left Logo plus Social Media', [], 'widgets', 'en'),
            $trans->trans('Navigation with Centered Logo', [], 'widgets', 'en'),
            $trans->trans('Footer', [], 'widgets', 'en'),
            $trans->trans('Footer with Newsletter', [], 'widgets', 'en'),
            $trans->trans('Footer with Logo', [], 'widgets', 'en'),
            $trans->trans('Footer with Social Media', [], 'widgets', 'en'),
            $trans->trans('Events Calendar', [], 'widgets', 'en'),
            $trans->trans('Recent Reviews', [], 'widgets', 'en'),
            $trans->trans('Recent Members', [], 'widgets', 'en'),
            $trans->trans('Listing Prices', [], 'widgets', 'en'),
            $trans->trans('Event Prices', [], 'widgets', 'en'),
            $trans->trans('Classified Prices', [], 'widgets', 'en'),
            $trans->trans('Banner Prices', [], 'widgets', 'en'),
            $trans->trans('Article Prices', [], 'widgets', 'en'),
            $trans->trans('Newsletter', [], 'widgets', 'en'),
            $trans->trans('Video Gallery', [], 'widgets', 'en'),
            $trans->trans('Lead Form', [], 'widgets', 'en'),
            $trans->trans('Social Network Bar', [], 'widgets', 'en'),
            $trans->trans('Contact Information Bar', [], 'widgets', 'en'),
            $trans->trans('Call to Action', [], 'widgets', 'en'),
            $trans->trans('Slider', [], 'widgets', 'en'),
            $trans->trans('2 Columns Recent Posts', [], 'widgets', 'en'),
            $trans->trans('Featured categories with images', [], 'widgets', 'en'),
            $trans->trans('All Categories', [], 'widgets', 'en'),
            $trans->trans('Featured categories with images (Type 2)', [], 'widgets', 'en'),
            $trans->trans('Featured categories', [], 'widgets', 'en'),
            $trans->trans('Featured categories with icons', [], 'widgets', 'en'),
            $trans->trans('Featured categories (Type 2)', [], 'widgets', 'en'),
            $trans->trans('Recent articles plus popular articles', [], 'widgets', 'en'),
            $trans->trans('One horizontal card', [], 'widgets', 'en'),
            $trans->trans('Three vertical cards', [], 'widgets', 'en'),
            $trans->trans('List of horizontal cards', [], 'widgets', 'en'),

            /*
             * CUSTOM ADDWIDGET
             * here are an example of how you add the widget 'Widget test' for all themes
             * if you need that 'Widget test' to be available only for a specific theme you have
             * to remove it from here and add at the right function below
             */
            /* $trans->trans('Widget test', [], 'widgets', 'en'), */
        ];

        /* ModStores Hooks */
        HookFire("themeservice_after_add_commonwidgets", [
            "widget" => &$widgets
        ]);

        return $widgets;
    }

    /**
     *  CUSTOM ADDTHEME
     *  here are an example of you add all the common widgets and the specific widgets to the Test Theme
     */
    /*public function getTestThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), [
            $trans->trans('Widget test', [], 'widgets', 'en'),
        ]);
    }*/

    //region Default Widgets of each page by theme
    /**
     * Each function of this region returns the widgets ordered of each page
     * The widgets returned are different for each theme
     * ALERT: DO NOT CHANGE THE FUNCTIONS NAME
     * They have to match its own PageType constant plus 'DefaultWidgets' for the reset feature at sitemgr
     * Ex:  constant HOME_PAGE
     *      function getHomePageDefaultWidgets()
     */

    /**
     * Returns the commons and the Default Theme widgets
     *
     * @return array
     */
    public function getDefaultThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), [
            /*
             * CUSTOM ADDWIDGET
             * here are an example of how you add the widget 'Widget test' for Default theme
             * if you need that 'Widget test' to be available for all themes you have
             * to remove it from here and add at the right function above
             *
             * $trans->trans('Widget test', [], 'widgets', 'en'),*/
        ]);
    }

    /**
     * Returns the commons and the Doctor Theme widgets
     *
     * @return array
     */
    public function getDoctorThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), []);
    }

    /**
     * Returns the commons and the Restaurant Theme widgets
     *
     * @return array
     */
    public function getRestaurantThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), []);
    }

    /**
     * Returns the commons and the Wedding Theme widgets
     *
     * @return array
     */
    public function getWeddingThemeWidgets()
    {
        $trans = $this->container->get('translator');

        return array_merge($this->getCommonThemeWidgets(), []);
    }

}
