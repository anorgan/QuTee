<?php

namespace Qutee\Persistor;

/**
 * Redis
 *
 * @author anorgan
 */
class Redis implements PersistorInterface
{

    /**
     * Default name for Redis key (queue name)
     */
    const DEFAULT_PREFIX = 'qutee:';

    /**
     *
     * @var array
     */
    private $_options = array();

    /**
     *
     * @var \Redis
     */
    private $_redis;

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
     * @return \Qutee\Persistor\Redis
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
     * @return \Qutee\Persistor\Redis
     */
    public function addTask(\Qutee\Task $task)
    {
        $queue = $this->_createQueueName($task->getPriority());

        // Remember all the queues we are using
        $this->_getRedis()->sadd('queues', $queue);

        $key = $this->_createKey($task);

        // Check if the task is unique and already exists
        if ($task->isUnique() && $this->_getRedis()->get($key) !== false) {
            return $this;
        }

        // Remember that we set the task to the queue
        $this->_getRedis()->set($key, $queue);

        // Add task to queue
        $data = serialize(array('task' => serialize($task), 'key' => $key));
        $this->_getRedis()->rpush($queue, $data);

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
            $queues = (array) $this->_createQueueName($priority);
        } else {
            // Get all queues
            $queues = (array) $this->_getRedis()->smembers('queues');
        }

        $data   = $this->_getRedis()->blpop($queues, 10);
        if (empty($data)) {
            return null;
        }

        list($queue, $taskData) = $data;
        $taskData = unserialize($taskData);

        // Forget we ever had this one
        // @todo events should fire methods: before processing, processing, afterFailedProcessing, afterSuccessfulProcessing
        $this->_getRedis()->del($taskData['key']);

        return unserialize($taskData['task']);
    }

    /**
     *
     * @param int $priority
     *
     * @return array
     */
    public function getTasks($priority = null)
    {
        $tasks  = array();

        if (null !== $priority) {
            // Get only the requested priority queue
            $queues = (array) $this->_createQueueName($priority);
        } else {
            // Get all queues
            $queues = (array) $this->_getRedis()->smembers('queues');
        }

        foreach ($queues as $queue) {
            $tasks += (array) $this->_getRedis()->lrange($queue, 0, 1000);
        }

        foreach ($tasks as $k => $data) {
            $data       = unserialize($data);
            $tasks[$k]  = unserialize($data['task']);
        }

        return $tasks;
    }


    /**
     * Clear queue
     *
     * @return boolean
     */
    public function clear()
    {
        return $this->_getRedis()->flushDB();
    }

    /**
     * Create queue name, which is in fact a priority filter
     *
     * @param int $priority
     *
     * @return string
     */
    protected function _createQueueName($priority)
    {
        return 'queue:priority_'. $priority;
    }

    /**
     *
     * @param \Qutee\Task $task
     *
     * @return string
     */
    protected function _createKey(\Qutee\Task $task)
    {
        if ($task->isUnique()) {
            $key = sprintf('task:%s:%s', $task->getName(), $task->getUniqueId());
        } else {
            $key = sprintf('task:%s:%s', $task->getName(), uniqid('', true));
        }

        return $key;
    }

    /**
     *
     * @return \Redis|\Predis\Client
     *
     * @throws \RuntimeException
     */
    protected function _getRedis()
    {
        if (null === $this->_redis) {

            $host = isset($this->_options['host']) ? $this->_options['host'] : '127.0.0.1';
            $port = isset($this->_options['port']) ? $this->_options['port'] : 6379;

            if (extension_loaded('redis')) {
                $redis = new \Redis;
                $redis->connect($host, $port);
                $redis->select(isset($this->_options['database']) ? $this->_options['database'] : 0);
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);

                if (isset($this->_options['prefix'])) {
                    // Enforce trailing semicollon
                    $prefix = rtrim($this->_options['prefix'], ':') .':';
                } else {
                    $prefix = self::DEFAULT_PREFIX;
                }

                $redis->setOption(\Redis::OPT_PREFIX, $prefix);
            } elseif (class_exists('\\Predis\\Client')) {
                $redis = new \Predis\Client(array(
                    'scheme' => 'tcp',
                    'host'   => $host,
                    'port'   => $port
                ));
            } else {
                throw new \RuntimeException('Redis persistor requires redis extension or predis client library.');
            }

            $this->_redis = $redis;
        }

        return $this->_redis;
    }

}