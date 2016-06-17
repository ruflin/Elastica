<?php
namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\QueryBuilder\DSL;

class SuggestTest extends AbstractDSLTest
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
    public function testInterface()
    {
        $suggestDSL = new DSL\Suggest();

        $this->_assertImplemented($suggestDSL, 'completion', 'Elastica\Suggest\Completion', array('name', 'field'));
        $this->_assertImplemented($suggestDSL, 'phrase', 'Elastica\Suggest\Phrase', array('name', 'field'));
        $this->_assertImplemented($suggestDSL, 'term', 'Elastica\Suggest\Term', array('name', 'field'));

        $this->_assertNotImplemented($suggestDSL, 'context', array());
    }
}
