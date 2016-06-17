<?php
namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Exception\NotImplementedException;
use Elastica\QueryBuilder\DSL;
use Elastica\Test\Base as BaseTest;

abstract class AbstractDSLTest extends BaseTest
{
    /**
     * @param DSL    $dsl
     * @param string $methodName
     * @param string $className
     * @param array  $arguments
     */
    protected function _assertImplemented(DSL $dsl, $methodName, $className, $arguments)
    {
        // Check method existence
        $this->assertTrue(method_exists($dsl, $methodName));

        // Check returned value
        $return = call_user_func_array(array($dsl, $methodName), $arguments);
        $this->assertTrue(class_exists($className), 'Class not exists but NotImplementedException is not thrown');
        $this->assertInstanceOf($className, $return);

        // Check method signature
        $class = new \ReflectionClass($className);
        $method = new \ReflectionMethod(get_class($dsl), $methodName);
        if (!$class->hasMethod('__construct')) {
            $this->assertEmpty($method->getParameters(), 'Constructor is not defined, but method has some parameters');
        } else {
            $this->_assertParametersEquals($class->getMethod('__construct')->getParameters(), $method->getParameters());
        }
    }

    /**
     * @param DSL    $dsl
     * @param string $name
     */
    protected function _assertNotImplemented(DSL $dsl, $methodName, $arguments)
    {
        try {
            call_user_func(array($dsl, $methodName), $arguments);
            $this->fail('NotImplementedException is not thrown');
        } catch (NotImplementedException $ex) {
            // expected
        }
    }

    /**
     * @param \ReflectionParameter[] $left
     * @param \ReflectionParameter[] $right
     */
    protected function _assertParametersEquals($left, $right)
    {
        $this->assertEquals(count($left), count($right), 'Parameters count mismatch');

        for ($i = 0; $i < count($left); ++$i) {
            $this->assertEquals($left[$i]->getName(), $right[$i]->getName(), 'Parameters names mismatch');
            $this->assertEquals($left[$i]->isOptional(), $right[$i]->isOptional(), 'Parameters optionality mismatch');
            $this->assertEquals($this->_getHintName($left[$i]), $this->_getHintName($right[$i]), 'Parameters typehints mismatch');
            $this->assertEquals($this->_getDefaultValue($left[$i]), $this->_getDefaultValue($right[$i]), 'Default values mismatch');
        }
    }

    /**
     * @param \ReflectionParameter $param
     *
     * @return string|null
     */
    protected function _getDefaultValue(\ReflectionParameter $param)
    {
        if ($param->isOptional()) {
            return $param->getDefaultValue();
        }
    }

    /**
     * @param \ReflectionParameter $param
     *
     * @return string|null
     */
    protected function _getHintName(\ReflectionParameter $param)
    {
        if (version_compare(phpversion(), '5.4', '>=') && $param->isCallable()) {
            return 'callable';
        }

        if ($param->isArray()) {
            return 'array';
        }

        if ($class = $param->getClass()) {
            return $class->getName();
        }
    }
}
