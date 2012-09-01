<?php
/*
* This file is part of rg\broker.
*
* (c) ResearchGate GmbH <bastian.hofmann@researchgate.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
require __DIR__ . '/config.php';
require __DIR__ . '/autoload.php';

$cli = new \Symfony\Component\Console\Application('rg\broker', '0.1.0');
$cli->setCatchExceptions(true);

$cli->addCommands(array(
    new \rg\broker\commands\AddRepository(),
    new \rg\broker\commands\RemoveRepository(),
));

$cli->run();