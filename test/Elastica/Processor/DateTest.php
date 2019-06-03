<?php

namespace Elastica\Test\Processor;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Processor\Date;
use Elastica\ResultSet;
use Elastica\Test\BasePipeline as BasePipelineTest;

class DateTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testDate()
    {
        $processor = new Date('initial_date', ['dd/MM/yyyy hh:mm:ss']);

        $expected = [
            'date' => [
                'field' => 'initial_date',
                'formats' => ['dd/MM/yyyy hh:mm:ss'],
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());

        $processor = new Date('initial_date', ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS']);

        $expected = [
            'date' => [
                'field' => 'initial_date',
                'formats' => ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS'],
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testDateWithNonDefaultOptions()
    {
        $processor = new Date('initial_date', ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS']);
        $processor->setTargetField('timestamp');
        $processor->setTimezone('Europe/Rome');
        $processor->setLocale('ITALIAN');

        $expected = [
            'date' => [
                'field' => 'initial_date',
                'formats' => ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS'],
                'target_field' => 'timestamp',
                'timezone' => 'Europe/Rome',
                'locale' => 'ITALIAN',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group functional
     */
    public function testDateField()
    {
        $date = new Date('date_field', ['yyyy dd MM hh:mm:ss']);
        $date->setTargetField('date_parsed');
        $date->setTimezone('Europe/Rome');
        $date->setLocale('IT');

        $pipeline = $this->_createPipeline('my_custom_pipeline', 'pipeline for Date');
        $pipeline->addProcessor($date)->create();

        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        // Add document to normal index
        $doc1 = new Document(null, ['date_field' => '2010 12 06 11:05:15']);

        $bulk = new Bulk($index->getClient());
        $bulk->setIndex($index);
        $bulk->setType($type);

        $bulk->addDocument($doc1);
        $bulk->setRequestParam('pipeline', 'my_custom_pipeline');

        $bulk->send();
        $index->refresh();

        /** @var ResultSet $result */
        $result = $index->search('*');

        $this->assertCount(1, $result->getResults());

        $results = $result->getResults();

        $this->assertEquals('2010-06-12T00:00:00.000+02:00', ($results[0]->getHit())['_source']['date_parsed']);
    }
}
