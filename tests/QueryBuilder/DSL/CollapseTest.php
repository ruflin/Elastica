<?php

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Collapse;
use Elastica\QueryBuilder\DSL;

/**
 * @internal
 */
class CollapseTest extends AbstractDSLTest
{
    /**
     * @group unit
     */
    public function testType(): void
    {
        $collapseDSL = new DSL\Collapse();

        $this->assertInstanceOf(DSL::class, $collapseDSL);
        $this->assertEquals(DSL::TYPE_COLLAPSE, $collapseDSL->getType());
    }

    /**
     * @group unit
     */
    public function testInterface(): void
    {
        $collapseDSL = new DSL\Collapse();

        $this->_assertImplemented($collapseDSL, 'inner_hits', Collapse\InnerHits::class, []);
        // Make sure collapse returns an instance of Collapse\InnerHits instead of Query\InnerHits
        $this->assertInstanceOf(Collapse\InnerHits::class, $collapseDSL->inner_hits());
    }
}
