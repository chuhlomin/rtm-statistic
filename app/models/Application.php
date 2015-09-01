<?php

namespace app\models;


use Pimple\Container;

class Application extends \Symfony\Component\Console\Application
{
    private $container;

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN', Container $container)
    {
        parent::__construct($name, $version);
        $this->container = $container;
    }
}
