<?php

namespace Elastica\Connection;

use Elastica\Transport\TransportInterface;

/**
 * Elastica connection instance to an ElasticSearch node.
 */
interface ConnectionInterface
{
    /**
     * Adds a HTTP Header.
     *
     * @param string $header      The HTTP Header
     * @param string $headerValue The HTTP Header Value
     */
    public function addHeader($header, $headerValue);

    /**
     * @return TransportInterface
     */
    public function getTransportObject();

    /**
     * If the connection is enabled and available for use.
     *
     * @return bool True if enabled
     */
    public function isEnabled();

    /**
     * Remove a HTTP Header.
     *
     * @param string $header The HTTP Header to remove
     */
    public function removeHeader($header);

    /**
     * Set if the connection is enabled or not.
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled);
}
