<?php

namespace Qutee;

use Qutee\Queue;

/**
 * Task
 *
 * @author anorgan
 */
class Task
{
    /**
     *
     * @var string
     */
    protected $_name;

    /**
     *
     * @var array
     */
    protected $_data;

    /**
     *
     * @var boolean
     */
    protected $_is_reserved = false;

    /**
     *
     * @param string $name
     *
     * @param array $data
     */
    public function __construct($name = null, $data = array())
    {
        $this->_name = $name;
        $this->_data = $data;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * @param string $name
     *
     * @return Task
     */
    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     *
     * @param array $data
     *
     * @return Task
     */
    public function setData(array $data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isReserved()
    {
        return $this->_is_reserved;
    }

    /**
     *
     * @param boolean $state
     *
     * @return Task
     */
    public function setReserved($state)
    {
        $this->_is_reserved = $state;

        return $this;
    }

    /**
     * Unserialized task should not be reserved
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_name', '_data');
    }

    /**
     *
     * @param string $name
     * @param array $data
     *
     * @return Task
     */
    public static function create($name, $data = null)
    {
        $queue  = new Queue;
        $task   = new self($name, $data);
        $queue->addTask($task);

        return $task;
    }
}