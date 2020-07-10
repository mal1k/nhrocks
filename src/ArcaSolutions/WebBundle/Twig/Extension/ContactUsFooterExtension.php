<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContactUsFooterExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction(
                'contactUs',
                [$this, 'contactUs'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),
            
            new \Twig_SimpleFunction(
                'contactUsSocial',
                [$this, 'contactUsSocial'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),

            new \Twig_SimpleFunction(
                'contactUsHeader',
                [$this, 'contactUsHeader'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param string|null $content
     */
    public function contactUs(\Twig_Environment $twig_Environment, $content = null)
    {
        /*
         * Address information should follow this format:
         * Line 1: Company name
         * Line 2: Address
         * Line 3: City, State Zip Code (city and state are separated by comma, and the zip code is preceded by a space
         * Line 4: country
         */
        $city = $this->container->get('settings')->getDomainSetting('contact_city');
        $state = $this->container->get('settings')->getDomainSetting('contact_state');
        $zipcode = $this->container->get('settings')->getDomainSetting('contact_zipcode');

        $addressInfo = [];
        $addressInfo[] = $this->container->get('settings')->getDomainSetting('contact_company'); //line 1
        $addressInfo[]  = $this->container->get('settings')->getDomainSetting('contact_address'); //line 2
        if ($city || $state || $zipcode) {
            $auxLine = $city.($state ? ', '.$state : '').($zipcode ? ' '.$zipcode : '');
            $addressInfo[] = $auxLine; //line 3
        }
        $addressInfo[] = $this->container->get('settings')->getDomainSetting('contact_country'); //line 4

        $addressInfo = array_filter($addressInfo);

        return $twig_Environment->render('::blocks/contactus.html.twig', [
            'content'   => $content,
            'address'   => implode('<br>', $addressInfo),
            'phone'     => $this->container->get('settings')->getDomainSetting('contact_phone'),
        ]);
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @param bool $social
     * @param string|null $content
     */
    public function contactUsSocial(\Twig_Environment $twig_Environment, $content = null)
    {
        $data = [
            'twitter' => $this->container->get('settings')->getDomainSetting('twitter_account'),
            'facebook' => $this->container->get('settings')->getDomainSetting('setting_facebook_link'),
            'linkedin' => $this->container->get('settings')->getDomainSetting('setting_linkedin_link'),
            'instagram' => $this->container->get('settings')->getDomainSetting('setting_instagram_link'),
            'pinterest' => $this->container->get('settings')->getDomainSetting('setting_pinterest_link'),
            'content'    => $content,

        ];

        return $twig_Environment->render('::blocks/contactus-social.html.twig', $data);
    }
    
    /**
     * @param \Twig_Environment $twig_Environment
     *
     * @param bool $notSocial
     * @param string $content
     * @return string
     */
    public function contactUsHeader(\Twig_Environment $twig_Environment, $content = null)
    {
        $socialMediaContent = [
            'twitter'    => $this->container->get('settings')->getDomainSetting('twitter_account'),
            'facebook'   => $this->container->get('settings')->getDomainSetting('setting_facebook_link'),
            'linkedin'   => $this->container->get('settings')->getDomainSetting('setting_linkedin_link'),
            'instagram'  => $this->container->get('settings')->getDomainSetting('setting_instagram_link'),
            'pinterest'  => $this->container->get('settings')->getDomainSetting('setting_pinterest_link'),
            'content'    => $content,
        ];

        return $twig_Environment->render('::blocks/contactus-social-header.html.twig', $socialMediaContent);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'contactus';
    }
}
