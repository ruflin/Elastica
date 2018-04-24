<?php
namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Response;
use Elastica\Status;
use Elastica\Task;
use Elastica\Test\Base;
use Elastica\Type;

class TaskTest extends Base
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
    public function testGet()
    {
        $index = $this->_createIndex();
        $type1 = new Type($index, 'test');
        $type1->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $type1->addDocument(new Document(2, ['name' => 'p10']));
        $index->refresh();

        // Delete first document
        $response = $index->deleteByQuery('ruflin', ['wait_for_completion' => 'false']);
        $id = $response->getData()['task'];
        $task = $this->sut->get($id);
        $this->assertTrue(is_array($task));
        $this->assertNotEmpty($task);
        $this->assertEquals($id, sprintf("%s:%s", $task['task']['node'], $task['task']['id']));
    }

    /**
     * @group functional
     */
    public function testGetList()
    {
        $indexName = 'test';
        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], true);
        $index = $this->_createIndex();
        $this->tasks = $this->sut->getTasks();
        $tasks = array_column($this->tasks['nodes'], 'tasks')[0];
        $this->assertTrue(!empty($tasks));
    }

    /**
     * @group functional
     */
    public function test()
    {

    }
}
