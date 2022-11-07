<?php

namespace Elastica\Exception;

use Throwable;

/**
 * Not found exception.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class NotFoundException extends \RuntimeException implements ExceptionInterface
{
    /** @var string[] */
    private array $notFoundIds;

    /**
     * @param mixed[] $notFoundIds
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
        array $notFoundIds = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->notFoundIds = $notFoundIds;
    }


    /**
     * @return string[]
     */
    public function getNotFoundIds(): array
    {
        return $this->notFoundIds;
    }
}
