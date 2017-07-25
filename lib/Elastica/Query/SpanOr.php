<?php
namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * SpanOr query.
 *
 * @author Marek Hernik <marek.hernik@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-term-query.html
 */
class SpanOr extends AbstractSpanQuery
{
    /**
     * Constructs a SpanOr query object.
     *
     * @param AbstractSpanQuery[] $clauses OPTIONAL
     */
    public function __construct($clauses = [])
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
