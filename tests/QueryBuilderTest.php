<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Collapse;
use Elastica\Exception\QueryBuilderException;
use Elastica\Query\AbstractQuery;
use Elastica\QueryBuilder;
use Elastica\Suggest\AbstractSuggest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class QueryBuilderTest extends Base
{
    #[Group('unit')]
    public function testCustomDSL(): void
    {
        $qb = new QueryBuilder();

        // test custom DSL
        $qb->addDSL(new CustomDSL());

        $this->assertTrue($qb->custom()->custom_method(), 'custom DSL execution failed');

        // test custom DSL exception message
        $exceptionMessage = '';
        try {
            $qb->invalid();
        } catch (QueryBuilderException $exception) {
            $exceptionMessage = $exception->getMessage();
        }

        $this->assertEquals('DSL "invalid" not supported', $exceptionMessage);
    }

    #[Group('unit')]
    public function testFacade(): void
    {
        $qb = new QueryBuilder();

        // test one example QueryBuilder flow for each default DSL type
        $this->assertInstanceOf(AbstractQuery::class, $qb->query()->match());
        $this->assertInstanceOf(AbstractAggregation::class, $qb->aggregation()->avg('name'));
        $this->assertInstanceOf(AbstractSuggest::class, $qb->suggest()->term('name', 'field'));

        // Collapse is a special case of the above; it doesn't have an abstract base class for individual parts right
        // now because 'inner_hits' is the only thing that can be set besides field and concurrency.
        $this->assertInstanceOf(Collapse\InnerHits::class, $qb->collapse()->inner_hits());
    }
}

class CustomDSL implements QueryBuilder\DSL
{
    public function getType(): string
    {
        return 'custom';
    }

    public function custom_method(): bool
    {
        return true;
    }
}
