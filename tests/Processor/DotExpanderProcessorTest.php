<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\DotExpanderProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class DotExpanderProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testDotExpander(): void
    {
        $processor = new DotExpanderProcessor('foo.bar');

        $expected = [
            'dot_expander' => [
                'field' => 'foo.bar',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testDotExpanderWithNonDefaultOptions(): void
    {
        $processor = (new DotExpanderProcessor('foo.bar'))
            ->setIgnoreFailure(true)
        ;

        $expected = [
            'dot_expander' => [
                'field' => 'foo.bar',
                'ignore_failure' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testDotExpanderField(): void
    {
        $dotExpander = new DotExpanderProcessor('foo.bar');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for DotExpander');
        $pipeline->addProcessor($dotExpander)->create();

        $index = $this->_createIndex();

        // Add document to normal index
        $doc1 = new Document(null, ['foo.bar' => 'value']);

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocument($doc1);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $expect = [
            'foo' => [
                'bar' => 'value',
            ],
        ];
        $results = $result->getResults();
        $this->assertEquals($expect, $results[0]->getHit()['_source']);
    }
}
