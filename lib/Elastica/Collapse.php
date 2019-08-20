<?php

namespace Elastica;

use Elastica\Collapse\InnerHits;

/**
 * Class Collapse.
 *
 * Implementation of Collapse
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-collapse.html
 */
class Collapse extends Param
{
    /**
     * Set field to collapse.
     *
     * @param $fieldName
     *
     * @return $this
     */
    public function setFieldname($fieldName): self
    {
        return $this->setParam('field', $fieldName);
    }

    /**
     * Set inner hits for collapsed field.
     *
     * @param InnerHits $innerHits
     *
     * @return $this
     */
    public function setInnerHits(InnerHits $innerHits): self
    {
        return $this->setParam('inner_hits', $innerHits);
    }

    /**
     * @param InnerHits $innerHits
     *
     * @return Collapse
     */
    public function addInnerHits(InnerHits $innerHits): self
    {
        $hits = [];

        if ($this->hasParam('inner_hits')) {
            $existingInnerHits = $this->getParam('inner_hits');

            $hits = $existingInnerHits instanceof InnerHits ? [$existingInnerHits] : $existingInnerHits;
        }

        $hits[] = $innerHits;

        return $this->setParam('inner_hits', $hits);
    }

    /**
     * @param int $groupSearches
     *
     * @return $this
     */
    public function setMaxConcurrentGroupSearches(int $groupSearches): self
    {
        return $this->setParam('max_concurrent_group_searches', $groupSearches);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->getParams();

        if (!empty($this->_rawParams)) {
            $data = \array_merge($data, $this->_rawParams);
        }

        return $this->_convertArrayable($data);
    }
}
