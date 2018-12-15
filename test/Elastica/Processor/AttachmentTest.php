<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Attachment;
use Elastica\Test\BasePipeline as BasePipelineTest;
use Elastica\Type;

class AttachmentTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testAttachment()
    {
        $processor = new Attachment('data');

        $expected = [
            'attachment' => [
                'field' => 'data',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testAttachmentWithNonDefaultOptions()
    {
        $processor = new Attachment('data');
        $processor->setIndexedChars(1000);
        $processor->setProperties(['content', 'title', 'language']);
        $processor->setTargetField('attachment-new-name');
        $processor->setIgnoreMissing(true);

        $expected = [
            'attachment' => [
                'field' => 'data',
                'indexed_chars' => 1000,
                'properties' => ['content', 'title', 'language'],
                'target_field' => 'attachment-new-name',
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testAttachmentAddPdf()
    {
        $attachment = new Attachment('data');
        $pipeline = $this->_createPipeline('my_custom_pipeline_attachment', 'pipeline for Attachment');
        $pipeline->addProcessor($attachment);
        $pipeline->create();

        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);
        $bulk->setType($type);

        $doc1 = new Document(null);
        $doc1->addFile('data', BASE_PATH.'/data/test.pdf');

        $doc2 = new Document(2, ['data' => '', 'text' => 'test running in basel']);

        $bulk->addDocuments([
            $doc1, $doc2,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline_attachment');

        $bulk->send();
        $index->refresh();

        $resultSet = $type->search('xodoa');
        $this->assertEquals(1, $resultSet->count());

        $resultSet = $type->search('test');
        $this->assertEquals(2, $resultSet->count());

        // Author is ruflin
        $resultSet = $type->search('ruflin');
        $this->assertEquals(1, $resultSet->count());

        // String does not exist in file
        $resultSet = $type->search('guschti');
        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testAttachmentAddPdfFileContent()
    {
        $attachment = new Attachment('data');
        $pipeline = $this->_createPipeline('my_custom_pipeline_attachment', 'pipeline for Attachment');
        $pipeline->addProcessor($attachment);
        $pipeline->create();

        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);
        $bulk->setType($type);

        $doc1 = new Document(null);
        $doc1->addFile('data', BASE_PATH.'/data/test.pdf');
        $doc1->set('text', 'basel world');

        $doc2 = new Document(2, ['data' => '', 'text' => 'test running in basel']);
        $doc2->set('text', 'running in basel');

        $bulk->addDocuments([
            $doc1, $doc2,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline_attachment');

        $bulk->send();
        $index->refresh();

        $resultSet = $type->search('xodoa');
        $this->assertEquals(1, $resultSet->count());

        $resultSet = $type->search('basel');
        $this->assertEquals(2, $resultSet->count());

        // Author is ruflin
        $resultSet = $type->search('ruflin');
        $this->assertEquals(1, $resultSet->count());

        // String does not exist in file
        $resultSet = $type->search('guschti');
        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testAddWordxFile()
    {
        $attachment = new Attachment('data');
        $pipeline = $this->_createPipeline('my_custom_pipeline_attachment', 'pipeline for Attachment');
        $pipeline->addProcessor($attachment);
        $pipeline->create();

        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);
        $bulk->setType($type);

        $doc1 = new Document(null);
        $doc1->addFile('data', BASE_PATH.'/data/test.docx');
        $doc1->set('text', 'basel world');

        $doc2 = new Document(2, ['data' => '', 'text' => 'test running in basel']);

        $bulk->addDocuments([
            $doc1, $doc2,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline_attachment');

        $bulk->send();
        $index->refresh();

        $resultSet = $type->search('basel');
        $this->assertEquals(2, $resultSet->count());

        $resultSet = $type->search('ruflin');
        $this->assertEquals(0, $resultSet->count());

        $resultSet = $type->search('Xodoa');
        $this->assertEquals(1, $resultSet->count());

        // String does not exist in file
        $resultSet = $type->search('guschti');
        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testExcludeFileSource()
    {
        $attachment = new Attachment('data');
        $pipeline = $this->_createPipeline('my_custom_pipeline_attachment', 'pipeline for Attachment');
        $pipeline->addProcessor($attachment);
        $pipeline->create();

        $indexMapping = ['data' => ['type' => 'text'], 'text' => ['type' => 'text', 'store' => true],
            'title' => ['type' => 'text', 'store' => true], ];

        $indexParams = ['settings' => ['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]]];

        $index = $this->_createIndex();
        $type = new Type($index, '_doc');

        $mapping = Type\Mapping::create($indexMapping);
        $mapping->setSource(['excludes' => ['data']]);

        $mapping->setType($type);

        $index->create($indexParams, true);
        $type->setMapping($mapping);

        $docId = 1;
        $text = 'Basel World';
        $title = 'No Title';

        $doc1 = new Document($docId);
        $doc1->set('text', $text);
        $doc1->set('title', $title);
        $doc1->addFile('data', BASE_PATH.'/data/test.docx');

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);
        $bulk->setType($type);

        $bulk->addDocuments([
            $doc1,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline_attachment');

        // Optimization necessary, as otherwise source still in realtime get
        $bulk->send();
        $index->forcemerge();

        $data = $type->getDocument($docId)->getData();
        $this->assertEquals($data['title'], $title);
        $this->assertEquals($data['text'], $text);
        $this->assertArrayNotHasKey('file', $data);
    }
}
