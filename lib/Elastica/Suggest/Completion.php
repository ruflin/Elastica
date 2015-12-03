<?php
namespace Elastica\Suggest;

/**
 * Comletion suggester.
 *
 * @author Igor Denisenko <im.denisenko@yahoo.com>
 *
 * @link   http://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html
 */
class Completion extends AbstractSuggest
{
    /**
     * Set fuzzy parameter.
     *
     * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html#fuzzy
     *
     * @param array $fuzzy
     *
     * @return $this
     */
    public function setFuzzy(array $fuzzy)
    {
        return $this->setParam('fuzzy', $fuzzy);
    }

    /**
     * Set Context parameter
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/suggester-context.html
     *
     * @param array @category
     */
    public function setContext(array $category)
    {
        return $this->setParam('context', $category);
    }
}
