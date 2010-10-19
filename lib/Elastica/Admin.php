<?php
/**
 * Client to connect the the elasticsearch server
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Admin
{
	/**
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/indices/aliases/
	 * @param array $args Args
	 */
	public function addAliases(array $args) {
		throw new Elastica_Exception('Not implemented yet');
	}
	
	/**
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/indices/aliases/
	 * @param array $args Args
	 */
	public function removeAliases(array $args) {
		throw new Elastica_Exception('Not implemented yet');
	}
}
