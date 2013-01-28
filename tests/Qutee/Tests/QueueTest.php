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
        $this->object = new Queue;
        $this->object->setPersistor(new \Qutee\Persistor\Memory);
    }

    /**
     * @covers \Qutee\Queue::getTask
     */
    public function testGettingTaskWithEmptyQueueReturnsNull()
    {
        $this->assertNull($this->object->getTask());
    }

    /**
     * @covers \Qutee\Queue::getTasks
     */
    public function testGettingTasksWithEmptyQueueReturnsEmptyArray()
    {
        $this->assertEmpty($this->object->getTasks());
    }

    /**
     * @covers \Qutee\Queue::clear
     */
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
     * @covers \Qutee\Queue::getTask
     */
    public function testGettingATaskReturnsNextOne()
    {
        $this->object
                ->addTask(new Task('task1'))
                ->addTask(new Task('task2'));

        $task1 = $this->object->getTask();
        $this->assertEquals('task1', $task1->getName());

        $task2 = $this->object->getTask();
        $this->assertEquals('task2', $task2->getName());
    }

    /**
     * @covers \Qutee\Queue::getTask
     */
    public function testGettingTaskObeyesWhitelist()
    {
        $task1 = new Task('task1');
        $task2 = new Task('task2');

        $this->object
                ->addTask($task1)
                ->addTask($task2);

        $params = array(
            'whitelist' => array('task2')
        );
        $outputTask1 = $this->object->getTask($params);
        $outputTask2 = $this->object->getTask($params);

        $this->assertSame($outputTask1, $task2);
        $this->assertNull($outputTask2);

    }

    /**
     * @covers \Qutee\Queue::getTasks
     */
    public function testGettingTasksObeyesWhitelist()
    {
        $task1 = new Task('task1');
        $task2 = new Task('task2');

        $this->object
                ->addTask($task1)
                ->addTask($task2);

        $params = array(
            'whitelist' => array('task2')
        );
        $outputTasks = $this->object->getTasks($params);

        $this->assertTrue(count($outputTasks) == 1);
        $this->assertSame(reset($outputTasks), $task2);
    }

    /**
     * @covers \Qutee\Queue::factory
     * @expectedException \InvalidArgumentException
     */
    public function testCreatingQueueViaFactoryThrowsExceptionForMissingPersistor()
    {
        Queue::factory(array('persistor' => 'NonExistingOne'));
    }

    /**
     * @covers \Qutee\Queue::factory
     */
    public function testCreatingQueueViaFactoryCreatesWithDefaultPersistor()
    {
        $queue = Queue::factory();

        $this->assertInstanceOf('\Qutee\Persistor\Memory', $queue->getPersistor());
    }

    /**
     * @covers \Qutee\Queue::factory
     */
    public function testCreatingQueueViaFactoryPassesOptionsToPersistor()
    {
        $options = array(
            'host'  => 'localhost',
            'port'  => '3333'
        );

        $queue = Queue::factory(array(
            'persistor' => 'memory',
            'options'   => $options
        ));

        $this->assertInstanceOf('\Qutee\Persistor\Memory', $queue->getPersistor());
        $this->assertSame($options, $queue->getPersistor()->getOptions());
    }

    /**
     * @covers \Qutee\Queue::get
     */
    public function testGettingQueueBehavesAsSingleton()
    {
        $queue = Queue::factory();

        $this->assertSame($queue, Queue::get());
    }

    /**
     * @covers \Qutee\Queue::get
     * @expectedException \Qutee\Exception
     */
    public function testGettingQueueWithoutPreviouslyCreatingItThrowsException()
    {
        Queue::setInstance(null);
        Queue::get();
    }
}