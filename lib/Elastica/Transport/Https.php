<?php

namespace Elastica\Transport;

/**
 * Elastica Http Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Https extends Http
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
