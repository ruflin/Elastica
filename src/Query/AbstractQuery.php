<?php

declare(strict_types=1);

namespace Elastica\Query;

use Elastica\Param;
use Elastica\Util;

/**
 * Abstract query object. Should be extended by all query types.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class AbstractQuery extends Param
{
    protected function _getBaseName()
    {
        $shortName = (new \ReflectionClass($this))->getShortName();
        $shortName = \preg_replace('/Query$/', '', $shortName);

        return Util::toSnakeCase($shortName);
    }

    public function isNotAssociativeArray(array $arr)
    {
        if ([] === $arr) {
            return true;
        }

        return \array_keys($arr) === \range(0, \count($arr) - 1);
    }
}
