<?php
namespace Elastica\Query;

/**
 * ParentId query.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-parent-id-query.html
 */
class ParentId extends AbstractQuery
{
    /**
     * ParentId constructor.
     *
     * @param $type
     * @param $id
     * @param bool $ignoreUnmapped
     */
    public function __construct($type, $id, $ignoreUnmapped = false)
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
     * @param int $id
     */
    private function setId($id)
    {
        $this->setParam('id', $id);
    }

    /**
     * @param bool $ignoreUnmapped
     */
    private function setIgnoreUnmapped($ignoreUnmapped)
    {
        $this->setParam('ignore_unmapped', $ignoreUnmapped);
    }
}
