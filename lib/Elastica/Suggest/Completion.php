<?php

namespace Elastica\Suggest;

/**
 * Completion suggester.
 *
 * @author Igor Denisenko <im.denisenko@yahoo.com>
 *
 * @link   https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html
 */
class Completion extends AbstractSuggest
{
    /**
     * Set fuzzy parameter.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters-completion.html#fuzzy
     *
     * @param array $fuzzy
     *
     * @return $this
     */
    public function setFuzzy(array $fuzzy)
    {
        return $this->setParam('fuzzy', $fuzzy);
    }
}
