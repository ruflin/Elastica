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
        $type = $index->getType('helloworld');

        $type->addDocuments([
            new Document(1, ['name' => 'hello world']),
            new Document(2, ['name' => 'nicolas ruflin']),
            new Document(3, ['name' => 'ruflin']),
        ]);

        $query = new Terms();
        $query->setTerms('name', ['nicolas', 'hello']);

        $index->refresh();

        $resultSet = $type->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query->addTerm('ruflin');
        $resultSet = $type->search($query);

        $this->assertEquals(3, $resultSet->count());
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
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidParams()
    {
        $query = new Terms();

        $query->toArray();
    }
}
