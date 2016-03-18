<?php

namespace Elastica\Test;

/**
 * Errors collector for testing.
 *
 * @author Evgeniy Sokolov <ewgraf@gmail.com>
 */
class ErrorsCollector
{
    private $errors = array();

    /**
     * @var \PHPUnit_Framework_TestCase
     */
    private $testCase;

    public function __construct(\PHPUnit_Framework_TestCase $testCase = null)
    {
        $this->testCase = $testCase;
    }

    public function add($error)
    {
        $this->errors[] = $error;
    }

    public function getCount()
    {
        return count($this->errors);
    }

    public function assertOnlyOneDeprecatedError($deprecationMessage)
    {
        $this->testCase->assertSame(1, $this->getCount());
        $this->testCase->assertSame(1, $this->getDeprecatedCount());
        $this->testCase->assertSame($deprecationMessage, $this->getMessage(0));
    }

    public function assertOnlyDeprecatedErrors(array $deprecationMessages)
    {
        $this->testCase->assertSame(count($deprecationMessages), $this->getCount());
        $this->testCase->assertSame(count($deprecationMessages), $this->getDeprecatedCount());

        foreach ($deprecationMessages as $index => $message) {
            $this->testCase->assertSame($message, $this->getMessage($index));
        }
    }

    public function getDeprecatedCount()
    {
        $count = 0;

        foreach ($this->errors as $error) {
            if (E_USER_DEPRECATED === $error[0]) {
                ++$count;
            }
        }

        return $count;
    }

    public function getType($index)
    {
        return $this->errors[$index][0];
    }

    public function getMessage($index)
    {
        return $this->errors[$index][1];
    }
}
