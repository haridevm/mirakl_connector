<?php

declare(strict_types=1);

namespace Mirakl\Event\Console\Command;

use Magento\Framework\Console\Cli;
use Mirakl\Event\Helper\Data as EventHelper;
use Mirakl\Event\Model\Event;
use Mirakl\Process\Model\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EventCommand extends Command
{
    /**
     * Event type to execute
     */
    public const EVENT_TYPE = 'type';

    /**
     * @var EventHelper
     */
    private $eventHelper;

    /**
     * @param EventHelper $eventHelper
     * @param string|null $name
     */
    public function __construct(
        EventHelper $eventHelper,
        $name = null
    ) {
        parent::__construct($name);
        $this->eventHelper = $eventHelper;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::EVENT_TYPE,
                null,
                InputOption::VALUE_OPTIONAL,
                sprintf(
                    'Execute connector events for a specific synchronization type. One of: %s',
                    implode(', ', Event::getShortTypes())
                )
            )
        ];

        $this->setName('mirakl:event')
            ->setDescription('Handles execution of connector events synchronization')
            ->setDefinition($options);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $eventType = $input->getOption(self::EVENT_TYPE);

            if ($eventType && !in_array($eventType, Event::getShortTypes())) {
                $output->writeln(sprintf(
                    '<error>Invalid event type "%s". Valid types: %s</error>',
                    $eventType,
                    implode(', ', Event::getShortTypes())
                ));

                return Cli::RETURN_FAILURE;
            }

            if ($eventType) {
                $output->writeln(sprintf('<info>Executing asynchronous %s events...</info>', $eventType));
            } else {
                $output->writeln('<info>Executing asynchronous events...</info>');
            }

            $process = $this->eventHelper->getOrCreateEventProcess(Process::TYPE_CLI, $eventType);

            $process->execute();
            $output->writeln('<info>Done!</info>');
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
