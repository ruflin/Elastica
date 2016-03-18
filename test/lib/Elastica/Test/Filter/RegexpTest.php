<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\Regexp;
use Elastica\Test\DeprecatedClassBase as BaseTest;
use Elastica\Type\Mapping;

class RegexpTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new Regexp());
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $field = 'name';
        $regexp = 'ruf';

        $filter = new Regexp($field, $regexp);

        $expectedArray = array(
            'regexp' => array(
                $field => $regexp,
            ),
        );

        $this->assertequals($expectedArray, $filter->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayWithOptions()
    {
        $field = 'name';
        $regexp = 'ruf';
        $options = array(
            'flags' => 'ALL',
        );

        $filter = new Regexp($field, $regexp, $options);

        $expectedArray = array(
            'regexp' => array(
                $field => array(
                    'value' => $regexp,
                    'flags' => 'ALL',
                ),
            ),
        );

        $this->assertequals($expectedArray, $filter->toArray());
    }

    /**
     * @group functional
     */
    public function testDifferentRegexp()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $index->create(array(), true);
        $type = $index->getType('test');

        $mapping = new Mapping($type, array(
                'name' => array('type' => 'string', 'store' => 'no', 'index' => 'not_analyzed'),
            )
        );
        $type->setMapping($mapping);
        $type->addDocuments(array(
            new Document(1, array('name' => 'Basel-Stadt')),
            new Document(2, array('name' => 'New York')),
            new Document(3, array('name' => 'Baden')),
            new Document(4, array('name' => 'Baden Baden')),
            new Document(5, array('name' => 'New Orleans')),
        ));

        $index->refresh();

        $query = new Regexp('name', 'Ba.*');
        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());

        // Lower case should not return a result
        $query = new Regexp('name', 'ba.*');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());

        $query = new Regexp('name', 'Baden.*');
        $resultSet = $index->search($query);
        $this->assertEquals(2, $resultSet->count());

        $query = new Regexp('name', 'Baden B.*');
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        $query = new Regexp('name', 'Baden Bas.*');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testDifferentRegexpLowercase()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');

        $indexParams = array(
            'analysis' => array(
                'analyzer' => array(
                    'lw' => array(
                        'type' => 'custom',
                        'tokenizer' => 'keyword',
                        'filter' => array('lowercase'),
                    ),
                ),
            ),
        );

        $index->create($indexParams, true);
        $type = $index->getType('test');

        $mapping = new Mapping($type, array(
                'name' => array('type' => 'string', 'store' => 'no', 'analyzer' => 'lw'),
            )
        );
        $type->setMapping($mapping);
        $type->addDocuments(array(
            new Document(1, array('name' => 'Basel-Stadt')),
            new Document(2, array('name' => 'New York')),
            new Document(3, array('name' => 'Baden')),
            new Document(4, array('name' => 'Baden Baden')),
            new Document(5, array('name' => 'New Orleans')),
        ));

        $index->refresh();

        $query = new Regexp('name', 'ba.*');
        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());

        // Upper case should not return a result
        $query = new Regexp('name', 'Ba.*');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());

        $query = new Regexp('name', 'baden.*');
        $resultSet = $index->search($query);
        $this->assertEquals(2, $resultSet->count());

        $query = new Regexp('name', 'baden b.*');
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        $query = new Regexp('name', 'baden bas.*');
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());
    }
}
