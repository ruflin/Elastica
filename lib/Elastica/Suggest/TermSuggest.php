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
class TermSuggest extends AbstractSuggest
{
	 /**
     * Name
     *
     * @var string Name
     */
    protected $_name;

    /**
     * Options
     *
     * @var array parameters
     */
    protected $_parameters = array();

    /**
     * Creates term suggest object
     */
    public function construct($name, $parameters)
    {
    	$this->_name = $name;
    	$this->_params[] = $parameters;

    	return $this;
    }

    public function toArray()
    {
        return $this->_params;
    }

    

}