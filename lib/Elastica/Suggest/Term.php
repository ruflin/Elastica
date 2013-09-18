<?php

namespace Elastica\Suggest;

/**
 * Text query
 *
 * @category Xodoa
 * @package Elastica
 * @author Imanol Cea <imanol.cea@gmail.com>
 * @link http://www.elasticsearch.org/guide/reference/api/search/term-suggest/
 */
class Term extends AbstractSuggest
{
	 /**
     * Global text
     *
     * @var string Global text
     */
    protected $_globalText;

    /**
     * Options
     *
     * @var array parameters
     */
    protected $_parameters = array();

    public function toArray()
    {
        return $this->_params;
    }

    public function addTerm($name, Array $term) {

        $this->addParam($name, $term);
    }

    public function setGlobalText($text) {
        $this->_globalText = $text;
    }


    /**
     * Adds a param to the list
     *
     * This function can be used to add an array of params
     *
     * @param  string         $key   Param key
     * @param  mixed          $value Value to set
     * @return \Elastica\Param
     */
    public function addParam($key, $value)
    {
        if (!isset($this->_params[$key])) {
            $this->_params[$key] = array();
        }

        $this->_params[$key] = $value;

        return $this;
    }
}
