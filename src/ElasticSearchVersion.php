<?php declare(strict_types = 1);

namespace Elastica;

use MabeEnum\Enum;

/**
 * @method int getValue()
 */
class ElasticSearchVersion extends Enum
{
    public const VERSION_6 = 6;

    public const VERSION_7 = 7;
}
