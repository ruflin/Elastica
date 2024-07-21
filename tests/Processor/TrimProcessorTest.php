<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\TrimProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class TrimProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testTrim(): void
    {
        $processor = new TrimProcessor('foo');

        $expected = [
            'trim' => [
                'field' => 'foo',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testTrimWithNonDefaultOptions(): void
    {
        $processor = (new TrimProcessor('foo'))
            ->setIgnoreFailure(true)
            ->setIgnoreMissing(true)
        ;

        $expected = [
            'trim' => [
                'field' => 'foo',
                'ignore_failure' => true,
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testTrimField(): void
    {
        $trim = new TrimProcessor('name');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Trim');
        $pipeline->addProcessor($trim)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => '   ruflin   ']),
            new Document(null, ['name' => '     nicolas     ']),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(2, $result->getResults());

        $results = $result->getResults();
        $this->assertSame('ruflin', $results[0]->getHit()['_source']['name']);
        $this->assertSame('nicolas', $results[1]->getHit()['_source']['name']);
    }
}
