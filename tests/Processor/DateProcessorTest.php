<?php

declare(strict_types=1);

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\DateProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class DateProcessorTest extends BasePipelineTest
{
    #[Group('unit')]
    public function testDate(): void
    {
        $processor = new DateProcessor('initial_date', ['dd/MM/yyyy hh:mm:ss']);

        $expected = [
            'date' => [
                'field' => 'initial_date',
                'formats' => ['dd/MM/yyyy hh:mm:ss'],
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());

        $processor = new DateProcessor('initial_date', ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS']);

        $expected = [
            'date' => [
                'field' => 'initial_date',
                'formats' => ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS'],
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('unit')]
    public function testDateWithNonDefaultOptions(): void
    {
        $processor = (new DateProcessor('initial_date', ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS']))
            ->setIgnoreFailure(true)
            ->setTargetField('timestamp')
            ->setTimezone('Europe/Rome')
            ->setLocale('ITALIAN')
        ;

        $expected = [
            'date' => [
                'field' => 'initial_date',
                'formats' => ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS'],
                'ignore_failure' => true,
                'target_field' => 'timestamp',
                'timezone' => 'Europe/Rome',
                'locale' => 'ITALIAN',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    #[Group('functional')]
    public function testDateField(): void
    {
        $date = new DateProcessor('date_field', ['yyyy dd MM hh:mm:ss']);
        $date->setTargetField('date_parsed');
        $date->setTimezone('Europe/Rome');
        $date->setLocale('IT');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Date');
        $pipeline->addProcessor($date)->create();

        $index = $this->_createIndex();

        // Add document to normal index
        $doc1 = new Document(null, ['date_field' => '2010 12 06 11:05:15']);

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);

        $bulk->addDocument($doc1);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $results = $result->getResults();

        $this->assertEquals('2010-06-12T00:00:00.000+02:00', $results[0]->getHit()['_source']['date_parsed']);
    }
}
