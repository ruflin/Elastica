<?php

declare(strict_types=1);

namespace Elastica\Query;

use Elastica\Query as BaseQuery;

/**
 * Returns child documents having parent docs matching the query.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-has-parent-query.html
 *
 * @phpstan-import-type TCreateQueryArgsMatching from BaseQuery
 */
class HasParent extends AbstractQuery
{
    /**
     * @param AbstractQuery|array|BaseQuery|string|null $query
     * @param string                                    $type  Parent document type
     *
     * @phpstan-param TCreateQueryArgsMatching $query
     */
    public function __construct($query, string $type)
    {
        $this->setQuery($query);
        $this->setType($type);
    }

    /**
     * Sets query object.
     *
     * @param AbstractQuery|array|BaseQuery|string|null $query
     *
     * @phpstan-param TCreateQueryArgsMatching $query
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
    public function setType(string $type): self
    {
        return $this->setParam('parent_type', $type);
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
