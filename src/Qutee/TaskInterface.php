<?php

namespace Qutee;

/**
 * Task Interface
 *
 * @author anorgan
 */
interface TaskInterface
{
    /**
     * Set data needed for the task to run
     *
     * @param array $data
     */
    public function setData(array $data);

    /**
     * Run the task
     */
    public function run();
}
