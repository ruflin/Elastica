<?php
namespace Elastica\Facet;

/**
 * Implements the statistical facet.
 *
 * @author Robert Katzki <robert@katzki.de>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-facets-statistical-facet.html
 * @deprecated Facets are deprecated and will be removed in a future release. You are encouraged to migrate to aggregations instead.
 */
class Statistical extends AbstractFacet
{
    /**
     * Sets the field for the statistical query.
     *
     * @param string $field The field name for the statistical query.
     *
     * @return $this
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Sets multiple fields for the statistical query.
     *
     * @param array $fields Numerical array with the fields for the statistical query.
     *
     * @return $this
     */
    public function setFields(array $fields)
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Sets a script to calculate statistical information.
     *
     * @param string $script The script to do calculations on the statistical values
     *
     * @return $this
     */
    public function setScript($script)
    {
        return $this->setParam('script', $script);
    }

    /**
     * Creates the full facet definition, which includes the basic
     * facet definition of the parent.
     *
     * @see \Elastica\Facet\AbstractFacet::toArray()
     *
     * @return array
     */
    public function toArray()
    {
        $this->_setFacetParam('statistical', $this->_params);

        return parent::toArray();
    }
}
