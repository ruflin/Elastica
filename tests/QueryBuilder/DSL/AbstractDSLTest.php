<?php

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Exception\NotImplementedException;
use Elastica\QueryBuilder\DSL;
use Elastica\Test\Base as BaseTest;

abstract class AbstractDSLTest extends BaseTest
{
    protected function _assertImplemented(DSL $dsl, string $methodName, string $className, array $arguments): void
    {
        // Check method existence
        $this->assertTrue(\method_exists($dsl, $methodName));

        // Check returned value
        $return = \call_user_func_array([$dsl, $methodName], $arguments);
        $this->assertTrue(\class_exists($className), 'Class not exists but NotImplementedException is not thrown');
        $this->assertInstanceOf($className, $return);

        // Check method signature
        $class = new \ReflectionClass($className);
        $method = new \ReflectionMethod(\get_class($dsl), $methodName);
        if (!$class->hasMethod('__construct')) {
            $this->assertEmpty($method->getParameters(), 'Constructor is not defined, but method has some parameters');
        } else {
            $this->_assertParametersEquals($class->getMethod('__construct')->getParameters(), $method->getParameters());
        }
    }

    protected function _assertNotImplemented(DSL $dsl, string $methodName, array $arguments): void
    {
        try {
            \call_user_func([$dsl, $methodName], $arguments);
            $this->fail('NotImplementedException is not thrown');
        } catch (NotImplementedException $ex) {
            // expected
        }
    }

    /**
     * @param \ReflectionParameter[] $left
     * @param \ReflectionParameter[] $right
     */
    protected function _assertParametersEquals(array $left, array $right): void
    {
        $countLeft = \count($left);
        $this->assertCount($countLeft, $right, 'Parameters count mismatch');

        for ($i = 0; $i < $countLeft; ++$i) {
            $this->assertEquals($left[$i]->getName(), $right[$i]->getName(), 'Parameters names mismatch');
            $this->assertEquals($left[$i]->isOptional(), $right[$i]->isOptional(), 'Parameters optionality mismatch');
            $this->assertEquals($this->_getHintName($left[$i]), $this->_getHintName($right[$i]), 'Parameters typehints mismatch');
            $this->assertEquals($this->_getDefaultValue($left[$i]), $this->_getDefaultValue($right[$i]), 'Default values mismatch');
        }
    }

    /**
     * @return string|null
     */
    protected function _getDefaultValue(\ReflectionParameter $param)
    {
        if ($param->isOptional()) {
            return $param->getDefaultValue();
        }

        return null;
    }

    /**
     * @return string|null
     */
    protected function _getHintName(\ReflectionParameter $param)
    {
        if ($param->isCallable()) {
            return 'callable';
        }

        if ($param->isArray()) {
            return 'array';
        }

        if ($class = $param->getClass()) {
            return $class->getName();
        }

        return null;
    }
}
