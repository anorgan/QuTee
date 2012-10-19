<?php

namespace Qutee;

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
     * @return \Qutee\Task
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
     * @return \Qutee\Task
     */
    public function setData($data)
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
     * @return \Qutee\Task
     */
    public function setReserved($state)
    {
        $this->_is_reserved = $state;

        return $this;
    }

    /**
     *
     * @param string $name
     * @param array $data
     */
    public static function create($name, $data = null)
    {
        \Qutee\Queue::push(new self($name, $data));
    }
}