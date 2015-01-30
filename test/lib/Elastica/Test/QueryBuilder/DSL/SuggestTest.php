<?php

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\QueryBuilder\DSL;

class SuggestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array (method name => arguments)
     */
    private $suggesters = array(
        'term' => array('name', 'field'),
        'phrase' => array('name', 'field'),
        'completion' => array(),
        'context' => array(),
    );

    public function __construct()
    {
    }

    public function testType()
    {
        $suggestDSL = new DSL\Suggest();

        $this->assertInstanceOf('Elastica\QueryBuilder\DSL', $suggestDSL);
        $this->assertEquals(DSL::TYPE_SUGGEST, $suggestDSL->getType());
    }

    public function testFilters()
    {
        $suggestDSL = new DSL\Suggest();

        foreach ($this->suggesters as $methodName => $arguments) {
            $this->assertTrue(
                method_exists($suggestDSL, $methodName),
                'method for suggest "'.$methodName.'" not found'
            );

            try {
                $return = call_user_func_array(array($suggestDSL, $methodName), $arguments);
                $this->assertInstanceOf('Elastica\Suggest\AbstractSuggest', $return);
            } catch (\Exception $exception) {
                $this->assertInstanceOf(
                    'Elastica\Exception\NotImplementedException',
                    $exception,
                    'breaking change in suggest "'.$methodName.'" found: '.$exception->getMessage()
                );
            }
        }
    }
}
