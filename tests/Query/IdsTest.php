<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query\Ids;
use Elastica\Query\Type;
use Elastica\Test\Base as BaseTest;

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

        $this->index = $this->_createIndex();

        $this->index->addDocument(new Document(1, ['name' => 'hello world']));
        $this->index->addDocument(new Document(2, ['name' => 'nicolas ruflin']));
        $this->index->addDocument(new Document(3, ['name' => 'ruflin']));

        $this->index->refresh();
    }

    /**
     * @group functional
     */
    public function testSetIdsSearchSingle(): void
    {
        $query = new Ids();
        $query->setIds('1');

        $resultSet = $this->index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetIdsSearchArray(): void
    {
        $query = new Ids();
        $query->setIds(['1', '2']);

        $resultSet = $this->index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testAddIdsSearchSingle(): void
    {
        $query = new Ids();
        $query->addId('3');

        $resultSet = $this->index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testComboIdsSearchArray(): void
    {
        $query = new Ids();

        $query->setIds(['1', '2']);
        $query->addId('3');

        $resultSet = $this->index->search($query);

        $this->assertEquals(3, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetTypeSingleSearchSingle(): void
    {
        $query = new Ids();
        $query->setIds('1');
        $resultSet = $this->index->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetTypeSingleSearchArray(): void
    {
        $query = new Ids();
        $query->setIds(['1', '2']);

        $resultSet = $this->index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetTypeSingleSearchSingleDocInOtherType(): void
    {
        $query = new Ids();

        // Doc 4 is in the second type...
        $query->setIds('4');

        $resultSet = $this->index->search($query);

        // ...therefore 0 results should be returned
        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     */
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
