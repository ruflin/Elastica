<?php
/**
 * Match all query. Returns all results
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_MatchAll extends Elastica_Query_Abstract
{
	/**
	 * Converts match all query to array
	 *
	 * @return array Query array
	 * @see Elastica_Query_Abstract::toArray()
	 */
	public function toArray() {
		return array('match_all' => new stdClass());
	}
}
