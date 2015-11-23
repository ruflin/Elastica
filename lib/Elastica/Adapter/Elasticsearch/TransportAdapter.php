<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 2015/11/23
 * Time: 10:24 PM
 */

namespace Elastica\Adapter\Elasticsearch;


use Elastica\Request;
use Elasticsearch\Transport;

/**
 * Simple adapter so we can performRequest on a Request object,
 * instead of a set of strings and arrays
 *
 * Class TransportAdapter
 * @package Elastica\Adapter\Elasticsearch
 */
class TransportAdapter
{
    /** @var Transport */
    protected $transport;

    /**
     * @param Transport $transport
     */
    public function __construct(Transport $transport) {
        $this->transport = $transport;
    }

    /**
     * @inheritdoc
     */
    public function getConnection()
    {
        return $this->transport->getConnection();
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Elasticsearch\Common\Exceptions\NoNodesAvailableException
     * @throws \Exception
     */
    public function performRequest(Request $request)
    {
        return $this->transport->performRequest(
            $request->getMethod(),
            $request->getPath(),
            $request->getQuery(),
            $request->getParams()
        );
    }

    /**
     * @inheritdoc
     */
    public function shouldRetry($request)
    {
        return $this->transport->shouldRetry($request);
    }

    /**
     * @inheritdoc
     */
    public function getLastConnection()
    {
        return $this->transport->getLastConnection();
    }
}
