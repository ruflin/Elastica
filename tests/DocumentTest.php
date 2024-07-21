<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\Reindex;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class DocumentTest extends BaseTest
{
    #[Group('unit')]
    public function testAddFile(): void
    {
        $fileName = '/dev/null';
        if (!\file_exists($fileName)) {
            $this->markTestSkipped("File {$fileName} does not exist.");
        }
        $doc = new Document();
        $returnValue = $doc->addFile('key', $fileName);

        $this->assertSame($doc, $returnValue);
    }

    #[Group('unit')]
    public function testAddGeoPoint(): void
    {
        $doc = new Document();
        $returnValue = $doc->addGeoPoint('point', 38.89859, -77.035971);

        $this->assertSame($doc, $returnValue);
    }

    #[Group('unit')]
    public function testSetData(): void
    {
        $doc = new Document();
        $returnValue = $doc->setData(['data']);

        $this->assertSame($doc, $returnValue);
    }

    #[Group('unit')]
    public function testToArray(): void
    {
        $id = '17';
        $data = ['hello' => 'world'];
        $index = 'textindex';

        $doc = new Document($id, $data, $index);

        $result = [
            '_index' => $index,
            '_id' => $id,
            '_source' => $data,
        ];
        $this->assertEquals($result, $doc->toArray());
    }

    #[Group('unit')]
    public function testSetIndex(): void
    {
        $document = new Document();
        $document->setIndex('index2');

        $this->assertEquals('index2', $document->getIndex());

        $index = new Index($this->_getClient(), 'index');

        $document->setIndex($index);
        $this->assertEquals('index', $document->getIndex());
    }

    #[Group('unit')]
    public function testHasId(): void
    {
        $document = new Document();
        $this->assertFalse($document->hasId());
        $document->setId(null);
        $this->assertFalse($document->hasId());
        $document->setId('0');
        $this->assertTrue($document->hasId());
        $document->setId('hello');
        $this->assertTrue($document->hasId());
    }

    #[Group('unit')]
    public function testGetSetHasRefresh(): void
    {
        $document = new Document();
        $this->assertFalse($document->hasRefresh());

        try {
            $document->getRefresh();
            $this->fail('Undefined refresh option should throw exception');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }

        $document->setRefresh(false);
        $this->assertTrue($document->hasRefresh());
        $this->assertFalse($document->getRefresh());

        $document->setRefresh(true);
        $this->assertTrue($document->hasRefresh());
        $this->assertTrue($document->getRefresh());

        $document->setRefresh(Reindex::REFRESH_WAIT_FOR);
        $this->assertTrue($document->hasRefresh());
        $this->assertEquals(Reindex::REFRESH_WAIT_FOR, $document->getRefresh());
    }

    #[Group('unit')]
    public function testGetOptions(): void
    {
        $document = new Document();
        $document->setIndex('index');
        $document->setOpType('create');
        $document->setId('1');

        $options = $document->getOptions(['_index', 'type', '_id', 'op_type']);

        $this->assertIsArray($options);
        $this->assertCount(3, $options);
        $this->assertArrayHasKey('_index', $options);
        $this->assertArrayHasKey('_id', $options);
        $this->assertArrayHasKey('op_type', $options);
        $this->assertEquals('index', $options['_index']);
        $this->assertEquals(1, $options['_id']);
        $this->assertEquals('create', $options['op_type']);
        $this->assertArrayNotHasKey('type', $options);
        $this->assertArrayNotHasKey('index', $options);
        $this->assertArrayNotHasKey('id', $options);
        $this->assertArrayNotHasKey('parent', $options);
    }

    #[Group('unit')]
    public function testGetSetHasRemove(): void
    {
        $document = new Document('1', ['field1' => 'value1', 'field2' => 'value2', 'field3' => 'value3', 'field4' => null]);

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
        $this->assertInstanceOf(Document::class, $returnValue);
        $returnValue = $document->remove('field3');
        $this->assertInstanceOf(Document::class, $returnValue);
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

    #[Group('unit')]
    public function testDataPropertiesOverloading(): void
    {
        $document = new Document('1', ['field1' => 'value1', 'field2' => 'value2', 'field3' => 'value3', 'field4' => null]);

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
        $this->assertFalse(\property_exists($document, 'field3'));

        $newData = $document->getData();

        $this->assertNotEquals($data, $newData);
    }

    #[Group('unit')]
    public function testSerializedData(): void
    {
        $data = '{"user":"rolf"}';
        $document = new Document('1', $data);

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

    #[Group('unit')]
    public function testUpsert(): void
    {
        $document = new Document();

        $upsert = new Document();
        $upsert->setData(['someproperty' => 'somevalue']);

        $this->assertFalse($document->hasUpsert());

        $document->setUpsert($upsert);

        $this->assertTrue($document->hasUpsert());
        $this->assertSame($upsert, $document->getUpsert());
    }

    #[Group('unit')]
    public function testDocAsUpsert(): void
    {
        $document = new Document();

        $this->assertFalse($document->getDocAsUpsert());
        $this->assertSame($document, $document->setDocAsUpsert(true));
        $this->assertTrue($document->getDocAsUpsert());
    }
}
