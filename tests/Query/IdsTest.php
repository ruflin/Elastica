<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\Query\Ids;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class IdsTest extends BaseTest
{
    /** @var Index */
    protected $index;

    protected function setUp(): void
    {
        parent::setUp();

        if (!$this->_isFunctionalGroup()) {
            return;
        }

        $this->index = $this->_createIndex();

        $this->index->addDocument(new Document('1', ['name' => 'hello world']));
        $this->index->addDocument(new Document('2', ['name' => 'nicolas ruflin']));
        $this->index->addDocument(new Document('3', ['name' => 'ruflin']));

        $this->index->refresh();
    }

    #[DataProvider('nonListIdsProvider')]
    #[Group('unit')]
    public function testConstructorRejectsNonListIds(array $ids): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Ids must be a list. Use array_values() to reindex the ids.');

        new Ids($ids);
    }

    #[DataProvider('nonListIdsProvider')]
    #[Group('unit')]
    public function testSetIdsRejectsNonListIds(array $ids): void
    {
        $query = new Ids(['1']);

        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Ids must be a list. Use array_values() to reindex the ids.');

        try {
            $query->setIds($ids);
        } finally {
            $this->assertSame(['ids' => ['values' => ['1']]], $query->toArray());
        }
    }

    public static function nonListIdsProvider(): iterable
    {
        yield 'removed first id' => [[1 => '1', 2 => '2']];
        yield 'gap between ids' => [[0 => '1', 2 => '2']];
        yield 'out of order keys' => [[1 => '1', 0 => '2']];
        yield 'string keys' => [['first' => '1', 'second' => '2']];
    }

    #[DataProvider('listIdsProvider')]
    #[Group('unit')]
    public function testListIds(array $ids): void
    {
        $expected = ['ids' => ['values' => $ids]];
        $this->assertSame($expected, (new Ids($ids))->toArray());

        $query = new Ids(['initial']);
        $this->assertSame($query, $query->setIds($ids));
        $this->assertSame($expected, $query->toArray());
    }

    public static function listIdsProvider(): iterable
    {
        yield 'empty list' => [[]];
        yield 'string ids and duplicates' => [['1', '2', '1']];
        yield 'reindexed ids' => [\array_values([1 => '1', 2 => '2'])];
    }

    #[Group('functional')]
    public function testSetIdsSearchSingle(): void
    {
        $query = new Ids();
        $query->setIds('1');

        $resultSet = $this->index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    #[Group('functional')]
    public function testSetIdsSearchArray(): void
    {
        $query = new Ids();
        $query->setIds(['1', '2']);

        $resultSet = $this->index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    #[Group('functional')]
    public function testAddIdsSearchSingle(): void
    {
        $query = new Ids();
        $query->addId('3');

        $resultSet = $this->index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    #[Group('functional')]
    public function testComboIdsSearchArray(): void
    {
        $query = new Ids();

        $query->setIds(['1', '2']);
        $query->addId('3');

        $resultSet = $this->index->search($query);

        $this->assertEquals(3, $resultSet->count());
    }

    #[Group('functional')]
    public function testSetTypeSingleSearchSingle(): void
    {
        $query = new Ids();
        $query->setIds('1');
        $resultSet = $this->index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    #[Group('functional')]
    public function testSetTypeSingleSearchArray(): void
    {
        $query = new Ids();
        $query->setIds(['1', '2']);

        $resultSet = $this->index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    #[Group('functional')]
    public function testSetTypeSingleSearchSingleDocInOtherType(): void
    {
        $query = new Ids();

        // Doc 4 is in the second type...
        $query->setIds('4');

        $resultSet = $this->index->search($query);

        // ...therefore 0 results should be returned
        $this->assertEquals(0, $resultSet->count());
    }

    #[Group('functional')]
    public function testSetTypeSingleSearchArrayDocInOtherType(): void
    {
        $query = new Ids();

        // Doc 4 is in the second type...
        $query->setIds(['1', '4']);

        $resultSet = $this->index->search($query);

        // ...therefore only 1 result should be returned
        $this->assertEquals(1, $resultSet->count());
    }
}
