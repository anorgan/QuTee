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
     * @covers \Qutee\Task::setData
     */
    public function testSettingDataThrowsInvalidArgumentException()
    {
        try {
            $this->object->setData('string');
        } catch(\Exception $e) {
            $this->assertContains('array, string given', $e->getMessage());
        }
    }

    /**
     * @expectedException \Exception
     * @covers \Qutee\Task::setName
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
            'expected'  => 'Fqcn\\ForwardNamespace\\Task'
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
     * @covers \Qutee\Task::setMethodName
     */
    public function testCanSetAndGetMethodName()
    {
        $this->object->setMethodName('methodName');
        $this->assertEquals('methodName', $this->object->getMethodName());
    }

    /**
     * @expectedException \Exception
     * @dataProvider dataForMetodNameException
     * @covers \Qutee\Task::setMethodName
     */
    public function testExceptionThrownIfSettingIncorrectMethodName($methodName)
    {
        $this->object->setMethodName($methodName);
    }

    /**
     *
     * @return array
     */
    public function dataForMetodNameException()
    {
        $data = array();
        $data[''] = array(
            'methodName' => '',
        );
        $data['UcCamelCase'] = array(
            'methodName' => 'UcCamelCase',
        );
        $data['điberiš'] = array(
            'methodName' => 'điberiš',
        );
        $data['some whitespace'] = array(
            'methodName' => 'some whitespace',
        );
        return $data;
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
     * @covers \Qutee\Task::getPriority
     * @covers \Qutee\Task::setPriority
     */
    public function testCanSetAndGetPriority()
    {
        $this->object->setPriority(Task::PRIORITY_LOW);
        $this->assertEquals(1, $this->object->getPriority());
    }

    /**
     * @covers \Qutee\Task::getUniqueId
     * @covers \Qutee\Task::setUniqueId
     */
    public function testCanSetAndGetUniqueId()
    {
        $this->object->setName('Some Name');
        $this->object->setUniqueId('some_unique_id');

        $uniqueId = md5('Some Namesome_unique_id');

        $this->assertEquals($uniqueId, $this->object->getUniqueId());
    }

    /**
     * @covers \Qutee\Task::getUniqueId
     */
    public function testGettingUniqueIdReturnsFalseIfNotSet()
    {
        $this->assertFalse($this->object->getUniqueId());
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
        $task = new Task('TaskName', $data, Task::PRIORITY_HIGH, 'unique_identifier', 'methodName');
        $this->assertEquals('TaskName', $task->getName());
        $this->assertEquals($data, $task->getData());
        $this->assertEquals('methodName', $task->getMethodName());
        $this->assertEquals(3, $task->getPriority());
        $this->assertEquals(md5('TaskNameunique_identifier'), $task->getUniqueId());
    }

    /**
     * @covers \Qutee\Task::setName
     * @covers \Qutee\Task::setMethodName
     */
    public function testSettingMethodNameViaName()
    {
        $this->assertEquals('run', $this->object->getMethodName());

        $this->object->setName('TaskName::methodName');
        $this->assertEquals('TaskName',     $this->object->getName());
        $this->assertEquals('methodName',   $this->object->getMethodName());
    }

    /**
     * @expectedException \Qutee\Exception
     * @covers \Qutee\Task::getClassName
     */
    public function testExceptionThrownIfRequestingClassNameWithoutName()
    {
        $this->object->getClassName();
    }

    /**
     * @covers \Qutee\Task::isUnique
     */
    public function testShouldPassIfIsUniqueReturnsTrueIfTaskIsUnique()
    {
        $this->assertFalse($this->object->isUnique());
        $this->object->setUniqueId('SomeId');
        $this->assertTrue($this->object->isUnique());
    }

    /**
     * @covers \Qutee\Task::create
     * @todo This is integrational test, not unit test, remedy!
     */
    public function testCreatingTaskViaStaticMethodAddsTaskToQueue()
    {
        $queue = Queue::factory();
        $this->assertEmpty($queue->getTasks());

        $data = array(
            'test' => 'data'
        );
        Task::create('TestTask', $data, Task::PRIORITY_LOW, 'unq', 'methodName');

        $task = $queue->getTask();
        $this->assertInstanceOf('\Qutee\Task', $task);
        $this->assertEquals('TestTask', $task->getName());
        $this->assertEquals(Task::PRIORITY_LOW, $task->getPriority());
        $this->assertEquals(md5('TestTaskunq'), $task->getUniqueId());
        $this->assertEquals('methodName', $task->getMethodName());
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
        $this->object->setMethodName('methodName');
        $this->object->setPriority(Task::PRIORITY_HIGH);
        $this->object->setUniqueId('SomeUniqueId');

        $serialized     = serialize($this->object);
        $unserialized   = unserialize($serialized);

        $this->assertInstanceOf('\Qutee\Task', $unserialized);
        $this->assertEquals('TestTask', $unserialized->getName());
        $this->assertEquals('methodName', $unserialized->getMethodName());
        $this->assertSame($data, $unserialized->getData());
        $this->assertEquals(3, $unserialized->getPriority());
        $this->assertTrue($unserialized->isUnique());
        $this->assertEquals(md5('TestTaskSomeUniqueId'), $unserialized->getUniqueId());
    }
}