<?php

namespace App\ApplicationLayer\AbstractCommand\Json;

use App\ApplicationLayer\AbstractCommand\CommandHandlerAbstract;
use App\ApplicationLayer\AbstractCommand\CommandValidationResult;

abstract class HandlerAbstract extends CommandHandlerAbstract
{
    /**
     * @var JsonSchemaValidator
     */
    private $argsValidator;
    /**
     * @var string
     */
    private $jsonSchemaFile;

    public function __construct(
        string $jsonSchemaFile,
        JsonSchemaValidator $argsValidator
    ) {
        parent::__construct();
        $this->jsonSchemaFile = $jsonSchemaFile;
        $this->argsValidator = $argsValidator;
    }

    /**
     * @param $args
     */
    protected function validate($args): CommandValidationResult
    {
        return $this
            ->argsValidator
            ->validate(
                $args,
                $this->jsonSchemaFile
            );
    }
}
