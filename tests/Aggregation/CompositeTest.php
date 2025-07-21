<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Composite;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class CompositeTest extends BaseAggregationTestCase
{
    #[Group('unit')]
    public function testSize(): void
    {
        $composite = new Composite('products');
        $composite->setSize(200);
        $this->assertEquals(200, $composite->getParam('size'));

        $expected = [
            'composite' => [
                'size' => 200,
            ],
        ];
        $this->assertEquals($expected, $composite->toArray());
    }

    #[Group('unit')]
    public function testAddSource(): void
    {
        $expected = [
            'composite' => [
                'sources' => [
                    [
                        'product' => [
                            'terms' => [
                                'field' => 'product_id',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $composite = new Composite('products');
        $composite->addSource((new Terms('product'))->setField('product_id'));
        $this->assertEquals($expected, $composite->toArray());
    }

    #[Group('unit')]
    public function testAddAfter(): void
    {
        $checkpoint = ['checkpointproduct' => 'checkpoint'];
        $expected = [
            'composite' => [
                'after' => $checkpoint,
            ],
        ];

        $composite = new Composite('products');
        $composite->addAfter($checkpoint);
        $this->assertEquals($expected, $composite->toArray());
    }

    #[Group('functional')]
    public function testCompositeNoAfterAggregation(): void
    {
        $composite = new Composite('products');
        $composite->addSource((new Terms('color'))->setField('color.keyword'));

        $query = new Query();
        $query->addAggregation($composite);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('products');
        $expected = [
            'after_key' => [
                'color' => 'red',
            ],
            'buckets' => [
                [
                    'key' => [
                        'color' => 'blue',
                    ],
                    'doc_count' => 2,
                ],
                [
                    'key' => [
                        'color' => 'green',
                    ],
                    'doc_count' => 1,
                ],
                [
                    'key' => [
                        'color' => 'red',
                    ],
                    'doc_count' => 1,
                ],
            ],
        ];

        $this->assertEquals($expected, $results);
    }

    #[Group('functional')]
    public function testCompositeWithSizeAggregation(): void
    {
        $composite = new Composite('products');
        $composite->setSize(2);
        $composite->addSource((new Terms('color'))->setField('color.keyword'));

        $query = new Query();
        $query->addAggregation($composite);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('products');
        $expected = [
            'after_key' => [
                'color' => 'green',
            ],
            'buckets' => [
                [
                    'key' => [
                        'color' => 'blue',
                    ],
                    'doc_count' => 2,
                ],
                [
                    'key' => [
                        'color' => 'green',
                    ],
                    'doc_count' => 1,
                ],
            ],
        ];

        $this->assertEquals($expected, $results);
    }

    #[Group('functional')]
    public function testCompositeWithAfterAggregation(): void
    {
        $composite = new Composite('products');
        $composite->setSize(2);
        $composite->addSource((new Terms('color'))->setField('color.keyword'));
        $composite->addAfter(['color' => 'green']);
        $query = new Query();
        $query->addAggregation($composite);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('products');
        $expected = [
            'after_key' => [
                'color' => 'red',
            ],
            'buckets' => [
                [
                    'key' => [
                        'color' => 'red',
                    ],
                    'doc_count' => 1,
                ],
            ],
        ];

        $this->assertEquals($expected, $results);
    }

    #[Group('functional')]
    public function testCompositeWithNullAfter(): void
    {
        $composite = new Composite('products');
        $composite->setSize(2);
        $composite->addSource((new Terms('color'))->setField('color.keyword'));
        $composite->addAfter(null);

        $query = new Query();
        $query->addAggregation($composite);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('products');
        $expected = [
            'after_key' => [
                'color' => 'green',
            ],
            'buckets' => [
                [
                    'key' => [
                        'color' => 'blue',
                    ],
                    'doc_count' => 2,
                ],
                [
                    'key' => [
                        'color' => 'green',
                    ],
                    'doc_count' => 1,
                ],
            ],
        ];

        $this->assertEquals($expected, $results);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['price' => 5, 'color' => 'blue']),
            new Document('2', ['price' => 5, 'color' => 'blue']),
            new Document('3', ['price' => 3, 'color' => 'red']),
            new Document('4', ['price' => 3, 'color' => 'green']),
        ]);

        $index->refresh();

        return $index;
    }
}
