<?php

namespace Elastica\Test;

use Elastica\Document;
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
}
