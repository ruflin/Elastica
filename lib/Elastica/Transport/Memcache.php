<?php
/**
 * Elastica Memcache Transport object
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
class Elastica_Transport_Memcache extends Elastica_Transport_Abstract
{
    /**
     * Makes calls to the elasticsearch server
     *
     * @param  array             $params Host, Port, ...
     * @return Elastica_Response Response object
     */
    public function exec(array $params)
    {
        $request = $this->getRequest();

        $memcache = new Memcache();
        $memcache->connect($params['host'], $params['port']);

        // Finds right function name
        $function = strtolower($request->getMethod());

        $data = $request->getData();

        $content = '';

        if (!empty($data)) {
            if (is_array($data)) {
                $content = json_encode($data);
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
                echo $responseString . PHP_EOL;
                break;
            case 'delete':
                break;
            default:
                throw new Elastica_Exception_Invalid('Method ' . $function . ' is not supported in memcache transport');

        }

        $response = new Elastica_Response($responseString);

        if (defined('DEBUG') && DEBUG) {
            $response->setQueryTime($end - $start);
            $response->setTransferInfo(curl_getinfo($conn));
        }

        if ($response->hasError()) {
            throw new Elastica_Exception_Response($response);
        }

        return $response;
    }
}
