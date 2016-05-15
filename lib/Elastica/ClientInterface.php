<?php

namespace Elastica;

/**
 * Client to connect the the Elasticsearch server.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
interface ClientInterface
{
    /**
     * Returns the index for the given connection.
     *
     * @param string $name Index name to create connection to
     *
     * @return \Elastica\Index Index for the given name
     */
    public function getIndex($name);

    /**
     * Makes calls to the Elasticsearch server based on this index.
     *
     * It's possible to make any REST query directly over this method
     *
     * @param string $path Path to call
     * @param string $method Rest method to use (GET, POST, DELETE, PUT)
     * @param array $data OPTIONAL Arguments as array
     * @param array $query OPTIONAL Query params
     *
     * @throws Exception\ConnectionException|\Exception
     *
     * @return Response Response object
     */
    public function request($path, $method = Request::GET, $data = [], array $query = []);
}
