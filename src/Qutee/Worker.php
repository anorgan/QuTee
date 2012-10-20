<?php

namespace Qutee;

use Qutee\Queue;
use Qutee\Task;

/**
 * Worker
 *
 * @author anorgan
 */
class Worker
{
    /**
     * Run every 5 minutes by default
     */
    const DEFAULT_INTERVAL = 5;

    /**
     * Run every X minutes
     *
     * @var int
     */
    protected $_interval = self::DEFAULT_INTERVAL;

    /**
     * Do only these tasks
     *
     * @var array
     */
    protected $_whitelistedTasks = array();

    /**
     * Skip doing these tasks
     *
     * @var array
     */
    protected $_blacklistedTasks = array();

    /**
     *
     * @var Queue
     */
    protected $_queue;

    /**
     *
     * @var float
     */
    protected $_startTime;

    /**
     *
     * @var float
     */
    protected $_passedTime;

    /**
     *
     * @return int
     */
    public function getInterval()
    {
        return $this->_interval;
    }

    /**
     *
     * @param int $interval
     *
     * @return Worker
     */
    public function setInterval($interval)
    {
        $this->_interval = $interval;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getWhitelistedTasks()
    {
        return $this->_whitelistedTasks;
    }

    /**
     *
     * @param string|Task $task
     * @return boolean
     */
    public function isWhitelisted($task)
    {
        if ($task instanceof Task) {
            $task = $task->getName();
        }

        return in_array($task, $this->_whitelistedTasks);
    }

    /**
     *
     * @param string $taskName
     *
     * @return Worker
     * @throws Exception
     */
    public function setWhitelistedTask($taskName)
    {
        if (!empty($this->_blacklistedTasks)) {
            throw new Exception('Can not whitelist task if blacklisted tasks exist');
        }

        $this->_whitelistedTasks[] = $taskName;

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getBlacklistedTasks()
    {
        return $this->_blacklistedTasks;
    }

    /**
     *
     * @param string|Task $task
     *
     * @return boolean
     */
    public function isBlacklisted($task)
    {
        if ($task instanceof Task) {
            $task = $task->getName();
        }

        return in_array($task, $this->_blacklistedTasks);
    }

    /**
     *
     * @param string $taskName
     *
     * @return Worker
     * @throws Exception
     */
    public function setBlacklistedTask($taskName)
    {
        if (!empty($this->_whitelistedTasks)) {
            throw new Exception('Can not blacklist task if whitelisted tasks exist');
        }

        $this->_blacklistedTasks[] = $taskName;

        return $this;
    }

    /**
     *
     * @return Queue
     */
    public function getQueue()
    {
        if (null === $this->_queue) {
            $this->_queue = new Queue;
        }

        return $this->_queue;
    }

    /**
     *
     * @param Queue $queue
     *
     * @return Worker
     */
    public function setQueue(Queue $queue)
    {
        $this->_queue = $queue;

        return $this;
    }

    /**
     * Run the worker, get tasks of the queue, run them
     */
    public function run()
    {
        // Start timing
        $this->_startTime();

        while (true) {

            if ($this->getQueue()->isEmpty()) {
                $this->_sleep();
                continue;
            }

            $tasks = $this->getQueue()->getTasks();

            foreach ($tasks as $task) {
                // Get next task
                if ($task->isReserved()) {
                    continue;
                }

                // Can we run this task? If we have whitelisted tasks, do only those,
                // If not, do any task that is not blacklisted
                if (
                    (!empty($this->_whitelistedTasks) && !$this->isWhitelisted($task)) ||
                    $this->isBlacklisted($task)
                ) {
                    continue;
                }

                // Ok, we can run the task, reserve the task
                $task->setReserved(true);

                try {
                    $this->_runTask($task);
                } catch (Exception $e) {
                    echo $e->getTraceAsString();
                }
            }

            // After clearing the batch, sleep
            $this->_sleep();

            if (defined('TESTING_MODE') && TESTING_MODE === true) {
                // SUT, no need to wait indefinitely
                break;
            }
        }
    }

    /**
     * Start timing
     */
    protected function _startTime()
    {
        $this->_startTime = microtime(true);
    }

    /**
     * Get passed time
     *
     * @param boolean $minutes - if true, returns minutes, else seconds
     *
     * @return float
     */
    protected function _getPassedTime($minutes = false)
    {
        return abs(microtime(true) - $this->_startTime);
    }

    /**
     * Sleep
     *
     * @return null
     */
    protected function _sleep()
    {
        if (defined('TESTING_MODE') && TESTING_MODE === true) {
            // Don't sleep while running tests, didn't you play Portal!?
            return;
        }

        // Time ... enough
        if ($this->_getPassedTime(true) <= $this->_interval) {
            $remainder = ($this->_interval * 60) - $this->_getPassedTime();
            sleep($remainder);
        } // Task took more than the interval, don't sleep
    }

    protected function _runTask()
    {
        // Get class of the task, run it's default method or method specified in
        // task data [method]
        
    }
}