<?php

namespace ArcaSolutions\ImportBundle\Exception;

use Throwable;


/**
 * Class ImportException
 *
 * @author Roberto Silva <roberto.silva@arcasolutions.com>
 * @package ArcaSolutions\ImportBundle\Exception
 * @since 11.3.00
 */
class ImportException extends \Exception
{
    /**
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param Throwable $previous [optional] The previous throwable used for the exception chaining.
     * @since 11.3.00
     */
    public function __construct($message = "", $code = 1, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
