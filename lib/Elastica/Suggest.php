<?php

namespace Elastica;

use Elastica\Exception\NotImplementedException;
use Elastica\Suggest\AbstractSuggest;

/**
 * Class Suggest.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/search-suggesters.html
 */
class Suggest extends Param
{
    /**
     * @param AbstractSuggest $suggestion
     */
    public function __construct(AbstractSuggest $suggestion = null)
    {
        if (!is_null($suggestion)) {
            $this->addSuggestion($suggestion);
        }
    }

    /**
     * Set the global text for this suggester.
     *
     * @param string $text
     *
     * @return $this
     */
    public function setGlobalText($text)
    {
        return $this->setParam('text', $text);
    }

    /**
     * Add a suggestion to this suggest clause.
     *
     * @param AbstractSuggest $suggestion
     *
     * @return $this
     */
    public function addSuggestion(AbstractSuggest $suggestion)
    {
        return $this->addParam('suggestion', $suggestion);
    }

    /**
     * @param Suggest|AbstractSuggest $suggestion
     *
     * @throws Exception\NotImplementedException
     *
     * @return self
     */
    public static function create($suggestion)
    {
        switch (true) {
            case $suggestion instanceof self:
                return $suggestion;
            case $suggestion instanceof AbstractSuggest:
                return new self($suggestion);
        }
        throw new NotImplementedException();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $array = parent::toArray();

        $baseName = $this->_getBaseName();

        if (isset($array[$baseName]['suggestion'])) {
            $suggestion = $array[$baseName]['suggestion'];
            unset($array[$baseName]['suggestion']);

            foreach ($suggestion as $key => $value) {
                $array[$baseName][$key] = $value;
            }
        }

        return $array;
    }
}
