<?php
namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Append;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

class AppendTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testAppendSingleValue()
    {
        $processor = new Append('field1', 'item2');

        $expected = [
            'append' => [
                'field' => 'field1',
                'value' => 'item2',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testAppendArray()
    {
        $processor = new Append('field1', ['item2', 'item3', 'item4']);

        $expected = [
            'append' => [
                'field' => 'field1',
                'value' => ['item2', 'item3', 'item4'],
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testAppend()
    {
        $append = new Append('foo', ['item2', 'item3', 'item4']);

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Append');
        $pipeline->addProcessor($append)->create();

        $index = $this->_createIndex();
        $type = $index->getType('bulk_test');

        // Add document to normal index
        $doc1 = new Document(null, ['name' => 'ruflin', 'type' => 'elastica', 'foo' => null]);
        $doc2 = new Document(null, ['name' => 'nicolas', 'type' => 'elastica', 'foo' => null]);

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);
        $bulk->setType($type);

        $bulk->addDocuments([
            $doc1, $doc2,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        /** @var ResultSet $result */
        $result = $index->search('*');

        $this->assertCount(2, $result->getResults());

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertCount(4, $value['foo']);
            $this->assertNull($value['foo'][0]);
            $this->assertEquals('item2', $value['foo'][1]);
            $this->assertEquals('item3', $value['foo'][2]);
            $this->assertEquals('item4', $value['foo'][3]);
        }
    }
}
