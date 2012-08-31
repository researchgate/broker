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

$application = new \rg\broker\web\Application();
$application->run();
