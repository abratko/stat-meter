<?php

namespace App\ApplicationLayer\MeterEventLoggingBatch;

use App\ApplicationLayer\AbstractCommand\CommandValidationResult;
use App\ApplicationLayer\AbstractCommand\Json\JsonSchemaValidator;

class EventBatchValidator
{
    /**
     * @var array
     */
    private $schemas;
    /**
     * @var \App\ApplicationLayer\AbstractCommand\Json\JsonSchemaValidator
     */
    private $jsonSchemaValidator;

    public function __construct(
        array $schemas,
        JsonSchemaValidator $jsonSchemaValidator
    ) {
        $this->schemas = $schemas;
        $this->jsonSchemaValidator = $jsonSchemaValidator;
    }

    public function validate($event): CommandValidationResult
    {
        if (!isset($this->schemas[$event->type])) {
            return (new CommandValidationResult($event))
                ->addError(
                 'type',
                    "Event with type {$event->type} not found"
                );
        }

        return $this
            ->jsonSchemaValidator
            ->validate(
                $event,
                $this->schemas[$event->type]
            );
    }
}
