<?php

namespace App\Tests\MeterEventLogging;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BatchCreationEventsApiFailTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    public function testEventTypeNotFound(): void
    {
        $events = [];
        $companyId = 10;
        $eventDateTimeOnClient = (new \DateTime())->format('Y-m-d H:i:s');
        $mSec = 100;
        $type = "unknownType";

        $events[$type] = [
            "clientTimezone" => "Europe/Moscow",
            "sourceHostName" => "spb.yp.ru",
            "sourcePathName" => "some/path",
            "companyId" => $companyId,
            "eventDateTimeOnClient" => $eventDateTimeOnClient.".".($mSec+=10),
            "type" => $type,
            $type => 1
        ];

        $this->client->restart();
        $this->client->jsonRequest(
            'POST',
            "/api/event",
            array_values($events)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_MULTI_STATUS);

        $response = $this->client->getResponse();
        $expectedResponse = [
            'success' => null,
            'errors' => [[['type' => ["Event with type {$type} not found"]]]],
        ];

        $this->assertEquals(json_encode($expectedResponse), $response->getContent());
    }

    public function testEventTypeSchemaFileNotFound(): void
    {
        $events = [];
        $companyId = 10;
        $eventDateTimeOnClient = (new \DateTime())->format('Y-m-d H:i:s');
        $mSec = 100;
        $type = "someEventType";

        $events[$type] = [
            "clientTimezone" => "Europe/Moscow",
            "sourceHostName" => "spb.yp.ru",
            "sourcePathName" => "some/path",
            "companyId" => $companyId,
            "eventDateTimeOnClient" => $eventDateTimeOnClient.".".($mSec+=10),
            "type" => $type,
            $type => 1
        ];

        $this->client->restart();
        $this->client->jsonRequest(
            'POST',
            "/api/event",
            array_values($events)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_MULTI_STATUS);

        $response = $this->client->getResponse();
        $expectedResponse = [
            'success' => null,
            'errors' => [[["type" => ["Event with type someEventType not found"]]]],
        ];

        $this->assertEquals(json_encode($expectedResponse), $response->getContent());
    }

    protected function setUp(): void
    {
        $this->client = static::createClient(
            [],
            [
                'HTTP_USER_AGENT' => 'SymfonyClient',
                'REMOTE_ADDR' => '127.0.0.1',
            ]
        );

        parent::setUp();
    }
}
