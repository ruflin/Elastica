<?php

declare(strict_types=1);

namespace Elastica\Exception;

final class RequestEntityTooLargeException extends \RuntimeException implements ExceptionInterface
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Request entity is too large.', 0, $previous);
    }
}
