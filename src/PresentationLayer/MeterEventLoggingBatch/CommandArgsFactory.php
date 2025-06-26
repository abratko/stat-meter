<?php

declare(strict_types=1);

namespace App\PresentationLayer\MeterEventLoggingBatch;

use App\ApplicationLayer\MeterEventLoggingBatch\ArgsValueObjInterface;
use Symfony\Component\HttpFoundation\Request;

class CommandArgsFactory
{
    public function __invoke(Request $request): object
    {
        return $this->createFromRequest($request);
    }

    /**
     * @throws \JsonException
     */
    public function createFromRequest(Request $request): ArgsValueObjInterface
    {
        $receivingDateTime = \DateTime::createFromFormat('U.u', (string) ($_SERVER['REQUEST_TIME_FLOAT']));

        return new ArgsValueObj(
            [
                'eventReceivingDateTimeOnServer' => $receivingDateTime->format('Y-m-d H:i:s.u'),
                'userAgent' => $request->server->get('HTTP_USER_AGENT', ''),
                'clientIp' => $request->server->get('REMOTE_ADDR', ''),
            ],
            json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR),
        );
    }
}
