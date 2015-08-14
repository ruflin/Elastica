<?php
namespace Elastica\Facet;

use Elastica\Exception\InvalidException;
use Elastica\Script;

/**
 * Implements the terms facet.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Jasper van Wanrooy <jasper@vanwanrooy.net>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-facets-terms-facet.html
 * @deprecated Facets are deprecated and will be removed in a future release. You are encouraged to migrate to aggregations instead.
 */
class Terms extends AbstractFacet
{
    /**
     * Holds the types of ordering which are allowed
     * by Elasticsearch.
     *
     * @var array
     */
    protected $_orderTypes = array('count', 'term', 'reverse_count', 'reverse_term');

    /**
     * Sets the field for the terms.
     *
     * @param string $field The field name for the terms.
     *
     * @return $this
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Sets the script for the term.
     *
     * @param string $script The script for the term.
     *
     * @return $this
     */
    public function setScript($script)
    {
        $this->setParam('script', Script::create($script));

        return $this;
    }

    /**
     * Sets multiple fields for the terms.
     *
     * @param array $fields Numerical array with the fields for the terms.
     *
     * @return $this
     */
    public function setFields(array $fields)
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Sets the flag to return all available terms. When they
     * don't have a hit, they have a count of zero.
     *
     * @param bool $allTerms Flag to fetch all terms.
     *
     * @return $this
     */
    public function setAllTerms($allTerms)
    {
        return $this->setParam('all_terms', (bool) $allTerms);
    }

    /**
     * Sets the ordering type for this facet. Elasticsearch
     * internal default is count.
     *
     * @param string $type The order type to set use for sorting of the terms.
     *
     * @throws \Elastica\Exception\InvalidException When an invalid order type was set.
     *
     * @return $this
     */
    public function setOrder($type)
    {
        if (!in_array($type, $this->_orderTypes)) {
            throw new InvalidException('Invalid order type: '.$type);
        }

        return $this->setParam('order', $type);
    }

    /**
     * Set an array with terms which are omitted in the search.
     *
     * @param array $exclude Numerical array which includes all terms which needs to be ignored.
     *
     * @return $this
     */
    public function setExclude(array $exclude)
    {
        return $this->setParam('exclude', $exclude);
    }

    /**
     * Sets the amount of terms to be returned.
     *
     * @param int $size The amount of terms to be returned.
     *
     * @return $this
     */
    public function setSize($size)
    {
        return $this->setParam('size', (int) $size);
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
        $this->_setFacetParam('terms', $this->_params);

        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        if (isset($array[$baseName]['script'])) {
            $array[$baseName]['script'] = $array[$baseName]['script']['script'];
        }

        return $array;
    }
}
