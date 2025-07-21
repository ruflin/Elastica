<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\UppercaseProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class UppercaseProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testUppercase(): void
    {
        $processor = new UppercaseProcessor('foo');

        $expected = [
            'uppercase' => [
                'field' => 'foo',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testUppercaseWithNonDefaultOptions(): void
    {
        $processor = (new UppercaseProcessor('foo'))
            ->setIgnoreFailure(true)
            ->setIgnoreMissing(true)
        ;

        $expected = [
            'uppercase' => [
                'field' => 'foo',
                'ignore_failure' => true,
                'ignore_missing' => true,
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testUppercaseField(): void
    {
        $ucase = new UppercaseProcessor('name');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Uppercase');
        $pipeline->addProcessor($ucase)->create();

        $index = $this->_createIndex();
        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocuments([
            new Document(null, ['name' => 'ruflin']),
            new Document(null, ['name' => 'nicolas']),
        ]);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(2, $result->getResults());

        $results = $result->getResults();
        $this->assertSame('RUFLIN', $results[0]->getHit()['_source']['name']);
        $this->assertSame('NICOLAS', $results[1]->getHit()['_source']['name']);
    }
}
