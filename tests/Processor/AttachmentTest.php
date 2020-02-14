<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Mapping;
use Elastica\Processor\Attachment;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @group functional
 *
 * @internal
 */
class AttachmentTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testAttachment(): void
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
    public function testAttachmentWithNonDefaultOptions(): void
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

    public function testAttachmentAddPdf(): void
    {
        $attachment = new Attachment('data');
        $pipeline = $this->_createPipeline('my_custom_pipeline_attachment', 'pipeline for Attachment');
        $pipeline->addProcessor($attachment);
        $pipeline->create();

        $index = $this->_createIndex();

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $doc1 = new Document(null);
        $doc1->addFile('data', __DIR__.'/../data/test.pdf');
        $doc2 = new Document(2, ['data' => '', 'text' => 'test running in basel']);

        $bulk->addDocuments([
            $doc1, $doc2,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline_attachment');

        $bulk->send();
        $index->refresh();

        $resultSet = $index->search('xodoa');
        $this->assertEquals(1, $resultSet->count());

        $resultSet = $index->search('test');
        $this->assertEquals(2, $resultSet->count());

        // Author is ruflin
        $resultSet = $index->search('ruflin');
        $this->assertEquals(1, $resultSet->count());

        // String does not exist in file
        $resultSet = $index->search('guschti');
        $this->assertEquals(0, $resultSet->count());
    }

    public function testAttachmentAddPdfFileContent(): void
    {
        $attachment = new Attachment('data');
        $pipeline = $this->_createPipeline('my_custom_pipeline_attachment', 'pipeline for Attachment');
        $pipeline->addProcessor($attachment);
        $pipeline->create();

        $index = $this->_createIndex();

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $doc1 = new Document(null);
        $doc1->addFile('data', __DIR__.'/../data/test.pdf');
        $doc1->set('text', 'basel world');

        $doc2 = new Document(2, ['data' => '', 'text' => 'test running in basel']);
        $doc2->set('text', 'running in basel');

        $bulk->addDocuments([
            $doc1, $doc2,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline_attachment');

        $bulk->send();
        $index->refresh();

        $resultSet = $index->search('xodoa');
        $this->assertEquals(1, $resultSet->count());

        $resultSet = $index->search('basel');
        $this->assertEquals(2, $resultSet->count());

        // Author is ruflin
        $resultSet = $index->search('ruflin');
        $this->assertEquals(1, $resultSet->count());

        // String does not exist in file
        $resultSet = $index->search('guschti');
        $this->assertEquals(0, $resultSet->count());
    }

    public function testAddWordxFile(): void
    {
        $attachment = new Attachment('data');
        $pipeline = $this->_createPipeline('my_custom_pipeline_attachment', 'pipeline for Attachment');
        $pipeline->addProcessor($attachment);
        $pipeline->create();

        $index = $this->_createIndex();

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $doc1 = new Document(null);
        $doc1->addFile('data', __DIR__.'/../data/test.docx');
        $doc1->set('text', 'basel world');

        $doc2 = new Document(2, ['data' => '', 'text' => 'test running in basel']);

        $bulk->addDocuments([
            $doc1, $doc2,
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline_attachment');

        $bulk->send();
        $index->refresh();

        $resultSet = $index->search('basel');
        $this->assertEquals(2, $resultSet->count());

        $resultSet = $index->search('ruflin');
        $this->assertEquals(0, $resultSet->count());

        $resultSet = $index->search('Xodoa');
        $this->assertEquals(1, $resultSet->count());

        // String does not exist in file
        $resultSet = $index->search('guschti');
        $this->assertEquals(0, $resultSet->count());
    }

    public function testExcludeFileSource(): void
    {
        $attachment = new Attachment('data');
        $pipeline = $this->_createPipeline('my_custom_pipeline_attachment', 'pipeline for Attachment');
        $pipeline->addProcessor($attachment);
        $pipeline->create();
        $index = $this->_createIndex();

        $mapping = new Mapping([
            'data' => ['type' => 'text'],
            'text' => ['type' => 'text', 'store' => true],
            'title' => ['type' => 'text', 'store' => true],
        ]);
        $mapping->setSource(['excludes' => ['data']]);

        $index->setMapping($mapping);

        $docId = 1;
        $text = 'Basel World';
        $title = 'No Title';

        $doc1 = new Document($docId);
        $doc1->set('text', $text);
        $doc1->set('title', $title);
        $doc1->addFile('data', __DIR__.'/../data//test.docx');

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([$doc1]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline_attachment');

        // Optimization necessary, as otherwise source still in realtime get
        $bulk->send();
        $index->forcemerge();

        $data = $index->getDocument($docId)->getData();
        $this->assertEquals($data['title'], $title);
        $this->assertEquals($data['text'], $text);
        $this->assertArrayNotHasKey('file', $data);
    }
}
