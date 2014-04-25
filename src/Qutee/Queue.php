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
     * @param int $priority
     *
     * @return \Qutee\Task
     */
    public function getTask($priority = null)
    {
        return $this->getPersistor()->getTask($priority);
    }

    /**
     *
     * @param int $priority
     *
     * @return array
     */
    public function getTasks($priority = null)
    {
        return $this->getPersistor()->getTasks($priority);
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
            if (class_exists($persistorClass)) {
                $persistor = new $persistorClass;
            } elseif (class_exists($config['persistor'])) {
                $persistor = new $config['persistor'];
            } else {
                throw new \InvalidArgumentException(sprintf('Persistor "%s" doesn\'t exist', $config['persistor']));
            }

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