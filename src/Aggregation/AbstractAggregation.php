<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;
use Elastica\NameableInterface;
use Elastica\Param;

abstract class AbstractAggregation extends Param implements NameableInterface
{
    protected const METADATA_KEY = 'meta';

    /**
     * @var string The name of this aggregation
     */
    protected $_name;

    /**
     * @var array Subaggregations belonging to this aggregation
     */
    protected $_aggs = [];

    /**
     * @param string $name the name of this aggregation
     */
    public function __construct(string $name)
    {
        $this->setName($name);
    }

    /**
     * Set the name of this aggregation.
     *
     * @return $this
     */
    public function setName(string $name): NameableInterface
    {
        $this->_name = $name;

        return $this;
    }

    /**
     * Retrieve the name of this aggregation.
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Retrieve all subaggregations belonging to this aggregation.
     */
    public function getAggs(): array
    {
        return $this->_aggs;
    }

    /**
     * Add a sub-aggregation.
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return $this
     */
    public function addAggregation(AbstractAggregation $aggregation): self
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
    public function setMeta(array $meta): self
    {
        if (empty($meta)) {
            return $this->clearMeta();
        }

        $this->_setRawParam(self::METADATA_KEY, $meta);

        return $this;
    }

    /**
     * Retrieve the currently configured metadata for the aggregation.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/agg-metadata.html
     * @see \Elastica\Aggregation\AbstractAggregation::setMeta()
     * @see \Elastica\Aggregation\AbstractAggregation::clearMeta()
     */
    public function getMeta(): ?array
    {
        return $this->_rawParams[self::METADATA_KEY] ?? null;
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
    public function clearMeta(): self
    {
        unset($this->_rawParams[self::METADATA_KEY]);

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        if (\array_key_exists('global_aggregation', $array)) {
            // compensate for class name GlobalAggregation
            $array = ['global' => new \stdClass()];
        }
        if (\count($this->_aggs)) {
            $array['aggs'] = $this->_convertArrayable($this->_aggs);
        }

        return $array;
    }
}
