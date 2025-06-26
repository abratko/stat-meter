<?php

namespace App\ApplicationLayer\AbstractCommand;

interface CommandHandlerInterface
{
    /**
     * @param $command
     * @param null $transaction
     */
    public function handle($command, $transaction = null);
}
