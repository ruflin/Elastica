<?php

namespace Elastica\Index;

use Elastica\Index as BaseIndex;
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
     *
     * @var BaseIndex Index object
     */
    protected $_index;

    /**
     * Construct.
     *
     * @param BaseIndex $index Index object
     */
    public function __construct(BaseIndex $index)
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
    public function get()
    {
        $data = $this->getData();

        foreach (\func_get_args() as $arg) {
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
     *
     * @return BaseIndex Index object
     */
    public function getIndex(): BaseIndex
    {
        return $this->_index;
    }

    /**
     * Returns response object.
     *
     * @return Response Response object
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
        $this->_response = $this->getIndex()->requestEndpoint(new \Elasticsearch\Endpoints\Indices\Stats());
        $this->_data = $this->getResponse()->getData();
    }
}
