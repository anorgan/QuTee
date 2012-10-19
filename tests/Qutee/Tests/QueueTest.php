<?php

namespace Qutee\Tests;

use Qutee\Queue;
use Qutee\Task;

/**
 * Queue
 *
 * @author anorgan
 */
class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var \Qutee\Queue
     */
    protected $object;

    public function setUp()
    {
        $this->object = new \Qutee\Queue();
    }

    public function tearDown()
    {
        $this->object->clear();
    }

    public function testClearingQueueClearsAllTasks()
    {
        $this->assertEmpty($this->object->getTasks());
        $this->object->addTask(new Task('test'));
        $this->object->clear();
        $this->assertEmpty($this->object->getTasks());
    }

    /**
     * @expectedException \Exception
     * @covers \Qutee\Queue::addTask
     */
    public function testAddingTaskUsingWrongArgumentThrowsException()
    {
        $this->object->addTask(array('bad'));
    }

    /**
     * @covers \Qutee\Queue::addTask
     */
    public function testAddingTask()
    {
        $task = $this->getMock('Qutee\Task');
        $this->object->addTask($task);
    }

    /**
     * @covers \Qutee\Queue::addTask
     * @covers \Qutee\Queue::getTasks
     */
    public function testGettingTasks()
    {
        $tasks = $this->object->getTasks();
        $this->assertEmpty($tasks);

        $task = $this->getMock('Qutee\Task');
        $this->object->addTask($task);

        $tasks = $this->object->getTasks();
        $this->assertNotEmpty($tasks);
    }

    /**
     * @covers \Qutee\Queue::getNextTask
     */
    public function testGettingATaskReturnsNextOne()
    {
        $this->object
                ->addTask(new Task('task1'))
                ->addTask(new Task('task2'));

        $task1 = $this->object->getNextTask();
        $this->assertEquals('task1', $task1->getName());

        $task2 = $this->object->getNextTask();
        $this->assertEquals('task2', $task2->getName());
    }

    /**
     * @covers \Qutee\Queue::getNextTask
     */
    public function testGettingATaskReservesThatTask()
    {
        $task1 = new Task('task1');
        $task2 = new Task('task2');
        $this->object
                ->addTask($task1)
                ->addTask($task2);

        $this->assertFalse($task1->isReserved());
        $this->assertFalse($task2->isReserved());

        $task = $this->object->getNextTask();
        $this->assertTrue($task->isReserved());
    }
}