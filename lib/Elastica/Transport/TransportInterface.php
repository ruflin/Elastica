<?php

namespace Elastica\Transport;

use Elastica\Request;
use Elastica\Response;

interface TransportInterface
{
    /**
     * Executes a request.
     *
     * @param Request $request
     * @param array $params Hostname, port, path, ...
     * @return Response
     */
    public function exec(Request $request, array $params);
}
