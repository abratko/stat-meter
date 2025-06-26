<?php

namespace App\PresentationLayer\MeterEventLoggingBatch;

use App\ApplicationLayer\MeterEventLoggingBatch\ArgsValueObjInterface;

class ArgsValueObj implements ArgsValueObjInterface
{
    /**
     * @var array
     */
    private $generalProps;
    /**
     * @var array
     */
    private $events;

    public function __construct(array $generalProps, array $events)
    {
        $this->generalProps = $generalProps;
        $this->events = $events;
    }

    public function getGeneralProps(): array
    {
        return $this->generalProps;
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
