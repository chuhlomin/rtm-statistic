<?php

namespace app\commands;


use app\models\service\Factory;
use app\models\service\ServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class StatCommand extends Command
{
    /** @var ServiceInterface */
    private $serviceModel;

    /** @var Factory */
    private $factory;

    /** @var Filesystem */
    private $fs;

    /**
     * @param Factory $factory
     * @param Filesystem $fs
     */
    public function __construct(Factory $factory, Filesystem $fs)
    {
        parent::__construct();
        $this->factory = $factory;
        $this->fs = $fs;
    }

    protected function configure()
    {
        $this
            ->setName('stat')
            ->addArgument('service', InputArgument::REQUIRED, '"rtm" is only one supported for now')
            ->addArgument('start', InputArgument::REQUIRED, 'Format: 2015-06-27')
            ->addArgument('end', InputArgument::REQUIRED, 'Format: 2015-06-29 (including)')
            ->addArgument('interval', InputArgument::OPTIONAL, '"1 day", "1 week" or something like this', '1 week');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serviceAlias = $input->getArgument('service');
        $startInput = $input->getArgument('start');
        $endInput = $input->getArgument('end');
        $intervalInput = $input->getArgument('interval');

        $this->serviceModel = $this->factory->createTaskService($serviceAlias);
        $this->serviceModel->init();

        $interval = \DateInterval::createFromDateString($intervalInput);
        $period = $this->getDatePeriod($startInput, $endInput, $interval);

        $fileContent = sprintf(
            "start: %s\nend: %s\ninterval: %s\n\n",
            $startInput,
            $endInput,
            $intervalInput
        );

        foreach ($period as $date) {
            $result = $this->getResultPerPeriod($date, $interval);

            $dataLine = implode("\t", array_values($result)) . "\n";

            $fileContent .= $dataLine;
            $output->write($dataLine);
        }

        $this->fs->dumpFile($this->getFilePath(), $fileContent);
    }

    /**
     * @param $startInput
     * @param $endInput
     * @param $interval
     * @return \DatePeriod
     */
    private function getDatePeriod($startInput, $endInput, $interval)
    {
        $start = new \DateTime($startInput);
        $end = new \DateTime($endInput);
        return new \DatePeriod($start, $interval, $end);
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return ROOT . '/data/' . date('Y-m-d_H:i:s') . substr((string)microtime(), 1, 8) . '.csv';
    }

    /**
     * @param \DateTime $date
     * @param \DateInterval $interval
     * @return array
     */
    private function getResultPerPeriod($date, $interval)
    {
        $nextPeriod = clone $date;
        $nextPeriod = $nextPeriod->add($interval);

        $from = $date->format('Y-m-d');
        $till = $nextPeriod->format('Y-m-d');

        return [
            'date' => $from,
            'completed' => $this->serviceModel->getAmountOfCompletedTasks($from, $till),
            'added' => $this->serviceModel->getAmountOfAddedTasks($from, $till),
            'total' => $this->serviceModel->getTotalTaskCount($till),
            'days' => $this->serviceModel->getLifeDays($date, $till)
        ];
    }
}
