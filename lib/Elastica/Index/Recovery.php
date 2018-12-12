<?php

namespace Elastica\Index;

use Elastica\Index as BaseIndex;

/**
 * Elastica index recovery object.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/indices-recovery.html
 */
class Recovery
{
    /**
     * Response.
     *
     * @var \Elastica\Response Response object
     */
    protected $_response;

    /**
     * Recovery info.
     *
     * @var array Recovery info
     */
    protected $_data = [];

    /**
     * Index.
     *
     * @var \Elastica\Index Index object
     */
    protected $_index;

    /**
     * Construct.
     *
     * @param \Elastica\Index $index Index object
     */
    public function __construct(BaseIndex $index)
    {
        $this->_index = $index;
        $this->refresh();
    }

    /**
     * Returns the index object.
     *
     * @return \Elastica\Index Index object
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * Returns response object.
     *
     * @return \Elastica\Response Response object
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Returns the raw recovery info.
     *
     * @return array Recovery info
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @return mixed
     */
    protected function getRecoveryData()
    {
        $endpoint = new \Elasticsearch\Endpoints\Indices\Recovery();

        $this->_response = $this->getIndex()->requestEndpoint($endpoint);

        return $this->getResponse()->getData();
    }

    /**
     * Retrieve the Recovery data.
     *
     * @return $this
     */
    public function refresh()
    {
        $this->_data = $this->getRecoveryData();

        return $this;
    }
}
