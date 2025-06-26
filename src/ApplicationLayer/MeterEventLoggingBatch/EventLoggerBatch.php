<?php

declare(strict_types=1);

namespace App\ApplicationLayer\MeterEventLoggingBatch;

use Symfony\Component\Finder\Finder;

class EventLoggerBatch
{
    private const EXCLUDE_PROPS = ['type'];

    /**
     * @var array
     */
    private $contentByFiles = [];

    /**
     * @var string
     */
    private $logDir;

    private $size = 0;

    public function __construct(string $logDir)
    {
        $this->logDir = $logDir;
    }

    public function commit($event): self
    {
        $fileUri = $this->buildfilePath($event);
        $serializedEvent = $this->serializeEvent($event);

        $this->contentByFiles[$fileUri] =
            isset($this->contentByFiles[$fileUri])
                ? $this->contentByFiles[$fileUri].$serializedEvent."\n"
                : $serializedEvent."\n";
        ++$this->size;

        return $this;
    }

    protected function buildFilePath($event): string
    {
        $dirName = explode(' ', $event->eventReceivingDateTimeOnServer)[0];
        $logFileDir = "{$this->logDir}/{$dirName}";

        return "{$logFileDir}/{$event->type}.log";
    }

    protected function serializeEvent($event): string
    {
        $row = clone $event;
        foreach (self::EXCLUDE_PROPS as $propName) {
            unset($row->$propName);
        }

        return json_encode($row);
    }

    public function push(): void
    {
        foreach ($this->contentByFiles as $filename => $content) {
            $logFileDir = dirname($filename);
            if (!is_dir($logFileDir)) {
                $this->createDir($logFileDir);
            }

            file_put_contents($filename, $content, FILE_APPEND);
        }
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

    public function getSize(): int
    {
        return $this->size;
    }
}
