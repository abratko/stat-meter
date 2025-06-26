<?php

namespace App\Tests\MeterEventLogging;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

class BatchCreationEventsApiSuccessTest extends WebTestCase
{
    /**
     * @var string
     */
    private $rootDir;
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    private $eventTypes = [
        "showPhone" => "universalEventFactory",
        "showSite" => "universalEventFactory",
        "showCompanyPage" => "universalEventFactory",
        "visitSiteFromCompanyCard" => "universalEventFactory",
        "visitSiteFromCompanyPage" => "universalEventFactory",
        "showCompanyCardInSearchList" => "showCompanyCardInSearchListEventFactory",
        "showCompanyCardInRubricList" => "showCompanyCardInRubricListEventFactory",
    ];


    private $generalProperties = [
        "clientTimezone" => "Europe/Moscow",
        "sourceHostName" => "spb.yp.ru",
        "sourcePathName" => "some/path",
    ];

    /**
     * @return void
     */
    public function testEventsBatch(): void
    {
        $events = [];
        $companyId = 10;
        $eventDateTimeOnClient = (new \DateTime())->format('Y-m-d H:i:s');
        $mSec = 100;
        foreach ($this->eventTypes as $type => $factory) {
            $events[$type] = $this->$factory($type, $companyId++, $mSec += 10);
        }

        $this->client->restart();
        $this->client->jsonRequest(
            'POST',
            "/api/event",
            array_values($events)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $filesystem = new Filesystem();
        $dateWithTime = (new \DateTime('now'))->format('Y-m-d H:i');

        $files = [];

        foreach ($events as $event) {
            $filePath = "{$this->rootDir}/{$event['type']}.log";
            $this->assertTrue($filesystem->exists($filePath), "File {$filePath} not found");

            if (empty($files[$filePath])) {
                $files[$filePath] = new \SplFileObject($filePath, 'r');
            }

            $file = $files[$filePath];
            $line = trim($file->fgets());
            if (empty($line)) {
                continue;
            }
            $lineAsArray = json_decode($line, true);

            $this->assertEquals('SymfonyClient', $lineAsArray['userAgent'], 'Invalid userAgent' );
            $this->assertEquals('127.0.0.1', $lineAsArray['clientIp'], 'Isnvalid clientApi');
            $this->assertStringStartsWith($dateWithTime, $lineAsArray['eventReceivingDateTimeOnServer']);
            $this->assertStringStartsWith($eventDateTimeOnClient, $lineAsArray['eventDateTimeOnClient']);

            unset(
                $lineAsArray['userAgent'],
                $lineAsArray['clientIp'],
                $lineAsArray['eventReceivingDateTimeOnServer'],
                $event['type'],
            );

            $this->assertEquals($event, $lineAsArray);
        }
    }

    protected function universalEventFactory(
        string $type,
        int $companyId,
        int $mSec
    ): array {
        $eventDateTimeOnClient = (new \DateTime())->format('Y-m-d H:i:s');

        return $this->generalProperties + [
                "companyId" => $companyId,
                "eventDateTimeOnClient" => $eventDateTimeOnClient.".".$mSec,
                "type" => $type,
                $type => 1,
            ];
    }

    protected function showCompanyCardInSearchListEventFactory(
        string $type,
        int $companyId,
        int $mSec
    ): array {
        $eventDateTimeOnClient = (new \DateTime())->format('Y-m-d H:i:s');

        return $this->generalProperties + [
                "companyId" => $companyId,
                "eventDateTimeOnClient" => $eventDateTimeOnClient.".".$mSec,
                "type" => "showCompanyCardInList",
                "showCompanyCardInList" => 1,
                "listType" => "search",
            ];
    }

    protected function showCompanyCardInRubricListEventFactory(
        string $type,
        int $companyId,
        int $mSec
    ): array {
        $eventDateTimeOnClient = (new \DateTime())->format('Y-m-d H:i:s');

        return $this->generalProperties + [
                "companyId" => $companyId,
                "eventDateTimeOnClient" => $eventDateTimeOnClient.".".$mSec,
                "type" => "showCompanyCardInList",
                "showCompanyCardInList" => 1,
                "listType" => "rubric",
                "rubricId" => "1000080",
            ];
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

        $container = $this->client->getContainer();

        $date = (new \DateTime('now'))->format('Y-m-d');
        $this->rootDir = "{$container->getParameter('stats_meter.logs_dir')}/$date";

        $this->filesystem = new Filesystem();
        if ($this->filesystem->exists($this->rootDir)) {
            $this->filesystem->remove($this->rootDir);
        }

        parent::setUp(); // TODO: Change the autogenerated stub
    }

    protected function tearDown(): void
    {
        $this->filesystem = new Filesystem();
        if ($this->filesystem->exists($this->rootDir)) {
            $this->filesystem->remove($this->rootDir);
        }

        parent::tearDown(); // TODO: Change the autogenerated stub
    }
}
