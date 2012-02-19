<?php

define('ROOT', __DIR__);
define('ROOTURL', 'http://localhost:8888/Checkouts/broker');

require __DIR__ . '/vendor/.composer/autoload.php';

$cli = new \Symfony\Component\Console\Application('rg\broker', '1.0.0');
$cli->setCatchExceptions(true);

$cli->addCommands(array(
    new \rg\broker\commands\AddRepository(),
    new \rg\broker\commands\RemoveRepository(),
));

$cli->run();