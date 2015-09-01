<?php

namespace tests\commands;


use app\commands\TokenCommand;
use Symfony\Component\Console\Application;

class TokenCommandTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \Mockery::close();
    }

    /** @test */
    public function shouldGetToken()
    {
        $serviceMock = \Mockery::mock('app\models\service\RtmService');
        $serviceMock->shouldReceive('getToken')->andReturn(0);

        /** @var \app\models\service\Factory|\Mockery\Mock $factoryMock */
        $factoryMock = \Mockery::mock('app\models\service\Factory');
        $factoryMock->shouldReceive('createTaskService')->andReturn($serviceMock);

        /** @var \Symfony\Component\Console\Input\InputInterface|\Mockery\Mock $inputMock */
        $inputMock = \Mockery::mock('\Symfony\Component\Console\Input\InputInterface');
        $inputMock->shouldReceive('getArgument')->with('service')->andReturn('rtm');
        $inputMock->shouldIgnoreMissing();

        /** @var \Symfony\Component\Console\Output\OutputInterface|\Mockery\Mock $outputMock */
        $outputMock = \Mockery::mock('\Symfony\Component\Console\Output\OutputInterface');
        $outputMock->shouldReceive('write');

        $application = new Application();
        $command = new TokenCommand($factoryMock);
        $command->setApplication($application);

        $result = $command->run($inputMock, $outputMock);

        self::assertEquals(0, $result);
    }
}
