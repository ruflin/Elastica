<?php

declare(strict_types=1);

namespace Elastica\QueryBuilder\Version;

/**
 * Latest elasticsearch DSL.
 *
 * Latest refers to the version mentioned in README.md.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/index.html
 *
 * @author Manuel Andreo Garcia <andreo.garcia@gmail.com>
 */
class Latest extends Version700
{
    public function __construct()
    {
        $this->aggregations[] = 'multi_terms';
    }
}
