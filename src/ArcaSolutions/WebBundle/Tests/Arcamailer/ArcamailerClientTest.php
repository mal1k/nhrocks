<?php

namespace ArcaSolutions\WebBundle\Tests\Arcamailer;

use ArcaSolutions\WebBundle\Arcamailer\ArcamailerClient;

class ArcamailerClientTest extends \PHPUnit_Framework_TestCase
{
    public function testRegistration()
    {
        $email = 'diego.biagi@arcasolutions.com';
        $name = 'eDirectory';
        $country = 'Brazil';
        $timezone = '(GMT-03:00) Brasilia';

        $helper = new ArcamailerClient();

        $result = $helper->register($name, $email, $country, $timezone);

        $this->assertArrayHasKey('customer_ID', $result);
    }

    public function testLogin()
    {
        $email = 'diego.biagi@arcasolutions.com';
        $password = 'arcapwd@';

        $helper = new ArcamailerClient();

        $result = $helper->login($email, $password);

        foreach (['Country', 'Name', 'customer_ID', 'TimeZone'] as $key) {
            $this->assertArrayHasKey($key, $result);
        }
    }

    public function testGetInfo()
    {
        $helper = new ArcamailerClient();

        $info = $helper->getInfo();

        $this->assertInternalType('array', $info);
        $this->assertArrayHasKey('timezones', $info);
        $this->assertArrayHasKey('contries', $info);
    }

    public function testCreateList()
    {
        $customerID = '2931cb9c9027c8df1715c197bf5e2cc8';

        $helper = new ArcamailerClient();

        $listId = $helper->createList($customerID, 'Default List ' . microtime());

        $this->assertNotEmpty($listId);
        $this->assertInternalType('string', $listId);
    }
}