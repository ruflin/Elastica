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
     	return new Elastica_Response(null);
    }
}
