<?php

namespace App\ApplicationLayer\AbstractCommand\Exception;

use App\ApplicationLayer\AbstractCommand\CommandValidationResult;

class CommandValidationException extends CommandException
{
    protected $validationResul;

    /**
     * @var \App\ApplicationLayer\AbstractCommand\CommandValidationResult
     */
    private $validationResult;

    public function __construct(CommandValidationResult $validationResult)
    {
        parent::__construct('Command args is not valid');
        $this->validationResult = $validationResult;
    }

    public function getValidationResult()
    {
        return $this->validationResult;
    }
}
