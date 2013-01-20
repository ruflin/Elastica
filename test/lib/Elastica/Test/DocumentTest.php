<?php

namespace Elastica\Test;

use Elastica\Document;
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

	public function testHasId() {
		$document = new Document();
		$this->assertFalse($document->hasId());
		$document->setId('');
		$this->assertFalse($document->hasId());
		$document->setId(0);
		$this->assertTrue($document->hasId());
		$document->setId('hello');
		$this->assertTrue($document->hasId());
	}
}
