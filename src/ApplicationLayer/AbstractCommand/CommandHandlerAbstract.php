<?php

namespace App\ApplicationLayer\AbstractCommand;

// use App\ApplicationLayer\AbstractCommand\Exception\CommandArgValidationException;
use App\ApplicationLayer\AbstractCommand\Exception\CommandValidationException;

abstract class CommandHandlerAbstract implements CommandHandlerInterface
{
    /**
     * @var \App\ApplicationLayer\AbstractCommand\CommandValidatorAbstract
     */
    private $validator;

    public function __construct(
        CommandValidatorAbstract $validator = null
    ) {
        $this->validator = $validator;
    }

    public function getValidator(): ?CommandValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @param object $command
     * @param null   $transaction
     *
     * @return mixed
     *
     * @throws \App\ApplicationLayer\AbstractCommand\Exception\CommandValidationException
     */
    public function handle($command, $transaction = null)
    {
        $validationResult = $this->validate($command);
        if ($validationResult->hasErrors()) {
            throw new CommandValidationException($validationResult);
        }

        return $this->execute($command);
    }

    public function hasValidator(): bool
    {
        return (bool) $this->validator;
    }

    /**
     * @param object $command
     *
     * @throws \App\ApplicationLayer\AbstractCommand\Exception\CommandValidationException
     */
    protected function validate($command): CommandValidationResult
    {
        if (!$this->validator) {
            return new CommandValidationResult($command);
        }

        return $this->validator->validate($command);
    }

    abstract protected function execute($command);
}
