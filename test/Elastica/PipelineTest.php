<?php
namespace Elastica\Test;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Pipeline;
use Elastica\Processor\Rename;
use Elastica\Processor\Set;
use Elastica\Processor\Trim;

class PipelineTest extends BasePipeline
{
    /**
     * @group unit
     */
    public function testProcessor()
    {
        $trim = new Trim('field1');
        $rename = new Rename('foo', 'target.field');
        $set = new Set('field4', 324);

        $processors = new Pipeline($this->_getClient());
        $processors->setDescription('this is a new pipeline');
        $processors->addProcessor($trim);
        $processors->addProcessor($rename);
        $processors->addProcessor($set);

        $expected = [
            'description' => 'this is a new pipeline',
            'processors' => [[
                'trim' => [
                    'field' => 'field1',
                ],
                'rename' => [
                    'field' => 'foo',
                    'target_field' => 'target.field',
                ],
                'set' => [
                    'field' => 'field4',
                    'value' => 324,
                ],
            ]],
        ];

        $this->assertEquals($expected, $processors->toArray());
    }

    /**
     * @group functional
     */
    public function testPipelineCreate()
    {
        $set = new Set('field4', 333);
        $trim = new Trim('field1');
        $rename = new Rename('foo', 'target.field');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Set');
        $pipeline->addProcessor($set);
        $pipeline->addProcessor($trim);
        $pipeline->addProcessor($rename);

        $result = $pipeline->create();

        $this->assertContains('acknowledged', $result->getData());

        $pipeGet = $pipeline->getPipeline('my_custom_pipeline');
        $result = $pipeGet->getData();

        $this->assertContains($result['my_custom_pipeline']['description'], 'pipeline for Set');
        $this->assertContains($result['my_custom_pipeline']['processors'][0]['set']['field'], 'field4');
        $this->assertContains($result['my_custom_pipeline']['processors'][0]['set']['value'], '333');
        $this->assertContains($result['my_custom_pipeline']['processors'][0]['trim']['field'], 'field1');
    }

    /**
     * @group functional
     */
    public function testPipelineonIndex()
    {
        $set = new Set('foo', 333);

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Set');
        $pipeline->addProcessor($set);

        $result = $pipeline->create();

        $this->assertContains('acknowledged', $result->getData());

        $index = $this->_createIndex('testpipelinecreation');
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
        $result = $index->search('elastica');

        $this->assertCount(2, $result->getResults());

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertEquals(333, $value['foo']);
        }
    }

    /**
     * @group functional
     */
    public function testDeletePipeline()
    {
        $pipeline = $this->_createPipeline();
        try {
            $pipeline->deletePipeline('non_existent_pipeline');
            $this->fail('an exception should be raised!');
        } catch (ResponseException $e) {
            $result = $e->getResponse()->getFullError();

            $this->assertEquals('resource_not_found_exception', $result['type']);
            $this->assertEquals('pipeline [non_existent_pipeline] is missing', $result['reason']);
        }
    }
}
