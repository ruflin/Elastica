<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\PercentilesBucket;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class PercentilesBucketTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testPercentilesBucketAggregation(): void
    {
        $query = Query::create([])
            ->addAggregation(
                (new Terms('pages'))
                    ->setField('page')
                    ->setSize(3)
                    ->addAggregation(
                        (new Avg('avg_likes'))
                            ->setField('likes')
                    )
            )
            ->addAggregation(
                (new PercentilesBucket('percentiles_likes_by_page', 'pages>avg_likes'))
                    ->setPercents([5, 50, 95])
            )
        ;

        $results = $this->getIndexForTest()->search($query)->getAggregations();

        $this->assertEquals(5, $results['pages']['buckets'][0]['avg_likes']['value']);
        $this->assertEquals(100, $results['pages']['buckets'][1]['avg_likes']['value']);
        $this->assertEquals(150, $results['pages']['buckets'][2]['avg_likes']['value']);
        $this->assertEquals(
            ['5.0' => 5, '50.0' => 100, '95.0' => 150],
            $results['percentiles_likes_by_page']['values']
        );
    }

    #[Group('unit')]
    public function testConstructThroughSetters(): void
    {
        $aggregation = (new PercentilesBucket('percentiles_bucket', 'pages>avg_likes_by_page'))
            ->setFormat('test_format')
            ->setPercents([10, 80])
            ->setGapPolicy(GapPolicyInterface::INSERT_ZEROS)
            ->setKeyed(false)
        ;

        $expected = [
            'percentiles_bucket' => [
                'buckets_path' => 'pages>avg_likes_by_page',
                'format' => 'test_format',
                'gap_policy' => GapPolicyInterface::INSERT_ZEROS,
                'percents' => [10, 80],
                'keyed' => false,
            ],
        ];

        $this->assertEquals($expected, $aggregation->toArray());
    }

    private function getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            Document::create(['page' => 1, 'likes' => 5]),
            Document::create(['page' => 2, 'likes' => 100]),
            Document::create(['page' => 3, 'likes' => 150]),
        ], ['refresh' => 'true']);

        return $index;
    }
}
