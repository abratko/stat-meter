<?php declare(strict_types=1);

namespace App\ApplicationLayer\MeterEventLogging;

use App\ApplicationLayer\AbstractCommand\Json\HandlerAbstract as JsonHandlerAbstract;
use App\ApplicationLayer\AbstractCommand\Json\JsonSchemaValidator;

class CommandHandler extends JsonHandlerAbstract
{
    /**
     * @var \App\ApplicationLayer\MeterEventLogging\EventLogger
     */
    private $eventLogger;

    public function __construct(
        string $jsonSchemaFile,
        JsonSchemaValidator $jsonSchemaValidator,
        EventLogger $eventLogger
    ) {
        parent::__construct($jsonSchemaFile, $jsonSchemaValidator);
        $this->eventLogger = $eventLogger;
    }

    /**
     * @param object $command
     * @param null $transaction
     */
    protected function execute($args): void
    {
        ($this->eventLogger)($args);
    }
}
