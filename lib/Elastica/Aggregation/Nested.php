<?php
namespace Elastica\Aggregation;

/**
 * Class Nested.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-nested-aggregation.html
 */
class Nested extends AbstractAggregation
{
    /**
     * @param string $name the name of this aggregation
     * @param string $path the nested path for this aggregation
     */
    public function __construct($name, $path)
    {
        parent::__construct($name);
        $this->setPath($path);
    }

    /**
     * Set the nested path for this aggregation.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        return $this->setParam('path', $path);
    }
}
