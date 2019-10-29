<?php

namespace Elastica\QueryBuilder\DSL;

use Elastica\Query\InnerHits;
use Elastica\QueryBuilder\DSL;

/**
 * Class Collapse.
 *
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-collapse.html
 */
class Collapse implements DSL
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return self::TYPE_COLLAPSE;
    }

    /**
     * @return InnerHits
     */
    public function inner_hits(): InnerHits
    {
        return new InnerHits();
    }
}
