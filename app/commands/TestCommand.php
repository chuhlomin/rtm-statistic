<?php

namespace app\commands;


use Rtm\Rtm;
use Rtm\Service\Lists;
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
        /** @var Lists $listsService */
        $listsService = $this->rtm->getService(Rtm::SERVICE_LISTS);

        $lists = $listsService->getList();
        $result = [];

        $isInboxFound = false;
        $listId = null;
        foreach ($lists as $list) {
            $name = $list->getName();
            if ($name === 'Inbox') {
                $isInboxFound = true;
                $listId = $list->getId();
                break;
            }
        }

        if ($isInboxFound === false) {
            throw new \LogicException('Inbox list not found');
        }

        $completed = $this->getCountPerListAndFilter($listId, 'completed:6/26/2015');
        $added = $this->getCountPerListAndFilter($listId, 'added:6/26/2015');

        $result = [
            $completed,
            $added
        ];

        /** @var \Rtm\Service\Auth $rtmAuth */
        $output->writeln(print_r($result, true));
    }

    /**
     * @param $listId
     * @param $filter
     * @return int
     */
    private function getCountPerListAndFilter($listId, $filter)
    {
        /** @var Tasks $tasksService */
        $tasksService = $this->rtm->getService(Rtm::SERVICE_TASKS);
        $tasks = $tasksService->getList($filter, $listId)->toArray()[0];
        return count($tasks['taskseries']);
    }
}
