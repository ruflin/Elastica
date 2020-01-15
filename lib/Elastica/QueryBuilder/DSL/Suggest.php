<?php

namespace Elastica\QueryBuilder\DSL;

use Elastica\QueryBuilder\DSL;
use Elastica\Suggest\Completion;
use Elastica\Suggest\Phrase;
use Elastica\Suggest\Term;

/**
 * elasticsearch suggesters DSL.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters.html
 */
class Suggest implements DSL
{
    /**
     * must return type for QueryBuilder usage.
     */
    public function getType(): string
    {
        return self::TYPE_SUGGEST;
    }

    /**
     * term suggester.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-term.html
     */
    public function term(string $name, string $field): Term
    {
        return new Term($name, $field);
    }

    /**
     * phrase suggester.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-phrase.html
     */
    public function phrase(string $name, string $field): Phrase
    {
        return new Phrase($name, $field);
    }

    /**
     * completion suggester.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html
     */
    public function completion(string $name, string $field): Completion
    {
        return new Completion($name, $field);
    }
}
