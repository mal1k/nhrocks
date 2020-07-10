<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SocialMediaExtension extends \Twig_Extension
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
                'socialMedia',
                [$this, 'socialMedia'],
                ['needs_environment' => true, 'is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param \Twig_Environment $twig_Environment
     */
    public function socialMedia(\Twig_Environment $twig_Environment, $view = 'footer')
    {
        $data = [
            'twitter' => $this->container->get('settings')->getDomainSetting('twitter_account'),
            'facebook' => $this->container->get('settings')->getDomainSetting('setting_facebook_link'),
            'linkedin' => $this->container->get('settings')->getDomainSetting('setting_linkedin_link'),
            'instagram' => $this->container->get('settings')->getDomainSetting('setting_instagram_link'),
            'pinterest' => $this->container->get('settings')->getDomainSetting('setting_pinterest_link'),
            'view' => $view,
        ];

        return $twig_Environment->render('::blocks/social-media.html.twig', $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'socialMedia';
    }
}
