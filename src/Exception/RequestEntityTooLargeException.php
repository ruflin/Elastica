<?php

namespace Elastica\Exception;

use Throwable;

class RequestEntityTooLargeException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('Request entity is too large.', 0, $previous);
    }
}
