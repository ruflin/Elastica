<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\SpanNear;
use Elastica\Query\SpanNot;
use Elastica\Query\SpanTerm;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SpanNotTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $field = 'name';
        $spanTermQuery1 = new SpanTerm([$field => 'nicolas']);
        $spanTermQuery2 = new SpanTerm([$field => ['value' => 'alekitto', 'boost' => 1.5]]);
        $spanTermQuery3 = new SpanTerm([$field => 'foobar']);
        $spanNearQuery = new SpanNear([$spanTermQuery1, $spanTermQuery2], 0, true);

        $spanContainingQuery = new SpanNot($spanTermQuery3, $spanNearQuery);

        $expected = [
            'span_not' => [
                'include' => [
                    'span_term' => [
                        'name' => 'foobar',
                    ],
                ],
                'exclude' => [
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
                        'slop' => 0,
                        'in_order' => true,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $spanContainingQuery->toArray());
    }

    #[Group('functional')]
    public function testSpanNot(): void
    {
        $field = 'lorem';
        $value = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse odio lacus, aliquam nec nulla quis, aliquam eleifend eros.';

        $index = $this->_createIndex();

        $docHitData = [$field => $value];
        $doc = new Document('1', $docHitData);
        $index->addDocument($doc);
        $index->refresh();

        $spanTermQuery = new SpanTerm([$field => 'amet']);
        $spanTermQuery1 = new SpanTerm([$field => 'adipiscing']);
        $spanTermQuery2 = new SpanTerm([$field => 'lorem']);
        $spanNearQuery = new SpanNear([$spanTermQuery1, $spanTermQuery2], 0);

        $spanContainingQuery = new SpanNot($spanTermQuery, $spanNearQuery);
        $resultSet = $index->search($spanContainingQuery);
        $this->assertEquals(1, $resultSet->count());

        $spanNearQuery->setSlop(5);
        $spanContainingQuery = new SpanNot($spanTermQuery, $spanNearQuery);
        $resultSet = $index->search($spanContainingQuery);
        $this->assertEquals(0, $resultSet->count());
    }
}
