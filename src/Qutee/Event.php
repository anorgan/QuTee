<?php

namespace Qutee;

use Qutee\Task;

/**
 * Description of Event
 *
 * @author anorgan
 */
class Event extends \Symfony\Component\EventDispatcher\GenericEvent
{

    /**
     *
     * @var Task
     */
    protected $task;

    /**
     * 
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * 
     * @param \Qutee\Task $task
     *
     * @return \Qutee\Event
     */
    public function setTask(Task $task)
    {
        $this->task = $task;
        
        return $this;
    }

}
