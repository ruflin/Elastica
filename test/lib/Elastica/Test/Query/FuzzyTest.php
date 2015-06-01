<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Fuzzy;
use Elastica\Test\Base as BaseTest;

class FuzzyTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $fuzzy = new Fuzzy();
        $fuzzy->addField('user', array('value' => 'Nicolas', 'boost' => 1.0));
        $expectedArray = array(
            'fuzzy' => array(
                'user' => array(
                    'value' => 'Nicolas',
                    'boost' => 1.0,
                ),
            ),
        );
        $this->assertEquals($expectedArray, $fuzzy->toArray(), 'Deprecated method failed');

        $fuzzy = new Fuzzy('user', 'Nicolas');
        $expectedArray = array(
            'fuzzy' => array(
                'user' => array(
                    'value' => 'Nicolas',
                ),
            ),
        );
        $this->assertEquals($expectedArray, $fuzzy->toArray());

        $fuzzy = new Fuzzy();
        $fuzzy->setField('user', 'Nicolas')->setFieldOption('boost', 1.0);
        $expectedArray = array(
            'fuzzy' => array(
                'user' => array(
                    'value' => 'Nicolas',
                    'boost' => 1.0,
                ),
            ),
        );
        $this->assertEquals($expectedArray, $fuzzy->toArray());
    }

    /**
     * @group functional
     */
    public function testQuery()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $type->addDocuments(array(
            new Document(1, array('name' => 'Basel-Stadt')),
            new Document(2, array('name' => 'New York')),
            new Document(3, array('name' => 'Baden')),
            new Document(4, array('name' => 'Baden Baden')),
        ));

        $index->refresh();

        $field = 'name';

        $query = new Fuzzy();
        $query->setField($field, 'Baden');

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group unit
     */
    public function testBadArguments()
    {
        $this->setExpectedException('Elastica\Exception\InvalidException');
        $query = new Fuzzy();
        $query->addField('name', array(array('value' => 'Baden')));

        $this->setExpectedException('Elastica\Exception\InvalidException');
        $query = new Fuzzy();
        $query->setField('name', array());

        $this->setExpectedException('Elastica\Exception\InvalidException');
        $query = new Fuzzy();
        $query->setField('name', 'value');
        $query->setField('name1', 'value1');
    }

    /**
     * @group functional
     */
    public function testFuzzyWithFacets()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->addDocuments(array(
            new Document(1, array('name' => 'Basel-Stadt')),
            new Document(2, array('name' => 'New York')),
            new Document(3, array('name' => 'Baden')),
            new Document(4, array('name' => 'Baden Baden')),
        ));

        $index->refresh();

        $field = 'name';

        $fuzzyQuery = new Fuzzy();
        $fuzzyQuery->setField($field, 'Baden');

        $facet = new \Elastica\Facet\Terms('test');
        $facet->setField('name');

        $query = new \Elastica\Query($fuzzyQuery);
        $query->addFacet($facet);

        $resultSet = $index->search($query);

        // Assert query worked ok
        $this->assertEquals(2, $resultSet->count());

        // Check Facets
        $this->assertTrue($resultSet->hasFacets());
        $facets = $resultSet->getFacets();
        $this->assertEquals(2, $facets['test']['total']);
    }
}
