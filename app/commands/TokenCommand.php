<?php

namespace app\commands;


use app\models\service\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TokenCommand extends Command
{
    /** @var Factory */
    private $factory;

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        parent::__construct();
        $this->factory = $factory;
    }

    protected function configure()
    {
        $this
            ->setName('token')
            ->addArgument('service', InputArgument::REQUIRED, '"rtm" is only one supported for now');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serviceAlias = $input->getArgument('service');
        $service = $this->factory->createTaskService($serviceAlias);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        return $service->getToken($input, $output, $helper);
    }
}
