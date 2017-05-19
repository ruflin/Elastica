<?php
namespace Elastica\Query;

class ParentId extends AbstractQuery
{
    public function __construct($type, $id, $ignoreUnmapped = false)
    {
        $this->setType($type);
        $this->setId($id);
        $this->setIgnoreUnmapped($ignoreUnmapped);
    }

    private function setType($type)
    {
        $this->setParam('type', $type);
    }

    private function setId($id)
    {
        $this->setParam('id', $id);
    }

    private function setIgnoreUnmapped($ignoreUnmapped)
    {
        $this->setParam('ignore_unmapped', $ignoreUnmapped);
    }
}
