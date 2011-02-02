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
	abstract public function toArray();
}
