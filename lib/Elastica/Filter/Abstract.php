<?php
/**
 * Abstract filter object. Should be extended by all filter types
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class Elastica_Filter_Abstract extends Elastica_Param
{

	public function setCached() {
		$this->_setRawParam('_cache', true);
	}
}
