<?php
namespace Elastica\Query;

/**
 * SpanNot query.
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-not-query.html
 */
class SpanNot extends AbstractSpanQuery
{
    /**
     * Constructs a SpanWithin query object.
     *
     * @param AbstractSpanQuery $include OPTIONAL
     * @param AbstractSpanQuery $exclude OPTIONAL
     */
    public function __construct(AbstractSpanQuery $include = null, AbstractSpanQuery $exclude = null)
    {
        if (null !== $include) {
            $this->setInclude($include);
        }

        if (null !== $exclude) {
            $this->setExclude($exclude);
        }
    }

    /**
     * @param AbstractSpanQuery $include
     *
     * @return $this
     */
    public function setInclude(AbstractSpanQuery $include)
    {
        return $this->setParam('include', $include);
    }

    /**
     * @param AbstractSpanQuery $exclude
     *
     * @return $this
     */
    public function setExclude(AbstractSpanQuery $exclude)
    {
        return $this->setParam('exclude', $exclude);
    }
}
