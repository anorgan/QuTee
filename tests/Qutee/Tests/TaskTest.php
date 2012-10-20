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
        $this->object->setName('Task with spaces - dashes _ 4343');
        $this->assertEquals('Task with spaces - dashes _ 4343', $this->object->getName());
    }

    /**
     * @expectedException \Exception
     */
    public function testSettingDataThrowsInvalidArgumentException()
    {
        $this->object->setData('string');
    }

    /**
     * @expectedException \Exception
     */
    public function testSettingWrongNameThrowsInvalidArgumentException()
    {
        $this->object->setName('Test- !a');
    }

    /**
     * @dataProvider dataForClassNameTest
     * @covers \Qutee\Task::getClassName
     * @param string $name
     * @param string $expectedClassName
     */
    public function testGettingClassName($name, $expectedClassName)
    {
        $this->object->setName($name);
        $this->assertEquals($expectedClassName, $this->object->getClassName());
    }

    /**
     *
     * @return array
     */
    public function dataForClassNameTest()
    {
        $data = array();

        $data['Task'] = array(
            'name'      => 'Task',
            'expected'  => 'Task'
        );

        $data['lowercased'] = array(
            'name'      => 'lowercased',
            'expected'  => 'Lowercased'
        );

        $data['ALLCAPS'] = array(
            'name'      => 'ALLCAPS',
            'expected'  => 'Allcaps'
        );

        $data['some task - with_descriptive _ name'] = array(
            'name'      => 'some task - with_descriptive _ name',
            'expected'  => 'SomeTaskWithDescriptiveName'
        );

        $data['Fqcn\\Namespace\\Task'] = array(
            'name'      => 'Fqcn\\Namespace\\Task',
            'expected'  => 'Fqcn\\Namespace\\Task'
        );

        $data['Fqcn/ForwardNamespace/Task'] = array(
            'name'      => 'Fqcn/ForwardNamespace/Task',
            'expected'  => 'Fqcn\ForwardNamespace\Task'
        );

        return $data;
    }

    /**
     * @covers \Qutee\Task::getMethodName
     */
    public function testGettingMethodNameReturnsDefaultMethod()
    {
        $this->assertEquals('run', $this->object->getMethodName());
    }

    /**
     * @covers \Qutee\Task::getMethodName
     */
    public function testGettingMethodNameDefinedInData()
    {
        $this->object->setData(array('method' => 'methodName'));
        $this->assertEquals('methodName', $this->object->getMethodName());
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
     * @expectedException \Qutee\Exception
     */
    public function testExceptionThrownIfRequestingClassNameWithoutName()
    {
        $this->object->getClassName();
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