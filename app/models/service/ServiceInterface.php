<?php

namespace app\models\service;


interface ServiceInterface
{
    public function init();

    public function getAmountOfCompletedTasks($from, $till);

    public function getAmountOfAddedTasks($from, $till);

    public function getTotalTaskCount($till);

    public function getLifeDays(\DateTime $date, $till);
}
