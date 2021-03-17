<?php

namespace Elastica\Aggregation;

use Elastica\Exception\InvalidException;

/**
 * Class WeightedAvg.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-weight-avg-aggregation.html
 */
class WeightedAvg extends AbstractAggregation
{
    /**
     * Set the value for this aggregation.
     *
     * @param mixed $missing
     *
     * @return $this
     */
    public function setValue(string $field, $missing = null)
    {
        if ($this->hasParam('value') && isset($this->getParam('value')['script'])) {
            throw new InvalidException('Weighted Average aggregation with a value mixing field and script is not possible.');
        }

        $value = ['field' => $field];

        if (null !== $missing) {
            $value['missing'] = $missing;
        }

        return $this->setParam('value', $value);
    }

    /**
     * Set the value as a script for this aggregation.
     *
     * @return $this
     */
    public function setValueScript(string $script)
    {
        if ($this->hasParam('value') && isset($this->getParam('value')['field'])) {
            throw new InvalidException('Weighted Average aggregation with a value mixing field and script is not possible.');
        }

        return $this->setParam('value', ['script' => $script]);
    }

    /**
     * Set the weight for this aggregation.
     *
     * @param mixed $missing
     *
     * @return $this
     */
    public function setWeight(string $field, $missing = null)
    {
        if ($this->hasParam('weight') && isset($this->getParam('weight')['script'])) {
            throw new InvalidException('Weighted Average aggregation with a weight mixing field and script is not possible.');
        }

        $weight = ['field' => $field];

        if (null !== $missing) {
            $weight['missing'] = $missing;
        }

        return $this->setParam('weight', $weight);
    }

    /**
     * Set the weight as a script for this aggregation.
     *
     * @return $this
     */
    public function setWeightScript(string $script)
    {
        if ($this->hasParam('weight') && isset($this->getParam('weight')['field'])) {
            throw new InvalidException('Weighted Average aggregation with a weight mixing field and script is not possible.');
        }

        return $this->setParam('weight', ['script' => $script]);
    }

    /**
     * Set the format for this aggregation.
     *
     * @param string $format
     *
     * @return $this
     */
    public function setFormat($format)
    {
        return $this->setParam('format', $format);
    }

    /**
     * Set the value_type for this aggregation.
     *
     * @param mixed $valueType
     *
     * @return $this
     */
    public function setValueType($valueType)
    {
        return $this->setParam('value_type', $valueType);
    }
}
