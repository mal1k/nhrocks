<?php
namespace ArcaSolutions\WebBundle\Twig\Extension;

use ArcaSolutions\CoreBundle\Form\Type\CaptchaType;
use ArcaSolutions\WebBundle\Form\Type\SendMailType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class UserExtension
 *
 * @package ArcaSolutions\WebBundle\Twig\Extension
 */
final class UserExtension extends \Twig_Extension
{
    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * UserExtension constructor.
     *
     * @param RequestStack $request
     * @param ContainerInterface $container
     */
    public function __construct(RequestStack $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('loginNavBar', [$this, 'loginNavBar'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('recentMembers', [$this, 'recentMembers'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
            new \Twig_SimpleFunction('getUser', [$this, 'getUser']),
            new \Twig_SimpleFunction('getAccount', [$this, 'getAccount']),
            new \Twig_SimpleFunction('getLoginData', [$this, 'getLoginData']),
            new \Twig_SimpleFunction('honeyPot', [$this, 'honeyPot'], [
                'needs_environment' => true,
                'is_safe'           => ['html'],
            ]),
        ];
    }

    /**
     * Returns login navbar, checking user credentials
     *
     * @param \Twig_Environment $twig_Environment
     *
     * @param null $content
     * @param null $widget
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function loginNavBar(\Twig_Environment $twig_Environment, $content = null, $widget = null)
    {
        $user = $this->container->get('user')->getUser();

        $twigFile = '::blocks/login/navbar';
        $twigFile .= $widget? $widget.'.html.twig' : '.html.twig';

        return $twig_Environment->render($twigFile, [
            'user'    => $user,
            'content' => $content,
        ]);
    }

    /**
     * Returns recent members
     *
     * @param \Twig_Environment $twig_Environment
     *
     * @param int $quantity
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function recentMembers(\Twig_Environment $twig_Environment, $quantity = 10)
    {
        $doctrine = $this->container->get('doctrine');

        $members = $doctrine->getRepository('CoreBundle:Account',
            'main')->findBy([
            'hasProfile' => 'y',
        ], ['entered' => 'DESC'], $quantity);

        return $twig_Environment->render('::blocks/recent-members.html.twig', ['members' => $members]);
    }

    /**
     * @param $referer
     * @return array
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getLoginData($referer = '')
    {
        $socialnetworkFeature = $this->container->get('settings')->getDomainSetting('socialnetwork_feature');
        $urlPath = ($socialnetworkFeature == 'on' ? 'profile' : 'sponsors');

        $facebookLoginUrl = $this->container->get('socialmedia.login')->getFacebookLoginURL($referer);
        $googleLoginUrl = $this->container->get('socialmedia.login')->getGoogleLogin($referer);
        $loginUrl = $this->container->get('request')->getSchemeAndHttpHost() . '/' . $urlPath . '/login.php?userperm=true';
        $addUrl = $this->container->get('request')->getSchemeAndHttpHost() . '/profile/add.php?userperm=true';
        $forgotUrl = $this->container->get('request')->getSchemeAndHttpHost() . '/' . $urlPath . '/forgot.php';

        if ($this->container->get('settings')->getDomainSetting('google_recaptcha_status') === 'on') {
            $options = [
                'label'  => false
            ];

            $captcha = $this->container->get('form.factory')->createBuilder(CaptchaType::class, null, $options);
        } else {
            $options = [
                'reload' => true,
                'as_url' => true,
                'label'  => false
            ];

            $captcha = $this->container->get('form.factory')->createBuilder();

            $captcha->add('registerCaptcha', CaptchaType::class, $options);
        }

        return [
            'facebookLoginUrl'     => $facebookLoginUrl,
            'googleLoginUrl'       => $googleLoginUrl,
            'loginUrl'             => $loginUrl,
            'addUrl'               => $addUrl,
            'forgotUrl'            => $forgotUrl,
            'socialnetworkFeature' => $socialnetworkFeature,
            'captcha'              => $captcha->getForm()->createView()
        ];
    }

    /**
     * Checks if user is logged or not
     *
     * @return \ArcaSolutions\WebBundle\Entity\Accountprofilecontact|false
     */
    public function getUser()
    {
        return $this->container->get('user')->getUser();
    }

    /**
     * Gets account information from user logged
     *
     * @return \ArcaSolutions\WebBundle\Entity\Accountprofilecontact|false
     */
    public function getAccount()
    {
        return $this->container->get('user')->getAccount();
    }

    /**
     * @param \Twig_Environment $twig_Environment
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function honeyPot(\Twig_Environment $twig_Environment)
    {
        return $twig_Environment->render('@Web/honeypot.html.twig');
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'login_nav_bar';
    }
}
