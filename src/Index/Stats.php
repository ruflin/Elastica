<?php

declare(strict_types=1);

namespace Elastica\Index;

use Elastica\Index;
use Elastica\Response;

/**
 * Elastica index stats object.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-stats.html
 */
class Stats
{
    /**
     * Response.
     *
     * @var Response Response object
     */
    protected $_response;

    /**
     * Stats info.
     *
     * @var array Stats info
     */
    protected $_data = [];

    /**
     * Index.
     */
    protected Index $_index;

    /**
     * Construct.
     */
    public function __construct(Index $index)
    {
        $this->_index = $index;
        $this->refresh();
    }

    /**
     * Returns the raw stats info.
     *
     * @return array Stats info
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * Returns the entry in the data array based on the params.
     * Various params possible.
     *
     * @return mixed Data array entry or null if not found
     */
    public function get(...$args)
    {
        $data = $this->getData();

        foreach ($args as $arg) {
            if (isset($data[$arg])) {
                $data = $data[$arg];
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * Returns the index object.
     */
    public function getIndex(): Index
    {
        return $this->_index;
    }

    /**
     * Returns response object.
     */
    public function getResponse(): Response
    {
        return $this->_response;
    }

    /**
     * Reloads all status data of this object.
     */
    public function refresh(): void
    {
        $client = $this->getIndex()->getClient();

        $this->_response = $client->toElasticaResponse(
            $client->indices()->stats(
                ['index' => $this->getIndex()->getName()]
            )
        );

        $this->_data = $this->getResponse()->getData();
    }
}
