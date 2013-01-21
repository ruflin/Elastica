<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Script;
use Elastica\Index;
use Elastica\Type;
use Elastica\Test\Base as BaseTest;

class DocumentTest extends BaseTest
{
    public function testAdd()
    {
        $doc = new Document();
        $returnValue = $doc->add('key', 'value');
        $data = $doc->getData();
        $this->assertArrayHasKey('key', $data);
        $this->assertEquals('value', $data['key']);
        $this->assertInstanceOf('Elastica\Document', $returnValue);
    }

    public function testAddFile()
    {
        $doc = new Document();
        $returnValue = $doc->addFile('key', '/dev/null');
        $this->assertInstanceOf('Elastica\Document', $returnValue);
    }

    public function testAddGeoPoint()
    {
        $doc = new Document();
        $returnValue = $doc->addGeoPoint('point', 38.89859, -77.035971);
        $this->assertInstanceOf('Elastica\Document', $returnValue);
    }

    public function testSetData()
    {
        $doc = new Document();
        $returnValue = $doc->setData(array('data'));
        $this->assertInstanceOf('Elastica\Document', $returnValue);
    }

    public function testToArray()
    {
        $id = 17;
        $data = array('hello' => 'world');
        $type = 'testtype';
        $index = 'textindex';

        $doc = new Document($id, $data, $type, $index);

        $result = array('_index' => $index, '_type' => $type, '_id' => $id, '_source' => $data);
        $this->assertEquals($result, $doc->toArray());
    }

    public function testSetType()
    {
        $document = new Document();
        $document->setType('type');

        $this->assertEquals('type', $document->getType());

        $index = new Index($this->_getClient(), 'index');
        $type = $index->getType('type');

        $document->setIndex('index2');
        $this->assertEquals('index2', $document->getIndex());

        $document->setType($type);

        $this->assertEquals('index', $document->getIndex());
        $this->assertEquals('type', $document->getType());
    }

    public function testSetIndex()
    {
        $document = new Document();
        $document->setIndex('index2');
        $document->setType('type2');

        $this->assertEquals('index2', $document->getIndex());
        $this->assertEquals('type2', $document->getType());

        $index = new Index($this->_getClient(), 'index');

        $document->setIndex($index);

        $this->assertEquals('index', $document->getIndex());
        $this->assertEquals('type2', $document->getType());
    }

    public function testHasId()
    {
        $document = new Document();
        $this->assertFalse($document->hasId());
        $document->setId('');
        $this->assertFalse($document->hasId());
        $document->setId(0);
        $this->assertTrue($document->hasId());
        $document->setId('hello');
        $this->assertTrue($document->hasId());
    }
    
    public function testGetOptions()
    {
        $document = new Document();
        $document->setVersion(1);
        $document->setVersionType(2);
        $document->setParent(3);
        $document->setOpType('create');
        $document->setPercolate('percolate');
        $document->setRouting('routing');

        $document->setRetryOnConflict(2);
        $document->setFieldsSource();

        $options = $document->getOptions();

        $this->assertArrayHasKey('version', $options);
        $this->assertEquals('1', $options['version']);
        $this->assertArrayHasKey('version_type', $options);
        $this->assertEquals('2', $options['version_type']);
        $this->assertArrayHasKey('parent', $options);
        $this->assertEquals('3', $options['parent']);
        $this->assertArrayHasKey('op_type', $options);
        $this->assertEquals('create', $options['op_type']);
        $this->assertArrayHasKey('percolate', $options);
        $this->assertEquals('percolate', $options['percolate']);
        $this->assertArrayHasKey('routing', $options);
        $this->assertEquals('routing', $options['routing']);
        $this->assertArrayNotHasKey('retry_on_conflict', $options);
        $this->assertArrayNotHasKey('fields', $options);

        $options = $document->getOptions(true);

        $this->assertArrayHasKey('version', $options);
        $this->assertArrayHasKey('version_type', $options);
        $this->assertArrayHasKey('parent', $options);
        $this->assertArrayHasKey('op_type', $options);
        $this->assertArrayHasKey('percolate', $options);
        $this->assertArrayHasKey('routing', $options);
        $this->assertArrayHasKey('retry_on_conflict', $options);
        $this->assertEquals(2, $options['retry_on_conflict']);
        $this->assertArrayHasKey('fields', $options);
        $this->assertEquals('_source', $options['fields']);

        $document2 = new Document();
        $document2->setFields(array('field1', 'field2'));
        $options = $document2->getOptions(true);

        $this->assertArrayHasKey('fields', $options);
        $this->assertEquals('field1,field2', $options['fields']);

        $document3 = new Document();
        $document3->addField('field1');
        $document3->addField('field2');
        $document3->addField('field3');
        $options = $document3->getOptions(true);

        $this->assertArrayHasKey('fields', $options);
        $this->assertEquals('field1,field2,field3', $options['fields']);

        $document3->setFields(array('field1,field2'));
        $options = $document3->getOptions(true);
        $this->assertEquals('field1,field2', $options['fields']);
    }

    public function testDataPropertiesOverloading()
    {
        $document = new Document(1, array('field1' => 'value1', 'field2' => 'value2', 'field3' => 'value3', 'field4' => null));

        $this->assertEquals('value1', $document->field1);
        $this->assertEquals('value2', $document->field2);
        $this->assertEquals('value3', $document->field3);
        $this->assertNull($document->field4);
        try {
            $document->field5;
            $this->fail('Undefined field get should throw exception');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }

        $this->assertTrue(isset($document->field1));
        $this->assertTrue(isset($document->field2));
        $this->assertTrue(isset($document->field3));
        $this->assertFalse(isset($document->field4), 'Field4 should not be isset, because it is null');
        $this->assertFalse(isset($document->field5), 'Field5 should not be isset, because it is not set');

        $data = $document->getData();

        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);
        $this->assertArrayHasKey('field3', $data);
        $this->assertEquals('value3', $data['field3']);
        $this->assertArrayHasKey('field4', $data);
        $this->assertNull($data['field4']);

        $document->field1 = 'changed1';
        unset($document->field3);
        try {
            unset($document->field5);
            $this->fail('Undefined field unset should throw exception');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }

        $this->assertEquals('changed1', $document->field1);
        $this->assertFalse(isset($document->field3));

        $newData = $document->getData();

        $this->assertNotEquals($data, $newData);
    }

    public function testSetTtl()
    {
        $document = new Document();

        $data = $document->getData();
        $this->assertArrayNotHasKey('_ttl', $data);

        $document->setTtl('1d');

        $newData = $document->getData();

        $this->assertArrayHasKey('_ttl', $newData);
        $this->assertEquals('1d', $newData['_ttl']);
        $this->assertNotEquals($data, $newData);
    }

    public function testSetScript()
    {
        $document = new Document();

        $script = new Script('ctx._source.counter += count');
        $script->setParam('count', 1);

        $this->assertFalse($document->hasScript());

        $document->setScript($script);

        $this->assertTrue($document->hasScript());
        $this->assertSame($script, $document->getScript());
    }
}
