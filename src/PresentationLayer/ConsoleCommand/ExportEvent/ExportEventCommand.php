<?php

namespace App\PresentationLayer\ConsoleCommand\ExportEvent;

use App\ApplicationLayer\AbstractCommand\Exception\CommandValidationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ExportEventCommand extends Command
{
    protected static $defaultName = 'app:meter-event:export';

    /**
     * @var \Symfony\Component\DependencyInjection\ServiceLocator
     */
    private $locator;
    /**
     * @var \App\PresentationLayer\MeterEventLogging\CommandArgsFactory
     */
    private $commandArgsFactory;

    public function __construct(
        ServiceLocator $handlerCollection,
        CommandArgsFactory $commandArgsFactory
    ) {
        $this->locator = $handlerCollection;
        $this->commandArgsFactory = $commandArgsFactory;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'date',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Date logs',
                (new \DateTime('now'))->sub(new \DateInterval('P1D'))->format('Y-m-d')
            )
            ->addOption(
                    'type',
                't',
                InputOption::VALUE_OPTIONAL,
                'Event type',
                ''
            )
            ->addOption(
                'remove-source',
                'r',
                    InputOption::VALUE_NEGATABLE,
                'Remove event log source file',
                false
            )
            ->addOption(
                'destination-table',
                '',
                InputOption::VALUE_OPTIONAL,
                'Table name to insert events',
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $handlerKey = self::$defaultName.':handler';

        if (!$this->locator->has($handlerKey)) {
            $output->writeln('Handler for command'.self::$defaultName.' not found.');

            return Command::INVALID;
        }

        try {
            $args = ($this->commandArgsFactory)($input);
            $handler = ($this->locator)($handlerKey);
            $result = $handler($args);

            echo $result->getValue();

            if ($result->isOk()) {
                return Command::SUCCESS;
            }

            $output->writeln($result->getValue());
        } catch (CommandValidationException $error) {
            $output->writeln($error->getMessage());

            return Command::INVALID;
        }

        return Command::FAILURE;
    }
}
