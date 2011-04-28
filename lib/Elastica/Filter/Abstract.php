<?php
/**
 * Abstract filter object. Should be extended by all filter types
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class Elastica_Filter_Abstract
{
	/**
	 * Converts filter to array.
	 *
	 * Has to be overwritten by every filter to create array representation
	 *
	 * @return array Filter array
	 */
	abstract public function toArray();
}
