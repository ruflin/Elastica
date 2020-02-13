<?php

namespace Elastica\Query;

/**
 * SpanNot query.
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-not-query.html
 */
class SpanNot extends AbstractSpanQuery
{
    /**
     * Constructs a SpanWithin query object.
     *
     * @param AbstractSpanQuery $include
     * @param AbstractSpanQuery $exclude
     */
    public function __construct(?AbstractSpanQuery $include = null, ?AbstractSpanQuery $exclude = null)
    {
        if (null !== $include) {
            $this->setInclude($include);
        }

        if (null !== $exclude) {
            $this->setExclude($exclude);
        }
    }

    /**
     * @return $this
     */
    public function setInclude(AbstractSpanQuery $include): self
    {
        return $this->setParam('include', $include);
    }

    /**
     * @return $this
     */
    public function setExclude(AbstractSpanQuery $exclude): self
    {
        return $this->setParam('exclude', $exclude);
    }
}
