<?php

namespace tests\models\service;


use app\models\service\RtmService;

class RtmServiceTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \Mockery::close();
    }

    /** @test */
    public function shouldCreateService()
    {
        /** @var \Rtm\Rtm|\Mockery\Mock $clientRtmMock */
        $clientRtmMock = \Mockery::mock('Rtm\Rtm');

        new RtmService($clientRtmMock);
    }

    /** @test */
    public function shouldInitService()
    {
        $listMock = \Mockery::mock();
        $listMock->shouldReceive('getName')->andReturn('Inbox');
        $listMock->shouldReceive('getId')->andReturn(123456);

        $listsMock = \Mockery::mock('Rtm\Service\Lists');
        $listsMock->shouldReceive('getList')->andReturn([$listMock]);

        $authMock = \Mockery::mock('Rtm\Service\Auth');
        $authMock->shouldReceive('checkToken');

        /** @var \Rtm\Rtm|\Mockery\Mock $clientRtmMock */
        $clientRtmMock = \Mockery::mock('Rtm\Rtm');
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Lists')->andReturn($listsMock);
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Auth')->andReturn($authMock);

        $service = new RtmService($clientRtmMock);

        $service->init();
    }

    /** @test */
    public function shouldInitServiceAndThrowExceptionIfNoInboxList()
    {
        $listsMock = \Mockery::mock('Rtm\Service\Lists');
        $listsMock->shouldReceive('getList')->andReturn([]);

        $authMock = \Mockery::mock('Rtm\Service\Auth');
        $authMock->shouldReceive('checkToken');

        /** @var \Rtm\Rtm|\Mockery\Mock $clientRtmMock */
        $clientRtmMock = \Mockery::mock('Rtm\Rtm');
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Lists')->andReturn($listsMock);
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Auth')->andReturn($authMock);

        $service = new RtmService($clientRtmMock);

        $this->setExpectedException('LogicException', 'Inbox list not found');
        $service->init();
    }

    /** @test */
    public function shouldGetAmountOfCompletedTasks()
    {
        $listMock = \Mockery::mock();
        $listMock->shouldReceive('getName')->andReturn('Inbox');
        $listMock->shouldReceive('getId')->andReturn(123456);

        $listsMock = \Mockery::mock('Rtm\Service\Lists');
        $listsMock->shouldReceive('getList')->andReturn([$listMock]);

        $tasksArrayMock = \Mockery::mock();
        $tasksArrayMock->shouldReceive('toArray')->andReturn(
            [
                [
                    'taskseries' => [
                        '...',
                        '...',
                        '...'
                    ]
                ]
            ]
        );

        $tasksMock = \Mockery::mock('Rtm\Service\Tasks');
        $tasksMock
            ->shouldReceive('getList')
            ->withArgs(['completedAfter:2015-05-01 AND completedBefore:2015-05-31', null])
            ->andReturn($tasksArrayMock);

        /** @var \Rtm\Rtm|\Mockery\Mock $clientRtmMock */
        $clientRtmMock = \Mockery::mock('Rtm\Rtm');
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Lists')->andReturn($listsMock);
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Tasks')->andReturn($tasksMock);

        $service = new RtmService($clientRtmMock);

        $result = $service->getAmountOfCompletedTasks('2015-05-01', '2015-05-31');

        self::assertEquals(3, $result);
    }

    /** @test */
    public function shouldGetAmountOfAddedTasks()
    {
        $listMock = \Mockery::mock();
        $listMock->shouldReceive('getName')->andReturn('Inbox');
        $listMock->shouldReceive('getId')->andReturn(123456);

        $listsMock = \Mockery::mock('Rtm\Service\Lists');
        $listsMock->shouldReceive('getList')->andReturn([$listMock]);

        $tasksArrayMock = \Mockery::mock();
        $tasksArrayMock->shouldReceive('toArray')->andReturn(
            [
                [
                    'taskseries' => [
                        '...',
                        '...',
                        '...'
                    ]
                ]
            ]
        );

        $tasksMock = \Mockery::mock('Rtm\Service\Tasks');
        $tasksMock
            ->shouldReceive('getList')
            ->withArgs(['addedAfter:2015-05-01 AND addedBefore:2015-05-31', null])
            ->andReturn($tasksArrayMock);

        /** @var \Rtm\Rtm|\Mockery\Mock $clientRtmMock */
        $clientRtmMock = \Mockery::mock('Rtm\Rtm');
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Lists')->andReturn($listsMock);
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Tasks')->andReturn($tasksMock);

        $service = new RtmService($clientRtmMock);

        $result = $service->getAmountOfAddedTasks('2015-05-01', '2015-05-31');

        self::assertEquals(3, $result);
    }

    /**
     * @return array
     */
    public function providerGetLifeDays()
    {
        return [
            [
                [
                    ['created' => '2015-04-01']
                ],
                30
            ],
            [
                [
                    ['created' => '2015-04-01'],
                    ['created' => '2015-04-01']
                ],
                60
            ],
            [
                [
                    ['created' => '2015-05-01'],
                    ['created' => '2015-04-01']
                ],
                30
            ],
            [
                [
                    ['created' => '2015-04-30'], // 1
                    ['created' => '2015-04-29'], // 2
                    ['created' => '2015-04-28'] // 3
                ],
                6
            ]
        ];
    }

    /**
     * @test
     * @dataProvider providerGetLifeDays
     * @param array $taskSeries
     * @param int $lifeDays
     */
    public function shouldGetLifeDaysForOneTask($taskSeries, $lifeDays)
    {
        $listMock = \Mockery::mock();
        $listMock->shouldReceive('getName')->andReturn('Inbox');
        $listMock->shouldReceive('getId')->andReturn(123456);

        $listsMock = \Mockery::mock('Rtm\Service\Lists');
        $listsMock->shouldReceive('getList')->andReturn([$listMock]);

        $tasksArrayMock = \Mockery::mock();
        $tasksArrayMock->shouldReceive('toArray')->andReturn([['taskseries' => $taskSeries]]);

        $tasksMock = \Mockery::mock('Rtm\Service\Tasks');
        $tasksMock
            ->shouldReceive('getList')
            ->withArgs(['status:incomplete AND addedBefore:2015-05-31', null])
            ->andReturn($tasksArrayMock);

        /** @var \Rtm\Rtm|\Mockery\Mock $clientRtmMock */
        $clientRtmMock = \Mockery::mock('Rtm\Rtm');
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Lists')->andReturn($listsMock);
        $clientRtmMock->shouldReceive('getService')->with('Rtm\Service\Tasks')->andReturn($tasksMock);

        $service = new RtmService($clientRtmMock);

        $result = $service->getLifeDays(
            new \DateTime('2015-05-01', new \DateTimeZone('America/New_York')),
            '2015-05-31'
        );

        self::assertEquals($lifeDays, $result);
    }
}
