<?php

namespace App\ApplicationLayer\ExportEventIntoClickHouse;

use App\ApplicationLayer\AbstractCommand\CommandResult;
use App\ApplicationLayer\AbstractCommand\Json\HandlerAbstract as JsonHandlerAbstract;
use App\ApplicationLayer\AbstractCommand\Json\JsonSchemaValidator;
use App\ApplicationLayer\MeterEventLogging\EventLogger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CommandHandler extends JsonHandlerAbstract
{
    private const LOG_TO_TABLE = [
        'tgbClick' => 'CompanyEvent',
        'tgbView' => 'CompanyEvent',
        'showPhone' => 'CompanyEvent',
        'showCompanyPage' => 'CompanyEvent',
        'showSite' => 'CompanyEvent',
        'visitSiteFromCompanyCard' => 'CompanyEvent',
        'visitSiteFromCompanyPage' => 'CompanyEvent',
        'showCompanyCardInList' => 'CompanyEvent',
    ];

    /**
     * @var string
     */
    private $clickHouseCommand;
    /**
     * @var \App\ApplicationLayer\MeterEventLogging\EventLogger
     */
    private $meterEventLogger;

    public function __construct(
        string $clickHouseCommand,
        JsonSchemaValidator $validator,
        EventLogger $meterEventLogger
    ) {
        parent::__construct(__DIR__.'/JsonSchema/ArgsSchema.json', $validator);
        $this->clickHouseCommand = $clickHouseCommand;
        $this->meterEventLogger = $meterEventLogger;
    }

    public function __invoke($args): CommandResult
    {
        return $this->handle($args);
    }

    protected function execShellCommand(string $command): Process
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(3600);
        $process->mustRun();

        return $process;
    }

    protected function execute($args): CommandResult
    {
        try {
            $files = $this->meterEventLogger->getFiles(
                $args->date ?? null,
                $args->type ?? null
            );
        } catch (DirectoryNotFoundException $exception) {
            return CommandResult::fail($exception->getMessage());
        }

        if (0 === $files->count()) {
            return CommandResult::fail('Files for export not found.');
        }

        foreach ($files as $file) {
            $logFileName = $file->getFilenameWithoutExtension();
            $tableName = self::LOG_TO_TABLE[$logFileName] ?? '';

            if (!$tableName) {
                return CommandResult::fail("Undefined destination table for $logFileName");
            }

            $result = $this->exportFromFile($file, $tableName);
            if ($result->isFail()) {
                return $result;
            }

            if ($args->shouldRemoveSourceFile) {
                (new Filesystem())->remove($file->getRealPath());
            }
        }

        return CommandResult::ok();
    }

    protected function exportFromFile(\SplFileInfo $file, string $destinationTableName): CommandResult
    {
        $shellCommand = "cat {$file->getRealPath()} | {$this->clickHouseCommand} --query=\"INSERT INTO $destinationTableName FORMAT JSONEachRow\"";
        try {
            $process = $this->execShellCommand($shellCommand);
        } catch (ProcessFailedException $exception) {
            return CommandResult::fail($exception->getMessage());
        }

        return CommandResult::ok($process->getOutput());
    }
}
