<?php
namespace Elastica\Filter;

use Elastica\Exception\InvalidException;

/**
 * Terms filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-filter.html
 */
class Terms extends AbstractFilter
{
    /**
     * Terms.
     *
     * @var array Terms
     */
    protected $_terms = array();

    /**
     * Params.
     *
     * @var array Params
     */
    protected $_params = array();

    /**
     * Terms key.
     *
     * @var string Terms key
     */
    protected $_key = '';

    /**
     * Creates terms filter.
     *
     * @param string $key   Terms key
     * @param array  $terms Terms values
     */
    public function __construct($key = '', array $terms = array())
    {
        $this->setTerms($key, $terms);
    }

    /**
     * Sets key and terms for the filter.
     *
     * @param string $key   Terms key
     * @param array  $terms Terms for the query.
     *
     * @return $this
     */
    public function setTerms($key, array $terms)
    {
        $this->_key = $key;
        $this->_terms = array_values($terms);

        return $this;
    }

    /**
     * Set the lookup parameters for this filter.
     *
     * @param string                       $key     terms key
     * @param string|\Elastica\Type        $type    document type from which to fetch the terms values
     * @param string                       $id      id of the document from which to fetch the terms values
     * @param string                       $path    the field from which to fetch the values for the filter
     * @param string|array|\Elastica\Index $options An array of options or the index from which to fetch the terms values. Defaults to the current index.
     *
     * @return $this
     */
    public function setLookup($key, $type, $id, $path, $options = array())
    {
        $this->_key = $key;
        if ($type instanceof \Elastica\Type) {
            $type = $type->getName();
        }
        $this->_terms = array(
            'type' => $type,
            'id' => $id,
            'path' => $path,
        );

        $index = $options;
        if (is_array($options)) {
            if (isset($options['index'])) {
                $index = $options['index'];
                unset($options['index']);
            }
            $this->_terms = array_merge($options, $this->_terms);
        }

        if (!is_null($index)) {
            if ($index instanceof \Elastica\Index) {
                $index = $index->getName();
            }
            $this->_terms['index'] = $index;
        }

        return $this;
    }

    /**
     * Adds an additional term to the query.
     *
     * @param string $term Filter term
     *
     * @return $this
     */
    public function addTerm($term)
    {
        $this->_terms[] = $term;

        return $this;
    }

    /**
     * Converts object to an array.
     *
     * @see \Elastica\Filter\AbstractFilter::toArray()
     *
     * @throws \Elastica\Exception\InvalidException
     *
     * @return array
     */
    public function toArray()
    {
        if (empty($this->_key)) {
            throw new InvalidException('Terms key has to be set');
        }
        $this->_params[$this->_key] = $this->_terms;

        return array('terms' => $this->_params);
    }

    /**
     * Set execution mode.
     *
     * @param string $execution Options: "bool", "and", "or", "plain" or "fielddata"
     *
     * @return $this
     */
    public function setExecution($execution)
    {
        return $this->setParam('execution', (string) $execution);
    }
}
