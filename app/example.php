<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Qutee\Queue;
use Qutee\Task;
use Qutee\Worker;

Task::create('SendMail', array(
    'to'        => 'marin.crnkovic@gmail.com',
    'from'      => 'qutee@nowhere.tld',
    'subject'   => 'Hi!',
    'text'      => 'It\'s your faithful QuTee!'
));