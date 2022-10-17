<?php

namespace Elastica\ResultSet;

use Elastica\Query;
use Elastica\Response;
use Elastica\ResultSet;

class ProcessingBuilder implements BuilderInterface
{
    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    public function __construct(BuilderInterface $builder, ProcessorInterface $processor)
    {
        $this->builder = $builder;
        $this->processor = $processor;
    }

    /**
     * Runs any registered transformers on the ResultSet before
     * returning it, allowing the transformers to inject additional
     * data into each Result.
     */
    public function buildResultSet(Response $response, Query $query, int $apiVersion): ResultSet
    {
        $resultSet = $this->builder->buildResultSet($response, $query, $apiVersion);

        $this->processor->process($resultSet);

        return $resultSet;
    }
}
