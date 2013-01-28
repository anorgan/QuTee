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
     * @var \Qutee\Persistor\PersistorInterface
     */
    protected $_persistor;

    /**
     *
     * @var \Qutee\Queue
     */
    static protected $_instance;

    public function __construct()
    {
        self::$_instance = $this;
    }

    /**
     *
     * @return \Qutee\Persistor\PersistorInterface
     */
    public function getPersistor()
    {
        return $this->_persistor;
    }

    /**
     *
     * @param \Qutee\Persistor\PersistorInterface $persistor
     *
     * @return \Qutee\Queue
     */
    public function setPersistor(\Qutee\Persistor\PersistorInterface $persistor)
    {
        $this->_persistor = $persistor;

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
        $this->getPersistor()->addTask($task);

        return $this;
    }

    /**
     *
     * @param array $params
     *
     * @return \Qutee\Task
     */
    public function getTask($params = array())
    {
        return $this->getPersistor()->getTask($params);
    }

    /**
     *
     * @param array $params
     *
     * @return array
     */
    public function getTasks($params = array())
    {
        return $this->getPersistor()->getTasks($params);
    }

    /**
     * Clear all tasks
     *
     * @return boolean
     */
    public function clear()
    {
        return $this->getPersistor()->clear();
    }

    /**
     * Create queue
     *
     * @param array $config:
     *  persistor: name of the persistor adapter
     *  options:   array with options for the persistor
     *
     * @return \Qutee\Queue
     * @throws \InvalidArgumentException
     */
    static public function factory($config = array())
    {
        if (isset($config['persistor'])) {
            $persistorClass = 'Qutee\\Persistor\\'. ucfirst($config['persistor']);
            if (!class_exists($persistorClass)) {
                throw new \InvalidArgumentException(sprintf('Persistor "%s" doesn\'t exist', $config['persistor']));
            }

            $persistor = new $persistorClass;

            if (isset($config['options'])) {
                $persistor->setOptions($config['options']);
            }
        } else {
            // Default persistor
            $persistor = new \Qutee\Persistor\Memory;
        }

        $queue = new self;
        $queue->setPersistor($persistor);

        return $queue;
    }

    static public function setInstance($instance)
    {
        self::$_instance = $instance;
    }

    /**
     *
     * @return \Qutee\Queue
     */
    static public function get()
    {
        if (null === self::$_instance) {
            throw new Exception('Queue not created');
        }

        return self::$_instance;
    }
}