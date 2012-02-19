<?php

define('ROOT', __DIR__);
define('ROOTURL', 'http://localhost:8888/Checkouts/broker');

require __DIR__ . '/vendor/.composer/autoload.php';

$universalLoader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$universalLoader->registerPrefix('Slim_', __DIR__ . '/vendor/codeguy/slim');
$universalLoader->register();

$mapLoader = new \Symfony\Component\ClassLoader\MapClassLoader(array(
    'Slim' => __DIR__ . '/vendor/codeguy/slim/Slim/Slim.php'
));
$mapLoader->register();

$application = new \rg\broker\web\Application();
$application->run();