<?php

declare(strict_types=1);

namespace Elastica\Test\Exception;

use Elastica\Exception\ExceptionInterface;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

abstract class AbstractExceptionTestCase extends BaseTest
{
    #[Group('unit')]
    public function testInheritance(): void
    {
        $className = $this->_getExceptionClass();
        $reflection = new \ReflectionClass($className);
        $this->assertTrue($reflection->isSubclassOf(\Exception::class));
        $this->assertTrue($reflection->implementsInterface(ExceptionInterface::class));
    }

    protected function _getExceptionClass()
    {
        $reflection = new \ReflectionObject($this);

        // Elastica\Test\Exception\RuntimeExceptionTest => Elastica\Exception\RuntimeExceptionTest
        $name = \preg_replace('/^Elastica\\\\Test/', 'Elastica', $reflection->getName());

        // Elastica\Exception\RuntimeExceptionTest => Elastica\Exception\RuntimeException
        return \preg_replace('/Test$/', '', $name);
    }
}
