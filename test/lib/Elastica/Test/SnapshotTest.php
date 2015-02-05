<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Index;
use Elastica\Snapshot;

class SnapshotTest extends Base
{
    /**
     * @var Snapshot
     */
    protected $_snapshot;

    /**
     * @var Index
     */
    protected $_index;

    /**
     * @var Document[]
     */
    protected $_docs;

    protected function setUp()
    {
        parent::setUp();
        $this->_snapshot = new Snapshot($this->_getClient());

        $this->_index = $this->_createIndex();
        $this->_docs = array(
            new Document("1", array("city" => "San Diego")),
            new Document("2", array("city" => "San Luis Obispo")),
            new Document("3", array("city" => "San Francisco")),
        );
        $this->_index->getType("test")->addDocuments($this->_docs);
        $this->_index->refresh();
    }

    public function testRegisterRepository()
    {
        $name = "test_register";
        $location = "/tmp/test_register";

        $response = $this->_snapshot->registerRepository($name, "fs", array("location" => $location));
        $this->assertTrue($response->isOk());

        $response = $this->_snapshot->getRepository($name);
        $this->assertEquals($location, $response["settings"]["location"]);

        // attempt to retrieve a repository which does not exist
        $this->setExpectedException('Elastica\Exception\NotFoundException');
        $this->_snapshot->getRepository("foobar");
    }

    public function testSnapshotAndRestore()
    {
        $repositoryName = "test_repository";
        $location = "/tmp/{$repositoryName}";

        // register the repository
        $response = $this->_snapshot->registerRepository($repositoryName, "fs", array("location" => $location));
        $this->assertTrue($response->isOk());

        // create a snapshot of our test index
        $snapshotName = "test_snapshot_1";
        $response = $this->_snapshot->createSnapshot($repositoryName, $snapshotName, array("indices" => $this->_index->getName()), true);

        // ensure that the snapshot was created properly
        $this->assertTrue($response->isOk());
        $this->assertArrayHasKey("snapshot", $response->getData());
        $data = $response->getData();
        $this->assertContains($this->_index->getName(), $data["snapshot"]["indices"]);
        $this->assertEquals(1, sizeof($data["snapshot"]["indices"])); // only the specified index should be present
        $this->assertEquals($snapshotName, $data["snapshot"]["snapshot"]);

        // retrieve data regarding the snapshot
        $response = $this->_snapshot->getSnapshot($repositoryName, $snapshotName);
        $this->assertContains($this->_index->getName(), $response["indices"]);

        // delete our test index
        $this->_index->delete();

        // restore the index from our snapshot
        $response = $this->_snapshot->restoreSnapshot($repositoryName, $snapshotName, array(), true);
        $this->assertTrue($response->isOk());

        $this->_index->refresh();
        $this->_index->optimize();

        // ensure that the index has been restored
        $count = $this->_index->getType("test")->count();
        $this->assertEquals(sizeof($this->_docs), $count);

        // delete the snapshot
        $response = $this->_snapshot->deleteSnapshot($repositoryName, $snapshotName);
        $this->assertTrue($response->isOk());

        // ensure that the snapshot has been deleted
        $this->setExpectedException('Elastica\Exception\NotFoundException');
        $this->_snapshot->getSnapshot($repositoryName, $snapshotName);
    }
}
