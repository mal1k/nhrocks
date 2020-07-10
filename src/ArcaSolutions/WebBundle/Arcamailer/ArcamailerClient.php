<?php

namespace ArcaSolutions\WebBundle\Arcamailer;

use GuzzleHttp\Client;

/**
 * Class ArcamailerClient
 *
 * @author Diego de Biagi <diego.biagi@arcasolutions.com>
 * @since VERSION
 */
class ArcamailerClient
{
    const ARCAMAILER_API = 'http://arcamailer.com/api/api.php';

    /** @var Client */
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_url'        => self::ARCAMAILER_API,
            'timeout'         => 60,
            'allow_redirects' => true,
            'headers'         => [
                'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',
            ],
        ]);
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param $email
     * @param $password
     * @throws \RuntimeException When login fails
     * @return array
     */
    public function login($email, $password)
    {
        $response = $this->client->post('', [
            'body' => [
                'action'   => 'doLogin',
                'email'    => $email,
                'password' => $password,
            ],
        ]);

        $contents = unserialize($response->getBody()->getContents());

        if (!$contents['success']) {
            throw new \RuntimeException($contents['arrayReturn']->Message);
        }

        return $contents['arrayReturn'];
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param $name
     * @param $email
     * @param $country
     * @param $timezone
     * @throws \RuntimeException When registration fails
     * @return mixed
     */
    public function register($name, $email, $country, $timezone)
    {
        $response = $this->client->post('', [
            'body' => [
                'action'        => 'signUP',
                'edir_name'     => $name,
                'edir_email'    => $email,
                'edir_country'  => $country,
                'edir_timezone' => $timezone,
            ],
        ]);

        $contents = unserialize($response->getBody()->getContents());

        if (!$contents['success']) {
            throw new \RuntimeException($contents['message']);
        }

        return $contents;
    }

    /**
     * Get timezones and contries data
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @return array
     */
    public function getInfo()
    {
        $response = $this->client->get('', [
            'query' => [
                'getInfo' => 'true',
            ],
        ]);

        $contents = unserialize($response->getBody()->getContents());

        return $contents;
    }

    /**
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param $customerID
     * @param $listName
     * @return string List id
     */
    public function createList($customerID, $listName)
    {
        $response = $this->client->post('', [
            'body' => [
                'action' => 'allowService',
                'listName' => $listName,
                'customerID' => $customerID
            ]
        ]);

        $contents = unserialize($response->getBody()->getContents());

        if(!$contents['success']) {
            throw new \RuntimeException($contents['arrayReturn']->Message);
        }

        return $contents['arrayReturn'];
    }
}