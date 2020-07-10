<?php
namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\WysiwygBundle\Entity\PageType;
use Google_Client;
use mysql_xdevapi\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class SocialMediaLogin
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * FacebookLogin constructor.
     *
     * @param ContainerInterface $container
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $referer
     * @return string
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getFacebookLoginURL($referer = '')
    {
        /*
         * The referer should be defined to make sure the user will be redirected back to the original page after the login.
         * When the login is done from the page profile/login.php, $referer is being defined as "profile/login.php" by this function, when it was already defined as null before on the file profile/login.php.
         * This causes a Cross-site request forgery error.
         */
        if (strpos($referer, 'login.php') !== false || strpos($referer, 'add.php') !== false) {
            $referer = null;
        }
        $facebookAppID = $this->container->get('settings')->getDomainSetting('foreignaccount_facebook_apiid');
        $facebookAppSecret = $this->container->get('settings')->getDomainSetting('foreignaccount_facebook_apisecret');
        $allow = $this->container->get('settings')->getDomainSetting('foreignaccount_facebook');

        $facebookLoginUrl = '';

        if ($allow == 'on' and $facebookAppID and $facebookAppSecret) {
            try {
                $fb = new \Facebook\Facebook([
                    'app_id'                => $facebookAppID,
                    'app_secret'            => $facebookAppSecret,
                    'default_graph_version' => 'v2.10',
                ]);

                $helper = $fb->getRedirectLoginHelper();

                $redirectURI_params = [
                    'destiny' => 'referer',
                    'referer' => $referer
                ];

                $helper->getPersistentDataHandler()->set('state', json_encode($redirectURI_params));
                $permissions = ['email']; // Optional permissions

                $redirectURL = $this->container->get('request_stack')->getCurrentRequest()->getSchemeAndHttpHost().'/sponsors/facebookauth.php';

                $facebookLoginUrl = $helper->getLoginUrl($redirectURL, $permissions);
            } catch (Exception $e) {
                $facebookLoginUrl = '';
            }
        }

        return $facebookLoginUrl;
    }

    /**
     * @param string $referer
     * @return string
     */
    public function getGoogleLogin($referer = '')
    {
        $clientId = $this->container->get('settings')->getDomainSetting('foreignaccount_google_clientid');
        $clientSecret = $this->container->get('settings')->getDomainSetting('foreignaccount_google_clientid');
        $allow = $this->container->get('settings')->getDomainSetting('foreignaccount_google');

        $googleLoginUrl = '';

        if ($allow == 'on' and $clientId and $clientSecret) {
            $urlRedirect = [
                'destiny' => 'referer',
                'referer' => $referer
            ];

            try {
                // Call Google API
                $gClient = new Google_Client();
                $gClient->setApplicationName($this->container->get('multi_domain.information')->getTitle());
                $gClient->setClientId($clientId);
                $gClient->setClientSecret($clientSecret);
                $gClient->setRedirectUri($this->container->get('request_stack')->getCurrentRequest()->getSchemeAndHttpHost().'/sponsors/googleauth.php');
                $gClient->addScope(['profile', 'email']);
                $gClient->setState(json_encode($urlRedirect));

                $googleLoginUrl = $gClient->createAuthUrl();
            } catch (Exception $e) {
                $googleLoginUrl = '';
            }
        }

        // Get login url
        return $googleLoginUrl;
    }
}
