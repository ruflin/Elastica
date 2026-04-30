<?php

declare(strict_types=1);

namespace Elastica\Exception;

/**
 * Invalid exception.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
final class InvalidException extends \InvalidArgumentException implements ExceptionInterface
{
}
