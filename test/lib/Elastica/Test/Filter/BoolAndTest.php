<?php
namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\BoolAnd;
use Elastica\Filter\Ids;
use Elastica\Test\DeprecatedClassBase as BaseTest;

class BoolAndTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new BoolAnd());
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use BoolQuery::addMust. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $and = new BoolAnd();
        $this->assertEquals(['and' => []], $and->toArray());

        $idsFilter = new Ids();
        $idsFilter->setIds(12);

        $and->addFilter($idsFilter);
        $and->addFilter($idsFilter);

        $expectedArray = [
            'and' => [
                $idsFilter->toArray(),
                $idsFilter->toArray(),
            ],
        ];

        $this->assertEquals($expectedArray, $and->toArray());
    }

    /**
     * @group functional
     */
    public function testSetCache()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('test');

        $type->addDocuments([
            new Document(1, ['name' => 'hello world']),
            new Document(2, ['name' => 'nicolas ruflin']),
            new Document(3, ['name' => 'ruflin']),
        ]);

        $and = new BoolAnd();

        $idsFilter1 = new Ids();
        $idsFilter1->setIds(1);

        $idsFilter2 = new Ids();
        $idsFilter2->setIds(1);

        $and->addFilter($idsFilter1);
        $and->addFilter($idsFilter2);

        $index->refresh();
        $and->setCached(true);

        $resultSet = $type->search($and);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group unit
     */
    public function testConstruct()
    {
        $ids1 = new Ids('foo', [1, 2]);
        $ids2 = new Ids('bar', [3, 4]);

        $and1 = new BoolAnd([$ids1, $ids2]);

        $and2 = new BoolAnd();
        $and2->addFilter($ids1);
        $and2->addFilter($ids2);

        $this->assertEquals($and1->toArray(), $and2->toArray());
    }
}
