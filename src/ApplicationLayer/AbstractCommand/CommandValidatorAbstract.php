<?php

namespace App\ApplicationLayer\AbstractCommand;

abstract class CommandValidatorAbstract implements CommandValidatorInterface
{
    abstract public function getCommandType(): string;

    public function validate($command): CommandValidationResult
    {
        return $this->validateCommandProps($command);
    }

    abstract protected function validateCommandProps($command): CommandValidationResult;
}
