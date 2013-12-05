<?php

namespace Elastica;


use Elastica\Param;
use Elastica\Suggest\AbstractSuggest;

/**
 * Class Suggest
 * @package Elastica\Suggest
 * @link http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-suggesters.html
 */
class Suggest extends Param
{
    /**
     * Set the global text for this suggester
     * @param string $text
     * @return \Elastica\Suggest
     */
    public function setGlobalText($text)
    {
        return $this->setParam("text", $text);
    }

    public function addSuggestion(AbstractSuggest $suggestion)
    {
        return $this->setParam($suggestion->getName(), $suggestion->toArray());
    }
}