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
     * @param string     $type
     * @param int|string $id
     * @param bool       $ignoreUnmapped
     */
    public function __construct(string $type, $id, bool $ignoreUnmapped = false)
    {
        $this->setType($type);
        $this->setId($id);
        $this->setIgnoreUnmapped($ignoreUnmapped);
    }

    /**
     * @param string $type
     */
    private function setType(string $type)
    {
        $this->setParam('type', $type);
    }

    /**
     * @param int|string $id
     */
    private function setId($id)
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
