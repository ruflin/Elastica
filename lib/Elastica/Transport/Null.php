<?php
/**
 * Elastica Null Transport object
 *
 * @package Elastica
 * @author James Boehmer <james.boehmer@jamesboehmer.com>
 */
class Elastica_Transport_Null extends Elastica_Transport_Abstract
{
	/**
     * Null transport.
     *
     * @param  array             $params Hostname, port, path, ...
     * @return Elastica_Response Response empty object
     */
    public function exec(array $params)
    {
    	$response = array(
    			"took" => 0,
    			"timed_out" => FALSE,
    			"_shards" => array(
    					"total" => 0,
    					"successful" => 0,
    					"failed" => 0
    					),
    			"hits" => array(
    					"total" => 0,
    					"max_score" => NULL,
    					"hits" => array()
    					),
    			"params" => $params
    			);
     	return new Elastica_Response(json_encode($response));
    }
}
