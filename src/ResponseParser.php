<?php

namespace Elastica;

use Elastica\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;

/**
 * @author PK <projekty@pawelkeska.eu>
 */
class ResponseParser
{
    /**
     * Error message.
     *
     * @return string Error message
     */
    public static function getError(ResponseInterface $response)
    {
        $error = self::getFullError($response);

        if (!$error) {
            return '';
        }

        if (\is_string($error)) {
            return $error;
        }

        $rootError = $error['root_cause'][0] ?? $error;

        $message = $rootError['reason'];
        if (isset($rootError['index'])) {
            $message .= ' [index: '.$rootError['index'].']';
        }

        if (isset($error['reason']) && $rootError['reason'] !== $error['reason']) {
            $message .= ' [reason: '.$error['reason'].']';
        }

        return $message;
    }

    /**
     * A keyed array representing any errors that occurred.
     *
     * In case of http://localhost:9200/_alias/test the error is a string
     *
     * @return array|string|null Error data or null if there is no error
     */
    public static function getFullError(ResponseInterface $response)
    {
        $response = \json_decode($response->getBody(), true);

        return $response['error'] ?? null;
    }

    /**
     * @return string Error string based on the error object
     */
    public static function getErrorMessage(ResponseInterface $response)
    {
        return self::getError($response);
    }

    /**
     * True if response has error.
     *
     * @return bool True if response has error
     */
    public static function hasError(ResponseInterface $response)
    {
        $response = $response = \json_decode($response->getBody(), true);

        return isset($response['error']);
    }

    /**
     * True if response has failed shards.
     *
     * @return bool True if response has failed shards
     */
    public static function hasFailedShards(ResponseInterface $response)
    {
        try {
            $shardsStatistics = self::getShardsStatistics($response);
        } catch (NotFoundException $e) {
            return false;
        }

        return \array_key_exists('failures', $shardsStatistics);
    }

    /**
     * Time request took.
     *
     * @throws NotFoundException
     *
     * @return int Time request took
     */
    public static function getEngineTime(ResponseInterface $response)
    {
        $data = \json_decode($response->getBody(), true);

        if (!isset($data['took'])) {
            throw new NotFoundException('Unable to find the field [took]from the response');
        }

        return $data['took'];
    }

    /**
     * Get the _shard statistics for the response.
     *
     * @throws NotFoundException
     *
     * @return array
     */
    public static function getShardsStatistics(ResponseInterface $response)
    {
        $data = \json_decode($response->getBody(), true);

        if (!isset($data['_shards'])) {
            throw new NotFoundException('Unable to find the field [_shards] from the response');
        }

        return $data['_shards'];
    }

    /**
     * Get the _scroll value for the response.
     *
     * @throws NotFoundException
     *
     * @return string
     */
    public static function getScrollId(ResponseInterface $response)
    {
        $data = \json_decode($response->getBody(), true);

        if (!isset($data['_scroll_id'])) {
            throw new NotFoundException('Unable to find the field [_scroll_id] from the response');
        }

        return $data['_scroll_id'];
    }
}
