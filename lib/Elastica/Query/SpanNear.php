<?php
namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * SpanNear query.
 *
 * @author Marek Hernik <marek.hernik@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-near-query.html
 */
class SpanNear extends AbstractSpanQuery
{
    /**
     * Constructs a SpanNear query object.
     *
     * @param AbstractSpanQuery[] $clauses OPTIONAL
     * @param int                 $slop    OPTIONAL maximum proximity
     * @param bool                $inOrder OPTIONAL true if order of searched clauses is important
     */
    public function __construct($clauses = [], $slop = 1, $inOrder = false)
    {
        if (!empty($clauses)) {
            foreach ($clauses as $clause) {
                if (!is_subclass_of($clause, AbstractSpanQuery::class)) {
                    throw new InvalidException(
                        'Invalid parameter. Has to be array or instance of Elastica\Query\SpanQuery'
                    );
                }
            }
        }
        $this->setParams(['clauses' => $clauses]);
        $this->setSlop($slop);
        $this->setInOrder($inOrder);
    }

    /**
     * @param int $slop
     */
    public function setSlop($slop)
    {
        $this->setParam('slop', $slop);
    }

    /**
     * @param bool $inOrder
     */
    public function setInOrder($inOrder)
    {
        $this->setParam('in_order', $inOrder);
    }

    /**
     * Add clause part to query.
     *
     * @param AbstractSpanQuery $clause
     *
     * @throws InvalidException If not valid query
     *
     * @return $this
     */
    public function addClause($clause)
    {
        if (!is_subclass_of($clause, AbstractSpanQuery::class)) {
            throw new InvalidException('Invalid parameter. Has to be array or instance of Elastica\Query\SpanQuery');
        }

        return $this->addParam('clauses', $clause);
    }
}
