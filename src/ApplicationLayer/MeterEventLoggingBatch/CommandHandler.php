<?php

namespace App\ApplicationLayer\MeterEventLoggingBatch;

use App\ApplicationLayer\AbstractCommand\CommandResult;
use App\ApplicationLayer\AbstractCommand\CommandValidationResult;

class CommandHandler
{
    /**
     * @var \App\ApplicationLayer\MeterEventLoggingBatch\EventLoggerBatchFactory
     */
    private $eventLoggerBatchFactory;
    /**
     * @var \App\ApplicationLayer\MeterEventLoggingBatch\EventBatchValidator
     */
    private $eventValidator;

    public function __construct(
        EventBatchValidator $eventValidator,
        EventLoggerBatchFactory $eventLoggerBatchFactory
    ) {
        $this->eventLoggerBatchFactory = $eventLoggerBatchFactory;
        $this->eventValidator = $eventValidator;
    }

    public function __invoke(ArgsValueObjInterface $argsValueObj): CommandResult
    {
        return $this->execute($argsValueObj);
    }

    protected function execute(ArgsValueObjInterface $args): CommandResult
    {
        $validationResult = new CommandValidationResult($this);
        $eventLogger = ($this->eventLoggerBatchFactory)();
        $generalProps = $args->getGeneralProps();
        foreach ($args->getEvents() as $i => $event) {
            $eventValObj = (object) ($generalProps + $event);
            $eventValidationResult = $this->eventValidator->validate($eventValObj);
            if ($eventValidationResult->hasErrors()) {
                $validationResult->addError((string) $i, $eventValidationResult->getErrors());

                continue;
            }

            $eventLogger->commit($eventValObj);
        }

        $countCommittedEvents = $eventLogger->getSize();
        $eventLogger->push();

        if (!$validationResult->hasErrors()) {
            return CommandResult::ok($countCommittedEvents);
        }

        if (0 === $countCommittedEvents) {
            return CommandResult::fail($validationResult->getErrors());
        }

        return CommandResult::partialOk(
            $validationResult,
            $countCommittedEvents
        );
    }
}
