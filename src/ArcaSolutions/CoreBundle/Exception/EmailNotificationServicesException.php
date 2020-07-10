<?php

namespace ArcaSolutions\CoreBundle\Exception;


use Throwable;

/**
 * Class EmailNotificationServicesException
 *
 * @author Diego Mosela <diego.mosela@arcasolutions.com>
 * @package ArcaSolutions\CoreBundle\Exception
 * @since 11.3.00
 */
class EmailNotificationServicesException extends \Exception
{
    /**
     * EmailNotificationServicesException constructor.
     *
     * @author Diego Mosela <diego.mosela@arcasolutions.com>
     * @since 11.3.00
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        $message = "Email notification service not instantiated",
        $code = 500,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
