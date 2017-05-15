<?php
namespace Elastica\Query;

use Elastica\Document;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class ParentIdTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $type = 'test';

        $query = new ParentId($type, 1);

        $expectedArray = [
            'parent_id' => [
                'type' => 'test',
                'id' => 1,
                'ignore_unmapped' => false,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testParentId()
    {
        $index = $this->_createIndex();

        $shopType = $index->getType('shop');
        $productType = $index->getType('product');
        $mapping = new Mapping();
        $mapping->setParent('shop');
        $productType->setMapping($mapping);

        $shopType->addDocuments(
            [
                new Document('zurich', ['brand' => 'google']),
                new Document('london', ['brand' => 'apple']),
            ]
        );

        $doc1 = new Document(1, ['device' => 'chromebook']);
        $doc1->setParent('zurich');

        $doc2 = new Document(2, ['device' => 'macmini']);
        $doc2->setParent('london');

        $productType->addDocument($doc1);
        $productType->addDocument($doc2);

        $index->refresh();

        $parentQuery = new ParentId($productType->getName(), 'zurich');
        $search = new Search($index->getClient());
        $results = $search->search($parentQuery);
        $this->assertEquals(1, $results->count());
        $result = $results->current();
        $data = $result->getData();
        $this->assertEquals($data['device'], 'chromebook');
    }
}
