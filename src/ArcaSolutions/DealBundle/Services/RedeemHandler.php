<?php

namespace ArcaSolutions\DealBundle\Services;

use ArcaSolutions\CoreBundle\Entity\Contact;
use ArcaSolutions\DealBundle\Entity\Promotion;
use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WebBundle\Services\EmailNotificationService;

/**
 * Class RedeemHandler
 *
 * @package \ArcaSolutions\DealBundle\Services
 */
class RedeemHandler
{
    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * @var EmailNotificationService
     */
    private $emailNotification;

    /**
     * RedeemHandler constructor.
     * @param DoctrineRegistry $doctrine
     * @param EmailNotificationService $emailNotification
     */
    public function __construct(DoctrineRegistry $doctrine, EmailNotificationService $emailNotification)
    {
        $this->doctrine = $doctrine;
        $this->emailNotification = $emailNotification;
    }

    public function makeRedeem(Promotion $deal, Contact $contact)
    {
        /*
         * Validations
         */
        if ($deal === null) {
            throw new \Exception('Not Found.');
        }

        if (0 == $deal->getListingId()) {
            throw new \Exception('This deal is not available.');
        }

        if ('A' != $deal->getListing()->getStatus()) {
            throw new \Exception('This deal is not available.');
        }

        $today = new \DateTime('now');
        $endDate = clone $deal->getEndDate();
        /* workaround to fix edirectory behavior */
        if (0 == $deal->getAmount() || ($endDate->modify('+1 day') < $today)) {
            throw new \Exception('Sold out.');
        }

        /* Check if it was already redeemed */
        $redeem = $this->doctrine->getRepository('DealBundle:PromotionRedeem')
            ->existUserCodeForDeal($deal, $contact->getAccount()->getId());

        /* Generate the code if it was not */
        if ($redeem === null) {
            /* generate code */
            $redeem = $this->doctrine->getRepository('DealBundle:PromotionRedeem')->redeemCode($deal,
                $contact->getAccount()->getId());

            $from_sitemgr = $this->doctrine->getRepository('WebBundle:Setting')
                ->getSetting('emailconf_email');

            $sendTo = $contact->getEmail();
            $name = sprintf('%s %s', $contact->getFirstName(), $contact->getLastName());

            if ($accountId = $deal->getListing()->getAccountId()) {
                $owner_listing_contact = $this->doctrine->getManager('main')->getRepository('CoreBundle:Contact')
                    ->findOneBy(['account' => $accountId]);

                if ($owner_listing_contact) {
                    $owner_name = sprintf('%s %s', $owner_listing_contact->getFirstName(),
                        $owner_listing_contact->getLastName());

                    /* Send listing's owner email */
                    if ($notification = $this->emailNotification->getEmailMessage(EmailNotificationService::DEAL_REDEEM_OWNER)) {
                        try {
                            $notification->setTo($owner_listing_contact->getEmail())
                                ->setFrom($from_sitemgr)
                                ->setPlaceholder('ACCOUNT_NAME', $owner_name)
                                ->sendEmail();
                        } catch (\Exception $e) {
                        }
                    }
                }
            }

            /* Send user email */
            if ($notification = $this->emailNotification->getEmailMessage(EmailNotificationService::DEAL_REDEEM_VISITOR)) {
                try {
                    $notification->setTo($sendTo)
                        ->setFrom($from_sitemgr)
                        ->setPlaceholder('ACCOUNT_NAME', $name)
                        ->setPlaceholder('REDEEM_CODE', $redeem->getRedeemCode())
                        ->sendEmail();
                } catch (\Exception $e) {
                }
            }
        }

        return $redeem;
    }
}
