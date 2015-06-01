<?php
namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\QueryBuilder\DSL;
use Elastica\Test\Base as BaseTest;

class SuggestTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testType()
    {
        $suggestDSL = new DSL\Suggest();

        $this->assertInstanceOf('Elastica\QueryBuilder\DSL', $suggestDSL);
        $this->assertEquals(DSL::TYPE_SUGGEST, $suggestDSL->getType());
    }

    /**
     * @group unit
     */
    public function testFilters()
    {
        $suggestDSL = new DSL\Suggest();

        foreach ($this->_getSuggesters() as $methodName => $arguments) {
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

    /**
     * @return array
     */
    protected function _getSuggesters()
    {
        return array(
            'term' => array('name', 'field'),
            'phrase' => array('name', 'field'),
            'completion' => array('name', 'field'),
            'context' => array(),
        );
    }
}
