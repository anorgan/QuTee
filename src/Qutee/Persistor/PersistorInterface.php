<?php

namespace Qutee\Persistor;

/**
 * Persistor interface
 *
 * @author anorgan
 */
interface PersistorInterface
{

    /**
     * Set options
     *
     * @param array $options
     *
     * @return PersistorInterface
     */
    public function setOptions(array $options);

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Add task to the queue
     *
     * @param \Qutee\Task $name
     *
     * @return PersistorInterface
     */
    public function addTask(\Qutee\Task $task);

    /**
     * Get next task from the queue
     *
     * @param array $params:
     *  whitelist: return only tasks with those names
     *
     *
     * @return \Qutee\Task
     */
    public function getTask(array $params = array());

    /**
     * Get all tasks from the queue
     *
     * @return array array of tasks
     */
    public function getTasks(array $params = array());

    /**
     * Clear all tasks from queue
     *
     * @return boolean
     */
    public function clear();
}