<?php

namespace Elastica\QueryBuilder;


use Elastica\Exception\QueryBuilderException;
use Elastica\QueryBuilder\DSL;
use Elastica\QueryBuilder\Version;

/**
 * Facade for a specific DSL object
 *
 * @package Elastica
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 **/
class Facade {

    /**
     * @var DSL
     */
    private $dsl;

    /**
     * @var Version
     */
    private $version;

    /**
     * Constructor
     *
     * @param DSL $dsl
     * @param Version $version
     */
    public function __construct(DSL $dsl, Version $version) {
        $this->dsl = $dsl;
        $this->version = $version;
    }

    /**
     * Executes DSL methods
     *
     * @param $name
     * @param array $arguments
     * @return mixed
     * @throws QueryBuilderException
     */
    public function __call($name, array $arguments) {
        // defined check
        if(false === method_exists($this->dsl, $name)) {
            throw new QueryBuilderException(
                'undefined ' . $this->dsl->getType() . ' "' . $name . '"'
            );
        }

        // version support check
        if(false === $this->version->supports($name, $this->dsl->getType())) {
            $reflection = new \ReflectionClass($this->version);
            throw new QueryBuilderException(
                $this->dsl->getType() . ' "' . $name . '" in ' . $reflection->getShortName() . ' not supported'
            );
        }

        return call_user_func_array(array($this->dsl, $name), $arguments);
    }

} 