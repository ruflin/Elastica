<?php
/**
 * And Filter
 *
 * @uses Elastica_Filter_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/and-filter.html
 */
class Elastica_Filter_And extends Elastica_Filter_Abstract
{
	/**
	 * Adds one more filter to the and filter
	 *
	 * @param Elastica_Filter_Abstract $filter
	 * @return Elastica_Filter_And Current object
	 */
	public function addFilter(Elastica_Filter_Abstract $filter) {
		$this->_params[] = $filter->toArray();
		return $this;
	}
}
