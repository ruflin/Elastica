<?php

namespace Elastica\Test\Processor;

use Elastica\Processor\DateIndexNameProcessor;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class DateIndexNameProcessorTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testDateIndexName(): void
    {
        $processor = new DateIndexNameProcessor('date1', 'M');

        $expected = [
            'date_index_name' => [
                'field' => 'date1',
                'date_rounding' => 'M',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }

    /**
     * @group unit
     */
    public function testDateIndexNameWithNonDefaultOptions(): void
    {
        $processor = (new DateIndexNameProcessor('date1', 'M'))
            ->setIgnoreFailure(true)
            ->setTimezone('Europe/Rome')
            ->setLocale('ITALIAN')
            ->setIndexNamePrefix('myindex-')
            ->setDateFormats(['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS'])
        ;

        $expected = [
            'date_index_name' => [
                'field' => 'date1',
                'date_rounding' => 'M',
                'ignore_failure' => true,
                'timezone' => 'Europe/Rome',
                'locale' => 'ITALIAN',
                'date_formats' => ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS'],
                'index_name_prefix' => 'myindex-',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }
}
