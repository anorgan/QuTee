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
     * Array for unique tasks
     *
     * @var array
     */
    private $_uniqueTasks = array();

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
        $key = $this->_createKey($task);

        // Check if the task is unique and already exists
        if ($task->isUnique() && array_key_exists($key, $this->_uniqueTasks)) {
            return $this;
        }

        $this->_uniqueTasks[$key] = true;

        $this->_storage[] = serialize($task);

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
        if (null !== $priority) {
            foreach ($this->_storage as $k => $task) {
                $task = unserialize($task);
                if ($task->getPriority() == $priority) {
                    unset($this->_storage[$k]);
                    return $task;
                }
            }
        } else {
            $task = array_shift($this->_storage);

            if (null !== $task) {
                $task = unserialize($task);
            }

            return $task;
        }
    }

    /**
     *
     * @param int $priority
     *
     * @return array
     */
    public function getTasks($priority = null)
    {
        $tasks = array();
        foreach ($this->_storage as $task) {
            $task = unserialize($task);
            if (null !== $priority && $task->getPriority() != $priority) {
                continue;
            }

            $tasks[] = $task;
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
        $this->_storage = array();

        return true;
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
}