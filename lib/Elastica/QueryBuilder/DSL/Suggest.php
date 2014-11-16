<?php

namespace Elastica\QueryBuilder\DSL;

use Elastica\Exception\NotImplementedException;
use Elastica\QueryBuilder\DSL;
use Elastica\Suggest\Phrase;
use Elastica\Suggest\Term;

/**
 * elasticsearch suggesters DSL
 *
 * @package Elastica
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-suggesters.html
 */
class Suggest implements DSL
{
    /**
     * must return type for QueryBuilder usage
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE_SUGGEST;
    }

    /**
     * term suggester
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-suggesters-phrase.html
     * @param $name
     * @param $field
     * @return Term
     */
    public function term($name, $field)
    {
        return new Term($name, $field);
    }

    /**
     * phrase suggester
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-suggesters-phrase.html
     * @param $name
     * @param $field
     * @return Phrase
     */
    public function phrase($name, $field)
    {
        return new Phrase($name, $field);
    }

    /**
     * completion suggester
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-suggesters-completion.html
     */
    public function completion()
    {
        throw new NotImplementedException();
    }

    /**
     * context suggester
     *
     * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/suggester-context.html
     */
    public function context()
    {
        throw new NotImplementedException();
    }
}
