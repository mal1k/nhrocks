<?php

namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\CoreBundle\Entity\Account;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WebBundle\Entity\Accountprofilecontact;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class UserLogin
 *
 * @author Matheus Faustino <matheus.faustino@arcasolutions.com>
 * @since 11.0.00
 * @package ArcaSolutions\WebBundle\Services
 */
class UserLogin
{
    /**
     * Edir Session name. It is set in sitemgr
     */
    const SESSION_EDIR = 'SESS_ACCOUNT_ID';

    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * @var Accountprofilecontact
     */
    private $user;

    /**
     * @var Account
     */
    private $account;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * UserLogin constructor.
     *
     * @param DoctrineRegistry $doctrine
     * @param RequestStack $request
     */
    public function __construct(DoctrineRegistry $doctrine, RequestStack $request)
    {
        $this->request = $request;
        $this->doctrine = $doctrine;
    }

    /**
     * @author Matheus Faustino <matheus.faustino@arcasolutions.com>
     * @version 11.0.00
     *
     * @return Accountprofilecontact
     */
    public function getUser()
    {
        if (!$this->initialized) {
            $this->setUserFromEdirectory();
        }

        return $this->user;
    }

    /**
     * @author Matheus Faustino <matheus.faustino@arcasolutions.com>
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @version 11.0.00
     */
    private function setUserFromEdirectory()
    {
        $this->initialized = true;
        $request = $this->request->getCurrentRequest();

        if (!$request) {
            return;
        }

        $session = $request->getSession();
        $cookies = $request->cookies;

        if (null !== $session && $id = $session->get(self::SESSION_EDIR)) {
            if (!$this->user = $this->doctrine->getRepository('WebBundle:Accountprofilecontact')->find($id)) {
                $session->remove(self::SESSION_EDIR);

                return;
            }

            $this->account = $this->doctrine->getRepository('CoreBundle:Account','main')
                ->find($this->user->getAccountId());

            return;
        }

        if (
            $cookies->has('automatic_login_members')
            && $cookies->get('automatic_login_members') !== 'false'
            && $cookies->has('username_members')
            && $cookies->get('username_members') !== ''
            && $cookies->has('complementary_info_members')
            && $cookies->get('complementary_info_members') !== ''
        ) {
            $this->account = $this->doctrine->getRepository('CoreBundle:Account', 'main')->findOneBy([
                'username'          => $cookies->get('username_members'),
                'complementaryInfo' => $cookies->get('complementary_info_members'),
            ]);

            if(!$this->account) {
                $this->clearCookies();

                return;
            }

            $this->user = $this->doctrine->getRepository('WebBundle:Accountprofilecontact')->findOneBy([
                'accountId' => $this->account->getId(),
            ]);

            if ($this->account->getId()) {
                $session = new Session();
                $session->set($this::SESSION_EDIR, $this->account->getId());
                $request->setSession($session);

                $this->initialized = true;

                return;
            }

            $this->clearCookies();
        }
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     */
    private function clearCookies() {
        $response = new Response();

        $response->headers->setCookie(new Cookie('automatic_login_members', 'false'));
        $response->headers->setCookie(new Cookie('username_members', ''));
        $response->headers->setCookie(new Cookie('complementary_info_members', ''));

        $response->prepare(Request::createFromGlobals());
        $response->sendHeaders();
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.1.00
     *
     * @return Account
     */
    public function getAccount()
    {
        if (!$this->initialized) {
            $this->setUserFromEdirectory();
        }

        return $this->account;
    }
}