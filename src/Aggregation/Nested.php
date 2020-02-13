<?php

namespace Elastica\Aggregation;

/**
 * Class Nested.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-nested-aggregation.html
 */
class Nested extends AbstractAggregation
{
    /**
     * @param string $name the name of this aggregation
     * @param string $path the nested path for this aggregation
     */
    public function __construct(string $name, string $path)
    {
        parent::__construct($name);
        $this->setPath($path);
    }

    /**
     * Set the nested path for this aggregation.
     *
     * @return $this
     */
    public function setPath(string $path): self
    {
        return $this->setParam('path', $path);
    }
}
