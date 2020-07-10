<?php

namespace ArcaSolutions\WebBundle\Tests\Arcamailer;

use ArcaSolutions\CoreBundle\Services\Settings;
use ArcaSolutions\WebBundle\Arcamailer\ArcamailerService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArcamailerServiceTest extends KernelTestCase
{
    /** @var ArcamailerService */
    private $service;

    /** @var Settings */
    private $settings;

    protected function setUp()
    {
        self::bootKernel();

        $container = self::$kernel->getContainer();

        $this->service = $container->get('arcamailer.service');
        $this->settings = $container->get('settings');
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     */
    public function testLogin()
    {
        $email = 'diego.biagi@arcasolutions.com';
        $pwd = 'arcapwd@';

        $this->service->login($email, $pwd);

        $this->assertSame($this->settings->getDomainSetting('arcamailer_customer_email'), $email);

        foreach ($this->getArcamailerSettings() as $setting) {
            $this->assertNotEmpty($setting);
        }
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     */
    public function testRegistration()
    {
        $email = 'diego.biagi@arcasolutions.com';
        $name = 'eDirectory';
        $country = 'Brazil';
        $timezone = '(GMT-03:00) Brasilia';

        $this->service->register($name, $email, $country, $timezone);

        foreach ($this->getArcamailerSettings() as $setting) {
            $this->assertNotEmpty($setting);
        }
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @group current
     */
    public function testCreateList()
    {
        $name = 'Default List ' . microtime();

        $this->service->createList($name);

        $listId = $this->settings->getDomainSetting('arcamailer_customer_listid', true);
        $listName = $this->settings->getDomainSetting('arcamailer_customer_listname', true);

        $this->assertNotEmpty($listId);
        $this->assertSame($name, $listName);
    }

    private function getArcamailerSettings() {
        $settings = [
            'arcamailer_customer_id' => null,
            'arcamailer_customer_name' => null,
            'arcamailer_customer_email' => null,
            'arcamailer_customer_country' => null,
            'arcamailer_customer_timezone' => null
        ];

        foreach ($settings as $setting => &$value) {
            $value = $this->settings->getDomainSetting($setting, true);
        }

        return $settings;
    }
}