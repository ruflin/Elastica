<?php

namespace Elastica\Transport;

use Elastica\Exception\InvalidException;
use Elastica\Exception\PartialShardFailureException;
use Elastica\Exception\ResponseException;
use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;

/**
 * Elastica Memcache Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Memcache extends AbstractTransport
{
    /**
     * Makes calls to the elasticsearch server
     *
     * @param  \Elastica\Request                     $request
     * @param  array                                 $params  Host, Port, ...
     * @throws \Elastica\Exception\ResponseException
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Response                    Response object
     */
    public function exec(Request $request, array $params)
    {
        $memcache = new \Memcache();
        $memcache->connect($this->getConnection()->getHost(), $this->getConnection()->getPort());

        $data = $request->getData();

        $content = '';

        if (!empty($data) || '0' === $data) {
            if (is_array($data)) {
                $content = JSON::stringify($data);
            } else {
                $content = $data;
            }

            // Escaping of / not necessary. Causes problems in base64 encoding of files
            $content = str_replace('\/', '/', $content);
        }

        $responseString = '';

        switch ($request->getMethod()) {
            case Request::POST:
            case Request::PUT:
                $memcache->set($request->getPath(), $content);
                break;
            case Request::GET:
                $responseString = $memcache->get($request->getPath().'?source='.$content);
                break;
            case Request::DELETE:
                $responseString = $memcache->delete($request->getPath().'?source='.$content);
                break;
            default:
            case Request::HEAD:
                throw new InvalidException('Method '.$request->getMethod().' is not supported in memcache transport');
        }

        $response = new Response($responseString);

        if ($response->hasError()) {
            throw new ResponseException($request, $response);
        }

        if ($response->hasFailedShards()) {
            throw new PartialShardFailureException($request, $response);
        }

        return $response;
    }
}
