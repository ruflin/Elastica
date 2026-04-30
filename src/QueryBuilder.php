<?php

declare(strict_types=1);

namespace Elastica;

use Elastica\Exception\QueryBuilderException;
use Elastica\QueryBuilder\DSL;
use Elastica\QueryBuilder\Facade;
use Elastica\QueryBuilder\Version;

/**
 * Query Builder.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 */
class QueryBuilder
{
    private Version $_version;

    /**
     * @var Facade[]
     */
    private $_facades = [];

    /**
     * Constructor.
     */
    public function __construct(?Version $version = null)
    {
        $this->_version = $version ?? new Version\Latest();

        $this->addDSL(new DSL\Query());
        $this->addDSL(new DSL\Aggregation());
        $this->addDSL(new DSL\Suggest());
        $this->addDSL(new DSL\Collapse());
    }

    /**
     * Returns Facade for custom DSL object.
     *
     * @throws QueryBuilderException
     */
    public function __call(string $dsl, array $arguments): Facade
    {
        if (false === isset($this->_facades[$dsl])) {
            throw new QueryBuilderException('DSL "'.$dsl.'" not supported');
        }

        return $this->_facades[$dsl];
    }

    /**
     * Adds a new DSL object.
     */
    public function addDSL(DSL $dsl): void
    {
        $this->_facades[$dsl->getType()] = new Facade($dsl, $this->_version);
    }

    /*
     * convenience methods
     */

    /**
     * Query DSL.
     *
     * @return DSL\Query
     */
    public function query()
    {
        return $this->_facades[DSL::TYPE_QUERY];
    }

    /**
     * Aggregation DSL.
     *
     * @return DSL\Aggregation
     */
    public function aggregation()
    {
        return $this->_facades[DSL::TYPE_AGGREGATION];
    }

    /**
     * Suggest DSL.
     *
     * @return DSL\Suggest
     */
    public function suggest()
    {
        return $this->_facades[DSL::TYPE_SUGGEST];
    }

    /**
     * Collapse DSL.
     *
     * @return DSL\Collapse
     */
    public function collapse()
    {
        return $this->_facades[DSL::TYPE_COLLAPSE];
    }
}
