<?php
namespace Elastica\Test\Exception;

use Elastica\Test\Base as BaseTest;

abstract class AbstractExceptionTest extends BaseTest
{
    protected function _getExceptionClass()
    {
        $reflection = new \ReflectionObject($this);

        // Elastica\Test\Exception\RuntimeExceptionTest => Elastica\Exception\RuntimeExceptionTest
        $name = preg_replace('/^Elastica\\\\Test/', 'Elastica', $reflection->getName());

        // Elastica\Exception\RuntimeExceptionTest => Elastica\Exception\RuntimeException
        $name = preg_replace('/Test$/', '', $name);

        return $name;
    }

    /**
     * @group unit
     */
    public function testInheritance()
    {
        $className = $this->_getExceptionClass();
        $reflection = new \ReflectionClass($className);
        $this->assertTrue($reflection->isSubclassOf('Exception'));
        $this->assertTrue($reflection->implementsInterface('Elastica\Exception\ExceptionInterface'));
    }
}
