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
    public function testAddFile()
    {
        $fileName = '/dev/null';
        if(!file_exists($fileName)){
            $this->markTestSkipped("File {$fileName} does not exist.");
        }
        $doc = new Document();
        $returnValue = $doc->addFile('key', $fileName);
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
        $document->setIndex('index');
        $document->setOpType('create');
        $document->setParent('2');
        $document->setId(1);

        $options = $document->getOptions(array('index', 'type', 'id', 'parent'));

        $this->assertInternalType('array', $options);
        $this->assertEquals(3, count($options));
        $this->assertArrayHasKey('index', $options);
        $this->assertArrayHasKey('id', $options);
        $this->assertArrayHasKey('parent', $options);
        $this->assertEquals('index', $options['index']);
        $this->assertEquals(1, $options['id']);
        $this->assertEquals('2', $options['parent']);
        $this->assertArrayNotHasKey('type', $options);
        $this->assertArrayNotHasKey('op_type', $options);
        $this->assertArrayNotHasKey('_index', $options);
        $this->assertArrayNotHasKey('_id', $options);
        $this->assertArrayNotHasKey('_parent', $options);

        $options = $document->getOptions(array('parent', 'op_type', 'percolate'), true);

        $this->assertInternalType('array', $options);
        $this->assertEquals(2, count($options));
        $this->assertArrayHasKey('_parent', $options);
        $this->assertArrayHasKey('_op_type', $options);
        $this->assertEquals('2', $options['_parent']);
        $this->assertEquals('create', $options['_op_type']);
        $this->assertArrayNotHasKey('percolate', $options);
        $this->assertArrayNotHasKey('op_type', $options);
        $this->assertArrayNotHasKey('parent', $options);
    }

    public function testGetSetHasRemove()
    {
        $document = new Document(1, array('field1' => 'value1', 'field2' => 'value2', 'field3' => 'value3', 'field4' => null));

        $this->assertEquals('value1', $document->get('field1'));
        $this->assertEquals('value2', $document->get('field2'));
        $this->assertEquals('value3', $document->get('field3'));
        $this->assertNull($document->get('field4'));
        try {
            $document->get('field5');
            $this->fail('Undefined field get should throw exception');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }

        $this->assertTrue($document->has('field1'));
        $this->assertTrue($document->has('field2'));
        $this->assertTrue($document->has('field3'));
        $this->assertTrue($document->has('field4'));
        $this->assertFalse($document->has('field5'), 'Field5 should not be isset, because it is not set');

        $data = $document->getData();

        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);
        $this->assertArrayHasKey('field3', $data);
        $this->assertEquals('value3', $data['field3']);
        $this->assertArrayHasKey('field4', $data);
        $this->assertNull($data['field4']);

        $returnValue = $document->set('field1', 'changed1');
        $this->assertInstanceOf('Elastica\Document', $returnValue);
        $returnValue = $document->remove('field3');
        $this->assertInstanceOf('Elastica\Document', $returnValue);
        try {
            $document->remove('field5');
            $this->fail('Undefined field unset should throw exception');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }

        $this->assertEquals('changed1', $document->get('field1'));
        $this->assertFalse($document->has('field3'));

        $newData = $document->getData();

        $this->assertNotEquals($data, $newData);
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

        $this->assertFalse($document->hasTtl());
        $options = $document->getOptions();
        $this->assertArrayNotHasKey('ttl', $options);

        $document->setTtl('1d');

        $newOptions = $document->getOptions();

        $this->assertArrayHasKey('ttl', $newOptions);
        $this->assertEquals('1d', $newOptions['ttl']);
        $this->assertNotEquals($options, $newOptions);

        $this->assertTrue($document->hasTtl());
        $this->assertEquals('1d', $document->getTtl());
    }

    public function testSerializedData()
    {
        $data = '{"user":"rolf"}';
        $document = new Document(1, $data);

        $this->assertFalse($document->has('user'));

        try {
            $document->get('user');
            $this->fail('User field should not be available');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }

        try {
            $document->remove('user');
            $this->fail('User field should not be available for removal');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }

        try {
            $document->set('name', 'shawn');
            $this->fail('Document should not allow to set new data');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }
    }

    public function testUpsert()
    {
        $document = new Document();

        $upsert = new Document();
        $upsert->setData(array('someproperty' => 'somevalue'));

        $this->assertFalse($document->hasUpsert());

        $document->setUpsert($upsert);

        $this->assertTrue($document->hasUpsert());
        $this->assertSame($upsert, $document->getUpsert());
    }

    public function testDocAsUpsert()
    {
        $document = new Document();

        $this->assertFalse($document->getDocAsUpsert());
        $this->assertSame($document, $document->setDocAsUpsert(true));
        $this->assertTrue($document->getDocAsUpsert());
    }
}
