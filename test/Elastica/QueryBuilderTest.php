<?php

namespace Elastica\Test;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Exception\QueryBuilderException;
use Elastica\Query\AbstractQuery;
use Elastica\QueryBuilder;
use Elastica\Suggest\AbstractSuggest;

class QueryBuilderTest extends Base
{
    /**
     * @group unit
     */
    public function testCustomDSL()
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

    /**
     * @group unit
     */
    public function testFacade()
    {
        $qb = new QueryBuilder();

        // test one example QueryBuilder flow for each default DSL type
        $this->assertInstanceOf(AbstractQuery::class, $qb->query()->match());
        $this->assertInstanceOf(AbstractAggregation::class, $qb->aggregation()->avg('name'));
        $this->assertInstanceOf(AbstractSuggest::class, $qb->suggest()->term('name', 'field'));
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
