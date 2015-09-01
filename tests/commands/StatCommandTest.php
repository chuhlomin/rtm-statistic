<?php

namespace tests\commands;


use app\commands\StatCommand;

class StatCommandTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \Mockery::close();
    }

    /** @test */
    public function shouldGetStatFromRtm()
    {
        $serviceMock = \Mockery::mock('app\models\service\RtmService');
        $serviceMock->shouldReceive('init');
        $serviceMock->shouldReceive('getAmountOfCompletedTasks')->withArgs(['2015-05-01', '2015-06-01'])->andReturn(30);
        $serviceMock->shouldReceive('getAmountOfAddedTasks')->withArgs(['2015-05-01', '2015-06-01'])->andReturn(40);
        $serviceMock->shouldReceive('getLifeDays')->withArgs([\Mockery::any(), '2015-06-01'])->andReturn(1000);
        $serviceMock->shouldReceive('getTotalTaskCount')->with('2015-06-01')->andReturn(15);

        /** @var \app\models\service\Factory|\Mockery\Mock $factoryMock */
        $factoryMock = \Mockery::mock('app\models\service\Factory');
        $factoryMock->shouldReceive('createTaskService')->andReturn($serviceMock);

        /** @var \Symfony\Component\Filesystem\Filesystem|\Mockery\Mock $fsMock */
        $fsMock = \Mockery::mock('\Symfony\Component\Filesystem\Filesystem');
        $fsMock
            ->shouldReceive('dumpFile')
            ->withArgs(
                [
                    \Mockery::any(),
                    "start: 2015-05-01\n" .
                    "end: 2015-05-30\n" .
                    "interval: \"1 month\"\n\n" .
                    "2015-05-01	30	40	1000	15\n"
                ]
            );

        /** @var \Symfony\Component\Console\Input\InputInterface|\Mockery\Mock $inputMock */
        $inputMock = \Mockery::mock('\Symfony\Component\Console\Input\InputInterface');
        $inputMock->shouldReceive('getArgument')->with('service')->andReturn('rtm');
        $inputMock->shouldReceive('getArgument')->with('start')->andReturn('2015-05-01');
        $inputMock->shouldReceive('getArgument')->with('end')->andReturn('2015-05-30');
        $inputMock->shouldReceive('getArgument')->with('interval')->andReturn('"1 month"');
        $inputMock->shouldIgnoreMissing();

        /** @var \Symfony\Component\Console\Output\OutputInterface|\Mockery\Mock $outputMock */
        $outputMock = \Mockery::mock('\Symfony\Component\Console\Output\OutputInterface');
        $outputMock->shouldReceive('write');

        $command = new StatCommand($factoryMock, $fsMock);
        $command->run($inputMock, $outputMock);
    }

    /** @test */
    public function shouldGetStatFromRtmSeveralIntervals()
    {
        $serviceMock = \Mockery::mock('app\models\service\RtmService');
        $serviceMock->shouldReceive('init');
        $serviceMock->shouldReceive('getAmountOfCompletedTasks')->withArgs(['2015-03-01', '2015-04-01'])->andReturn(30);
        $serviceMock->shouldReceive('getAmountOfCompletedTasks')->withArgs(['2015-04-01', '2015-05-01'])->andReturn(31);
        $serviceMock->shouldReceive('getAmountOfCompletedTasks')->withArgs(['2015-05-01', '2015-06-01'])->andReturn(32);
        $serviceMock->shouldReceive('getAmountOfAddedTasks')->withArgs(['2015-03-01', '2015-04-01'])->andReturn(40);
        $serviceMock->shouldReceive('getAmountOfAddedTasks')->withArgs(['2015-04-01', '2015-05-01'])->andReturn(41);
        $serviceMock->shouldReceive('getAmountOfAddedTasks')->withArgs(['2015-05-01', '2015-06-01'])->andReturn(42);
        $serviceMock->shouldReceive('getLifeDays')->withArgs([\Mockery::any(), '2015-04-01'])->andReturn(1000);
        $serviceMock->shouldReceive('getLifeDays')->withArgs([\Mockery::any(), '2015-05-01'])->andReturn(1001);
        $serviceMock->shouldReceive('getLifeDays')->withArgs([\Mockery::any(), '2015-06-01'])->andReturn(1002);
        $serviceMock->shouldReceive('getTotalTaskCount')->with('2015-04-01')->andReturn(15);
        $serviceMock->shouldReceive('getTotalTaskCount')->with('2015-05-01')->andReturn(16);
        $serviceMock->shouldReceive('getTotalTaskCount')->with('2015-06-01')->andReturn(17);

        /** @var \app\models\service\Factory|\Mockery\Mock $factoryMock */
        $factoryMock = \Mockery::mock('app\models\service\Factory');
        $factoryMock->shouldReceive('createTaskService')->andReturn($serviceMock);

        /** @var \Symfony\Component\Filesystem\Filesystem|\Mockery\Mock $fsMock */
        $fsMock = \Mockery::mock('\Symfony\Component\Filesystem\Filesystem');
        $fsMock
            ->shouldReceive('dumpFile')
            ->withArgs(
                [
                    \Mockery::any(),
                    "start: 2015-03-01\n" .
                    "end: 2015-05-30\n" .
                    "interval: \"1 month\"\n\n" .
                    "2015-03-01	30	40	1000	15\n" .
                    "2015-04-01	31	41	1001	16\n" .
                    "2015-05-01	32	42	1002	17\n"
                ]
            );

        /** @var \Symfony\Component\Console\Input\InputInterface|\Mockery\Mock $inputMock */
        $inputMock = \Mockery::mock('\Symfony\Component\Console\Input\InputInterface');
        $inputMock->shouldReceive('getArgument')->with('service')->andReturn('rtm');
        $inputMock->shouldReceive('getArgument')->with('start')->andReturn('2015-03-01');
        $inputMock->shouldReceive('getArgument')->with('end')->andReturn('2015-05-30');
        $inputMock->shouldReceive('getArgument')->with('interval')->andReturn('"1 month"');
        $inputMock->shouldIgnoreMissing();

        /** @var \Symfony\Component\Console\Output\OutputInterface|\Mockery\Mock $outputMock */
        $outputMock = \Mockery::mock('\Symfony\Component\Console\Output\OutputInterface');
        $outputMock->shouldReceive('write');

        $command = new StatCommand($factoryMock, $fsMock);
        $command->run($inputMock, $outputMock);
    }
}
