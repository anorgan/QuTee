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
        Queue::factory();
        $this->object = new Worker;
    }

    public function tearDown()
    {
        // I know, I know...
        Queue::get()->clear();
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
     * @covers \Qutee\Worker::addWhitelistedTask
     */
    public function testCanWhitelistTask()
    {
        $this->assertEmpty($this->object->getWhitelistedTasks());
        $this->object->addWhitelistedTask('TestTask');
        $this->assertTrue(in_array('TestTask', $this->object->getWhitelistedTasks()));
    }

    /**
     * @covers \Qutee\Worker::getQueue
     */
    public function testGettingQueueReturnsDefaultQueue()
    {
        $queue = $this->object->getQueue();
        $this->assertInstanceOf('\Qutee\Queue', $queue);
    }

}