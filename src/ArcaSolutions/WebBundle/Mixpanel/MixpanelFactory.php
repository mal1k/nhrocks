<?php

namespace ArcaSolutions\WebBundle\Mixpanel;

use ArcaSolutions\CoreBundle\Kernel\Kernel;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MixpanelFactory
{
    /** @var string */
    private $token;

    /** @var Connection */
    private $conn;

    /** @var RequestStack */
    private $requestStack;

    /** @var LoggerInterface */
    private $logger;

    public function __construct($token, Connection $conn, RequestStack $requestStack, LoggerInterface $logger)
    {
        $this->token = $token;
        $this->conn = $conn;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    /**
     * @return MixpanelHelper
     */
    public function createMixpanel()
    {
        $mixpanel = null;

        $request = $this->requestStack->getCurrentRequest();

        try {
            $mixpanel = new \Mixpanel($this->token);
        } catch (\Exception $e) {
            $this->logger->error('Error creating mixpanel service. Message: '.$e->getMessage());
        }

        $helper = new MixpanelHelper();

        $helper->setEdirectoryVersion(Kernel::VERSION)
            ->setConnection($this->conn)
            ->setMixpanel($mixpanel)
            ->setRemoteAddr($request->server->get('REMOTE_ADDR'))
            ->setHost($request->getHost())
            ->setLogger($this->logger);

        return $helper;
    }
}