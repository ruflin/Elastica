<?php

namespace Elastica\Connection;

use Elastica\Transport\TransportInterface;

/**
 * Elastica connection instance to an ElasticSearch node.
 */
interface ConnectionInterface
{
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
     * Set if the connection is enabled or not.
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled);
}
