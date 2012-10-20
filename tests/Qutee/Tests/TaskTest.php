<?php

namespace Qutee\Tests;

use Qutee\Queue;
use Qutee\Task;

/**
 * TaskTest
 *
 * @author anorgan
 */
class TaskTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Task
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Task;
    }

    /**
     * @covers \Qutee\Task::getName
     * @covers \Qutee\Task::setName
     */
    public function testCanSetAndGetName()
    {
        $this->assertEmpty($this->object->getName());
        $this->object->setName('Task');
        $this->assertEquals('Task', $this->object->getName());
    }

    /**
     * @expectedException \Exception
     */
    public function testSettingDataThrowsInvalidArgumentException()
    {
        $this->object->setData('string');
    }

    /**
     * @covers \Qutee\Task::getData
     * @covers \Qutee\Task::setData
     */
    public function testCanSetAndGetData()
    {
        $this->assertEmpty($this->object->getData());
        $data = array(
            'test' => 'data'
        );
        $this->object->setData($data);
        $this->assertSame($data, $this->object->getData());
    }

    /**
     * @covers \Qutee\Task::__construct
     * @depends testCanSetAndGetName
     * @depends testCanSetAndGetData
     */
    public function testCanSettAttributesUpponInstantiation()
    {
        $data = array(
            'test' => 'data'
        );
        $task = new Task('TaskName', $data);
        $this->assertEquals('TaskName', $task->getName());
        $this->assertEquals($data, $task->getData());
    }

    /**
     * @covers \Qutee\Task::isReserved
     */
    public function testTaskIsNotReservedOnCreation()
    {
        $this->assertFalse($this->object->isReserved());
    }

    /**
     * @covers \Qutee\Task::isReserved
     * @covers \Qutee\Task::setReserved
     */
    public function testCanReserveAndUnreserveTask()
    {
        $this->object->setReserved(true);
        $this->assertTrue($this->object->isReserved());

        $this->object->setReserved(false);
        $this->assertFalse($this->object->isReserved());
    }

    /**
     * @covers \Qutee\Task::create
     */
    public function testCreatingTaskViaStaticMethodAddsTaskToQueue()
    {
        $queue = new Queue;
        $queue->clear();
        $this->assertTrue($queue->isEmpty());

        $data = array(
            'test' => 'data'
        );
        Task::create('TestTask', $data);

        $task = $queue->getNextTask();
        $this->assertInstanceOf('\Qutee\Task', $task);
        $this->assertEquals('TestTask', $task->getName());
        $this->assertSame($data, $task->getData());
    }

    /**
     * @covers \Qutee\Task::__sleep
     */
    public function testSerializingAndUnserializingTask()
    {
        $data = array(
            'test' => 'data'
        );
        $this->object->setName('TestTask');
        $this->object->setData($data);
        $this->object->setReserved(true);

        $serialized     = serialize($this->object);
        $unserialized   = unserialize($serialized);

        $this->assertInstanceOf('\Qutee\Task', $unserialized);
        $this->assertEquals('TestTask', $unserialized->getName());
        $this->assertSame($data, $unserialized->getData());
        $this->assertFalse($unserialized->isReserved());
    }
}