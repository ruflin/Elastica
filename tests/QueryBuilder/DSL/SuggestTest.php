<?php

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\QueryBuilder\DSL;
use Elastica\Suggest;

/**
 * @internal
 */
class SuggestTest extends AbstractDSLTest
{
    /**
     * @group unit
     */
    public function testType(): void
    {
        $suggestDSL = new DSL\Suggest();

        $this->assertInstanceOf(DSL::class, $suggestDSL);
        $this->assertEquals(DSL::TYPE_SUGGEST, $suggestDSL->getType());
    }

    /**
     * @group unit
     */
    public function testInterface(): void
    {
        $suggestDSL = new DSL\Suggest();

        $this->_assertImplemented($suggestDSL, 'completion', Suggest\Completion::class, ['name', 'field']);
        $this->_assertImplemented($suggestDSL, 'phrase', Suggest\Phrase::class, ['name', 'field']);
        $this->_assertImplemented($suggestDSL, 'term', Suggest\Term::class, ['name', 'field']);
    }
}
