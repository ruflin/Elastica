<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Nested;
use Elastica\Aggregation\ReverseNested;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class ReverseNestedTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $mapping = new Mapping();
        $mapping->setProperties([
            'comments' => [
                'type' => 'nested',
                'properties' => [
                    'name' => ['type' => 'string'],
                    'body' => ['type' => 'string'],
                ],
            ],
        ]);
        $type = $index->getType('test');
        $type->setMapping($mapping);

        $type->addDocuments([
            new Document(1, [
                'comments' => [
                    [
                        'name' => 'bob',
                        'body' => 'this is bobs comment',
                    ],
                    [
                        'name' => 'john',
                        'body' => 'this is johns comment',
                    ],
                ],
                'tags' => ['foo', 'bar'],
            ]),
            new Document(2, [
                 'comments' => [
                    [
                        'name' => 'bob',
                        'body' => 'this is another comment from bob',
                    ],
                    [
                        'name' => 'susan',
                        'body' => 'this is susans comment',
                    ],
                ],
                'tags' => ['foo', 'baz'],
            ]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     */
    public function testPathNotSetIfNull()
    {
        $agg = new ReverseNested('nested');
        $this->assertFalse($agg->hasParam('path'));
    }

    /**
     * @group unit
     */
    public function testPathSetIfNotNull()
    {
        $agg = new ReverseNested('nested', 'some_field');
        $this->assertEquals('some_field', $agg->getParam('path'));
    }

    /**
     * @group functional
     */
    public function testReverseNestedAggregation()
    {
        $agg = new Nested('comments', 'comments');
        $names = new Terms('name');
        $names->setField('comments.name');

        $tags = new Terms('tags');
        $tags->setField('tags');

        $reverseNested = new ReverseNested('main');
        $reverseNested->addAggregation($tags);

        $names->addAggregation($reverseNested);

        $agg->addAggregation($names);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('comments');

        $this->assertArrayHasKey('name', $results);
        $nameResults = $results['name'];

        $this->assertCount(3, $nameResults['buckets']);

        // bob
        $this->assertEquals('bob', $nameResults['buckets'][0]['key']);
        $tags = [
            ['key' => 'foo', 'doc_count' => 2],
            ['key' => 'bar', 'doc_count' => 1],
            ['key' => 'baz', 'doc_count' => 1],
        ];
        $this->assertEquals($tags, $nameResults['buckets'][0]['main']['tags']['buckets']);

        // john
        $this->assertEquals('john', $nameResults['buckets'][1]['key']);
        $tags = [
            ['key' => 'bar', 'doc_count' => 1],
            ['key' => 'foo', 'doc_count' => 1],
        ];
        $this->assertEquals($tags, $nameResults['buckets'][1]['main']['tags']['buckets']);

        // susan
        $this->assertEquals('susan', $nameResults['buckets'][2]['key']);
        $tags = [
            ['key' => 'baz', 'doc_count' => 1],
            ['key' => 'foo', 'doc_count' => 1],
        ];
        $this->assertEquals($tags, $nameResults['buckets'][2]['main']['tags']['buckets']);
    }
}
