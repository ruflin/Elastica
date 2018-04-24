<?php
namespace Elastica\Test;

use Elastica\Exception\ResponseException;
use Elastica\Response;
use Elastica\Status;
use Elastica\Task;
use Elastica\Test\Base as BaseTest;

class TaskTest extends BaseTest
{
    /**
     * @var Task
     */
    protected $sut;

    protected $tasks;

    public function setUp()
    {
        parent::setUp();
        $this->sut = new Task($this->_getClient());
    }
    /**
     * @group functional
     */
    public function testGetList()
    {
        file_put_contents('log.txt', var_export($this->tasks, false));
        $indexName = 'test';
        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], true);
        $index = $this->_createIndex();
        $index->refresh();
        $index->forcemerge();
        $this->tasks = $this->sut->getTasks();
        $this->assertTrue(is_array($this->tasks));
    }

}
