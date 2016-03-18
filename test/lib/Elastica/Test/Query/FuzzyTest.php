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
    public function testAddField()
    {
        $fuzzy = new Fuzzy();

        $this->hideDeprecated();
        $fuzzy->addField('user', array('value' => 'Nicolas', 'boost' => 1.0));
        $this->showDeprecated();

        $sameFuzzy = new Fuzzy();
        $sameFuzzy->setField('user', 'Nicolas');
        $sameFuzzy->setFieldOption('boost', 1.0);

        $this->assertEquals($sameFuzzy->toArray(), $fuzzy->toArray());
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $fuzzy = new Fuzzy();

        $fuzzy->setField('user', 'Nicolas');
        $fuzzy->setFieldOption('boost', 1.0);

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

        $this->hideDeprecated();
        $query->addField('name', array(array('value' => 'Baden')));
        $this->showDeprecated();

        $this->setExpectedException('Elastica\Exception\InvalidException');
        $query = new Fuzzy();
        $query->setField('name', array());

        $this->setExpectedException('Elastica\Exception\InvalidException');
        $query = new Fuzzy();
        $query->setField('name', 'value');
        $query->setField('name1', 'value1');
    }

    /**
     * @group unit
     */
    public function testAddFieldDeprecated()
    {
        $query = new Fuzzy();
        $errorCollector = $this->startCollectErrors();
        $query->addField('user', array('value' => 'Nicolas', 'boost' => 1.0));
        $this->finishCollectErrors();

        $errorCollector->assertOnlyOneDeprecatedError('Query\Fuzzy::addField is deprecated. Use setField and setFieldOption instead. This method will be removed in further Elastica releases');
    }
}
