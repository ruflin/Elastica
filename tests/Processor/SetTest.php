<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Set;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

class SetTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testSet()
    {
        $processor = new Set('field1', 582.1);

        $expected = [
            'set' => [
                'field' => 'field1',
                'value' => 582.1,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testSetWithNonDefaultOptions()
    {
        $processor = new Set('field1', 582.1);
        $processor->setOverride(false);

        $expected = [
            'set' => [
                'field' => 'field1',
                'value' => 582.1,
                'override' => false,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testSetField()
    {
        $set = new Set('package', 'Elastica');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Set');
        $pipeline->addProcessor($set)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'nicolas', 'package' => 'Elastico']),
            new Document(null, ['name' => 'ruflin']),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        /** @var ResultSet $result */
        $result = $index->search('*');

        $this->assertCount(2, $result->getResults());

        foreach ($result->getResults() as $rx) {
            $value = $rx->getData();
            $this->assertSame('Elastica', $value['package']);
        }
    }
}
