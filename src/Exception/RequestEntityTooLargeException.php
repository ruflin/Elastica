<?php

namespace Elastica\Exception;

class RequestEntityTooLargeException extends \RuntimeException implements ExceptionInterface
{
    /**
     * @var string
     */
    protected $message = 'Request entity is too large.';
}
