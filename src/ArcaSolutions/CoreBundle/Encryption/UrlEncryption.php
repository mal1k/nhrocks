<?php


namespace ArcaSolutions\CoreBundle\Encryption;

/**
 * Class UrlEncryption
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @since 11.0.00
 * @package ArcaSolutions\CoreBundle\Encryption
 */
class UrlEncryption
{
    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.0.00
     *
     * @param $plainText
     * @return mixed
     */
    public function encrypt($plainText)
    {
        return $this->base64UrlEncode($plainText);
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.0.00
     *
     * @param $encrypted
     * @return string
     */
    public function decrypt($encrypted)
    {
        return trim($this->base64UrlDecode($encrypted));
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.0.00
     *
     * @param $data
     * @return string
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.0.00
     *
     * @param $data
     * @return bool|string
     */
    private function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
