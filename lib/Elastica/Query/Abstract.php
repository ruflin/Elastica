<?php
/**
 * Abstract query object. Should be extended by all query types.
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class Elastica_Query_Abstract
{
	/**
	 * Converts a query to an array
	 *
	 * All query objects have to implement this function
	 *
	 * @return array Query array
	 */
	abstract public function toArray();
}
