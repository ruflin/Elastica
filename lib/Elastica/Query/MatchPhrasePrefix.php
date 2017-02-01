<?php
namespace Elastica\Query;

/**
 * Match Phrase Prefix query.
 *
 * @author Jacques Moati <jacques@moati.net>
 * @author Tobias Schultze <http://tobion.de>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query-phrase-prefix.html
 */
class MatchPhrasePrefix extends MatchPhrase
{
    const DEFAULT_MAX_EXPANSIONS = 50;

    /**
     * Set field max expansions.
     *
     * Controls to how many prefixes the last term will be expanded (default 50).
     *
     * @param string $field
     * @param int    $maxExpansions
     *
     * @return $this
     */
    public function setFieldMaxExpansions($field, $maxExpansions = self::DEFAULT_MAX_EXPANSIONS)
    {
        return $this->setFieldParam($field, 'max_expansions', (int) $maxExpansions);
    }
}
