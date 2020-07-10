<?php

namespace ArcaSolutions\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FileExistExtension
 *
 * Adds php's file_exists in twig
 *
 * @package ArcaSolutions\CoreBundle\Twig\Extension
 */
class ColorSchemeExtension extends \Twig_Extension
{
    /**
     * ContainerInterface
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
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
            new \Twig_SimpleFunction('getColorScheme', [$this, 'getColorScheme'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('getTheme', [$this, 'getTheme'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'color_scheme';
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function getColorScheme(\Twig_Environment $twig_Environment)
    {
        $theme = lcfirst($this->container->get('theme.service')->getSelectedTheme()->getTitle());

        $colorscheme = $this->container->get('settings')->getDomainSetting('colorscheme_'. $theme);

        $colorschemeArr = json_decode($colorscheme, true);

        $colorBlock = '';

        if(!empty($colorschemeArr)) {
            foreach ($colorschemeArr as $key => $colorVar) {
                if (strpos($key, '-base') !== false) {
                    $colorBlock .= $colorVar;
                }
            }
        }

        $defaultFontFamilyParagraph = [
            'default'    => '',
            'doctor'     => 'Rubik:300,500,700,900',
            'restaurant' => 'Merriweather Sans:300,700,800',
            'wedding'    => 'Raleway:100,200,300,500,600,700,800,900',
        ];

        $defaultFontFamilyHeading = [
            'default'    => '',
            'doctor'     => 'Poppins:100,200,300,500,600,700,800,900',
            'restaurant' => 'Titillium Web:200,300,600,700,900',
            'wedding'    => 'Playfair Display:700,900',
        ];

        $paragraphFont = !empty($colorschemeArr['paragraph_font']) ? $colorschemeArr['paragraph_font'] : $defaultFontFamilyParagraph[$theme];
        $headingFont = !empty($colorschemeArr['heading_font']) ? $colorschemeArr['heading_font'] : $defaultFontFamilyHeading[$theme];

        $paragraphFontName = $paragraphFont;
        $headingFontName = $headingFont;

        if (strpos($paragraphFont, ':') !== false) {
            preg_match('/.+?(?=:)/', $paragraphFont, $paragraphFontName);
            $paragraphFontName = $paragraphFontName[0];
        }
        if (strpos($headingFont, ':') !== false) {
            preg_match('/.+?(?=:)/', $headingFont, $headingFontName);
            $headingFontName = $headingFontName[0];
        }

        return $twig_Environment->render('::style.html.twig', [
            'colorBlock'        => $colorBlock,
            'paragraphFont'     => $paragraphFont,
            'headingFont'       => $headingFont,
            'headingFontName'   => $headingFontName,
            'paragraphFontName' => $paragraphFontName
        ]);
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->container->get('multi_domain.information')->getTemplate();
    }
}
