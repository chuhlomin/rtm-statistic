<?php

namespace app\commands;


use app\models\service\Factory;
use Rtm\Rtm;
use Rtm\Service\Auth;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class TokenCommand extends Command
{
    /** @var Factory */
    private $factory;

    /** @var \app\models\service\RtmService */
    private $rtm;

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        parent::__construct();
        $this->factory = $factory;
        $this->rtm = $this->factory->createTaskService('rtm');
    }

    protected function configure()
    {
        $this->setName('token');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
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
    }
}
