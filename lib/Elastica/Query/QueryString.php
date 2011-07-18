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
	protected $_fields = array();
	protected $_useDisMax = null;

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
	 * Whether to use bool or dis_max quueries to internally combine results for multi field search.
	 * @param bool $value
	 * Determines whether to use
	 */
	public function setUseDisMax($value) {
		$this->_useDisMax = ($value == true);
	}

	/**
	 * Sets the fields
	 *
	 * If no fields are set, _all is chosen
	 *
	 * @param array $fields Fields
	 * @return Elastica_Query_QueryString Current object
	 */
	public function setFields($fields) {
		if (!is_array($fields)) {
			throw new Elastica_Exception_Invalid('Parameter has to be an array');
		}

		$this->_fields = $fields;
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

		if(!empty($this->_fields)) {
			$args['fields'] = $this->_fields;
		}

		if(! is_null($this->_useDisMax)) {
			$args['use_dis_max'] = $this->_useDisMax;
		}

		return array('query_string' => $args);
	}
}
