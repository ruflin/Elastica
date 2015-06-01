<?php
namespace Elastica\Filter;

/**
 * Returns child documents having parent docs matching the query.
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-parent-filter.html
 */
class HasParent extends AbstractFilter
{
    /**
     * Construct HasParent filter.
     *
     * @param string|\Elastica\Query|\Elastica\Filter\AbstractFilter $query Query string or a Query object or a filter
     * @param string|\Elastica\Type                                  $type  Parent document type
     */
    public function __construct($query, $type)
    {
        if ($query instanceof AbstractFilter) {
            $this->setFilter($query);
        } else {
            $this->setQuery($query);
        }
        $this->setType($type);
    }

    /**
     * Sets query object.
     *
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     *
     * @return $this
     */
    public function setQuery($query)
    {
        $query = \Elastica\Query::create($query);
        $data = $query->toArray();

        return $this->setParam('query', $data['query']);
    }

    /**
     * Sets filter object.
     *
     * @param \Elastica\Filter\AbstractFilter $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        return $this->setParam('filter', $filter->toArray());
    }

    /**
     * Set type of the parent document.
     *
     * @param string|\Elastica\Type $type Parent document type
     *
     * @return $this
     */
    public function setType($type)
    {
        if ($type instanceof \Elastica\Type) {
            $type = $type->getName();
        }

        return $this->setParam('type', (string) $type);
    }
}
