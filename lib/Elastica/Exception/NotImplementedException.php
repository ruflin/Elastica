<?php
namespace Elastica\Exception;

/**
 * Not implemented exception.
 *
 * Is thrown if a function or feature is not implemented yet
 *
 * @category Xodoa
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class NotImplementedException extends \BadMethodCallException implements ExceptionInterface
{
}
