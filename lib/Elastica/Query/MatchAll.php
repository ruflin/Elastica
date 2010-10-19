<?php
/**
 * Match all query. Returns all results
 *
 * @link 
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Query_MatchAll extends Elastica_Query_Abstract
{
	public function toArray() {
		return array('match_all' => new stdClass());
	}
}
