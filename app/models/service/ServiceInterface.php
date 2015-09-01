<?php

namespace app\models\service;


use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ServiceInterface
{
    public function init();

    public function getToken(InputInterface $input, OutputInterface $output, QuestionHelper $helper);

    public function getAmountOfCompletedTasks($from, $till);

    public function getAmountOfAddedTasks($from, $till);

    public function getTotalTaskCount($till);

    public function getLifeDays(\DateTime $date, $till);
}
