<?php
namespace Elastica\Test;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query\Match;
use Elastica\Reindex;
use Elastica\Type;

class ReindexTest extends Base
{
    /**
     * Test default reindex.
     *
     * @group functional
     */
    public function testReindex()
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $this->_addDocs($oldIndex->getType('resetTest'), 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $reindex = new Reindex($oldIndex, $newIndex);
        $this->assertInstanceOf(
            Index::class,
            $newIndex
        );
        $newIndex = $reindex->run();

        $this->assertEquals(10, $newIndex->count());

        $oldResult = [];

        foreach ($oldIndex->search()->getResults() as $result) {
            $oldResult[] = $result->getData();
        }

        $newResult = [];

        foreach ($newIndex->search()->getResults() as $result) {
            $newResult[] = $result->getData();
        }

        $this->assertEquals($oldResult, $newResult);
    }

    /**
     * Test reindex type option.
     *
     * @group functional
     */
    public function testReindexTypeOption()
    {
        $oldIndex = $this->_createIndex('', true, 2);
        $type1 = $oldIndex->getType('crossIndexTest_1');
        $type2 = $oldIndex->getType('crossIndexTest_2');

        $docs1 = $this->_addDocs($type1, 10);
        $docs2 = $this->_addDocs($type2, 10);

        $newIndex = $this->_createIndex(null, true, 2);

        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::TYPE => 'crossIndexTest_1',
        ]);
        $reindex->run();

        $this->assertEquals(10, $newIndex->count());
        $newIndex->deleteDocuments($docs1);

        // string
        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::TYPE => 'crossIndexTest_2',
        ]);
        $reindex->run();
        $this->assertEquals(10, $newIndex->count());
        $newIndex->deleteDocuments($docs2);

        // array
        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::TYPE => [
                $type1,
                'crossIndexTest_2',
            ],
        ]);
        $reindex->run();
        $this->assertEquals(20, $newIndex->count());
    }

    /**
     * @group functional
     */
    public function testReindexOpTypeOptionWithProceedSetOnConflicts()
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $type1 = $oldIndex->getType('crossIndexTest_1');

        $docs1 = $this->_addDocs($type1, 10);

        $subDocs1 = array_splice($docs1, 0, 5);

        $newIndex = $this->_createIndex('idx2', true, 2);
        $newIndex->addDocuments($subDocs1);
        $newIndex->refresh();

        $this->assertEquals(5, $newIndex->count());

        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::OPERATION_TYPE => Reindex::OPERATION_TYPE_CREATE,
            Reindex::CONFLICTS => Reindex::CONFLICTS_PROCEED,
        ]);

        $reindex->run();

        $this->assertEquals(10, $newIndex->count());
    }

    /**
     * @group functional
     */
    public function testReindexWithQueryOption()
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $type1 = $oldIndex->getType('crossIndexTest_1');
        $docs1 = $this->_addDocs($type1, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $query = new Match('id', 8);

        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::QUERY => $query,
        ]);
        $reindex->run();

        $results = $newIndex->search()->getResults();
        $this->assertEquals(1, $newIndex->count());
        foreach ($results as $result) {
            $this->assertEquals($docs1[7]->getData(), $result->getData());
        }
    }

    /**
     * @group functional
     */
    public function testReindexWithSizeOption()
    {
        $oldIndex = $this->_createIndex('idx1', true, 2);
        $type1 = $oldIndex->getType('crossIndexTest_1');
        $this->_addDocs($type1, 10);

        $newIndex = $this->_createIndex('idx2', true, 2);

        $reindex = new Reindex($oldIndex, $newIndex, [
            Reindex::SIZE => 5,
        ]);
        $reindex->run();

        $this->assertEquals(5, $newIndex->count());
    }

    /**
     * @param Type $type
     * @param int  $docs
     *
     * @return array
     */
    private function _addDocs(Type $type, $docs)
    {
        $insert = [];
        for ($i = 1; $i <= $docs; ++$i) {
            $insert[] = new Document($i, ['id' => $i, 'key' => 'value']);
        }

        $type->addDocuments($insert);
        $type->getIndex()->refresh();

        return $insert;
    }
}
