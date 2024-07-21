<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\ConvertProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ConvertProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testConvert(): void
    {
        $processor = new ConvertProcessor('foo', 'integer');

        $expected = [
            'convert' => [
                'field' => 'foo',
                'type' => 'integer',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testConvertWithNonDefaultOptions(): void
    {
        $processor = (new ConvertProcessor('foo', 'integer'))
            ->setIgnoreFailure(true)
            ->setIgnoreMissing(true)
        ;

        $expected = [
            'convert' => [
                'field' => 'foo',
                'type' => 'integer',
                'ignore_failure' => true,
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());

        $processor->setTargetField('field2');

        $expected = [
            'convert' => [
                'field' => 'foo',
                'type' => 'integer',
                'ignore_failure' => true,
                'ignore_missing' => true,
                'target_field' => 'field2',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testConvertField(): void
    {
        $append = new ConvertProcessor('foo', 'float');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Convert');
        $pipeline->addProcessor($append)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'ruflin', 'type' => 'elastica', 'foo' => '5.290']),
            new Document(null, ['name' => 'nicolas', 'type' => 'elastica', 'foo' => '6.908']),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('elastica');

        $this->assertCount(2, $result->getResults());

        $results = $result->getResults();
        foreach ($results as $result) {
            $value = $result->getData();
            $this->assertIsFloat($value['foo']);
        }

        $this->assertSame(5.290, $results[0]->getHit()['_source']['foo']);
        $this->assertSame(6.908, $results[1]->getHit()['_source']['foo']);
    }
}
