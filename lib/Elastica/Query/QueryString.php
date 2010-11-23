<?php
/**
 * QueryString query
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/query_string_query
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_QueryString extends Elastica_Query_Abstract
{
	protected $_queryString = '';
	protected $_defaultOperator = '';
	
	public function __construct($queryString) {
		$this->setQueryString($queryString);
	}
	
	public function setQueryString($queryString) {
		if (!is_string($queryString)) {
			throw new Elastica_Exception('Parameter has to be a string');
		}
		
		$this->_queryString = $queryString;
	}
	
	public function setDefaultOperator($operator)
	{
		$this->_defaultOperator = $operator;
	}
	
	public function toArray() {		   
		$args['query'] = $this->_queryString;
		
		if(!empty($this->_defaultOperator))
			$args['default_operator'] = $this->_defaultOperator;
			
		return array('query_string' => $args);
	}
	
	/**
	 * Escapes the following characters (because part of the query language)
	 * + - && || ! ( ) { } [ ] ^ " ~ * ? : \
	 * 
	 * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Escaping%20Special%20Characters
	 * @param string $term Query term to escape
	 * @return string Escaped query term
	 */
	public static function escapeTerm($term) {
		
		// \ escaping has to be first, otherwise escaped later once again
		$chars = array('\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':');
		
		foreach ($chars as $char) {
			$term = str_replace($char, '\\' . $char, $term);
		}

		return $term;
	}
}
