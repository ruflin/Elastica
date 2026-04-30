<?php

declare(strict_types=1);

namespace Elastica\Index;

use Elastica\Index as BaseIndex;
use Elastica\Response;

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
     * @var Response Response object
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
    protected BaseIndex $_index;

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
     */
    public function getResponse(): Response
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
        $client = $this->getIndex()->getClient();
        $this->_response = $client->toElasticaResponse($client->indices()->recovery(['index' => $this->getIndex()->getName()]));

        return $this->getResponse()->getData();
    }
}
