<?php

namespace app\commands;


use app\exceptions\RtmException;
use Rtm\Exception;
use Rtm\Rtm;
use Rtm\Service\Auth;
use Rtm\Service\Lists;
use Rtm\Service\Tasks;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RtmStatCommand extends Command
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
        $this
            ->setName('rtm:stat')
            ->addArgument('start', InputArgument::REQUIRED, 'Format: 2015-06-27')
            ->addArgument('end', InputArgument::REQUIRED, 'Format: 2015-06-29 (including)')
            ->addArgument('interval', InputArgument::OPTIONAL, '"1 day", "1 week" or something like this', '1 week');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startInput = $input->getArgument('start');
        $endInput = $input->getArgument('end');
        $intervalInput = $input->getArgument('interval');

        $this->checkToken();

        $listId = $this->getInboxListId();

        $interval = \DateInterval::createFromDateString($intervalInput);

        $period = $this->getDatePeriod($startInput, $endInput, $interval);

        $filePath = $this->getFilePath();
        $file = fopen($filePath, 'w+');

        fwrite(
            $file,
            sprintf(
                "start: %s\nend: %s\ninterval: %s\n\n",
                $startInput,
                $endInput,
                $intervalInput
            )
        );

        foreach ($period as $date) {
            $result = $this->getResultPerPeriod($date, $interval, $listId);

            $dataLine = implode("\t", array_values($result)) . "\n";

            fwrite($file, $dataLine);
            $output->write($dataLine);
        }

        fclose($file);
    }

    /**
     * @param integer $listId
     * @param string $filter
     * @return integer
     */
    private function getCountPerListAndFilter($listId, $filter)
    {
        return count($this->getTasksPerListAndFilter($listId, $filter));
    }

    /**
     * @param integer $listId
     * @param string $filter
     * @return array
     */
    private function getTasksPerListAndFilter($listId, $filter)
    {
        /** @var Tasks $tasksService */
        $tasksService = $this->rtm->getService(Rtm::SERVICE_TASKS);
        $tasks = $tasksService->getList($filter, $listId)->toArray()[0];
        return $tasks['taskseries'];
    }

    /**
     * @return integer
     */
    private function getInboxListId()
    {
        /** @var Lists $listsService */
        $listsService = $this->rtm->getService(Rtm::SERVICE_LISTS);

        $lists = $listsService->getList();

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

        return $listId;
    }

    /**
     * @throws \Exception
     */
    private function checkToken()
    {
        /** @var Auth $rtmAuth */
        $rtmAuth = $this->rtm->getService(Rtm::SERVICE_AUTH);

        try {
            $rtmAuth->checkToken();
        } catch (Exception $e) {
            if ($e->getMessage() === 'rtm.auth.checkToken: Login failed / Invalid auth token') {
                throw new RtmException('Invalid AuthToken. Run rtm:token command to get one.');
            } else {
                throw $e;
            }
        }
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
     * @param $date
     * @param $interval
     * @param $listId
     * @return array
     */
    private function getResultPerPeriod($date, $interval, $listId)
    {
        /** @var \DateTime $date */
        $dateFormatted = $date->format('Y-m-d');
        $nextPeriod = $date->add($interval);
        $nextPeriodFormatted = $nextPeriod->format('Y-m-d');

        $tasks = $this->getTasksPerListAndFilter(
            $listId,
            sprintf('status:incomplete AND addedBefore:%s', $nextPeriodFormatted)
        );

        $lifeDays = 0;
        foreach ($tasks as $task) {
            $taskCreated = new \DateTime($task['created']);
            $taskLife = $date->diff($taskCreated);

            $taskLifeDays = (int)$taskLife->format('%a');
            $lifeDays += $taskLifeDays;
        }

        return [
            'date' => $dateFormatted,
            'completed' => $this->getCountPerListAndFilter(
                $listId,
                sprintf(
                    'completedAfter:%s AND completedBefore:%s',
                    $dateFormatted,
                    $nextPeriodFormatted
                )
            ),
            'added' => $this->getCountPerListAndFilter(
                $listId,
                sprintf(
                    'addedAfter:%s AND addedBefore:%s',
                    $dateFormatted,
                    $nextPeriodFormatted
                )
            ),
            'total' => count($tasks),
            'days' => $lifeDays
        ];
    }
}
