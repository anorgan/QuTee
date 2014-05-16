<?php

namespace Qutee\Persistor;

use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use Qutee\Task;

/**
 * Beanstalk
 *
 * @author anorgan
 */
class Beanstalk implements PersistorInterface
{

    const TUBE_NAME = 'qutee';

    /**
     *
     * @var array
     */
    private $_options = array();

    /**
     *
     * @var Pheanstalk
     */
    private $_client;

    /**
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     *
     * @param array $options
     *
     * @return \Qutee\Persistor\Beanstalk
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;

        return $this;
    }

    /**
     * Add task to queue
     *
     * @param Task $task
     *
     * @return \Qutee\Persistor\Beanstalk
     */
    public function addTask(Task $task)
    {
        // Add task to queue
        $data = serialize($task);
        $this->_getClient()->putInTube(self::TUBE_NAME, $data, $this->_convertPriority($task->getPriority()));

        return $this;
    }

    /**
     *
     * @param int $priority
     *
     * @return Task|null
     */
    public function getTask($priority = null)
    {
        $data   = $this->_getClient()->reserveFromTube(self::TUBE_NAME, 10);

        if (empty($data)) {
            return null;
        }

        /* @var $task Task */
        $task = unserialize($data->getData());

        if (null !== $priority && $task->getPriority() !== $priority) {
            // These is not the task you are looking for :)
            $this->_getClient()->release($data);

            return null;
        }

        $this->_getClient()->delete($data);

        return $task;
    }

    /**
     *
     * @param int $priority
     *
     * @return array
     */
    public function getTasks($priority = null)
    {
        throw new \BadMethodCallException('Method not supported for Beanstalk persistor');
    }

    /**
     * Clear queue
     *
     * @return boolean
     */
    public function clear()
    {
        while ($job = $this->_getClient()->peekReady(self::TUBE_NAME)) {
            $this->_getClient()->delete($job);
        }
    }

    /**
     *
     * @return Pheanstalk
     *
     * @throws \RuntimeException
     */
    protected function _getClient()
    {
        if (null === $this->_client) {
            
            $host = isset($this->_options['host']) ? $this->_options['host'] : '127.0.0.1';
            $port = isset($this->_options['port']) ? $this->_options['port'] : 11300;
            $connectTimeout = isset($this->_options['connect_timeout']) ? $this->_options['connect_timeout'] : null;

            $this->_client = new Pheanstalk($host, $port, $connectTimeout);
        }

        return $this->_client;
    }

    /**
     * QuTee - Higher the number, higher the priority; 
     * Beanstalkd - Lower the number, higher the priority
     * 
     * @param int $priority
     * 
     * @return int
     */    
    protected function _convertPriority($priority)
    {
        if (!is_numeric($priority)) {
            throw new \Exeption('Error while converting priority, agrument is '
                    . 'not a number, '. gettype($priority) .' sent');
        }

        switch ($priority) {
            case Task::PRIORITY_LOW:
                // Least urgent Beanstalkd priority
                return 4294967295;
                
            case Task::PRIORITY_NORMAL:
                return PheanstalkInterface::DEFAULT_PRIORITY;

            case Task::PRIORITY_HIGH:
                return 1024;

            default:
                return 512;
        }
    }
}