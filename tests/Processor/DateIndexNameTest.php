<?php

namespace Elastica\Test\Processor;

use Elastica\Processor\DateIndexName;
use Elastica\Test\BasePipeline as BasePipelineTest;

/**
 * @internal
 */
class DateIndexNameTest extends BasePipelineTest
{
    /**
     * @group unit
     */
    public function testDateIndexName(): void
    {
        $processor = new DateIndexName('date1', 'M');

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
        $processor = new DateIndexName('date1', 'M');
        $processor->setTimezone('Europe/Rome');
        $processor->setLocale('ITALIAN');
        $processor->setIndexNamePrefix('myindex-');
        $processor->setDateFormats(['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS']);

        $expected = [
            'date_index_name' => [
                'field' => 'date1',
                'date_rounding' => 'M',
                'timezone' => 'Europe/Rome',
                'locale' => 'ITALIAN',
                'date_formats' => ['dd/MM/yyyy hh:mm:ss', 'ISO8601', 'UNIX', 'UNIX_MS'],
                'index_name_prefix' => 'myindex-',
            ],
        ];

        $this->assertEquals($expected, $processor->toArray());
    }
}
