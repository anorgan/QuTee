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
     * @covers \Qutee\Queue::isEmpty
     */
    public function testTestingIfQueueHasTasks()
    {
        $this->assertTrue($this->object->isEmpty());

        $task = $this->getMock('Qutee\Task', array('isReserved'));
        $task->expects($this->once())
             ->method('isReserved')
             ->will($this->returnValue(false));

        $this->object->addTask($task);

        $this->assertFalse($this->object->isEmpty());

        $task = $this->getMock('Qutee\Task', array('isReserved'));
        $task->expects($this->once())
             ->method('isReserved')
             ->will($this->returnValue(true));

        $this->object->clear()->addTask($task);
        $this->assertTrue($this->object->isEmpty());
    }

    /**
     * @covers \Qutee\Queue::getNextTask
     * @depends testTestingIfQueueHasTasks
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
     * @depends testTestingIfQueueHasTasks
     */
    public function testGettingNextTaskDoesNotReturnReservedTask()
    {
        $task1 = $this->getMock('Qutee\Task', array('isReserved', 'getName'));
        $task1->expects($this->any())
             ->method('isReserved')
             ->will($this->returnValue(false));
        $task1->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('task1'));

        $task2 = $this->getMock('Qutee\Task', array('isReserved', 'getName'));
        $task2->expects($this->any())
             ->method('isReserved')
             ->will($this->returnValue(true));
        $task2->expects($this->never())
             ->method('getName')
             ->will($this->returnValue('task2'));

        $task3 = $this->getMock('Qutee\Task', array('isReserved', 'getName'));
        $task3->expects($this->any())
             ->method('isReserved')
             ->will($this->returnValue(false));
        $task3->expects($this->once())
             ->method('getName')
             ->will($this->returnValue('task3'));

        $this->object->addTask($task1);
        $this->object->addTask($task2);
        $this->object->addTask($task3);

        $task = $this->object->getNextTask();
        $this->assertEquals('task1', $task->getName());

        // Task 2 is reserved, getName is never called, for us, it does not exist

        $task = $this->object->getNextTask();
        $this->assertEquals('task3', $task->getName());

    }

    /**
     * @covers \Qutee\Queue::getNextTask
     * @depends testTestingIfQueueHasTasks
     */
    public function testGettingTasksReturnsThemInRoundRobbinOrder()
    {
        $this->object
                ->addTask(new Task('task1'))
                ->addTask(new Task('task2'));

        $task1 = $this->object->getNextTask();
        $this->assertEquals('task1', $task1->getName());

        $task2 = $this->object->getNextTask();
        $this->assertEquals('task2', $task2->getName());

        $task1 = $this->object->getNextTask();
        $this->assertEquals('task1', $task1->getName());

        $task2 = $this->object->getNextTask();
        $this->assertEquals('task2', $task2->getName());
    }
}