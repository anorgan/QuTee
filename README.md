QuTee
=====

[![Build Status](https://travis-ci.org/anorgan/QuTee.png)](https://travis-ci.org/anorgan/QuTee)

Simple queue manager and task processor for PHP

Example
-------
``` php
<?php

require_once __DIR__ . "/../vendor/autoload.php";

// Create Task
$task = new Task;
$task
    ->setName('Acme/SendMail')
    ->setData(array(
        'to'        => 'you@yourdomain.com',
        'from'      => 'qutee@nowhere.tld',
        'subject'   => 'Hi!',
        'text'      => 'It\'s your faithful QuTee!'
    ));

// Queue it
$queue = new Queue();
$queue->addTask($task);

// Or do this in one go
Task::create('Acme/SendMail', array(
    'to'        => 'you@yourdomain.com',
    'from'      => 'qutee@nowhere.tld',
    'subject'   => 'Hi!',
    'text'      => 'It\'s your faithful QuTee!'
));
```

``` php
<?php
// Worker - process queue

$worker = new Worker;
$worker->run();

// Or, with more configuration
$worker = new Worker;
$worker
    ->setInterval(30)                           // Run every 30 minutes
    ->setWhitelistedTask('Acme/DeleteCache')    // Will only do this tasks
    ->setWhitelistedTask('Acme/SendMail')       // Will only do this tasks
    ->run();

```

Disclaimer
----------

- Don't use it yet, don't know what version this is :)
- Will be extremely simple, does not pretend to replace 0MQ, Gearman, Redis and such

[TODO](https://github.com/anorgan/QuTee/issues?milestone=1&state=open)
----
- Add queue persistor using adapters (DB, MongoDB, Redis, Memcache)
- Make worker spawn child processes for running tasks
- Add method name property to task, together with current default one, so it is easier to set it
- Add logging
- Add reporting dashboard