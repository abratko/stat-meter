<?php

declare(strict_types=1);

namespace App\ApplicationLayer\MeterEventLoggingBatch;

class EventLoggerBatchFactory
{
    /**
     * @var string
     */
    private $logDir;

    public function __construct(string $logDir)
    {
        $this->logDir = $logDir;
    }

    public function __invoke(): EventLoggerBatch
    {
        return new EventLoggerBatch($this->logDir);
    }
}
