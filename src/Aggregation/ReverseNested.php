<?php

namespace Elastica\Aggregation;

/**
 * Reversed Nested Aggregation.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-bucket-reverse-nested-aggregation.html
 */
class ReverseNested extends AbstractAggregation
{
    /**
     * @param string      $name The name of this aggregation
     * @param string|null $path Optional path to the nested object for this aggregation. Defaults to the root of the main document.
     */
    public function __construct(string $name, ?string $path = null)
    {
        parent::__construct($name);

        if (null !== $path) {
            $this->setPath($path);
        }
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

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        // ensure we have an object for the reverse_nested key.
        // if we don't have a path, then this would otherwise get encoded as an empty array, which is invalid.
        $array['reverse_nested'] = (object) $array['reverse_nested'];

        return $array;
    }
}
