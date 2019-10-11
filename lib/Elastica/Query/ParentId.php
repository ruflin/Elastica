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
     * @param string $parentDocId    ID of the parent document. The query will return child documents of this parent document.
     * @param bool   $ignoreUnmapped Indicates whether to ignore an unmapped type and not return any documents instead of an error. Defaults to false.
     */
    public function __construct(string $type, string $parentDocId, bool $ignoreUnmapped = false)
    {
        $this->setRelationshipType($type);
        $this->setId($parentDocId);
        $this->setIgnoreUnmapped($ignoreUnmapped);
    }

    /**
     * @param string $type
     */
    private function setRelationshipType(string $type)
    {
        $this->setParam('type', $type);
    }

    /**
     * @param string $id
     */
    private function setId(string $id)
    {
        $this->setParam('id', $id);
    }

    /**
     * @param bool $ignoreUnmapped
     */
    private function setIgnoreUnmapped(bool $ignoreUnmapped = false)
    {
        $this->setParam('ignore_unmapped', $ignoreUnmapped);
    }
}
