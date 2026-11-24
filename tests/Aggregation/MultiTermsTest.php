<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Max;
use Elastica\Aggregation\MultiTerms;
use Elastica\Document;
use Elastica\Query;
use Elastica\QueryBuilder;
use Elastica\QueryBuilder\DSL;
use Elastica\QueryBuilder\Version\Version700;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class MultiTermsTest extends BaseTest
{
    #[Group('unit')]
    public function testQueryBuilder(): void
    {
        $aggregation = (new QueryBuilder())->aggregation()->multi_terms('products');
        $aggregation->setTerms([['field' => 'genre'], ['field' => 'product']]);

        $this->assertSame(['multi_terms' => ['terms' => [
            ['field' => 'genre'], ['field' => 'product'],
        ]]], $aggregation->toArray());
        $this->assertFalse((new Version700())->supports('multi_terms', DSL::TYPE_AGGREGATION));
    }

    #[Group('unit')]
    public function testQuerySerialization(): void
    {
        $terms = [['field' => 'genre'], ['field' => 'product', 'missing' => 'Unknown']];
        $aggregation = new MultiTerms('products');
        $this->assertSame($aggregation, $aggregation->setTerms($terms));
        $aggregation->setSize(5)->setShardSize(20)->setOrder('_count', 'desc');
        $aggregation->setMinimumDocumentCount(2)->setShardMinimumDocumentCount(1);
        $aggregation->setCollectMode('breadth_first')->setShowTermDocCountError(true);
        $aggregation->addAggregation((new Max('max_price'))->setField('price'));

        $query = new Query();
        $query->addAggregation($aggregation);

        $this->assertSame(['products' => [
            'multi_terms' => [
                'terms' => $terms,
                'size' => 5,
                'shard_size' => 20,
                'order' => ['_count' => 'desc'],
                'min_doc_count' => 2,
                'shard_min_doc_count' => 1,
                'collect_mode' => 'breadth_first',
                'show_term_doc_count_error' => true,
            ],
            'aggs' => ['max_price' => ['max' => ['field' => 'price']]],
        ]], $query->toArray()['aggs']);
    }

    #[Group('unit')]
    public function testReplacingAndAppendingTerms(): void
    {
        $script = ['script' => ['source' => '1', 'lang' => 'painless'], 'value_type' => 'long', 'missing' => 0];
        $aggregation = new MultiTerms('products');
        $aggregation->addTerm(['field' => 'discarded']);
        $aggregation->setTerms([['field' => 'genre']]);
        $this->assertSame($aggregation, $aggregation->addTerm($script));
        $aggregation->setOrders([['_count' => 'desc'], ['_key' => 'asc']]);
        $aggregation->setShowTermDocCountError(false);

        $this->assertSame(['multi_terms' => [
            'terms' => [['field' => 'genre'], $script],
            'order' => [['_count' => 'desc'], ['_key' => 'asc']],
            'show_term_doc_count_error' => false,
        ]], $aggregation->toArray());
    }

    #[Group('functional')]
    public function testGroupsDocumentsByMultipleFields(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['genre' => 'rock', 'product' => 'album']),
            new Document('2', ['genre' => 'rock', 'product' => 'album']),
            new Document('3', ['genre' => 'jazz', 'product' => 'album']),
        ]);
        $index->refresh();

        $aggregation = (new QueryBuilder())->aggregation()->multi_terms('products');
        $aggregation->addTerm(['field' => 'genre.keyword'])->addTerm(['field' => 'product.keyword']);
        $query = new Query();
        $query->setSize(0)->addAggregation($aggregation);
        $buckets = $index->search($query)->getAggregation('products')['buckets'];

        $this->assertCount(2, $buckets);
        $this->assertSame(['rock', 'album'], $buckets[0]['key']);
        $this->assertSame(2, $buckets[0]['doc_count']);
        $this->assertSame(['jazz', 'album'], $buckets[1]['key']);
        $this->assertSame(1, $buckets[1]['doc_count']);
    }
}
