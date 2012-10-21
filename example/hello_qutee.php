<?php

$loader = require_once __DIR__ . "/../vendor/autoload.php";
$loader->add('Acme', __DIR__);

use Qutee\Queue;
use Qutee\Task;
use Qutee\Worker;

// Create a task
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

// Send worker to do it
$worker = new Worker;
$worker->run();