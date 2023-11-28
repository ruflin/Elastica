<?php

namespace Elastica;

use Elastic\Elasticsearch\Response\Elasticsearch;

/**
 * @author PK <projekty@pawelkeska.eu>
 */
class ResponseChecker
{
    /**
     * True if response has error.
     *
     * @return bool True if response has error
     */
    public static function hasError(Elasticsearch $response)
    {
        $response = $response->asArray();

        return isset($response['error']);
    }

    /**
     * True if response has failed shards.
     *
     * @return bool True if response has failed shards
     */
    public static function hasFailedShards(Elasticsearch $response)
    {
        $data = $response->asArray();

        if (!isset($data['_shards'])) {
            return false;
        }

        return \array_key_exists('failures', $data['_shards']);
    }

    /**
     * Checks if the query returned ok.
     *
     * @return bool True if ok
     */
    public static function isOk(Elasticsearch $response)
    {
        $data = $response->asArray();

        // Bulk insert checks. Check every item
        if (isset($data['status'])) {
            return $data['status'] >= 200 && $data['status'] <= 300;
        }

        if (isset($data['items'])) {
            if (isset($data['errors']) && true === $data['errors']) {
                return false;
            }

            foreach ($data['items'] as $item) {
                if (isset($item['index']['ok']) && false == $item['index']['ok']) {
                    return false;
                }

                if (isset($item['index']['status']) && ($item['index']['status'] < 200 || $item['index']['status'] >= 300)) {
                    return false;
                }
            }

            return true;
        }

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() <= 300) {
            // http status is ok
            return true;
        }

        return isset($data['ok']) && $data['ok'];
    }
}
