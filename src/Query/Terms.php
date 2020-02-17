<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Terms query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Roberto Nygaard <roberto@nygaard.es>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html
 */
class Terms extends AbstractQuery
{
    /**
     * Terms.
     *
     * @var array Terms
     */
    protected $_terms;

    /**
     * Terms key.
     *
     * @var string Terms key
     */
    protected $_key;

    /**
     * Construct terms query.
     *
     * @param string $key   Terms key
     * @param array  $terms Terms list
     */
    public function __construct(string $key = '', array $terms = [])
    {
        $this->setTerms($key, $terms);
    }

    /**
     * Sets key and terms for the query.
     *
     * @param string $key   terms key
     * @param array  $terms terms for the query
     *
     * @return $this
     */
    public function setTerms(string $key, array $terms): self
    {
        $this->_key = $key;
        $this->_terms = \array_values($terms);

        return $this;
    }

    /**
     * Sets key and terms lookup for the query.
     *
     * @param string $key         terms key
     * @param array  $termsLookup terms lookup for the query
     *
     * @return $this
     */
    public function setTermsLookup(string $key, array $termsLookup): self
    {
        $this->_key = $key;
        $this->_terms = $termsLookup;

        return $this;
    }

    /**
     * Adds a single term to the list.
     *
     * @param string $term Term
     *
     * @return $this
     */
    public function addTerm(string $term): self
    {
        $this->_terms[] = $term;

        return $this;
    }

    /**
     * Sets the minimum matching values.
     *
     * @param int|string $minimum Minimum value
     *
     * @return $this
     */
    public function setMinimumMatch($minimum): self
    {
        return $this->setParam('minimum_match', $minimum);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        if (empty($this->_key)) {
            throw new InvalidException('Terms key has to be set');
        }
        $this->setParam($this->_key, $this->_terms);

        return parent::toArray();
    }
}
