<?php
namespace Elastica\Facet;

/**
 * Implements the Histogram facet.
 *
 * @author Raul Martinez Jr  <juneym@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-facets-histogram-facet.html
 * @deprecated Facets are deprecated and will be removed in a future release. You are encouraged to migrate to aggregations instead.
 */
class Histogram extends AbstractFacet
{
    /**
     * Sets the field for histogram.
     *
     * @param string $field The name of the field for the histogram
     *
     * @return $this
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set the value for interval.
     *
     * @param string $interval
     *
     * @return $this
     */
    public function setInterval($interval)
    {
        return $this->setParam('interval', $interval);
    }

    /**
     * Set the fields for key_field and value_field.
     *
     * @param string $keyField   Key field
     * @param string $valueField Value field
     *
     * @return $this
     */
    public function setKeyValueFields($keyField, $valueField)
    {
        return $this->setParam('key_field', $keyField)->setParam('value_field', $valueField);
    }

    /**
     * Sets the key and value for this facet by script.
     *
     * @param string $keyScript   Script to check whether it falls into the range.
     * @param string $valueScript Script to use for statistical calculations.
     *
     * @return $this
     */
    public function setKeyValueScripts($keyScript, $valueScript)
    {
        return $this->setParam('key_script', $keyScript)
                    ->setParam('value_script', $valueScript);
    }

    /**
     * Set the "params" essential to the a script.
     *
     * @param array $params Associative array (key/value pair)
     *
     * @return $this
     */
    public function setScriptParams(array $params)
    {
        return $this->setParam('params', $params);
    }

    /**
     * Creates the full facet definition, which includes the basic
     * facet definition of the parent.
     *
     * @see \Elastica\Facet\AbstractFacet::toArray()
     *
     * @throws \Elastica\Exception\InvalidException When the right fields haven't been set.
     *
     * @return array
     */
    public function toArray()
    {
        /*
         * Set the range in the abstract as param.
         */
        $this->_setFacetParam('histogram', $this->_params);

        return parent::toArray();
    }
}
