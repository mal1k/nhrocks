<?php

namespace ArcaSolutions\WebBundle\DataFixtures\ORM\Common;

use ArcaSolutions\WebBundle\Entity\Setting;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadSettingData
 * @package ArcaSolutions\WebBundle\DataFixtures\ORM\Common
 */
class LoadSettingData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $translator = $this->container->get("translator");

        /* These are the standard data of the system */
        $standardInserts = [
            /* Setting table */
            [
                'review_listing_enabled',
                'on',
            ],
            [
                'custom_article_feature',
                'on',
            ],
            [
                'custom_banner_feature',
                'on',
            ],
            [
                'custom_blog_feature',
                'on',
            ],
            [
                'custom_classified_feature',
                'on',
            ],
            [
                'custom_event_feature',
                'on',
            ],
            [
                'custom_promotion_feature',
                'on',
            ],
            [
                'custom_has_promotion',
                'on',
            ],
            [
                'edir_default_language',
                $translator->trans('edir_default_language'),
            ],
            [
                'date_format',
                $translator->trans('date.format', [], 'units'),
            ],
            [
                'clock_type',
                $translator->trans('clock_type'),
            ],
            [
                'nearby_default_radius',
                '10',
            ],
            [
                'claim_approve',
                'on',
            ],
            [
                'claim_deny',
                'on',
            ],
            /* CustomText table */
            [
                'claim_textlink',
                $translator->trans('Is this your listing?'),
            ],
            [
                'payment_tax_label',
                $translator->trans('Sales Tax'),
            ],
            [
                'deal_default_conditions',
                $translator->trans('There is a limit of 1 deal per person. The promotional value of this deal expires in 3 months. Deal must be presented in order to receive discount. This deal is not valid for cash back, can only be used once, does not cover tax or gratuities. This deal can not be combined with other offers.'),
            ],
            /* Setting_Payment table */
            [
                'payment_currency_code',
                $translator->trans("payment_currency_code"),
            ],
            [
                'payment_currency_symbol',
                $translator->trans("payment_currency_symbol"),
            ],
            [
                'payment_invoice_status',
                'on',
            ],
            [
                'payment_manual_status',
                'on',
            ],
            [
                'payment_recurring_status',
                'off',
            ],
            /* Setting_Social_Network */
            [
                'visitor_profile_status',
                'on',
            ],
            [
                'listing_login_review',
                'on',
            ],
            [
                'socialnetwork_feature',
                'on'
            ],
            [
                'maintenance_mode',
                'off'
            ],
            [
                'result_size',
                'defaultSearchResultSize'
            ]
        ];

        $repository = $manager->getRepository('WebBundle:Setting');

        foreach ($standardInserts as list($name, $value)) {
            $query = $repository->findOneBy([
                'name' => $name,
            ]);

            $setting = new Setting();

            /* checks if the setting already exist so they can be updated or added */
            if ($query) {
                $setting = $query;
            }

            $setting->setName($name);
            $setting->setValue($value);

            $manager->persist($setting);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
