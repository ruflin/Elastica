<?php

declare(strict_types=1);

namespace Elastica\Query;

/**
 * SpanContaining query.
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-containing-query.html
 */
class SpanContaining extends AbstractSpanQuery
{
    public function __construct(?AbstractSpanQuery $little = null, ?AbstractSpanQuery $big = null)
    {
        if (null !== $little) {
            $this->setLittle($little);
        }

        if (null !== $big) {
            $this->setBig($big);
        }
    }

    /**
     * @return $this
     */
    public function setLittle(AbstractSpanQuery $little): self
    {
        return $this->setParam('little', $little);
    }

    /**
     * @return $this
     */
    public function setBig(AbstractSpanQuery $big): self
    {
        return $this->setParam('big', $big);
    }
}
