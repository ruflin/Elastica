<?php

namespace Elastica\Index;

use Elastic\Elasticsearch\Response\Elasticsearch;
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
     * @var Elasticsearch Response object
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
     * @return Elasticsearch Response object
     */
    public function getResponse(): Elasticsearch
    {
        return $this->_response;
    }

    /**
     * Returns the raw recovery info.
     *
     * @return array Recovery info
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * Retrieve the Recovery data.
     *
     * @return $this
     */
    public function refresh(): self
    {
        $this->_data = $this->getRecoveryData();

        return $this;
    }

    /**
     * @return array
     */
    protected function getRecoveryData()
    {
        $this->_response = $this->getIndex()->getClient()->indices()->recovery(['index' => $this->getIndex()->getName()]);

        return $this->getResponse()->asArray();
    }
}
