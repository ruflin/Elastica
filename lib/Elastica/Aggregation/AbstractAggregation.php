<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\NameableInterface;
use Elastica\Param;

abstract class AbstractAggregation extends Param implements NameableInterface
{
    /**
     * @var string The name of this aggregation
     */
    protected $_name;

    /**
     * @var array Subaggregations belonging to this aggregation
     */
    protected $_aggs = [];

    /**
     * @var array|null Metadata belonging to this aggregation
     */
    protected $_meta;

    /**
     * @param string $name the name of this aggregation
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     * Set the name of this aggregation.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * Retrieve the name of this aggregation.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Retrieve all subaggregations belonging to this aggregation.
     *
     * @return array
     */
    public function getAggs()
    {
        return $this->_aggs;
    }

    /**
     * Add a sub-aggregation.
     *
     * @param AbstractAggregation $aggregation
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addAggregation(AbstractAggregation $aggregation)
    {
        if ($aggregation instanceof GlobalAggregation) {
            throw new InvalidException('Global aggregators can only be placed as top level aggregators');
        }

        $this->_aggs[] = $aggregation;

        return $this;
    }

    /**
     * Add metadata to the aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/agg-metadata.html
     * @see \Elastica\Aggregation\AbstractAggregation::getMeta()
     * @see \Elastica\Aggregation\AbstractAggregation::clearMeta()
     *
     * @param array $meta Metadata to be attached to the aggregation
     *
     * @return $this
     */
    public function setMeta(array $meta)
    {
        $this->_meta = $meta;

        return $this;
    }

    /**
     * Retrieve the currently configured metadata for the aggregation
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/agg-metadata.html
     * @see \Elastica\Aggregation\AbstractAggregation::setMeta()
     * @see \Elastica\Aggregation\AbstractAggregation::clearMeta()
     *
     * @return array|null
     */
    public function getMeta()
    {
        return $this->_meta;
    }

    /**
     * Clears any previously set metadata for this aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/agg-metadata.html
     * @see \Elastica\Aggregation\AbstractAggregation::setMeta()
     * @see \Elastica\Aggregation\AbstractAggregation::getMeta()
     *
     * @return $this
     */
    public function clearMeta()
    {
        $this->_meta = null;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        if (array_key_exists('global_aggregation', $array)) {
            // compensate for class name GlobalAggregation
            $array = ['global' => new \stdClass()];
        }
        if (isset($this->_meta) && sizeof($this->_meta)) {
            $array['meta'] = $this->_convertArrayable($this->_meta);
        }
        if (sizeof($this->_aggs)) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
