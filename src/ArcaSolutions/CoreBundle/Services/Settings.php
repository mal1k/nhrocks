<?php

namespace ArcaSolutions\CoreBundle\Services;

use ArcaSolutions\MultiDomainBundle\Doctrine\DoctrineRegistry;
use ArcaSolutions\WebBundle\Entity\Setting;

class Settings
{
    const PAYMENT_AUTHORIZE_LOGIN = "payment_authorize_login";
    const PAYMENT_AUTHORIZE_STATUS = "payment_authorize_status";
    const PAYMENT_AUTHORIZE_TXNKEY = "payment_authorize_transactionkey";
    const PAYMENT_CURRENCY_SYMBOL = "payment_currency_symbol";
    const PAYMENT_INVOICE_STATUS = "payment_invoice_status";
    const PAYMENT_MANUAL_STATUS = "payment_manual_status";
    const PAYMENT_PAGSEGURO_EMAIL = "payment_pagseguro_email";
    const PAYMENT_PAGSEGURO_STATUS = "payment_pagseguro_status";
    const PAYMENT_PAGSEGURO_TOKEN = "payment_pagseguro_token";
    const PAYMENT_PAYFLOW_LOGIN = "payment_payflow_login";
    const PAYMENT_PAYFLOW_PARTNER = "payment_payflow_partner";
    const PAYMENT_PAYFLOW_STATUS = "payment_payflow_status";
    const PAYMENT_CURRENCY_CODE = "payment_currency_code";
    const PAYMENT_PAYPAL_ACCOUNT = "payment_paypal_account";
    const PAYMENT_PAYPAL_STATUS = "payment_paypal_status";
    const PAYMENT_PAYPALAPI_PASSWORD = "payment_paypalapi_password";
    const PAYMENT_PAYPALAPI_SIGNATURE = "payment_paypalapi_signature";
    const PAYMENT_PAYPALAPI_STATUS = "payment_paypalapi_status";
    const PAYMENT_PAYPALAPI_USERNAME = "payment_paypalapi_username";
    const PAYMENT_TWOCHECKOUT_LOGIN = "payment_twocheckout_login";
    const PAYMENT_TWOCHECKOUT_STATUS = "payment_twocheckout_status";
    const PAYMENT_WORLDPAY_INSTID = "payment_worldpay_installationid";
    const PAYMENT_WORLDPAY_STATUS = "payment_worldpay_status";

    /**
     * @var DoctrineRegistry
     */
    private $doctrine;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var boolean
     */
    private $loadedMain;

    /**
     * @var boolean
     */
    private $loadedDomain;

    /**
     * Settings constructor.
     *
     * @param DoctrineRegistry $doctrine
     */
    public function __construct(DoctrineRegistry $doctrine)
    {
        $this->doctrine = $doctrine;

        $this->loadedMain = false;
        $this->loadedDomain = false;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    private function checkKeyExists($key = '', $database = 'domain')
    {
        return isset($this->parameters[$database][$key]);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    private function getValue($key = '', $database = 'domain')
    {
        return isset($this->parameters[$database][$key]) ? $this->parameters[$database][$key] : null;
    }

    /**
     * @param string $key
     * @param string $value
     */
    private function setKey($key = '', $value = '', $database = 'domain')
    {
        $this->parameters[$database][$key] = $value;
    }

    /**
     * Gets key from Settings table of the database main
     *
     * @param string $key
     * @param boolean $reload
     *
     * @return mixed|null|string
     */
    public function getSetting($key = '', $reload = false)
    {
        if (!$this->loadedMain || $reload) {
            $settings = $this->doctrine->getRepository('CoreBundle:Setting', 'main')->findAll();
            foreach ($settings as $setting) {
                $this->setKey($setting->getName(), $setting->getValue(), 'main');
            }

            $this->loadedMain = true;
        }

        if ($this->checkKeyExists($key, 'main')) {
            return $this->getValue($key, 'main');
        }

        return null;
    }

    /**
     * Gets key from Settings table
     *
     * @param string $key
     * @param boolean $reload
     *
     * @return mixed|null|string
     */
    public function getDomainSetting($key = '', $reload = false)
    {
        if (!$this->loadedDomain || $reload) {
            $settings = $this->doctrine->getRepository('WebBundle:Setting')->findAll();
            foreach ($settings as $setting) {
                $this->setKey($setting->getName(), $setting->getValue());
            }

            $this->loadedDomain = true;
        }

        if ($this->checkKeyExists($key)) {
            return $this->getValue($key );
        }

        return null;
    }

    /**
     * Gets Items from Navigation Settings table
     *
     * @param string $area
     * @return \ArcaSolutions\WebBundle\Entity\SettingNavigation[]|array|mixed|null
     * @throws \Exception
     */
    public function getNavigationSetting($area = 'all')
    {
        if ($this->checkKeyExists($area)) {
            $values = $this->getValue($area);
        }

        if (!isset($values)) {
            if ($area !== 'all') {
                $values = $this->doctrine->getRepository('WebBundle:SettingNavigation')->getMenuByArea($area);
            } else {
                $values = $this->doctrine->getRepository('WebBundle:SettingNavigation')->findAll();
            }
        }

        if (null == $values) {
            return null;
        }

        $this->setKey($area, $values);

        return $values;
    }

    /**
     * Gets key from Settings Search Tag table
     *
     * @param string $key
     *
     * @return mixed|string
     */
    public function getSettingSearchTag($key = '')
    {
        if ($this->checkKeyExists($key)) {
            return $this->getValue($key);
        }

        $value = $this->doctrine->getRepository('WebBundle:SettingSearchTag')->findOneBy(['name' => $key]);

        if (null === $value) {
            return null;
        }

        $this->setKey($key, $value->getValue());

        return $value->getValue();
    }

    /**
     * Update or add a new setting on table Setting
     *
     * @param string $name
     * @param string $value
     * @since 11.2.00
     */
    public function setSetting($name, $value)
    {
        $value = trim($value);
        // Settings that must check if the url contains ://
        $checkUrlArr = [
            'setting_facebook_link',
            'setting_linkedin_link',
            'setting_instagram_link',
            'setting_pinterest_link',
            'twitter_account',
        ];

        if (in_array($name, $checkUrlArr) and strpos($value, "://") === false and !empty($value)) {
            $value = "https://".$value;
        }

        if ($setting = $this->doctrine->getRepository('WebBundle:Setting')->findOneBy(['name' => $name])) {
            $setting->setValue($value);
        } else {
            $setting = new Setting();
            $setting->setName($name);
            $setting->setValue($value);
            $this->doctrine->getManager('domain')->persist($setting);
        }

        $this->doctrine->getManager('domain')->flush($setting);
    }
}
