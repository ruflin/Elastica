<?php

declare(strict_types=1);

namespace Elastica\QueryBuilder;

use Elastica\Exception\QueryBuilderException;

/**
 * Facade for a specific DSL object.
 *
 * @author Manuel Andreo Garcia <andreo.garcia@googlemail.com>
 */
class Facade
{
    private DSL $_dsl;

    private Version $_version;

    /**
     * Constructor.
     */
    public function __construct(DSL $dsl, Version $version)
    {
        $this->_dsl = $dsl;
        $this->_version = $version;
    }

    /**
     * Executes DSL methods.
     *
     * @throws QueryBuilderException
     */
    public function __call(string $name, array $arguments)
    {
        // defined check
        if (false === \method_exists($this->_dsl, $name)) {
            throw new QueryBuilderException('undefined '.$this->_dsl->getType().' "'.$name.'"');
        }

        // version support check
        if (false === $this->_version->supports($name, $this->_dsl->getType())) {
            $reflection = new \ReflectionClass($this->_version);
            throw new QueryBuilderException($this->_dsl->getType().' "'.$name.'" in '.$reflection->getShortName().' not supported');
        }

        return $this->_dsl->{$name}(...$arguments);
    }
}
