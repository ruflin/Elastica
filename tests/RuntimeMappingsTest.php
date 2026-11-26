<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Elastica\RuntimeMappings;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class RuntimeMappingsTest extends BaseTest
{
    #[Group('unit')]
    public function testFieldDefinitions(): void
    {
        $fields = [
            'amount' => ['type' => 'double', 'script' => ['source' => 'emit(params.amount)', 'params' => ['amount' => 0]]],
            'parsed' => ['type' => 'composite', 'fields' => ['number' => ['type' => 'long']], 'script' => ['id' => 'parse_fields']],
            'customer' => ['type' => 'lookup', 'target_index' => 'customers', 'input_field' => 'customer_id', 'target_field' => 'id', 'fetch_fields' => ['name']],
        ];
        $mappings = new RuntimeMappings($fields);
        $this->assertSame($fields, $mappings->toArray());
        $this->assertSame($mappings, $mappings->addField('enabled', ['type' => 'boolean']));
        $this->assertSame(['type' => 'boolean'], $mappings->toArray()['enabled']);
        $this->assertSame($mappings, $mappings->setFields(['name' => ['type' => 'keyword']]));
        $this->assertSame(['name' => ['type' => 'keyword']], $mappings->toArray());
    }

    #[Group('unit')]
    public function testUnnamedFieldIsRejected(): void
    {
        $this->expectException(InvalidException::class);

        new RuntimeMappings(['' => ['type' => 'keyword']]);
    }

    #[Group('unit')]
    public function testQueryAndSearch(): void
    {
        $fields = ['computed' => ['type' => 'long', 'script' => ['source' => 'emit(1)']]];
        $query = new Query(new Term(['computed' => 1]));
        $this->assertSame($query, $query->setRuntimeMappings($fields));
        $this->assertSame($fields, $query->toArray()['runtime_mappings']);
        $this->assertSame(['term' => ['computed' => 1]], $query->toArray()['query']);

        $search = new Search($this->_getClient());
        $search->setQuery($query);
        $mappings = new RuntimeMappings(['other' => ['type' => 'keyword']]);
        $this->assertSame($search, $search->setRuntimeMappings($mappings));
        $this->assertSame($query, $search->getQuery());
        $this->assertSame($mappings, $query->getParam('runtime_mappings'));
        $this->assertSame($mappings->toArray(), $query->toArray()['runtime_mappings']);
    }

    #[Group('unit')]
    public function testEmptyMappingsSerializeAsAnObject(): void
    {
        $search = new Search($this->_getClient());
        $search->setRuntimeMappings([]);
        $this->assertSame('{}', \json_encode($search->getQuery()->toArray()['runtime_mappings']));
    }

    #[Group('functional')]
    public function testSearchOnRuntimeField(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['amount' => 25]),
            new Document('2', ['amount' => 75]),
        ]);
        $index->refresh();

        $query = new Query(new Range('doubled', ['gte' => 100]));
        $search = $index->createSearch($query);
        $search->setRuntimeMappings(new RuntimeMappings([
            'doubled' => ['type' => 'long', 'script' => ['source' => "emit(doc['amount'].value * 2)"]],
        ]));
        $results = $search->search();

        $this->assertCount(1, $results);
        $this->assertSame('2', $results->current()->getId());
    }
}
