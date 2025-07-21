<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Composite;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class TermsTest extends BaseAggregationTestCase
{
    #[Group('unit')]
    public function testIncludePattern(): void
    {
        $agg = new Terms('terms');
        $agg->setInclude('pattern*');

        $this->assertSame('pattern*', $agg->getParam('include'));
    }

    #[Group('unit')]
    public function testIncludeExactMatch(): void
    {
        $agg = new Terms('terms');
        $agg->setIncludeAsExactMatch(['first', 'second']);

        $this->assertSame(['first', 'second'], $agg->getParam('include'));
    }

    #[Group('unit')]
    public function testIncludeWithPartitions(): void
    {
        $agg = new Terms('terms');
        $agg->setIncludeWithPartitions(1, 23);

        $this->assertSame([
            'partition' => 1,
            'num_partitions' => 23,
        ], $agg->getParam('include'));
    }

    #[Group('unit')]
    public function testExcludePattern(): void
    {
        $agg = new Terms('terms');
        $agg->setExclude('pattern*');

        $this->assertSame('pattern*', $agg->getParam('exclude'));
    }

    #[Group('unit')]
    public function testExcludeExactMatch(): void
    {
        $agg = new Terms('terms');
        $agg->setExcludeAsExactMatch(['first', 'second']);

        $this->assertSame(['first', 'second'], $agg->getParam('exclude'));
    }

    #[Group('functional')]
    public function testTermsAggregation(): void
    {
        $agg = new Terms('terms');
        $agg->setField('color');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->getIndex()->search($query)->getAggregation('terms');

        $this->assertEquals(2, $results['buckets'][0]['doc_count']);
        $this->assertEquals('blue', $results['buckets'][0]['key']);
    }

    #[Group('functional')]
    public function testTermsSetOrder(): void
    {
        $agg = new Terms('terms');
        $agg->setField('color');
        $agg->setOrder('_count', 'asc');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->getIndex()->search($query)->getAggregation('terms');

        $this->assertEquals('blue', $results['buckets'][2]['key']);
    }

    #[Group('functional')]
    public function testTermsWithMissingAggregation(): void
    {
        $agg = new Terms('terms');
        $agg->setField('color');
        $agg->setMissing('blue');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->getIndex()->search($query)->getAggregation('terms');

        $this->assertEquals(3, $results['buckets'][0]['doc_count']);
        $this->assertEquals('blue', $results['buckets'][0]['key']);
    }

    #[Group('functional')]
    public function testTermsSetOrders(): void
    {
        $agg = new Terms('terms');
        $agg->setField('color');
        $agg->setOrders([
            ['_count' => 'asc'], // 1. red,   2. green, 3. blue
            ['_key' => 'asc'],   // 1. green, 2. red,   3. blue
        ]);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->getIndex()->search($query)->getAggregation('terms');

        $this->assertSame('green', $results['buckets'][0]['key']);
        $this->assertSame('red', $results['buckets'][1]['key']);
        $this->assertSame('blue', $results['buckets'][2]['key']);
    }

    #[Group('unit')]
    public function testTermsSetMissingBucketUnit(): void
    {
        $agg = new Terms('terms');
        $agg->setMissingBucket();

        $this->assertTrue($agg->getParam('missing_bucket'));
    }

    #[DataProvider('termsSetMissingBucketProvider')]
    #[Group('functional')]
    public function testTermsSetMissingBucketFunctional(
        string $field,
        int $expectedCountValues,
        bool $isSetMissingBucket
    ): void {
        $terms = new Terms('terms');
        $terms->setField($field);
        if ($isSetMissingBucket) {
            $terms->setMissingBucket();
        }

        $composite = new Composite('composite');
        $composite->addSource($terms);

        $query = new Query();
        $query->addAggregation($composite);
        $results = $this->getIndex()->search($query)->getAggregation('composite');

        $this->assertCount($expectedCountValues, $results['buckets']);
    }

    public static function termsSetMissingBucketProvider(): \Traversable
    {
        yield [
            'field' => 'color',
            'expectedCountValues' => 4,
            'isSetMissingBucket' => true,
        ];
        yield [
            'field' => 'color',
            'expectedCountValues' => 3,
            'isSetMissingBucket' => false,
        ];
        yield [
            'field' => 'anything',
            'expectedCountValues' => 2,
            'isSetMissingBucket' => true,
        ];
        yield [
            'field' => 'anything',
            'expectedCountValues' => 1,
            'isSetMissingBucket' => false,
        ];
    }

    private function getIndex(): Index
    {
        $index = $this->_createIndex();

        $mapping = new Mapping([
            'color' => ['type' => 'keyword'],
            'anything' => ['type' => 'keyword'],
        ]);
        $index->setMapping($mapping);

        $index->addDocuments([
            new Document('1', ['color' => 'blue']),
            new Document('2', ['color' => 'blue']),
            new Document('3', ['color' => 'red']),
            new Document('4', ['color' => 'green']),
            new Document('5', ['anything' => 'anything']),
        ]);

        $index->refresh();

        return $index;
    }
}
