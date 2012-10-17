<?php

namespace Qutee\Tests;

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

    public function testCanSetName()
    {
        $this->assertNull($this->object->getName());
        $this->object->setName('testName');
        $this->assertEquals('testName', $this->object->getName());
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
}