<?php

namespace Qutee\Persistor;

/**
 * Memory
 *
 * @author anorgan
 */
class Memory implements PersistorInterface
{

    /**
     *
     * @var array
     */
    private $_options = array();

    /**
     * Array for storage
     *
     * @var array
     */
    private $_storage = array();

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
     * @return \Qutee\Persistor\Memory
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
     * @return \Qutee\Persistor\Memory
     */
    public function addTask(\Qutee\Task $task)
    {
        $this->_storage[] = $task;

        return $this;
    }

    /**
     *
     * @param array $params
     *
     * @return \Qutee\Task
     */
    public function getTask(array $params = array())
    {
        if (!empty($params['whitelist'])) {
            foreach ($this->_storage as $k => $task) {
                if (in_array($task->getName(), $params['whitelist'])) {
                    unset($this->_storage[$k]);
                    return $task;
                }
            }
        } else {
            return array_shift($this->_storage);
        }
    }

    /**
     *
     * @param array $params
     *
     * @return array
     */
    public function getTasks(array $params = array())
    {
        if (!empty($params['whitelist'])) {
            $tasks = array();
            foreach ($this->_storage as $k => $task) {
                if (in_array($task->getName(), $params['whitelist'])) {
                    $tasks[] = $task;
                }
            }
            return $tasks;
        } else {
            return $this->_storage;
        }
    }


    /**
     * Clear queue
     *
     * @return boolean
     */
    public function clear()
    {
        $this->_storage = array();

        return true;
    }
}