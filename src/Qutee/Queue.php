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
     * @var array
     */
    protected static $_tasks = array();

    /**
     *
     * @return \Qutee\Queue
     */
    public function clear()
    {
        self::$_tasks = array();

        return $this;
    }

    /**
     *
     * @param \Qutee\Task $task
     *
     * @return \Qutee\Queue
     */
    public function addTask(Task $task)
    {
        self::$_tasks[$task->getName()] = $task;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getTasks()
    {
        return self::$_tasks;
    }

    /**
     *
     * @return \Qutee\Task
     */
    public function getNextTask()
    {
        $task = array_shift(self::$_tasks);
        $task->setReserved(true);

        return $task;
    }
}