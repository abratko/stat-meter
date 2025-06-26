<?php

declare(strict_types=1);

namespace App\ApplicationLayer\MeterEventLogging;

use Symfony\Component\Finder\Finder;

class EventLogger
{
    private const EXCLUDE_PROPS = ['type'];
    /**
     * @var string
     */
    private $logDir;

    public function __construct(string $logDir)
    {
        $this->logDir = $logDir;
    }

    public function __invoke($event): void
    {
        $this->write($event);
    }

    protected function write($event): void
    {
        $logFilePath = $this->buildFilePath($event);
        $row = clone $event;
        foreach (self::EXCLUDE_PROPS as $propName) {
            unset($row->$propName);
        }

        file_put_contents($logFilePath, \json_encode($row)."\n", \FILE_APPEND);
    }

    protected function buildFilePath($event): string
    {
        $dirName = explode(' ', $event->eventReceivingDateTimeOnServer)[0];
        $logFileDir = "{$this->logDir}/{$dirName}";
        if (!is_dir($logFileDir)) {
            $this->createDir($logFileDir);
        }

        return "{$logFileDir}/{$event->type}.log";
    }

    protected function createDir($dir): void
    {
        try {
            $created = mkdir($dir, 0777, true);
        } catch (\Exception $exception) {
            if (is_dir($dir)) {
                return;
            }

            throw $exception;
        }

        if (!$created) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }

    public function getFiles(string $date, string $type = ''): \IteratorAggregate
    {
        return (new Finder())->in("{$this->logDir}/$date/")->name(($type ?: '*').'.log')->files();
    }
}
