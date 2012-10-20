<?php

namespace Qutee\Tests;

use Qutee\Queue;
use Qutee\Task;
use Qutee\Worker;

/**
 * WorkerTest
 *
 * @author anorgan
 */
class WorkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var Worker
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Worker;
    }

    public function tearDown()
    {
        // I know, I know...
        $queue = new Queue;
        $queue->clear();
    }

    /**
     * @covers \Qutee\Worker::getInterval
     */
    public function testDefaultInterval()
    {
        $this->assertEquals(Worker::DEFAULT_INTERVAL, $this->object->getInterval());
    }

    /**
     * @covers \Qutee\Worker::getInterval
     * @covers \Qutee\Worker::setInterval
     */
    public function testCanSetAndGetInterval()
    {
        $this->object->setInterval(3600);
        $this->assertEquals(3600, $this->object->getInterval());
    }

    /**
     * @covers \Qutee\Worker::getWhitelistedTasks
     * @covers \Qutee\Worker::setWhitelistedTask
     * @covers \Qutee\Worker::isWhitelisted
     */
    public function testCanWhitelistTask()
    {
        $this->assertEmpty($this->object->getWhitelistedTasks());
        $this->assertFalse($this->object->isWhitelisted('TestTask'));
        $this->object->setWhitelistedTask('TestTask');
        $this->assertTrue($this->object->isWhitelisted('TestTask'));
    }

    /**
     * @covers \Qutee\Worker::getBlacklistedTask
     * @covers \Qutee\Worker::setBlacklistedTask
     * @covers \Qutee\Worker::isBlacklisted
     */
    public function testCanBlacklistTask()
    {
        $this->assertEmpty($this->object->getBlacklistedTasks());
        $this->assertFalse($this->object->isBlacklisted('TestTask'));
        $this->object->setBlacklistedTask('TestTask');
        $this->assertTrue($this->object->isBlacklisted('TestTask'));
    }

    /**
     * @expectedException \Qutee\Exception
     * @covers \Qutee\Worker::setWhitelistedTask
     * @covers \Qutee\Worker::getWhitelistedTask
     * @depends testCanWhitelistTask
     * @depends testCanBlacklistTask
     */
    public function testExceptionThrownIfWhitelistingAndBlacklistingAtTheSameTime()
    {
        $this->object->setWhitelistedTask('test');
        $this->object->setBlacklistedTask('test');
    }

    /**
     * @covers \Qutee\Worker::getQueue
     */
    public function testGettingQueueReturnsDefaultQueue()
    {
        $queue = $this->object->getQueue();
        $this->assertInstanceOf('\Qutee\Queue', $queue);
    }

    /**
     * @covers \Qutee\Worker::run
     * @depends testCanBlacklistTask
     */
    public function testCanNotRunTaskIfBlacklisted()
    {
        $task   = Task::create('task', array());
        $worker = $this->getMock('Qutee\Worker', array('_runTask'));
        $worker->expects($this->never())->method('_runTask');

        $worker->setInterval(0);
        $worker->setBlacklistedTask('task');
        $worker->run();
    }

    /**
     * @covers \Qutee\Worker::run
     * @depends testCanWhitelistTask
     */
    public function testCanNotRunTaskIfNotWhitelisted()
    {
        $task   = Task::create('task', array());
        $worker = $this->getMock('Qutee\Worker', array('_runTask'));
        $worker->expects($this->never())->method('_runTask');

        $worker->setInterval(0);
        $worker->setWhitelistedTask('some_other_task');
        $worker->run();
    }

    /**
     * @covers \Qutee\Worker::run
     * @depends testCanWhitelistTask
     */
    public function testRunsTaskIfWhitelisted()
    {
        $task   = Task::create('task', array());
        $worker = $this->getMock('Qutee\Worker', array('_runTask'));
        $worker->expects($this->once())->method('_runTask');

        $worker->setInterval(0);
        $worker->setWhitelistedTask('task');
        $worker->run();
    }

    /**
     * @covers \Qutee\Worker::run
     * @depends testCanBlacklistTask
     */
    public function testRunsTaskIfNotBlacklisted()
    {
        $task   = Task::create('test', array());
        $worker = $this->getMock('Qutee\Worker', array('_runTask'));
        $worker->expects($this->once())->method('_runTask');

        $worker->setInterval(0);
        $worker->setBlacklistedTask('some_other_task');
        $worker->run();
    }
}