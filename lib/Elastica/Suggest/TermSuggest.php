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
     * @var array options
     */
    protected $_options = array();

    /**
     * Creates term suggest object
     */
    public function construct($name, $options)
    {
    	$this->_name = $name;
    	$this->_options[] = $options;

    	return $this;
    }

    

}