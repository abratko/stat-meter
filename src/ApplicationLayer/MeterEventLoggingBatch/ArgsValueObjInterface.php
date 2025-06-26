<?php

namespace App\ApplicationLayer\MeterEventLoggingBatch;

interface ArgsValueObjInterface
{
    public function getGeneralProps(): array;

    public function getEvents(): array;
}
