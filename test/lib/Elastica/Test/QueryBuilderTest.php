<?php

namespace Elastica\Test;

use Elastica\Exception\QueryBuilderException;
use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\Suggest;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group grain
     */
    public function testQueryBuilder()
    {
        $qb = new QueryBuilder();

        $query = new Query();
        $query->setQuery(
            $qb->query()->filtered(
                $qb->query()->bool()
                    ->addMust(
                        $qb->query()->match()
                            ->setFieldAnalyzer('field', 'analyzer')
                    )
                    ->addShould(
                        $qb->query()->multi_match()
                            ->setTieBreaker(1.0)
                    )
                    ->addMustNot(
                        $qb->query()->boosting()
                            ->setNegativeBoost(1.5)
                    ),
                $qb->filter()->bool()
                    ->addMust(
                        $qb->filter()->exists('field')
                            ->setCached(false)
                    )
                    ->addShould(
                        $qb->filter()->geo_bounding_box(
                            'field',
                            array(
                                array(
                                    "lat" => 40.12,
                                    "lon" => -71.34
                                ), array(
                                    "lat" => 40.01,
                                    "lon" => -71.12
                                )
                            )
                        )
                    )
            )
        )->addAggregation(
            $qb->aggregation()->sum('name', 'field')
                ->addAggregation(
                    $qb->aggregation()->max('name', 'field')
                        ->addAggregation($qb->aggregation()->min('name', 'field'))
                )
        )->setSuggest(new Suggest(
                $qb->suggest()->phrase('name', 'field')
        ));
    }

    /**
     * @group grain
     */
    public function testCustomDSL()
    {
        $qb = new QueryBuilder();

        // test custom DSL
        $qb->addDSL(new CustomDSL());

        $this->assertTrue($qb->custom()->custom_method());

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
     * @group grain
     */
    public function testFacade()
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
