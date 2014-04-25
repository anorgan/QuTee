<?php
/*
 * php app.php
 */
$loader = require_once __DIR__ . "/../vendor/autoload.php";
$loader->add('Acme', __DIR__);

use Qutee\Queue;
use Qutee\Task;
use Qutee\Worker;

// Setup our queue with persistor of choice, preferably in
// Dependency Injection Container
$redisParams    = array(
    'host'  => '127.0.0.1',
    'port'  => 6379
);
$queuePersistor = new Qutee\Persistor\Redis($redisParams);

$queue          = new Queue();
$queue->setPersistor($queuePersistor);

// Create a task
$task = new Task;
$task
    ->setName('Acme/SendMail')
    ->setData(array(
        'to'        => 'someone@somewhere.com',
        'from'      => 'qutee@nowhere.tld',
        'subject'   => 'Hi!',
        'text'      => 'It\'s your faithful QuTee!'
    ))
    ->setPriority(Task::PRIORITY_HIGH)
    ->setUniqueId('send_mail_email@domain.tld');

// Queue it
$queue->addTask($task);

// Or do this in one go, if you set the queue (bootstrap maybe?)
Task::create(
    'Acme/SendMail',
    array(
        'to'        => 'someone@somewhere.com',
        'from'      => 'qutee@nowhere.tld',
        'subject'   => 'Hi!',
        'text'      => 'It\'s your faithful QuTee!'
    ),
    Task::PRIORITY_HIGH
);

// Send worker to do it
$worker = new Worker;
$worker
    ->setQueue($queue)
    ->setPriority(Task::PRIORITY_HIGH);

while (true) {
    try {
        $worker->run();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
    