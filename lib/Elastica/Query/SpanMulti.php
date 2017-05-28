<?php
namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * SpanMulti query.
 *
 * @author Marek Hernik <marek.hernik@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-near-query.html
 */
class SpanMulti extends SpanQuery
{
    /**
     * Constructs a SpanMulti query object.
     *
     * @param AbstractQuery $match OPTIONAL
     */
    public function __construct(AbstractQuery $match = null)
    {
        if ($match) {
            $this->setMatch($match);
        }
    }

    /**
     * Set match part to query.
     *
     * @param AbstractQuery $match
     *
     * @throws InvalidException
     *
     * @return $this
     */
    public function setMatch(AbstractQuery $match)
    {
        if (!in_array(get_class($match), [Wildcard::class, Fuzzy::class, Prefix::class, Regexp::class])) {
            throw new InvalidException(
                'Invalid parameter. Has to be instance of WildcardQuery or FuzzyQuery or PrefixQuery od RegexpQuery'
            );
        }

        return $this->setParams(['match' => $match]);
    }
}