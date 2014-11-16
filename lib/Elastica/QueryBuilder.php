<?php

namespace Elastica;

use Elastica\Exception\QueryBuilderException;
use Elastica\QueryBuilder\DSL;
use Elastica\QueryBuilder\Facade;
use Elastica\QueryBuilder\Version;
use Elastica\QueryBuilder\Version\Version140;

/**
 * Query Builder
 *
 * @package Elastica
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 */
class QueryBuilder
{
    /**
     * @var Version
     */
    private $version;

    /**
     * @var Facade[]
     */
    private $facades = array();

    /**
     * Constructor
     *
     * @param Version $version
     */
    public function __construct(Version $version = null)
    {
        $this->version = $version ?: new Version140();

        $this->addDSL(new DSL\Query());
        $this->addDSL(new DSL\Filter());
        $this->addDSL(new DSL\Aggregation());
        $this->addDSL(new DSL\Suggest());
    }

    /**
     * Returns Facade for custom DSL object
     *
     * @param $dsl
     * @param array $arguments
     * @return Facade
     * @throws QueryBuilderException
     */
    public function __call($dsl, array $arguments)
    {
        if (false === isset($this->facades[$dsl])) {
            throw new QueryBuilderException('DSL "' . $dsl . '" not supported');
        }

        return $this->facades[$dsl];
    }

    /**
     * Adds a new DSL object
     *
     * @param DSL $dsl
     */
    public function addDSL(DSL $dsl)
    {
        $this->facades[$dsl->getType()] = new Facade($dsl, $this->version);
    }

    /*
     * convenience methods
     */

    /**
     * Query DSL
     *
     * @return DSL\Query
     */
    public function query()
    {
        return $this->facades[DSL::TYPE_QUERY];
    }

    /**
     * Filter DSL
     *
     * @return DSL\Filter
     */
    public function filter()
    {
        return $this->facades[DSL::TYPE_FILTER];
    }

    /**
     * Aggregation DSL
     *
     * @return DSL\Aggregation
     */
    public function aggregation()
    {
        return $this->facades[DSL::TYPE_AGGREGATION];
    }

    /**
     * Suggest DSL
     *
     * @return DSL\Suggest
     */
    public function suggest()
    {
        return $this->facades[DSL::TYPE_SUGGEST];
    }
}
