<?php

namespace Elastica\Exception;

/**
 * JSON Parse exception.
 *
 * @deprecated since version 7.2.0, catch \JsonException instead
 */
class JSONParseException extends \JsonException implements ExceptionInterface
{
}
