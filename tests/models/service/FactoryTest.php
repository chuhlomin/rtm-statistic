<?php

namespace tests\models\service;


use app\models\service\Factory;
use Pimple\Container;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \Mockery::close();
    }

    /** @test */
    public function shouldCreateRtmService()
    {
        $clientRtmMock = \Mockery::mock('Rtm\Rtm');
        $container = new Container(['client_rtm' => $clientRtmMock]);
        $factory = new Factory($container);

        $service = $factory->createTaskService('rtm');

        self::assertEquals('app\models\service\RtmService', get_class($service));
    }

    /** @test */
    public function shouldThrowExceptionIfServiceUnknown()
    {
        $container = new Container();
        $factory = new Factory($container);

        $this->setExpectedException('InvalidArgumentException', 'foo is not valid task service alias');
        $factory->createTaskService('foo');
    }
}