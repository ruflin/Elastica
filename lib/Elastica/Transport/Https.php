<?php
/**
 * Elastica Http Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Transport_Https extends Elastica_Transport_Http
{
    /**
     * Https scheme
     *
     * @var string https scheme
     */
    protected $_scheme = 'https';

    /**
     * Overloads setupCurl to set SSL params
     *
     * @param resource $connection Curl connection resource
     */
    protected function _setupCurl($connection)
    {
        parent::_setupCurl($connection);
    }
}
