<?php

declare(strict_types=1);

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Collapse;
use Elastica\QueryBuilder\DSL;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('unit')]
class CollapseTest extends AbstractDSLTestCase
{
    public function testType(): void
    {
        $collapseDSL = new DSL\Collapse();

        $this->assertEquals(DSL::TYPE_COLLAPSE, $collapseDSL->getType());
    }

    public function testInterface(): void
    {
        $collapseDSL = new DSL\Collapse();

        $this->_assertImplemented($collapseDSL, 'inner_hits', Collapse\InnerHits::class, []);
    }
}
