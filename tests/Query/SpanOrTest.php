<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query\SpanOr;
use Elastica\Query\SpanTerm;
use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SpanOrTest extends BaseTest
{
    #[Group('unit')]
    public function testConstructWrongTypeInvalid(): void
    {
        $this->expectException(InvalidException::class);

        $term1 = new Term(['name' => 'marek']);
        $term2 = new Term(['name' => 'nicolas']);

        new SpanOr([$term1, $term2]);
    }

    #[Group('unit')]
    public function testConstructValid(): void
    {
        $field = 'name';
        $spanTermQuery1 = new SpanTerm([$field => ['value' => 'marek', 'boost' => 1.5]]);
        $spanTermQuery2 = new SpanTerm([$field => 'nicolas']);

        $spanOrQuery = new SpanOr([$spanTermQuery1, $spanTermQuery2]);

        $expected = [
            'span_or' => [
                'clauses' => [
                    [
                        'span_term' => [
                            'name' => [
                                'value' => 'marek',
                                'boost' => 1.5,
                            ],
                        ],
                    ],
                    [
                        'span_term' => [
                            'name' => 'nicolas',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $spanOrQuery->toArray());
    }

    #[Group('functional')]
    public function testSpanOrTerm(): void
    {
        $field = 'lorem';
        $text1 = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $text2 = 'Praesent gravida nisi in lorem consectetur, vel ullamcorper leo iaculis.';
        $text3 = 'Vivamus vitae mi nec tortor iaculis pellentesque at nec ipsum.';

        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', [$field => $text1]),
            new Document('2', [$field => $text2]),
            new Document('3', [$field => $text3]),
        ]);
        $index->refresh();

        // all 3 docs match
        $spanTermQuery1 = new SpanTerm([$field => 'lorem']);
        $spanTermQuery2 = new SpanTerm([$field => 'ipsum']);
        $spanOrQuery = new SpanOr([$spanTermQuery1, $spanTermQuery2]);
        $resultSet = $index->search($spanOrQuery);
        $this->assertEquals(3, $resultSet->count());

        // only 1 match hit
        $spanTermQuery1 = new SpanTerm([$field => 'amet']);
        $spanTermQuery2 = new SpanTerm([$field => 'sit']);
        $spanOrQuery = new SpanOr([$spanTermQuery1, $spanTermQuery2]);
        $resultSet = $index->search($spanOrQuery);
        $this->assertEquals(1, $resultSet->count());
    }
}
