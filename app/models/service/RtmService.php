<?php

namespace app\models\service;


use app\exceptions\RtmException;
use Rtm\Rtm;
use Rtm\Service\Auth;
use Rtm\Service\Lists;
use Rtm\Service\Tasks;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class RtmService implements ServiceInterface
{
    /** @var Rtm */
    private $rtm;

    /** @var int */
    private $listId;

    /** @var array */
    private $tasks = null;

    /**
     * @param Rtm $rtm
     */
    public function __construct(Rtm $rtm)
    {
        $this->rtm = $rtm;
    }

    public function init()
    {
        $this->checkToken();
        $this->listId = $this->getInboxListId();
    }

    /**
     * @param string $from
     * @param string $till
     * @return int
     */
    public function getAmountOfCompletedTasks($from, $till)
    {
        return $this->getCountPerListAndFilter(
            $this->listId,
            sprintf('completedAfter:%s AND completedBefore:%s', $from, $till)
        );
    }

    /**
     * @param string $from
     * @param string $till
     * @return int
     */
    public function getAmountOfAddedTasks($from, $till)
    {
        return $this->getCountPerListAndFilter(
            $this->listId,
            sprintf('addedAfter:%s AND addedBefore:%s', $from, $till)
        );
    }

    /**
     * @param string $till
     * @return int
     */
    public function getTotalTaskCount($till)
    {
        return count($this->getTasksInListBeforeDate($till));
    }

    /**
     * @param \DateTime $date
     * @param string $till
     * @return int
     */
    public function getLifeDays(\DateTime $date, $till)
    {
        $tasks = $this->getTasksInListBeforeDate($till);

        $lifeDays = 0;
        foreach ($tasks as $task) {
            $taskCreated = new \DateTime($task['created']);
            $taskLife = $date->diff($taskCreated);

            $taskLifeDays = (int)$taskLife->format('%a');
            $lifeDays += $taskLifeDays;
        }

        return $lifeDays;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param QuestionHelper $helper
     * @return int
     */
    public function getToken(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        $authUrl = $this->rtm->getAuthUrl();

        $output->writeln(
            'Make sure that you have <info>ApiKey</info> and <info>Secret</info> ' .
            'in your <comment>app/config/default.yaml</comment> file.' . PHP_EOL .
            'You may get new one from page https://www.rememberthemilk.com/services/api/keys.rtm.' . PHP_EOL
        );

        $output->writeln(
            sprintf(
                'Open URL in browser: <options=bold>%s</options=bold>' . PHP_EOL .
                'Login and copy <info>frob</info> value.' . PHP_EOL,
                $authUrl
            )
        );

        $question = new Question('Paste frob value: ');
        $frob = $helper->ask($input, $output, $question);

        /** @var Auth $authService */
        $authService = $this->rtm->getService(Rtm::SERVICE_AUTH);

        /** @var Auth $token */
        $token = $authService->getToken($frob);

        $output->writeln(
            sprintf(
                PHP_EOL . 'You <info>AuthToken</info> is <options=bold>%s</options=bold>' . PHP_EOL .
                'Copy paste it to your <comment>app/config/default.yaml</comment> in rtm:AuthToken section.' . PHP_EOL,
                $token->getToken()
            )
        );

        return 0;
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
        } catch (\Exception $e) {
            if ($e->getMessage() === 'rtm.auth.checkToken: Login failed / Invalid auth token') {
                throw new RtmException('Invalid AuthToken. Run rtm:token command to get one.');
            } else {
                throw $e;
            }
        }
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
     * @return int
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
     * @param $till
     * @return array
     */
    private function getTasksInListBeforeDate($till)
    {
        if ($this->tasks !== null) {
            return $this->tasks;
        }

        $this->tasks = $this->getTasksPerListAndFilter(
            $this->listId,
            sprintf('status:incomplete AND addedBefore:%s', $till)
        );

        return $this->tasks;
    }
}
