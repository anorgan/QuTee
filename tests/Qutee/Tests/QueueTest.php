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
    protected $_object;

    public function setUp()
    {
        $this->_object = new \Qutee\Queue();
    }

    public function testCanSetName()
    {
        $this->assertNull($this->_object->getName());
        $this->_object->setName('testName');
        $this->assertEquals('testName', $this->_object->getName());
    }

}