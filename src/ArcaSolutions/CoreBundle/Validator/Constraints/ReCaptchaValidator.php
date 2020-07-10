<?php

namespace ArcaSolutions\CoreBundle\Validator\Constraints;

use ArcaSolutions\CoreBundle\Services\Settings;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrueValidator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ValidatorException;

class ReCaptchaValidator extends IsTrueValidator
{
    public function __construct(
        $enabled,
        $privateKey,
        RequestStack $requestStack,
        array $httpProxy = [],
        Settings $settings
    ) {
        parent::__construct($enabled, $privateKey, $requestStack, $httpProxy);

        $this->privateKey = $settings->getDomainSetting('google_recaptcha_secretkey');
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        // if recaptcha is disabled, always valid
        if (!$this->enabled) {
            return true;
        }

        // define variable for recaptcha check answer
        $remoteip = $this->requestStack->getMasterRequest()->getClientIp();
        $response = $this->requestStack->getMasterRequest()->get('g-recaptcha-response');

        $isValid = $this->checkAnswer($this->privateKey, $remoteip, $response);

        if (!$isValid) {
            $this->context->addViolation($constraint->message);
        }
    }

    private function checkAnswer($privateKey, $remoteip, $response)
    {
        if ($remoteip == null || $remoteip == '') {
            throw new ValidatorException('For security reasons, you must pass the remote ip to reCAPTCHA');
        }

        // discard spam submissions
        if ($response == null || strlen($response) == 0) {
            return false;
        }

        $response = $this->httpGet(self::RECAPTCHA_VERIFY_SERVER, '/recaptcha/api/siteverify', [
            'secret'   => $privateKey,
            'remoteip' => $remoteip,
            'response' => $response,
        ]);

        $response = json_decode($response, true);

        return isset($response['success']) && $response['success'];
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server.
     *
     * @param string $host
     * @param string $path
     * @param array $data
     *
     * @return string
     */
    private function httpGet($host, $path, $data)
    {
        $host = sprintf('%s%s?%s', $host, $path, http_build_query($data));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $contents = curl_exec($ch);

        if (curl_errno($ch)) {
            return null;
        }

        curl_close($ch);

        return $contents ?: null;
    }
}
