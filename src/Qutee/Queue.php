<?php

namespace Qutee;

use Qutee\Task;

/**
 * Queue
 *
 * @author anorgan
 */
class Queue
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
    protected $_tasks;

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
     * @return \Qutee\Queue
     */
    public function setName($name)
    {
        $this->_name = $name;

        return $this;
    }

    public function addTask(Task $task)
    {
        $this->_tasks[$task->getName()] = $task;

        return $this;
    }

    public function getTasks()
    {
        return $this->_tasks;
    }
}