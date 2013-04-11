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

$requestedFile = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_NAME'];
if (php_sapi_name() == 'cli-server' && file_exists($requestedFile) && !is_dir($requestedFile)) {
    return false; // Documentation: http://php.net/manual/en/features.commandline.webserver.php
}

$application = new \rg\broker\web\Application();
$application->run();
