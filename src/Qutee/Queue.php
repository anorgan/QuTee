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
     * @return boolean
     */
    public function isEmpty()
    {
        if (empty(self::$_tasks)) {
            return true;
        }

        // Some tasks are reserved, make as they don't exist
        $nrOfTasks = count(self::$_tasks);

        // Don't mess with the order
        $tasks  = self::$_tasks;

        $cnt    = 0;
        foreach ($tasks as $task) {
            if (!$task->isReserved()) {
                continue;
            }
            $cnt++;
        }
        unset($tasks);

        if ($cnt !== $nrOfTasks) {
            // We have a mismatch of number of tasks and reserved tasks, queue
            // is not empty
            return false;
        }

        return true;
    }

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
        self::$_tasks[] = $task;

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
        if ($this->isEmpty()) {
            return;
        }

        $task = null;
        while ($task === null || $task->isReserved()) {
            if (false === ($task = current(self::$_tasks))) {
                $task = reset(self::$_tasks);
            }
            next(self::$_tasks);
        }

        return $task;
    }
}