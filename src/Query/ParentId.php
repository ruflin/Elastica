<?php

namespace Elastica\Query;

/**
 * ParentId query.
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-parent-id-query.html
 */
class ParentId extends AbstractQuery
{
    /**
     * ParentId constructor.
     *
     * @param string $type           Name of the child relationship mapped for the join field
     * @param string $id             ID of the parent document. The query will return child documents of this parent document.
     * @param bool   $ignoreUnmapped Indicates whether to ignore an unmapped type and not return any documents instead of an error. Defaults to false.
     */
    public function __construct(string $type, string $id, bool $ignoreUnmapped = false)
    {
        $this->setRelationshipType($type);
        $this->setId($id);
        $this->setIgnoreUnmapped($ignoreUnmapped);
    }

    private function setRelationshipType(string $type): void
    {
        $this->setParam('type', $type);
    }

    private function setId(string $id): void
    {
        $this->setParam('id', $id);
    }

    private function setIgnoreUnmapped(bool $ignoreUnmapped = false): void
    {
        $this->setParam('ignore_unmapped', $ignoreUnmapped);
    }
}
