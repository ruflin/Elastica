<?php
namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Json;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

class JsonTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testJson()
    {
        $processor = new Json('string_source');

        $expected = [
            'json' => [
                'field' => 'string_source',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testJsonWithNonDefaultOptions()
    {
        $processor = new Json('string_source');
        $processor->setTargetField('json_target');
        $processor->setAddToRoot(true);

        $expected = [
            'json' => [
                'field' => 'string_source',
                'target_field' => 'json_target',
                'add_to_root' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testJsonField()
    {
        $json = new Json('name');
        $json->setTargetField('realname');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Json');
        $pipeline->addProcessor($json)->create();

        $index = $this->_createIndex();
        $type = $index->getType('bulk_test');

        // Add document to normal index
        $doc1 = new Document(null, ['name' => json_encode(['foo' => 2000])]);

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);
        $bulk->setType($type);

        $bulk->addDocument($doc1);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        /** @var ResultSet $result */
        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $resultExpected = [
            'foo' => 2000,
        ];

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertEquals($resultExpected, $value['realname']);
        }
    }
}
