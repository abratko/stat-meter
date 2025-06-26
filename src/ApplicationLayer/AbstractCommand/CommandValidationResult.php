<?php

namespace App\ApplicationLayer\AbstractCommand;

class CommandValidationResult
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var object
     */
    private $command;

    public function __construct($command, \ArrayAccess $errors = null)
    {
        $this->command = $command;
        $this->errors = $errors;
    }

    public function addError($fieldName, $message): self
    {
        if (!isset($this->errors[$fieldName])) {
            $this->errors[$fieldName] = [];
        }
        $this->errors[$fieldName][] = $message;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getCommand(): object
    {
        return $this->command;
    }

    public function size(): int
    {
        return count($this->errors);
    }
}
