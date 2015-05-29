<?php
namespace Elastica\QueryBuilder\DSL;

use Elastica\Exception\NotImplementedException;
use Elastica\QueryBuilder\DSL;
use Elastica\Suggest\Completion;
use Elastica\Suggest\Phrase;
use Elastica\Suggest\Term;

/**
 * elasticsearch suggesters DSL.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters.html
 */
class Suggest implements DSL
{
    /**
     * must return type for QueryBuilder usage.
     *
     * @return string
     */
    public function getType()
    {
        return self::TYPE_SUGGEST;
    }

    /**
     * term suggester.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-term.html
     *
     * @param $name
     * @param $field
     *
     * @return Term
     */
    public function term($name, $field)
    {
        return new Term($name, $field);
    }

    /**
     * phrase suggester.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-phrase.html
     *
     * @param $name
     * @param $field
     *
     * @return Phrase
     */
    public function phrase($name, $field)
    {
        return new Phrase($name, $field);
    }

    /**
     * completion suggester.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html
     *
     * @param string $name
     * @param string $field
     *
     * @return Completion
     */
    public function completion($name, $field)
    {
        return new Completion($name, $field);
    }

    /**
     * context suggester.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/suggester-context.html
     */
    public function context()
    {
        throw new NotImplementedException();
    }
}
