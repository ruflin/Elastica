<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\Nested;
use Elastica\Query\Terms;
use Elastica\Search;
use Elastica\Test\DeprecatedClassBase as BaseTest;
use Elastica\Type\Mapping;

class NestedTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new Nested());
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    protected function _getIndexForTest()
    {
        $index = $this->_createIndex('elastica_test_filter_nested');
        $type = $index->getType('user');
        $mapping = new Mapping();
        $mapping->setProperties(
            array(
                'firstname' => array('type' => 'string', 'store' => 'yes'),
                // default is store => no expected
                'lastname' => array('type' => 'string'),
                'hobbies' => array(
                    'type' => 'nested',
                    'include_in_parent' => true,
                    'properties' => array('hobby' => array('type' => 'string')),
                ),
            )
        );
        $type->setMapping($mapping);

        $response = $type->addDocuments(array(
            new Document(1,
                array(
                    'firstname' => 'Nicolas',
                    'lastname' => 'Ruflin',
                    'hobbies' => array(
                        array('hobby' => 'opensource'),
                    ),
                )
            ),
            new Document(2,
                array(
                    'firstname' => 'Nicolas',
                    'lastname' => 'Ippolito',
                    'hobbies' => array(
                        array('hobby' => 'opensource'),
                        array('hobby' => 'guitar'),
                    ),
                )
            ),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $filter = new Nested();
        $this->assertEquals(array('nested' => array()), $filter->toArray());
        $query = new Terms();
        $query->setTerms('hobby', array('guitar'));
        $filter->setPath('hobbies');
        $filter->setQuery($query);

        $expectedArray = array(
            'nested' => array(
                'path' => 'hobbies',
                'query' => array('terms' => array(
                    'hobby' => array('guitar'),
                )),
            ),
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }

    /**
     * @group functional
     */
    public function testShouldReturnTheRightNumberOfResult()
    {
        $filter = new Nested();
        $this->assertEquals(array('nested' => array()), $filter->toArray());
        $query = new Terms();
        $query->setTerms('hobbies.hobby', array('guitar'));
        $filter->setPath('hobbies');
        $filter->setQuery($query);

        $search = new Search($this->_getClient());
        $search->addIndex($this->_getIndexForTest());
        $resultSet = $search->search($filter);

        $this->assertEquals(1, $resultSet->getTotalHits());

        $filter = new Nested();
        $this->assertEquals(array('nested' => array()), $filter->toArray());
        $query = new Terms();
        $query->setTerms('hobbies.hobby', array('opensource'));
        $filter->setPath('hobbies');
        $filter->setQuery($query);

        $search = new Search($this->_getClient());
        $search->addIndex($this->_getIndexForTest());
        $resultSet = $search->search($filter);
        $this->assertEquals(2, $resultSet->getTotalHits());
    }

    /**
     * @group unit
     */
    public function testSetJoin()
    {
        $filter = new Nested();

        $this->assertTrue($filter->setJoin(true)->getParam('join'));

        $this->assertFalse($filter->setJoin(false)->getParam('join'));

        $returnValue = $filter->setJoin(true);
        $this->assertInstanceOf('Elastica\Filter\Nested', $returnValue);
    }
}
