<?php

namespace ArcaSolutions\WebBundle\Services;

use ArcaSolutions\WebBundle\Entity\Leads;
use ArcaSolutions\WebBundle\Form\Builder\JsonFormBuilder;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;

class LeadHandler
{
    const ITEMTYPE_GENERAL = 'general';
    const ITEMTYPE_CLASSIFIED = 'classified';
    const ITEMTYPE_EVENT = 'event';
    const ITEMTYPE_LISTING = 'listing';

    const STATUS_READ = 'A';
    const STATUS_UNREAD = 'P';

    /** @var Session */
    private $session;
    /** @var ManagerRegistry */
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine, Session $session)
    {
        $this->doctrine = $doctrine;
        $this->session = $session;
    }

    /**
     * @param $type
     * @param int $itemId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phone
     * @param string $subject
     * @param string $message
     * @return Leads
     */
    public function add(
        $type,
        $itemId = 0,
        $firstName = '',
        $lastName = '',
        $email = '',
        $phone = '',
        $subject = '',
        $message = ''
    ) {
        $lead = new Leads();
        $lead->setItemId($itemId ? $itemId : 0);
        $lead->setMemberId($this->session->get('SESS_ACCOUNT_ID', 0));

        $lead->setType($type)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setPhone($phone)
            ->setSubject($subject)
            ->setMessage($message)
            ->setEntered(new \DateTime())
            ->setStatus(self::STATUS_UNREAD)
            ->setNew('y');

        $em = $this->doctrine->getManager();
        $em->persist($lead);
        $em->flush();

        return $lead;
    }
}
