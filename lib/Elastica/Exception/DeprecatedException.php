<?php

namespace Elastica\Exception;

/**
 * Deprecated exception.
 *
 * Is thrown if a function or feature is deprecated and its usage can't be supported by BC layer
 *
 * @author Evgeniy Sokolov <ewgraf@gmail.com>
 */
class DeprecatedException extends NotImplementedException
{
}
