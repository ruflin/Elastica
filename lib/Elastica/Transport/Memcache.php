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
     * @param \Elastica\Request $request
     * @param  array                               $params Host, Port, ...
     * @throws \Elastica\Exception\ResponseException
     * @throws \Elastica\Exception\InvalidException
     * @return \Elastica\Response                   Response object
     */
    public function exec(Request $request, array $params)
    {
        $memcache = new \Memcache();
        $memcache->connect($this->getConnection()->getHost(), $this->getConnection()->getPort());

        // Finds right function name
        $function = strtolower($request->getMethod());

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

        switch ($function) {
            case 'post':
            case 'put':
                $memcache->set($request->getPath(), $content);
                break;
            case 'get':
                $responseString = $memcache->get($request->getPath() . '?source=' . $content);
                break;
            case 'delete':
                break;
            default:
                throw new InvalidException('Method ' . $function . ' is not supported in memcache transport');

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
