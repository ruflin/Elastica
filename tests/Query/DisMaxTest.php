<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\DisMax;
use Elastica\Query\Ids;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class DisMaxTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $query = new DisMax();

        $idsQuery1 = new Ids();
        $idsQuery1->setIds('1');

        $idsQuery2 = new Ids();
        $idsQuery2->setIds('2');

        $idsQuery3 = new Ids();
        $idsQuery3->setIds('3');

        $boost = 1.2;
        $tieBreaker = 0.7;

        $query->setBoost($boost);
        $query->setTieBreaker($tieBreaker);
        $query->addQuery($idsQuery1);
        $query->addQuery($idsQuery2);
        $query->addQuery($idsQuery3->toArray());

        $expectedArray = [
            'dis_max' => [
                'tie_breaker' => $tieBreaker,
                'boost' => $boost,
                'queries' => [
                    $idsQuery1->toArray(),
                    $idsQuery2->toArray(),
                    $idsQuery3->toArray(),
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    #[Group('functional')]
    public function testQuery(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['name' => 'Basel-Stadt']),
            new Document('2', ['name' => 'New York']),
            new Document('3', ['name' => 'Baden']),
            new Document('4', ['name' => 'Baden Baden']),
        ]);

        $index->refresh();

        $queryString1 = new QueryString('Bade*');
        $queryString2 = new QueryString('Base*');

        $boost = 1.2;
        $tieBreaker = 0.5;

        $query = new DisMax();
        $query->setBoost($boost);
        $query->setTieBreaker($tieBreaker);
        $query->addQuery($queryString1);
        $query->addQuery($queryString2);
        $resultSet = $index->search($query);

        $this->assertEquals(3, $resultSet->count());
    }

    #[Group('functional')]
    public function testQueryArray(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['name' => 'Basel-Stadt']),
            new Document('2', ['name' => 'New York']),
            new Document('3', ['name' => 'Baden']),
            new Document('4', ['name' => 'Baden Baden']),
        ]);

        $index->refresh();

        $queryString1 = ['query_string' => [
            'query' => 'Bade*',
        ],
        ];

        $queryString2 = ['query_string' => [
            'query' => 'Base*',
        ],
        ];

        $boost = 1.2;
        $tieBreaker = 0.5;

        $query = new DisMax();
        $query->setBoost($boost);
        $query->setTieBreaker($tieBreaker);
        $query->addQuery($queryString1);
        $query->addQuery($queryString2);
        $resultSet = $index->search($query);

        $this->assertEquals(3, $resultSet->count());
    }
}
