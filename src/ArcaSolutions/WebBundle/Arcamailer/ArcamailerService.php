<?php

namespace ArcaSolutions\WebBundle\Arcamailer;

use ArcaSolutions\CoreBundle\Services\Settings;

/**
 * Class ArcamailerService
 *
 * @author Diego de Biagi <diego.biagi@arcasolutions.com>
 * @since VERSION
 */
class ArcamailerService
{
    /** @var Settings */
    private $settings;

    /** @var ArcamailerClient */
    private $client;

    public function __construct(Settings $settings, ArcamailerClient $client)
    {
        $this->settings = $settings;
        $this->client = $client;
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param $name
     * @param $email
     * @param $country
     * @param $timezone
     * @return string Customer ID
     * @throws \RuntimeException When registration fails
     */
    public function register($name, $email, $country, $timezone)
    {
        $data = $this->client->register($name, $email, $country, $timezone);

        $this->settings->setSetting('arcamailer_customer_id', $data['customer_ID']);
        $this->settings->setSetting('arcamailer_customer_name', $name);
        $this->settings->setSetting('arcamailer_customer_email', $email);
        $this->settings->setSetting('arcamailer_customer_country', $country);
        $this->settings->setSetting('arcamailer_customer_timezone', $timezone);

        return $data['customer_ID'];
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param string $email
     * @param string $password
     * @return string Customer ID
     */
    public function login($email, $password)
    {
        $data = $this->client->login($email, $password);

        $this->settings->setSetting('arcamailer_customer_id', $data['customer_ID']);
        $this->settings->setSetting('arcamailer_customer_name', $data['Name']);
        $this->settings->setSetting('arcamailer_customer_email', $email);
        $this->settings->setSetting('arcamailer_customer_country', $data['Country']);
        $this->settings->setSetting('arcamailer_customer_timezone', $data['TimeZone']);

        return $data['customer_ID'];
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param string $listName
     * @return string List ID
     * @throws \RuntimeException When list creation fails
     */
    public function createList($listName)
    {
        $customerID = $this->settings->getDomainSetting('arcamailer_customer_id');

        $listId = $this->client->createList($customerID, $listName);

        $this->settings->setSetting('arcamailer_customer_listid', $listId);
        $this->settings->setSetting('arcamailer_customer_listname', $listName);

        return $listId;
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     */
    public function logout()
    {
        $settings = [
            'arcamailer_customer_id',
            'arcamailer_customer_name',
            'arcamailer_customer_email',
            'arcamailer_customer_country',
            'arcamailer_customer_timezone',
            'arcamailer_customer_listid',
            'arcamailer_customer_listname',
        ];

        foreach ($settings as $setting) {
            $this->settings->setSetting($setting, null);
        }
    }

    public function getInfo()
    {
        return $this->client->getInfo();
    }
}