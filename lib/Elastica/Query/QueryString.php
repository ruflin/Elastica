<?php
/**
 * QueryString query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/query_dsl/query_string_query
 */
class Elastica_Query_QueryString extends Elastica_Query_Abstract
{
	protected $_queryString = '';
	protected $_defaultOperator = '';
	protected $_defaultField = '';

	/**
	 * Creates query string object. Calls setQuery with argument
	 *
	 * @param string $queryString OPTIONAL Query string for object
	 */
	public function __construct($queryString = '') {
		$this->setQueryString($queryString);
	}

	/**
	 * Sets a new query string for the object
	 *
	 * @param string $queryString Query string
	 * @return Elastica_Query_QueryString Current object
	 */
	public function setQueryString($queryString) {
		if (!is_string($queryString)) {
			throw new Elastica_Exception_Invalid('Parameter has to be a string');
		}

		$this->_queryString = $queryString;
		return $this;
	}

	/**
	 * Sets the default operator AND or OR
	 *
	 * If no operator is set, OR is chosen
	 *
	 * @param string $operator Operator
	 * @return Elastica_Query_QueryString Current object
	 */
	public function setDefaultOperator($operator) {
		$this->_defaultOperator = $operator;
		return $this;
	}

	/**
	 * Sets the default field
	 *
	 * If no field is set, _all is chosen
	 *
	 * @param string $field Field
	 * @return Elastica_Query_QueryString Current object
	 */
	public function setDefaultField($field) {
		$this->_defaultField = $field;
		return $this;
	}

	/**
	 * Converts the query string object to an array
	 *
	 * @return array Query string array
	 */
	public function toArray() {
		$args['query'] = $this->_queryString;

		if(!empty($this->_defaultOperator)) {
			$args['default_operator'] = $this->_defaultOperator;
		}

		if(!empty($this->_defaultField)) {
			$args['default_field'] = $this->_defaultField;
		}

		return array('query_string' => $args);
	}

	/**
	 * Escapes the following characters (because part of the query language)
	 * + - && || ! ( ) { } [ ] ^ " ~ * ? : \
	 *
	 * @param string $term Query term to escape
	 * @return string Escaped query term
	 * @link http://lucene.apache.org/java/2_4_0/queryparsersyntax.html#Escaping%20Special%20Characters
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
