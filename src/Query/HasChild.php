<?php

namespace Elastica\Query;

use Elastica\Query as BaseQuery;

/**
 * Returns parent documents having child docs matching the query.
 *
 * @author Fabian Vogler <fabian@equivalence.ch>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-child-query.html
 */
class HasChild extends AbstractQuery
{
    /**
     * Construct HasChild Query.
     *
     * @param AbstractQuery|BaseQuery|string $query
     * @param string                         $type  Parent document type
     */
    public function __construct($query, ?string $type = null)
    {
        $this->setType($type);
        $this->setQuery($query);
    }

    /**
     * Sets query object.
     *
     * @param AbstractQuery|BaseQuery|string $query
     *
     * @return $this
     */
    public function setQuery($query): self
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
    public function setType(?string $type = null): self
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
    public function setScope(string $scope): self
    {
        return $this->setParam('_scope', $scope);
    }

    /**
     * Set inner hits.
     *
     * @return $this
     */
    public function setInnerHits(InnerHits $innerHits): self
    {
        return $this->setParam('inner_hits', $innerHits);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        if (isset($array[$baseName]['query'])) {
            $array[$baseName]['query'] = $array[$baseName]['query']['query'];
        }

        return $array;
    }
}
