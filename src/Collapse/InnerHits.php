<?php

namespace Elastica\Collapse;

use Elastica\Collapse;
use Elastica\Query\InnerHits as BaseInnerHits;

/**
 * Class InnerHits.
 *
 * Basically identical to inner_hits on query level, but has support for a second level collapse as per
 * https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-body.html#_second_level_of_collapsing
 *
 * Collapse is part of the inner_hits construct in this case, which should be explicitly supported and not only
 * via calling InnerHits::setParam('collapse', $collapse).
 *
 * On the other hand, collapse cannot be used on query level invocations of inner_hits, which is why it may not be part
 * of Query\InnerHits.
 */
class InnerHits extends BaseInnerHits
{
    /**
     * @return $this
     */
    public function setCollapse(Collapse $collapse): self
    {
        return $this->setParam('collapse', $collapse);
    }
}
