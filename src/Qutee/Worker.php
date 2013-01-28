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
     * @param array $tasks
     *
     * @return Worker
     */
    public function setWhitelistedTasks(array $tasks)
    {
        $this->_whitelistedTasks = $tasks;

        return $this;
    }

    /**
     *
     * @param string $taskName
     *
     * @return Worker
     */
    public function addWhitelistedTask($taskName)
    {
        $this->_whitelistedTasks[] = $taskName;

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
     */
    public function run()
    {
        // Start timing
        $this->_startTime();

        $params = array(
            'whitelist' => $this->getWhitelistedTasks(),
        );

        while (true) {

            if (null === ($task = $this->getQueue()->getTask($params))) {
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

    /**
     * Get class of the task, run it's default method or method specified in
     * task data [method]
     *
     * @param Task $task
     */
    protected function _runTask(Task $task)
    {
        $taskClassName  = $task->getClassName();
        if (!class_exists($taskClassName)) {
            throw new \InvalidArgumentException(sprintf('Task class "%s" not found', $taskClassName));
        }

        $taskObject     = new $taskClassName;

        if ($taskObject instanceof TaskInterface) {

            $taskObject->setData($task->getData());
            $taskObject->run();

        } else {

            $methodName     = $task->getMethodName();
            $taskObject->$methodName($task->getData());

        }
    }

}