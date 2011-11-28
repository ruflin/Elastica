<?php
/**
 * Nested filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/nested-filter.html
 */
class Elastica_Filter_Nested extends Elastica_Filter_Abstract
{
	/**
	 * Adds field to mlt filter
	 *
	 * @param string $path Nested object path
	 * @return Elastica_Filter_Nested
	 */
	public function setPath($path) {
		return $this->setParam('path', $path);
	}
	
	/**
	 * Sets nested filter
	 * 
	 * @param Elastica_Filter_Abstract $filter
	 * @return Elastica_Filter_Nested
	 */
	public function setFilter(Elastica_Filter_Abstract $filter) {
		return $this->setParam('query', $filter->toArray());
	}
	
	/**
	 * @param string $scoreMode Options: avg, total, max and none.
	 * @return Elastica_Filter_Nested
	 */
	public function setScoreMode($scoreMode) {
		return $this->setParam('score_mode', $scoreMode);
	}
}
