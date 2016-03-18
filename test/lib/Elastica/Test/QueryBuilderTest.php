<?php

namespace Elastica\Test;

use Elastica\Exception\QueryBuilderException;
use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\Suggest;

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
        $this->assertInstanceOf('Elastica\Query\AbstractQuery', $qb->query()->match());
        $this->hideDeprecated();
        $this->assertInstanceOf('Elastica\Filter\AbstractFilter', $qb->filter()->bool());
        $this->showDeprecated();
        $this->assertInstanceOf('Elastica\Aggregation\AbstractAggregation', $qb->aggregation()->avg('name'));
        $this->assertInstanceOf('Elastica\Suggest\AbstractSuggest', $qb->suggest()->term('name', 'field'));
    }

    /**
     * @group unit
     */
    public function testFacadeException()
    {
        $qb = new QueryBuilder(new QueryBuilder\Version\Version100());

        // undefined
        $exceptionMessage = '';
        try {
            $qb->query()->invalid();
        } catch (QueryBuilderException $exception) {
            $exceptionMessage = $exception->getMessage();
        }

        $this->assertEquals('undefined query "invalid"', $exceptionMessage);

        // unsupported
        $exceptionMessage = '';
        try {
            $qb->aggregation()->top_hits('top_hits');
        } catch (QueryBuilderException $exception) {
            $exceptionMessage = $exception->getMessage();
        }

        $this->assertEquals('aggregation "top_hits" in Version100 not supported', $exceptionMessage);
    }
}

class CustomDSL implements QueryBuilder\DSL
{
    public function getType()
    {
        return 'custom';
    }

    public function custom_method()
    {
        return true;
    }
}
