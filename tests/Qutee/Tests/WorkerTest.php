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
        $this->object->setInterval(0.5);
    }

    /**
     * @covers \Qutee\Worker::getInterval
     */
    public function testDefaultInterval()
    {
        $object = new Worker;
        $this->assertEquals(Worker::DEFAULT_INTERVAL, $object->getInterval());
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
     * @covers \Qutee\Worker::setPriority
     * @expectedException \InvalidArgumentException
     */
    public function testSettingInvalidPriorityThrowsAnException()
    {
        $this->object->setPriority('SuperCool priority');
    }

    /**
     * @covers \Qutee\Worker::getPriority
     * @covers \Qutee\Worker::setPriority
     */
    public function testCanSetAndGetPriority()
    {
        $this->assertNull($this->object->getPriority());
        $this->object->setPriority(Task::PRIORITY_HIGH);
        $this->assertEquals(Task::PRIORITY_HIGH, $this->object->getPriority());

        $this->object->setPriority(Task::PRIORITY_LOW);
        $this->assertEquals(Task::PRIORITY_LOW, $this->object->getPriority());

        $this->object->setPriority(null);
        $this->assertNull($this->object->getPriority());
    }
    
    /**
     * @covers \Qutee\Worker::setQueue
     * @covers \Qutee\Worker::getQueue
     */
    public function testCanSetAndGetQueue()
    {
        $queue = $this->getMock('\Qutee\Queue');
        $this->object->setQueue($queue);
        $this->assertSame($queue, $this->object->getQueue());        
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
     * @covers \Qutee\Worker::_runTask
     * @covers \Qutee\Worker::_sleep
     * @covers \Qutee\Worker::_startTime
     * @covers \Qutee\Worker::_getPassedTime
     */
    public function testCallingRunRequestsTaskForSetPriority()
    {
        $priority   = Task::PRIORITY_LOW;
        $task       = $this->getMock('\Qutee\Task', array('getClassName'));

        $taskMockClass = $this->getMockClass('TaskInterface', array('run'));

        $task->expects($this->once())->method('getClassName')->will($this->returnValue($taskMockClass));

        $queue      = $this->getMock('\Qutee\Queue', array('getTask'));

        $queue
            ->expects($this->once())
            ->method('getTask')
            ->with($priority)
            ->will($this->returnValue($task));

        $this->object->setPriority($priority);
        $this->object->run();
        
        $this->assertTrue(TRUE);
    }
    
    /**
     * @covers \Qutee\Worker::run
     * @covers \Qutee\Worker::_runTask
     * @covers \Qutee\Worker::_sleep
     * @covers \Qutee\Worker::_startTime
     * @covers \Qutee\Worker::_getPassedTime
     */
    public function testCallingRunReturnsTaskWhichRan()
    {
        $priority   = Task::PRIORITY_LOW;
        $task       = $this->getMock('\Qutee\Task', array('getClassName'));

        $taskMockClass = $this->getMockForAbstractClass('\Qutee\TaskInterface', array('run'));

        $task->expects($this->once())->method('getClassName')->will($this->returnValue(get_class($taskMockClass)));

        $queue      = $this->getMock('\Qutee\Queue', array('getTask'));

        $queue
            ->expects($this->once())
            ->method('getTask')
            ->with($priority)
            ->will($this->returnValue($task));

        $this->object->setPriority($priority);
        $ranTask = $this->object->run();

        $this->assertSame($task, $ranTask);
    }

    /**
     * @covers \Qutee\Worker::run
     * @covers \Qutee\Worker::_runTask
     * @covers \Qutee\Worker::_sleep
     * @covers \Qutee\Worker::_startTime
     * @covers \Qutee\Worker::_getPassedTime
     */
    public function testCallingRunReturnsVoidIfNoTasksAreFound()
    {
        $queue      = $this->getMock('\Qutee\Queue', array('getTask'));
        $worker     = $this->getMock('\Qutee\Worker', array('_runTask'));

        $queue
            ->expects($this->once())
            ->method('getTask')
            ->with($worker->getPriority())
            ->will($this->returnValue(null));

        $worker->expects($this->never())->method('_runTask');
        $worker->run();
    }

    /**
     * @covers \Qutee\Worker::run
     * @covers \Qutee\Worker::_sleep
     * @covers \Qutee\Worker::_startTime
     * @covers \Qutee\Worker::_getPassedTime
     */
    public function testCallingRunSleepsUpToDefinedInterval()
    {
        $priority   = Task::PRIORITY_LOW;
        $task       = $this->getMock('\Qutee\Task', array('getClassName'));

        $taskMockClass = $this->getMockClass('TaskInterface', array('run'));

        $task->expects($this->once())->method('getClassName')->will($this->returnValue($taskMockClass));

        $queue      = $this->getMock('\Qutee\Queue', array('getTask'));

        $queue
            ->expects($this->once())
            ->method('getTask')
            ->with($priority)
            ->will($this->returnValue($task));

        $this->object->setPriority($priority);
        $this->object->setInterval(0.8337);

        $start  = microtime(true);
        $this->object->run();
        $end    = microtime(true) - $start;

        /**
         * Rounding decimals
         */
        $end = number_format($end, 4);

        // Give it a margin error bigger because precision is a computer/plataform dependence
        $this->assertEquals(0.8337, $end, '', 0.01);
    }

    /**
     * @covers \Qutee\Worker::run
     * @covers \Qutee\Worker::_runTask
     * @expectedException \InvalidArgumentException
     */
    public function testCallingRunThrowsExceptionIfTaskCanNotRun()
    {
        $priority   = Task::PRIORITY_LOW;
        $task       = $this->getMock('\Qutee\Task', array('getClassName'));

        $task->expects($this->once())->method('getClassName')->will($this->returnValue('UnknownClass'));

        $queue      = $this->getMock('\Qutee\Queue', array('getTask'));

        $queue
            ->expects($this->once())
            ->method('getTask')
            ->with($priority)
            ->will($this->returnValue($task));

        $this->object->setPriority($priority);
        
        $this->object->run();
    }
}