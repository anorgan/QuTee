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
     * Default name of the method to run the task
     */
    const DEFAULT_METHOD_NAME = 'run';

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
        if (null !== $name) {
            $this->setName($name);
        }

        if (null !== $data) {
            $this->setData($data);
        }
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
        // validate name
        if (!preg_match('/^[a-zA-Z0-9\/\\\ _-]+$/', $name)) {
            throw new \InvalidArgumentException('Name can be only alphanumerics, spaces, underscores and dashes');
        }

        $this->_name = $name;

        return $this;
    }

    /**
     *
     * @return string
     * @throws Exception
     */
    public function getClassName()
    {
        if ($this->_name === null) {
            throw new Exception('Name not set, can not create class name');
        }

        if (strpos($this->_name, '\\') !== false) {
            // FQCN?
            $className = $this->_name;
        } elseif (strpos($this->_name, '/') !== false) {
            // Forward slash FQCN?
            $className = str_replace('/', '\\', $this->_name);
        } else {
            $className = str_replace(array('-','_'), ' ', strtolower($this->_name));
            $className = str_replace(' ', '', ucwords($className));
        }

        return $className;
    }

    /**
     *
     * @return string
     */
    public function getMethodName()
    {
        $data = $this->getData();
        if (isset($data['method']) && strlen($data['method'])) {
            return $data['method'];
        } else {
            return self::DEFAULT_METHOD_NAME;
        }
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