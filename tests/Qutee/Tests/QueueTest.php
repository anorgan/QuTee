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
     * @covers \Qutee\Queue::__construct
     * @covers \Qutee\Queue::setInstance
     * @covers \Qutee\Queue::get
     */
    public function testSettingAndGettingInstance()
    {
        $instance = new Queue();
        Queue::setInstance($instance);
        $this->assertSame($instance, Queue::get());
    }

    /**
     * @covers \Qutee\Queue::setPersistor
     * @covers \Qutee\Queue::getPersistor
     */
    public function testGettingAndSettingPersistor()
    {
        $persistor = $this->object->getPersistor();
        $this->assertInstanceOf('\\Qutee\\Persistor\\Memory', $persistor);

        $this->object->setPersistor(new \Qutee\Persistor\Redis);
        $persistor = $this->object->getPersistor();
        $this->assertInstanceOf('\\Qutee\\Persistor\\Redis', $persistor);
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
     * @covers \Qutee\Queue::addTask
     */
    public function testAddingTaskUsingWrongArgumentThrowsException()
    {
        try {
            $this->object->addTask(array('bad'));
        } catch(\Exception $e) {
            $this->assertContains('must be an instance of Qutee\Task', $e->getMessage());
        }
    }

    /**
     * @covers \Qutee\Queue::addTask
     */
    public function testAddingTask()
    {
        $task = new \Qutee\Task;
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

        $task = new \Qutee\Task;
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
     * @covers \Qutee\Queue::addTask
     */
    public function testAddingUniqueTaskAddsOnlyOneTime()
    {
        $task = new \Qutee\Task;
        $task->setName('test');
        $task->setUniqueId('test');

        $this->object->addTask($task);
        $this->object->addTask($task);

        $tasks = $this->object->getTasks();
        $this->assertTrue(count($tasks) == 1);
        $outputTask = reset($tasks);
        $this->assertEquals($outputTask->getName(), $task->getName());
    }

    /**
     * @covers \Qutee\Queue::getTask
     */
    public function testGettingTaskQueriesGivenPriority()
    {
        $task1 = new Task('task1');
        $task1->setPriority(Task::PRIORITY_HIGH);

        $task2 = new Task('task2');
        $task2->setPriority(Task::PRIORITY_LOW);

        $this->object
                ->addTask($task1)
                ->addTask($task2);

        $outputTask1 = $this->object->getTask(Task::PRIORITY_LOW);
        $outputTask2 = $this->object->getTask(Task::PRIORITY_LOW);

        $this->assertEquals($outputTask1->getName(), $task2->getName());
        $this->assertNull($outputTask2);

    }

    /**
     * @covers \Qutee\Queue::getTasks
     */
    public function testGettingTasksQueriesGivenPriority()
    {
        $task1 = new Task('task1');
        $task1->setPriority(Task::PRIORITY_HIGH);

        $task2 = new Task('task2');
        $task2->setPriority(Task::PRIORITY_LOW);

        $this->object
                ->addTask($task1)
                ->addTask($task2);

        $outputTasks = $this->object->getTasks(Task::PRIORITY_LOW);

        $this->assertTrue(count($outputTasks) == 1);
        $outputTask = reset($outputTasks);
        $this->assertEquals($outputTask->getName(), $task2->getName());
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
    public function testCreatingQueueViaFactoryCreatesWithPassedThirdPartyPersistor()
    {
        $config = array('persistor' => $this->getMockClass('\Qutee\Persistor\PersistorInterface'));
        $queue = Queue::factory($config);

        $this->assertInstanceOf('\Qutee\Persistor\PersistorInterface', $queue->getPersistor());
    }

    /**
     * @covers \Qutee\Queue::factory
     * @expectedException \InvalidArgumentException
     */
    public function testCreatingQueueViaFactoryThrowsExceptionIfThirdPartyPersistorDoesNotImplementInterface()
    {
        $config = array('persistor' => '\SplQueue');
        $queue = Queue::factory($config);
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