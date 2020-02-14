<?php

namespace Elastica\Transport;

/**
 * Elastica Http Transport object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Https extends Http
{
    /**
     * Https scheme.
     *
     * @var string https scheme
     */
    protected $_scheme = 'https';

    /**
     * Overloads setupCurl to set SSL params.
     *
     * @param resource $connection Curl connection resource
     */
    protected function _setupCurl($connection): void
    {
        parent::_setupCurl($connection);
    }
}
