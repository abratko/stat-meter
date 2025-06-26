<?php

namespace App\ApplicationLayer\AbstractCommand;

interface CommandValidatorInterface
{
    public function validate($command): CommandValidationResult;
}
