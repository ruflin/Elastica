<?php

namespace Elastica\Aggregation;

/**
 * Reversed Nested Aggregation.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-reverse-nested-aggregation.html
 */
class ReverseNested extends AbstractAggregation
{
    /**
     * @param string $name The name of this aggregation
     * @param string $path Optional path to the nested object for this aggregation. Defaults to the root of the main document.
     */
    public function __construct($name, $path = null)
    {
        parent::__construct($name);

        if ($path !== null) {
            $this->setPath($path);
        }
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

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = parent::toArray();

        // ensure we have an object for the reverse_nested key.
        // if we don't have a path, then this would otherwise get encoded as an empty array, which is invalid.
        $array['reverse_nested'] = (object) $array['reverse_nested'];

        return $array;
    }
}
