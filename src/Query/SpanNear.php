<?php

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * SpanNear query.
 *
 * @author Marek Hernik <marek.hernik@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-near-query.html
 */
class SpanNear extends AbstractSpanQuery
{
    /**
     * Constructs a SpanNear query object.
     *
     * @param AbstractSpanQuery[] $clauses
     * @param int                 $slop    maximum proximity
     * @param bool                $inOrder true if order of searched clauses is important
     */
    public function __construct(array $clauses = [], int $slop = 1, bool $inOrder = false)
    {
        if (!empty($clauses)) {
            foreach ($clauses as $clause) {
                if (!$clause instanceof AbstractSpanQuery) {
                    throw new InvalidException('Invalid parameter. Has to be array or instance of '.AbstractSpanQuery::class);
                }
            }
        }
        $this->setParams(['clauses' => $clauses]);
        $this->setSlop($slop);
        $this->setInOrder($inOrder);
    }

    /**
     * @return $this
     */
    public function setSlop(int $slop): self
    {
        return $this->setParam('slop', $slop);
    }

    /**
     * @return $this
     */
    public function setInOrder(bool $inOrder): self
    {
        return $this->setParam('in_order', $inOrder);
    }

    /**
     * Add clause part to query.
     *
     * @return $this
     */
    public function addClause(AbstractSpanQuery $clause): self
    {
        return $this->addParam('clauses', $clause);
    }
}
