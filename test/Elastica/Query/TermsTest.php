<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Terms;
use Elastica\Test\Base as BaseTest;

class TermsTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testFilteredSearch()
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['name' => 'hello world']),
            new Document(2, ['name' => 'nicolas ruflin']),
            new Document(3, ['name' => 'ruflin']),
        ]);

        $query = new Terms();
        $query->setTerms('name', ['nicolas', 'hello']);

        $index->refresh();

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query->addTerm('ruflin');
        $resultSet = $index->search($query);

        $this->assertEquals(3, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testFilteredSearchWithLookup()
    {
        $index = $this->_createIndex();

        $lookupIndex = $this->_createIndex('lookup_index');
        $lookupType = $lookupIndex->getType('_doc');

        $lookupType->addDocuments([
            new Document(1, ['terms' => ['ruflin', 'nicolas']]),
        ]);

        $index->addDocuments([
            new Document(1, ['name' => 'hello world']),
            new Document(2, ['name' => 'nicolas ruflin']),
            new Document(3, ['name' => 'ruflin']),
        ]);

        $query = new Terms();

        $query->setTermsLookup('name', [
            'index' => $lookupIndex->getName(),
            'type' => $lookupType->getName(),
            'id' => '1',
            'path' => 'terms',
        ]);
        $index->refresh();
        $lookupIndex->refresh();

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    public function provideMinimumArguments()
    {
        return [
            [
                3,
            ],
            [
                -2,
            ],
            [
                '75%',
            ],
            [
                '-25%',
            ],
            [
                '3<90%',
            ],
            [
                '2<-25% 9<-3',
            ],
        ];
    }

    /**
     * @group unit
     * @dataProvider provideMinimumArguments
     */
    public function testSetMinimum($minimum)
    {
        $key = 'name';
        $terms = ['nicolas', 'ruflin'];

        $query = new Terms($key, $terms);
        $query->setMinimumMatch($minimum);

        $data = $query->toArray();
        $this->assertEquals($minimum, $data['terms']['minimum_match']);
    }

    /**
     * @group unit
     */
    public function testSetTermsLookup()
    {
        $key = 'name';
        $terms = [
            'index' => 'index_name',
            'type' => 'type_name',
             'id' => '1',
            'path' => 'terms',
        ];

        $query = new Terms();
        $query->setTermsLookup($key, $terms);
        $data = $query->toArray();
        $this->assertEquals($terms, $data['terms'][$key]);
    }

    /**
     * @group unit
     */
    public function testInvalidParams()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $query = new Terms();

        $query->toArray();
    }
}
