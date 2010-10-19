<?php
/**
 * Cluster informations for elasticsearch
 *
 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Cluster
{
	/**
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster/state
	 */
	public function getState($args = array()) {
		throw new Exception('not implemented yet');
	}
	
	/**
	 * Returns the statistics for the given nodes
	 * 
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster/nodes_stats/
	 */
	public function getStats(array $args) {
		throw new Exception('not implemented yet');		 
	}
	
	/**
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster/nodes_info/
	 */
	public function getInfo(array $args) {
		throw new Exception('not implemented yet');		 
	}
	
	/**
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster/health/
	 */
	public function getHealth($args = array()) {
		throw new Exception('not implemented yet');
	}
	
	/**
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster/nodes_restart/
	 */
	public function restart(array $args) {
		throw new Exception('not implemented yet');
	   
	}
	
	/**
	 * @link http://www.elasticsearch.com/docs/elasticsearch/rest_api/admin/cluster/nodes_shutdown/
	 */
	public function shutdown(array $args) {
		throw new Exception('not implemented yet');
	   
	}
}
