<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Exception\InvalidException;
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
        $fuzzy->setField('user', 'Nicolas');
        $fuzzy->setFieldOption('boost', 1.0);

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
     * @group unit
     */
    public function testNeedSetFieldBeforeOption()
    {
        $fuzzy = new Fuzzy();
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('No field has been set');
        $fuzzy->setFieldOption('boost', 1.0);
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

        $fuzzy = new Fuzzy();
        $fuzzy->setField($field, 'Baden');

        $resultSet = $index->search($fuzzy);

        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group unit
     */
    public function testResetSingleField()
    {
        $fuzzy = new Fuzzy();
        $fuzzy->setField('name', 'value');
        $fuzzy->setField('name', 'other');
        $expected = [
            'fuzzy' => [
                'name' => [
                    'value' => 'other',
                ],
            ],
        ];
        $this->assertEquals($expected, $fuzzy->toArray());
    }

    /**
     * @group unit
     */
    public function testOnlySetSingleField()
    {
        $fuzzy = new Fuzzy();
        $fuzzy->setField('name', 'value');
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Fuzzy query can only support a single field.');
        $fuzzy->setField('name1', 'value1');
    }

    /**
     * @group unit
     */
    public function testFieldNameMustBeString()
    {
        $fuzzy = new Fuzzy();
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('The field and value arguments must be of type string.');
        $fuzzy->setField(['name'], 'value');
    }

    /**
     * @group unit
     */
    public function testValueMustBeString()
    {
        $fuzzy = new Fuzzy();
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('The field and value arguments must be of type string.');
        $fuzzy->setField('name', ['value']);
    }
}
