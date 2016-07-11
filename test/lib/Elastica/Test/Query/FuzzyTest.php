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
        $fuzzy->addField('user', ['value' => 'Nicolas', 'boost' => 1.0]);
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

        $expectedArray = [
            'fuzzy' => [
                'user' => [
                    'value' => 'Nicolas',
                    'boost' => 1.0,
                ],
            ],
        ];
        $this->assertEquals($expectedArray, $fuzzy->toArray(), 'Deprecated method failed');

        $fuzzy = new Fuzzy('user', 'Nicolas');
        $expectedArray = [
            'fuzzy' => [
                'user' => [
                    'value' => 'Nicolas',
                ],
            ],
        ];
        $this->assertEquals($expectedArray, $fuzzy->toArray());

        $fuzzy = new Fuzzy();
        $fuzzy->setField('user', 'Nicolas')->setFieldOption('boost', 1.0);
        $expectedArray = [
            'fuzzy' => [
                'user' => [
                    'value' => 'Nicolas',
                    'boost' => 1.0,
                ],
            ],
        ];
        $this->assertEquals($expectedArray, $fuzzy->toArray());
    }

    /**
     * @group functional
     */
    public function testQuery()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('test');

        $type->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
            new Document(3, ['name' => 'Baden']),
            new Document(4, ['name' => 'Baden Baden']),
        ]);

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
        $query->addField('name', [['value' => 'Baden']]);
        $this->showDeprecated();

        $this->setExpectedException('Elastica\Exception\InvalidException');
        $query = new Fuzzy();
        $query->setField('name', []);

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
        $query->addField('user', ['value' => 'Nicolas', 'boost' => 1.0]);
        $this->finishCollectErrors();

        $errorCollector->assertOnlyOneDeprecatedError('Query\Fuzzy::addField is deprecated. Use setField and setFieldOption instead. This method will be removed in further Elastica releases');
    }
}
