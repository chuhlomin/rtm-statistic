<?php

namespace app\commands;


use Rtm\Rtm;
use Rtm\Service\Tasks;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    /**
     * @var Rtm
     */
    private $rtm;

    /**
     * @param Rtm $rtm
     */
    public function __construct(Rtm $rtm)
    {
        parent::__construct();
        $this->rtm = $rtm;
    }

    protected function configure()
    {
        $this->setName('test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Tasks $taskService */
        $taskService = $this->rtm->getService(Rtm::SERVICE_TASKS);
        $taskList = $taskService->getList();

        /** @var \Rtm\Service\Auth $rtmAuth */
        $output->writeln(gettype($taskList));
        $output->writeln(print_r($taskList, true));

    }
}
