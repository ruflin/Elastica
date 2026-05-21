<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\Knn;
use Elastica\Query\Range;
use Elastica\Query\Terms;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class KnnTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $knn = new Knn('vector', [0.1, 0.2, 0.3], 100, 200);

        $expected = [
            'knn' => [
                'field' => 'vector',
                'query_vector' => [0.1, 0.2, 0.3],
                'k' => 100,
                'num_candidates' => 200,
            ],
        ];

        $this->assertSame($expected, $knn->toArray());
    }

    #[Group('unit')]
    public function testToArrayWithFiltersSimilarityAndBoost(): void
    {
        $knn = new Knn('vector', [0.5, 0.5], 10, 20);
        $knn->addFilter(new Terms('tag', ['foo']));
        $knn->addFilter(new Range('age', ['gte' => 20]));
        $knn->setSimilarity(0.7);
        $knn->setBoost(1.5);

        $expected = [
            'knn' => [
                'field' => 'vector',
                'query_vector' => [0.5, 0.5],
                'k' => 10,
                'num_candidates' => 20,
                'filter' => [
                    ['terms' => ['tag' => ['foo']]],
                    ['range' => ['age' => ['gte' => 20]]],
                ],
                'similarity' => 0.7,
                'boost' => 1.5,
            ],
        ];

        $this->assertSame($expected, $knn->toArray());
    }

    #[Group('unit')]
    public function testQuerySetKnnEmbedsSingleKnnAtTopLevel(): void
    {
        $query = new Query();
        $query->setKnn(new Knn('vector', [0.1, 0.2], 5, 10));

        $body = $query->toArray();

        $this->assertSame([
            'field' => 'vector',
            'query_vector' => [0.1, 0.2],
            'k' => 5,
            'num_candidates' => 10,
        ], $body['knn']);
        $this->assertArrayNotHasKey('query', $body, 'knn-only requests must not be auto-padded with a match_all query');
    }

    #[Group('unit')]
    public function testQuerySetKnnAcceptsListOfKnnForMultipleKnnSearches(): void
    {
        $query = new Query();
        $query->setKnn([
            new Knn('a.vector', [0.1], 5, 10),
            new Knn('b.vector', [0.2], 5, 10),
        ]);

        $body = $query->toArray();

        $this->assertCount(2, $body['knn']);
        $this->assertSame('a.vector', $body['knn'][0]['field']);
        $this->assertSame('b.vector', $body['knn'][1]['field']);
        $this->assertArrayNotHasKey('query', $body, 'multi-knn requests must not be auto-padded with a match_all query');
    }

    #[Group('functional')]
    public function testKnnSearchAgainstDenseVectorField(): void
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'tag' => ['type' => 'keyword'],
            'vector' => [
                'type' => 'dense_vector',
                'dims' => 3,
                'index' => true,
                'similarity' => 'cosine',
            ],
        ]));

        $index->addDocuments([
            new Document('1', ['tag' => 'foo', 'vector' => [1.0, 0.0, 0.0]]),
            new Document('2', ['tag' => 'foo', 'vector' => [0.9, 0.1, 0.0]]),
            new Document('3', ['tag' => 'bar', 'vector' => [0.0, 0.0, 1.0]]),
        ]);
        $index->refresh();

        $knn = new Knn('vector', [1.0, 0.0, 0.0], 2, 10);
        $knn->addFilter(new Terms('tag', ['foo']));

        $query = new Query();
        $query->setKnn($knn);

        $results = $index->search($query);

        $ids = \array_map(static fn ($r): string => $r->getId(), $results->getResults());

        $this->assertContains('1', $ids);
        $this->assertContains('2', $ids);
        $this->assertNotContains('3', $ids, 'tag filter must exclude documents with another tag value');
    }
}
