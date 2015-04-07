QuTee
=====

[![Build Status](https://travis-ci.org/anorgan/QuTee.png)](https://travis-ci.org/anorgan/QuTee)
[![Coverage Status](https://coveralls.io/repos/anorgan/QuTee/badge.svg?branch=master)](https://coveralls.io/r/anorgan/QuTee?branch=master)

Simple queue manager and task processor for PHP using Beanstalkd, Redis or MySQL as backend. Event interface is provided for your logging or statsd-ing needs.

Example
-------
``` php
<?php
/*
 * Bootstrap / DIC
 */
$beanstalkdParams    = array(
    'host'  => '127.0.0.1',
    'port'  => 11300
);
$queuePersistor = new Qutee\Persistor\Beanstalk();
$queuePersistor->setOptions($beanstalkdParams);

// or...

$redisParams    = array(
    'host'  => '127.0.0.1',
    'port'  => 6379
);
$queuePersistor = new Qutee\Persistor\Redis();
$queuePersistor->setOptions($redisParams);

// or...

$pdoParams    = array(
    'dsn'       => 'mysql:host=127.0.0.1;dbname=test;charset=utf8',
    'username'  => 'root',
    'password'  => '',
    'table_name'=> 'queue'
);
$queuePersistor = new Qutee\Persistor\Pdo();
$queuePersistor->setOptions($pdoParams);

$queue          = new Queue();
$queue->setPersistor($queuePersistor);

/*
 * App
 */

// Create Task
$task = new Task;
$task
    ->setName('Acme/DeleteFolder')
    ->setData(array(
        'path'      => '/usr',
    ))
    ->setPriority(Task::PRIORITY_HIGH);

// Queue it
$queue->addTask($task);

// Or do this in one go
Task::create('Acme/DeleteFolder', array('path' => '/usr'), Task::PRIORITY_HIGH);
```

``` php
<?php
// Worker - process all queues
$worker = new Worker;
while (true) {
    try {
        $worker->run();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

// Or, with more configuration
$worker = new Worker;
$worker
    ->setInterval(30)                           // Run every 30 seconds
    ->setPriority(Task::PRIORITY_HIGH)          // Will only do tasks of this priority
    ;

while (true) {
    try {
        if (null !== ($task = $worker->run())) {
            echo 'Ran task: '. $task->getName() . PHP_EOL;
        }
    } catch (Exception $e) {
        echo 'Error: '. $e->getMessage() . PHP_EOL;
    }
}
```
    
Logging example
---------------

``` php
// Initialize queue with persistor
$queue          = new Qutee\Queue();

// Setup the dispatcher, and register your subscriber
$dispatcher = new Symfony\Component\EventDispatcher\EventDispatcher;
$dispatcher->addSubscriber(new QuteeEventSubscriber());
$queue->setEventDispatcher($dispatcher);

// The subscriber:
class QuteeEventSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            \Qutee\Queue::EVENT_ADD_TASK => array(
                'addTask',
                0
            ),
            \Qutee\Worker::EVENT_START_PROCESSING_TASK => array(
                'processTask',
                0
            ),
            \Qutee\Worker::EVENT_END_PROCESSING_TASK => array(
                'processTaskEnd',
                0
            ),
        );
    }
    
    public function addTask(Qutee\Event $event)
    {
        $this->log('Added task: '. $event->getTask()->getName());
    }
    
    public function processTask(Qutee\Event $event)
    {
        $this->log('Processing task '. $event->getTask()->getName() .' started');
    }
    
    public function processTaskEnd(Qutee\Event $event)
    {
        $this->log('Processing task '. $event->getTask()->getName() .' finished, lasted '. ($event->getArgument('elapsedTime') / 1000) .' seconds');
    }
    
    protected function log($message)
    {
        file_put_contents(__DIR__ .'/events.log', $message . PHP_EOL, FILE_APPEND);
    }

}

```

Notes
----------

- Use [supervisord](http://supervisord.org/) or similar for process monitoring / babysitting

[TODO](https://github.com/anorgan/QuTee/issues?milestone=1&state=open)
