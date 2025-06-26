<?php

declare(strict_types=1);

namespace App\PresentationLayer\MeterEventLogging;

use Symfony\Component\HttpFoundation\Request;

class CommandArgsFactory
{
    public function __invoke(string $uniqueEventType, Request $request): object
    {
        return $this->createFromRequest($uniqueEventType, $request);
    }

    public function createFromRequest(string $uniqueEventType, Request $request): object
    {
        $event = \json_decode($request->getContent(), true);
        $receivingDateTime = \DateTime::createFromFormat('U.u', (string) ($_SERVER['REQUEST_TIME_FLOAT']));

        return (object) array_merge(
            [
                'type' => $uniqueEventType,
                'eventReceivingDateTimeOnServer' => $receivingDateTime->format('Y-m-d H:i:s.u'),
                'userAgent' => $request->server->get('HTTP_USER_AGENT', ''),
                'clientIp' => $request->server->get('REMOTE_ADDR', ''),
            ],
            $event
        );
    }
}
