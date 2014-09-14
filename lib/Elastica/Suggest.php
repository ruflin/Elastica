<?php

namespace Elastica;


use Elastica\Exception\NotImplementedException;
use Elastica\Suggest\AbstractSuggest;

/**
 * Class Suggest
 * @package Elastica\Suggest
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-suggesters.html
 */
class Suggest extends Param
{
    /**
     * @param AbstractSuggest $suggestion
     */
    function __construct(AbstractSuggest $suggestion = NULL)
    {
        if (!is_null($suggestion)) {
            $this->addSuggestion($suggestion);
        }
    }


    /**
     * Set the global text for this suggester
     * @param string $text
     * @return \Elastica\Suggest
     */
    public function setGlobalText($text)
    {
        return $this->setParam("text", $text);
    }

    /**
     * Add a suggestion to this suggest clause
     * @param AbstractSuggest $suggestion
     * @return \Elastica\Suggest
     */
    public function addSuggestion(AbstractSuggest $suggestion)
    {
        return $this->setParam($suggestion->getName(), $suggestion->toArray());
    }

    /**
     * @param Suggest|AbstractSuggest $suggestion
     * @return \Elastica\Suggest
     * @throws Exception\NotImplementedException
     */
    public static function create($suggestion)
    {
        switch(true){
            case $suggestion instanceof Suggest:
                return $suggestion;
            case $suggestion instanceof AbstractSuggest:
                return new self($suggestion);
        }
        throw new NotImplementedException();
    }
}