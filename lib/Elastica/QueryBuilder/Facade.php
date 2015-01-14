<?php

namespace Elastica\QueryBuilder;

use Elastica\Exception\QueryBuilderException;

/**
 * Facade for a specific DSL object
 *
 * @package Elastica
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 **/
class Facade
{
    /**
     * @var DSL
     */
    private $_dsl;

    /**
     * @var Version
     */
    private $_version;

    /**
     * Constructor
     *
     * @param DSL     $dsl
     * @param Version $version
     */
    public function __construct(DSL $dsl, Version $version)
    {
        $this->_dsl = $dsl;
        $this->_version = $version;
    }

    /**
     * Executes DSL methods
     *
     * @param $name
     * @param  array                 $arguments
     * @return mixed
     * @throws QueryBuilderException
     */
    public function __call($name, array $arguments)
    {
        // defined check
        if (false === method_exists($this->_dsl, $name)) {
            throw new QueryBuilderException(
                'undefined '.$this->_dsl->getType().' "'.$name.'"'
            );
        }

        // version support check
        if (false === $this->_version->supports($name, $this->_dsl->getType())) {
            $reflection = new \ReflectionClass($this->_version);
            throw new QueryBuilderException(
                $this->_dsl->getType().' "'.$name.'" in '.$reflection->getShortName().' not supported'
            );
        }

        return call_user_func_array(array($this->_dsl, $name), $arguments);
    }
}
