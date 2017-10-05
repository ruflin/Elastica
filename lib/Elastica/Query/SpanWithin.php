<?php
namespace Elastica\Query;

/**
 * SpanWithin query.
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-within-query.html
 */
class SpanWithin extends AbstractSpanQuery
{
    /**
     * Constructs a SpanWithin query object.
     *
     * @param AbstractSpanQuery $little OPTIONAL
     * @param AbstractSpanQuery $big    OPTIONAL
     */
    public function __construct(AbstractSpanQuery $little = null, AbstractSpanQuery $big = null)
    {
        if (null !== $little) {
            $this->setLittle($little);
        }

        if (null !== $big) {
            $this->setBig($big);
        }
    }

    /**
     * @param AbstractSpanQuery $little
     *
     * @return $this
     */
    public function setLittle(AbstractSpanQuery $little)
    {
        return $this->setParam('little', $little);
    }

    /**
     * @param AbstractSpanQuery $big
     *
     * @return $this
     */
    public function setBig(AbstractSpanQuery $big)
    {
        return $this->setParam('big', $big);
    }
}
