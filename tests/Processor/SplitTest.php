<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Split;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class SplitTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testSplit(): void
    {
        $processor = new Split('joined_array_field', '-');

        $expected = [
            'split' => [
                'field' => 'joined_array_field',
                'separator' => '-',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testSplitWithNonDefaultOptions(): void
    {
        $processor = new Split('joined_array_field', '-');
        $processor->setIgnoreMissing(true);

        $expected = [
            'split' => [
                'field' => 'joined_array_field',
                'separator' => '-',
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testSplitField(): void
    {
        $split = new Split('name', '&');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Split');
        $pipeline->addProcessor($split)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'nicolas&ruflin']),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        /** @var ResultSet $result */
        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $results = $result->getResults();
        $this->assertSame(['nicolas', 'ruflin'], ($results[0]->getHit())['_source']['name']);
    }
}
