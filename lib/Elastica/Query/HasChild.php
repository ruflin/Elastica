<?php
namespace Elastica\Query;

use Elastica\Query as BaseQuery;

/**
 * Returns parent documents having child docs matching the query.
 *
 * @author Fabian Vogler <fabian@equivalence.ch>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-child-query.html
 */
class HasChild extends AbstractQuery
{
    /**
     * Construct HasChild Query.
     *
     * @param string|\Elastica\Query|\Elastica\Query\AbstractQuery $query
     * @param string                                               $type  Parent document type
     */
    public function __construct($query, $type = null)
    {
        $this->setType($type);
        $this->setQuery($query);
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
        return $this->setParam('query', BaseQuery::create($query));
    }

    /**
     * Set type of the parent document.
     *
     * @param string $type Parent document type
     *
     * @return $this
     */
    public function setType($type)
    {
        return $this->setParam('type', $type);
    }

    /**
     * Sets the scope.
     *
     * @param string $scope Scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        return $this->setParam('_scope', $scope);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        if (isset($array[$baseName]['query'])) {
            $array[$baseName]['query'] = $array[$baseName]['query']['query'];
        }

        return $array;
    }
}
