<?php

namespace App\PresentationLayer\MeterEventLoggingBatch;

use App\ApplicationLayer\AbstractCommand\CommandResult;
use App\ApplicationLayer\MeterEventLoggingBatch\CommandHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @var callable
     */
    private $commandArgsFactory;

    /**
     * @var string
     */
    private $test;
    /**
     * @var CommandHandler
     */
    private $commandHandler;

    public function __construct(
        CommandArgsFactory $commandArgsFactory,
        CommandHandler $commandHandler
    ) {
        $this->commandArgsFactory = $commandArgsFactory;
        $this->commandHandler = $commandHandler;
    }

    /**
     * @Route("/api/event", methods={"POST"}, name="new_event_batch")
     */
    public function createEventsBatch(
        Request $request
    ): Response {
        try {
            $args = ($this->commandArgsFactory)($request);
        } catch (\JsonException $exception) {
            return new JsonResponse('', Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var CommandResult $result
         */
        $result = ($this->commandHandler)($args);

        $data['success'] = $result
            ->mapOk(
                function ($value) {
                    return $value
                        ? ['body' => $value, 'status' => Response::HTTP_OK]
                        : null;
                })
            ->getValue();

        $data['errors'] = $result
            ->mapFail(function ($errors) { return $errors ?: []; })
            ->getValue();

        $status = Response::HTTP_OK;
        if (!empty($data['errors'])) {
            $status = Response::HTTP_MULTI_STATUS;
        }

        return new JsonResponse($data, $status);
    }
}
