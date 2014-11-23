<?php

namespace Elastica\Test;

use Elastica\Exception\QueryBuilderException;
use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\Suggest;

class QueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function example()
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
                    )
                ,
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
                        ->addAggregation(
                            $qb->aggregation()->min('name', 'field')
                        )
                )
        )->setSuggest(new Suggest(
            $qb->suggest()->phrase('name', 'field')
        ));
    }

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

    public function testVersions()
    {
        $dsl = array(
            new QueryBuilder\DSL\Query(),
            new QueryBuilder\DSL\Filter(),
            new QueryBuilder\DSL\Aggregation(),
            new QueryBuilder\DSL\Suggest(),
        );

        $versions = array(
            new QueryBuilder\Version\Version090(),
            new QueryBuilder\Version\Version100(),
            new QueryBuilder\Version\Version110(),
            new QueryBuilder\Version\Version120(),
            new QueryBuilder\Version\Version130(),
            new QueryBuilder\Version\Version140(),
        );

        foreach($versions as $version) {
            $this->assertVersions($version, $dsl);
        }
    }

    private function assertVersions(QueryBuilder\Version $version, array $dsl)
    {
        foreach ($version->getQueries() as $query) {
            $this->assertTrue(
                method_exists($dsl[0], $query),
                'query "' . $query . '" in ' . get_class($version) . ' must be defined in ' . get_class($dsl[0])
            );
        }

        foreach ($version->getFilters() as $filter) {
            $this->assertTrue(
                method_exists($dsl[1], $filter),
                'filter "' . $filter . '" in ' . get_class($version) . ' must be defined in ' . get_class($dsl[1])
            );
        }

        foreach ($version->getAggregations() as $aggregation) {
            $this->assertTrue(
                method_exists($dsl[2], $aggregation),
                'aggregation "' . $aggregation . '" in ' . get_class($version) . ' must be defined in ' . get_class($dsl[2])
            );
        }

        foreach ($version->getSuggesters() as $suggester) {
            $this->assertTrue(
                method_exists($dsl[3], $suggester),
                'suggester "' . $suggester . '" in ' . get_class($version) . ' must be defined in ' . get_class($dsl[3])
            );
        }
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
