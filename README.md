QuTee
=====

[![Build Status](https://travis-ci.org/anorgan/QuTee.png)](https://travis-ci.org/anorgan/QuTee)

Simple queue manager and task processor for PHP

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
// Worker - process all queues (folder_deleter.php)
$worker = new Worker;
$worker->run();

// Or, with more configuration
$worker = new Worker;
$worker
    ->setInterval(30)                           // Run every 30 seconds
    ->setPriority(Task::PRIORITY_HIGH)          // Will only do tasks of this priority
    ->run();

```

Disclaimer
----------

- Use supervisord or similar for process monitoring / babysitting
- Extremely simple, does not pretend to replace Gearman, but pretends to simplify background jobs processing

[TODO](https://github.com/anorgan/QuTee/issues?milestone=1&state=open)
----
- Add queue persistor using adapters (MySQL / PostgreSQL, Redis, Beanstalkd, MongoDB)
- Add logging
- Add reporting dashboard