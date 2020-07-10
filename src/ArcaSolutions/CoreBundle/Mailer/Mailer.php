<?php

namespace ArcaSolutions\CoreBundle\Mailer;

use ArcaSolutions\CoreBundle\Services\Settings;
use Monolog\Logger;
use Swift_Message;

/**
 * Class Mailer
 * @package ArcaSolutions\CoreBundle\Mailer
 *
 * This class is used as an interface for Swift_Message and Swift_Mailer.
 * The main goal of it is send the message with the 'To' of sitemgr, in a transparently way.
 * We do not extended the Swift_Message with this class, because it will throw an error in serialization
 * because of the dependencies classes
 */
class Mailer
{
    /** @var string */
    protected $sendMailParameter = 'sitemgr_send_email';

    /** @var string */
    protected $generalMailParameter = 'sitemgr_email';

    /** @var string */
    protected $fromMailParameter = 'emailconf_email';

    /** @var string */
    protected $fromNameParameter = 'header_title';

    /** @var \Swift_Mailer */
    private $mailer;

    /** @var Settings */
    private $settings;

    /** @var Swift_Message */
    private $message;

    /** @var Logger */
    private $logger;

    public function __construct(\Swift_Mailer $mailer, Settings $settings, Logger $logger)
    {
        $this->mailer = $mailer;
        $this->settings = $settings;
        $this->logger = $logger;
    }

    /**
     * Returns the HTML body for Sitemgr Messages
     * @param string $content
     * @return String
     */
    public static function getSitemgrHtmlBody($content)
    {
        return "
            <html>
                <head>
                    <style>
                        .email_style_settings{
                            font-size:12px;
                            font-family:Verdana, Arial, Sans-Serif;
                            color:#000;
                        }
                    </style>
                </head>
                <body>
                    <div class=\"email_style_settings\">
                    $content
                    </div>
                </body>
            </html>";
    }

    /**
     * Create a new Message.
     *
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     * @return Mailer
     */
    public function newMail($subject = '', $body = '', $contentType = null, $charset = 'utf-8')
    {
        $this->message = \Swift_Message::newInstance($subject, $body, $contentType, $charset);

        return $this;
    }

    /**
     * @param string|array $addresses
     * @param string $name
     * @param bool $sitemgrNotif
     * @return $this
     */
    public function setTo($addresses, $name = null, $sitemgrNotif = false)
    {
        if ($addresses && !is_array($addresses)) {
            $addresses = [$addresses => $name ?: $addresses];
        }

        $generalMail = $this->settings->getDomainSetting($this->sendMailParameter);

        if ($sitemgrNotif && $generalMail === 'on') {
            $generalTo = $this->settings->getDomainSetting($this->generalMailParameter);
            $generalTo = array_flip(explode(',', $generalTo));
            foreach ($generalTo as $key => $value) {
                $generalTo[$key] = $key;
            }
            /* It was used the cast to solve an error when the addresses is empty */
            $addresses = array_filter(array_merge_recursive((array)$addresses, $generalTo));
        }

        $this->message->setTo((array)$addresses);

        return $this;
    }

    /**
     * Set the subject of this message.
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->message->setSubject($subject);

        return $this;
    }

    /**
     * Set the from address of this message.
     *
     * You may pass an array of addresses if this message is from multiple people.
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param string|array $addresses
     * @param string $name optional
     *
     * @return $this
     */
    public function setFrom($addresses, $name = null)
    {
        $this->message->setFrom($addresses, $name);

        return $this;
    }

    /**
     * Set the reply-to address of this message.
     *
     * You may pass an array of addresses if replies will go to multiple people.
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param mixed $addresses
     * @param string $name optional
     *
     * @return $this
     */
    public function setReplyTo($addresses, $name = null)
    {
        $this->message->setReplyTo($addresses, $name);

        return $this;
    }

    /**
     * Set the Cc addresses of this message.
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param mixed $addresses
     * @param string $name optional
     *
     * @return $this
     */
    public function setCc($addresses, $name = null)
    {
        if (!empty($addresses)) {
            $this->message->setCc($addresses, $name);
        }

        return $this;
    }

    /**
     * Set the bcc address of this message.
     *
     * You may pass an array of addresses if bcc will go to multiple people.
     *
     * If $name is passed and the first parameter is a string, this name will be
     * associated with the address.
     *
     * @param mixed $addresses
     * @param string $name optional
     *
     * @return $this
     */
    public function setBcc($addresses, $name = null)
    {
        if (!empty($addresses)) {
            $this->message->setBcc($addresses, $name);
        }

        return $this;
    }

    /**
     * Set the body of this entity, either as a string, or as an instance of
     * {@link Swift_OutputByteStream}.
     *
     * @param mixed $body
     * @param string $contentType optional
     * @param string $charset optional
     *
     * @return $this
     */
    public function setBody($body, $contentType = null, $charset = null)
    {
        $this->message->setBody($body, $contentType, $charset);

        return $this;
    }

    /**
     * Send the given Message like it would be sent in a mail client.
     *
     * All recipients (with the exception of Bcc) will be able to see the other
     * recipients this message was sent to.
     *
     * Recipient/sender data will be retrieved from the Message object.
     *
     * The return value is the number of recipients who were accepted for
     * delivery.
     *
     * @param array $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(&$failedRecipients = null)
    {
        try {
            $this->message->setFrom(
                $this->settings->getDomainSetting($this->fromMailParameter),
                $this->settings->getDomainSetting($this->fromNameParameter)
            );

            return $this->mailer->send($this->message, $failedRecipients);
        } catch (\Exception $e) {
            $this->logger->error('Error sending email', [
                'exception' => $e
            ]);

            return 0;
        }
    }

    /**
     * Test swiftmailer transport with given configurations.
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param array $config
     *
     * @return bool
     */
    public function testSmtpTransport(array $config)
    {
        $transport = new \Swift_SmtpTransport($config['host'], $config['port']);
        $transport->setUsername($config['username'])
            ->setPassword($config['password'])
            ->setEncryption($config['encryption'])
            ->setAuthMode($config['auth'])
            ->setStreamOptions([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

        $mailer = new \Swift_Mailer($transport);

        $message = (new Swift_Message($config['subject']))
            ->setFrom($config['from'])
            ->setTo($config['to'])
            ->setBody($config['body']);

        try {
            $result = $mailer->send($message, $failed);
        } catch (\Swift_TransportException $e) {
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }

        if (count($failed) > 0) {
            throw new \RuntimeException(sprintf('Failed to deliver email to %s.', implode(', ', $failed)));
        }

        return $result > 0;
    }
}
