<?php
namespace Elastica;

use Elastica\Exception\QueryBuilderException;
use Elastica\QueryBuilder\DSL;
use Elastica\QueryBuilder\Facade;
use Elastica\QueryBuilder\Version;
use Elastica\QueryBuilder\Version\Version150;

/**
 * Query Builder.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 */
class QueryBuilder
{
    /**
     * @var Version
     */
    private $_version;

    /**
     * @var Facade[]
     */
    private $_facades = array();

    /**
     * Constructor.
     *
     * @param Version $version
     */
    public function __construct(Version $version = null)
    {
        $this->_version = $version ?: new Version150();

        $this->addDSL(new DSL\Query());
        $this->addDSL(new DSL\Filter());
        $this->addDSL(new DSL\Aggregation());
        $this->addDSL(new DSL\Suggest());
    }

    /**
     * Returns Facade for custom DSL object.
     *
     * @param $dsl
     * @param array $arguments
     *
     * @throws QueryBuilderException
     *
     * @return Facade
     */
    public function __call($dsl, array $arguments)
    {
        if (false === isset($this->_facades[$dsl])) {
            throw new QueryBuilderException('DSL "'.$dsl.'" not supported');
        }

        return $this->_facades[$dsl];
    }

    /**
     * Adds a new DSL object.
     *
     * @param DSL $dsl
     */
    public function addDSL(DSL $dsl)
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
     * Filter DSL.
     *
     * @return DSL\Filter
     */
    public function filter()
    {
        return $this->_facades[DSL::TYPE_FILTER];
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
}
