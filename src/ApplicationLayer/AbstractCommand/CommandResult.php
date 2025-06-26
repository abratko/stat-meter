<?php

namespace App\ApplicationLayer\AbstractCommand;

class CommandResult
{
    public const FAIL = 'fail';
    public const OK = 'ok';
    public const PARTIAL_OK = 'partial_ok';

    protected $value = null;
    protected $error = null;
    protected $state = self::OK;

    protected function __construct($value = null)
    {
        $this->value = $value;
    }

    public static function ok($value = null): self
    {
        return new self($value);
    }

    public static function partialOk($error, $okValue = null): self
    {
        $instance = new self($okValue);
        $instance->error = $error;
        $instance->state = self::PARTIAL_OK;

        return $instance;
    }

    public static function fail($error): self
    {
        $instance = new self();
        $instance->error = $error;
        $instance->state = self::FAIL;

        return $instance;
    }

    public function isOk(): bool
    {
        return self::OK === $this->state;
    }

    public function isFail(): bool
    {
        return self::FAIL === $this->state;
    }

    public function mapOk(callable $okHandler): self
    {
        if ($this->isFail()) {
            return self::ok($this->value);
        }

        return self::ok($okHandler($this->value));
    }

    public function mapFail(callable $failHandler = null): self
    {
        if ($this->isOk()) {
            return self::fail($this->error);
        }

        return self::fail($failHandler($this->error));
    }

    /**
     * @return null
     */
    public function getValue()
    {
        return $this->isOk() ? $this->value : $this->error;
    }
}
