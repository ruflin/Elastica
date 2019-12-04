<?php

namespace Elastica\Query;

/**
 * Match Phrase Prefix query.
 *
 * @author Jacques Moati <jacques@moati.net>
 * @author Tobias Schultze <http://tobion.de>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase-prefix.html
 */
class MatchPhrasePrefix extends MatchPhrase
{
    public const DEFAULT_MAX_EXPANSIONS = 50;

    /**
     * Set field max expansions.
     *
     * Controls to how many prefixes the last term will be expanded (default 50).
     *
     * @return $this
     */
    public function setFieldMaxExpansions(string $field, int $maxExpansions = self::DEFAULT_MAX_EXPANSIONS): self
    {
        return $this->setFieldParam($field, 'max_expansions', $maxExpansions);
    }
}
