<?php

namespace Qutee;

use Qutee\Exception;
use Qutee\Queue;
use Qutee\Task;
use Qutee\TaskInterface;

/**
 * Worker
 *
 * @author anorgan
 */
class Worker
{
    /**
     * Run every 5 seconds by default
     */
    const DEFAULT_INTERVAL = 5;

    /**
     * Run every X seconds
     *
     * @var int
     */
    protected $_interval = self::DEFAULT_INTERVAL;

    /**
     * Do only tasks with this priority or all if priority is null
     *
     * @var int
     */
    protected $_priority;

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
    public function getPriority()
    {
        return $this->_priority;
    }

    /**
     *
     * @param int $priority
     *
     * @return Worker
     *
     * @throws \InvalidArgumentException
     */
    public function setPriority($priority)
    {
        if ($priority !== null && !is_int($priority)) {
            throw new \InvalidArgumentException('Priority must be null or an integer');
        }

        $this->_priority = $priority;

        return $this;
    }

    /**
     *
     * @return Queue
     */
    public function getQueue()
    {
        if (null === $this->_queue) {
            $this->_queue = Queue::get();
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
     *
     * @throws \Exception
     */
    public function run()
    {
        // Start timing
        $this->_startTime();

        while (true) {

            // Get next task with set priority (or any task if priority not set)
            if (null === ($task = $this->getQueue()->getTask($this->getPriority()))) {
                $this->_sleep();
                continue;
            }

            try {
                $this->_runTask($task);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }

            // After working, sleep
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
     * @return float
     */
    protected function _getPassedTime()
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
        if ($this->_getPassedTime() <= $this->_interval) {
            $remainder = ($this->_interval) - $this->_getPassedTime();
            sleep($remainder);
        } // Task took more than the interval, don't sleep
    }

    /**
     * Get class of the task, run it's default method or method specified in
     * task data [method]
     *
     * @param Task $task
     */
    protected function _runTask(Task $task)
    {
        $taskClassName = $task->getClassName();
        if (!class_exists($taskClassName)) {
            throw new \InvalidArgumentException(sprintf('Task class "%s" not found', $taskClassName));
        }

        $taskObject = new $taskClassName;

        if ($taskObject instanceof TaskInterface) {

            $taskObject->setData($task->getData());
            $taskObject->run();
        } else {
            $methodName = $task->getMethodName();
            $data = $task->getData();
            $methodChecker = new ReflectionMethod($taskClassName, $methodName);
            if ($methodChecker->isStatic()) {
                $taskClassName::$methodName($data);
            } else {
                $taskObject->$methodName($data);
            }
        }
    }

}