<?php
namespace Elastica\Test\Processor;

use Elastica\Processor\Attachment;
use Elastica\Test\BasePipeline as BasePipelineTest;

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
                'field' => 'data'
            ]
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
                'ignore_missing' => true
            ]
        ];

        $this->assertEquals($expected, $processor->toArray());
    }
}