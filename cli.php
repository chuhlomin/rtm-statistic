#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__);

$container = new \Pimple\Container();

$container['config'] = function() {
    $parser = new \Symfony\Component\Yaml\Parser();
    return $parser->parse(file_get_contents(__DIR__.'/app/config/default.yaml'));
};

$container['client_rtm'] = function($c) {
    return new \Rtm\Rtm($c['config']['rtm']);
};

$serviceFactory = new \app\models\service\Factory($container);
$fs = new \Symfony\Component\Filesystem\Filesystem();

$application = new \app\models\Application('TaskStat', '1.0', $container);

$application->add(new \app\commands\TokenCommand($serviceFactory));
$application->add(new \app\commands\StatCommand($serviceFactory, $fs));

$application->run();
