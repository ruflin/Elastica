<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\SpanContaining;
use Elastica\Query\SpanNear;
use Elastica\Query\SpanTerm;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SpanContainingTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $field = 'name';
        $spanTermQuery1 = new SpanTerm([$field => 'nicolas']);
        $spanTermQuery2 = new SpanTerm([$field => ['value' => 'alekitto', 'boost' => 1.5]]);
        $spanTermQuery3 = new SpanTerm([$field => 'foobar']);
        $spanNearQuery = new SpanNear([$spanTermQuery1, $spanTermQuery2], 5);

        $spanContainingQuery = new SpanContaining($spanTermQuery3, $spanNearQuery);

        $expected = [
            'span_containing' => [
                'big' => [
                    'span_near' => [
                        'clauses' => [
                            [
                                'span_term' => [
                                    'name' => 'nicolas',
                                ],
                            ],
                            [
                                'span_term' => [
                                    'name' => [
                                        'value' => 'alekitto',
                                        'boost' => 1.5,
                                    ],
                                ],
                            ],
                        ],
                        'slop' => 5,
                        'in_order' => false,
                    ],
                ],
                'little' => [
                    'span_term' => [
                        'name' => 'foobar',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $spanContainingQuery->toArray());
    }

    #[Group('functional')]
    public function testSpanContaining(): void
    {
        $field = 'lorem';
        $value = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse odio lacus, aliquam nec nulla quis, aliquam eleifend eros.';

        $index = $this->_createIndex();

        $docHitData = [$field => $value];
        $doc = new Document('1', $docHitData);
        $index->addDocument($doc);
        $index->refresh();

        $spanTermQuery1 = new SpanTerm([$field => 'adipiscing']);
        $spanTermQuery2 = new SpanTerm([$field => 'lorem']);
        $spanNearQuery = new SpanNear([$spanTermQuery1, $spanTermQuery2], 5);

        $spanContainingQuery = new SpanContaining(new SpanTerm([$field => 'amet']), $spanNearQuery);
        $resultSet = $index->search($spanContainingQuery);
        $this->assertEquals(1, $resultSet->count());

        $spanContainingQuery = new SpanContaining(new SpanTerm([$field => 'not-matching']), $spanNearQuery);
        $resultSet = $index->search($spanContainingQuery);
        $this->assertEquals(0, $resultSet->count());
    }
}
