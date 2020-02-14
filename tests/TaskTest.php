<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Task;

/**
 * @internal
 */
class TaskTest extends Base
{
    /**
     * @group functional
     */
    public function testGetData(): void
    {
        $task = $this->_createTask();
        $data = $task->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    /**
     * @group functional
     */
    public function testGetId(): void
    {
        $task = $this->_createTask();
        $data = $task->getData();

        $this->assertNotEmpty($task->getId());
        $this->assertEquals($task->getId(), \sprintf('%s:%s', $data['task']['node'], $data['task']['id']));
    }

    /**
     * @group functional
     */
    public function testIsComplete(): void
    {
        $task = $this->_createTask();

        for ($i = 0; $i < 5; ++$i) {
            if ($task->isCompleted()) {
                break;
            }
            \sleep(1); // wait for task to complete
            $task->refresh();
        }

        $this->assertTrue($task->isCompleted());
    }

    /**
     * @group functional
     */
    public function testRefreshWithOptionsContainingOnWaitForResponseTrue(): void
    {
        $task = $this->_createTask();
        $task->refresh([Task::WAIT_FOR_COMPLETION => Task::WAIT_FOR_COMPLETION_TRUE]);
        $this->assertTrue($task->isCompleted());
    }

    /**
     * @group unit
     */
    public function testCancelThrowsExceptionWithEmptyTaskId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No task id given');

        $task = new Task($this->_getClient(), '');
        $task->cancel();
    }

    /**
     * @group functional
     */
    public function testCancelDoesntCancelCompletedTasks(): void
    {
        $task = $this->_createTask();
        $task->refresh([Task::WAIT_FOR_COMPLETION => Task::WAIT_FOR_COMPLETION_TRUE]);
        $response = $task->cancel();

        $task->refresh();
        $this->assertArrayNothasKey('canceled', $task->getData());
    }

    /**
     * Creates a task by issuing delete-by-query on an index.
     *
     * @return Task Task object
     */
    protected function _createTask(): Task
    {
        $index = $this->_createIndexWithDocument();

        // Create delete-by-query task
        $response = $index->deleteByQuery('ruflin', ['wait_for_completion' => 'false']);
        $id = $response->getData()['task'];

        $this->assertNotEmpty($id, 'Failed to create task');

        return new Task($this->_getClient(), $id);
    }

    protected function _createIndexWithDocument(): \Elastica\Index
    {
        $index = $this->_createIndex();
        $index->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $index->refresh();

        return $index;
    }
}
