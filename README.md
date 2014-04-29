QuTee
=====

[![Build Status](https://travis-ci.org/anorgan/QuTee.png)](https://travis-ci.org/anorgan/QuTee)

Simple queue manager and task processor for PHP using Redis or MySQL as backend.

Example
-------
``` php
<?php
/*
 * Bootstrap / DIC
 */
$redisParams    = array(
    'host'  => '127.0.0.1',
    'port'  => 6379
);
$queuePersistor = new Qutee\Persistor\Redis($redisParams);

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

Notes
----------

- Use [supervisord](http://supervisord.org/) or similar for process monitoring / babysitting

[TODO](https://github.com/anorgan/QuTee/issues?milestone=1&state=open)
----
- Add queue persistor using more adapters (Beanstalkd, MongoDB)
- Add logging
- Add reporting dashboard