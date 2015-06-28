#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__);

$parser= new \Symfony\Component\Yaml\Parser();

$config = $parser->parse(file_get_contents(__DIR__.'/app/config/default.yaml'));

$rtm = new \Rtm\Rtm($config['rtm']);

$application = new \Symfony\Component\Console\Application();

$application->add(new \app\commands\RtmStatCommand($rtm));

$application->run();
