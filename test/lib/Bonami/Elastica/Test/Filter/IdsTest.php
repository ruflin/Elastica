<?php
namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\Ids;
use Elastica\Filter\Type;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class IdsTest extends BaseTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        // Add documents to first type
        $docs = array();
        for ($i = 1; $i < 100; ++$i) {
            $docs[] = new Document($i, array('name' => 'ruflin'));
        }
        $index->getType('helloworld1')->addDocuments($docs);

        // Add documents to second type
        $docs = array();
        for ($i = 1; $i < 100; ++$i) {
            $docs[] = new Document($i, array('name' => 'ruflin'));
        }
        // This is a special id that will only be in the second type
        $docs[] = new Document(101, array('name' => 'ruflin'));
        $index->getType('helloworld2')->addDocuments($docs);

        $index->optimize();
        $index->refresh();

        return $index;
    }

    protected function _getTypeForTest()
    {
        return $this->_getIndexForTest()->getType('helloworld1');
    }

    /**
     * @group functional
     */
    public function testSetIdsSearchSingle()
    {
        $filter = new Ids();
        $filter->setIds('1');

        $query = Query::create($filter);
        $resultSet = $this->_getTypeForTest()->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetIdsSearchArray()
    {
        $filter = new Ids();
        $filter->setIds(array(1, 7, 13));

        $query = Query::create($filter);
        $resultSet = $this->_getTypeForTest()->search($query);

        $this->assertEquals(3, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testAddIdsSearchSingle()
    {
        $filter = new Ids();
        $filter->addId('39');

        $query = Query::create($filter);
        $resultSet = $this->_getTypeForTest()->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testAddIdsSearchSingleNotInType()
    {
        $filter = new Ids();
        $filter->addId('39');

        // Add an ID that is not in the index
        $filter->addId(104);

        $query = Query::create($filter);
        $resultSet = $this->_getTypeForTest()->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testComboIdsSearchArray()
    {
        $filter = new Ids();
        $filter->setIds(array(1, 7, 13));
        $filter->addId('39');

        $query = Query::create($filter);
        $resultSet = $this->_getTypeForTest()->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetTypeSingleSearchSingle()
    {
        $filter = new Ids();
        $filter->setIds('1');
        $filter->setType('helloworld1');

        $query = Query::create($filter);
        $resultSet = $this->_getIndexForTest()->search($query);

        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetTypeSingleSearchArray()
    {
        $filter = new Ids();
        $filter->setIds(array('1', '2'));
        $filter->setType('helloworld1');

        $query = Query::create($filter);
        $resultSet = $this->_getIndexForTest()->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetTypeSingleSearchSingleDocInOtherType()
    {
        $filter = new Ids();

        // Doc 4 is in the second type...
        $filter->setIds('101');
        $filter->setType('helloworld1');

        $query = Query::create($filter);
        $resultSet = $this->_getTypeForTest()->search($query);

        // ...therefore 0 results should be returned
        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetTypeSingleSearchArrayDocInOtherType()
    {
        $filter = new Ids();

        // Doc 4 is in the second type...
        $filter->setIds(array('1', '101'));
        $filter->setType('helloworld1');

        $query = Query::create($filter);
        $resultSet = $this->_getTypeForTest()->search($query);

        // ...therefore only 1 result should be returned
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetTypeArraySearchArray()
    {
        $filter = new Ids();
        $filter->setIds(array('1', '4'));
        $filter->setType(array('helloworld1', 'helloworld2'));

        $query = Query::create($filter);
        $resultSet = $this->_getIndexForTest()->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSetTypeArraySearchSingle()
    {
        $filter = new Ids();
        $filter->setIds('4');
        $filter->setType(array('helloworld1', 'helloworld2'));

        $query = Query::create($filter);
        $resultSet = $this->_getIndexForTest()->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group unit
     */
    public function testFilterTypeAndTypeCollision()
    {
        // This test ensures that Elastica\Type and Elastica\Filter\Type
        // do not collide when used together, which at one point
        // happened because of a use statement in Elastica\Filter\Ids
        // Test goal is to make sure a Fatal Error is not triggered
        $filterType = new Type();
        $filter = new Ids();
    }

    /**
     * @group unit
     */
    public function testAddType()
    {
        $type = $this->_getClient()->getIndex('indexname')->getType('typename');

        $filter = new Ids();

        $filter->addType('foo');
        $this->assertEquals(array('foo'), $filter->getParam('type'));

        $filter->addType($type);
        $this->assertEquals(array('foo', $type->getName()), $filter->getParam('type'));

        $returnValue = $filter->addType('bar');
        $this->assertInstanceOf('Elastica\Filter\Ids', $returnValue);
    }
}
