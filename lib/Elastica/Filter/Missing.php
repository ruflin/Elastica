<?php

/**
 * Missing Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Maciej Wiercinski <maciej@wiercinski.net>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/missing-filter.html  
 */
class Elastica_Filter_Missing extends Elastica_Filter_Abstract
{
	/**
	 * @param string $field OPTIONAL
	 */
	public function __construct($field = '') {
		if (strlen($field)) {
			$this->setField($field);
		} 
	}

	/**
	 * @param string $field
	 */
	public function setField($field) {
		return $this->setParam('field', (string) $field); 
	} 
}


