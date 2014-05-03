<?php

namespace Qutee\Persistor;

/**
 * Beanstalk
 *
 * @author anorgan
 */
class Beanstalk implements PersistorInterface
{

    /**
     *
     * @var array
     */
    private $_options = array();

    /**
     *
     * @var \Pheanstalk\Pheanstalk
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
     * @param \Qutee\Task $task
     *
     * @return \Qutee\Persistor\Beanstalk
     */
    public function addTask(\Qutee\Task $task)
    {
        $queue = $this->_createQueueName($task->getPriority());

        // Add task to queue
        $data = serialize($task);
        $this->_getClient()->putInTube($queue, $data, $task->getPriority());

        return $this;
    }

    /**
     *
     * @param int $priority
     *
     * @return \Qutee\Task|null
     */
    public function getTask($priority = null)
    {
        if (null !== $priority) {
            // Get only the requested priority queue
            $queue  = (array) $this->_createQueueName($priority);
            $data   = $this->_getClient()->reserveFromTube($queue, 10);
        } else {
            // Get all queues
            $data   = $this->_getClient()->reserve(10);
        }

        if (empty($data)) {
            return null;
        }

        $task = unserialize($data->getData());

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
        throw new \BadMethodCallException('Method not supported for Beanstalk persistor');
    }

    /**
     * Create queue name, which is in fact a tube
     *
     * @param int $priority
     *
     * @return string
     */
    protected function _createQueueName($priority)
    {
        return 'queue/priority_'. $priority;
    }

    /**
     *
     * @return \Pheanstalk\Pheanstalk
     *
     * @throws \RuntimeException
     */
    protected function _getClient()
    {
        if (null === $this->_client) {
            
            $host = isset($this->_options['host']) ? $this->_options['host'] : '127.0.0.1';
            $port = isset($this->_options['port']) ? $this->_options['port'] : 6379;
            $connectTimeout = isset($this->_options['connect_timeout']) ? $this->_options['connect_timeout'] : null;

            $this->_client = new \Pheanstalk\Pheanstalk($host, $port, $connectTimeout);
        }

        return $this->_client;
    }

}