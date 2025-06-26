<?php

namespace App\PresentationLayer\MeterEventLogging;

use App\ApplicationLayer\AbstractCommand\Exception\CommandValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @var \Symfony\Component\DependencyInjection\ServiceLocator
     */
    private $locator;
    /**
     * @var callable
     */
    private $commandArgsFactory;

    /**
     * @var string
     */
    private $test;

    public function __construct(
        ServiceLocator $handlerCollection,
        CommandArgsFactory $commandArgsFactory
    ) {
        $this->locator = $handlerCollection;
        $this->commandArgsFactory = $commandArgsFactory;
    }

    /**
     * @Route("/api/event/{uniqueEventType}", methods={"POST"}, name="new_event")
     */
    public function create(
        string $uniqueEventType,
        Request $request
    ): Response {
        if (!$this->locator->has($uniqueEventType)) {
            return new JsonResponse('', Response::HTTP_NOT_FOUND);
        }

        try {
            $args = ($this->commandArgsFactory)($uniqueEventType, $request);
            $handler = ($this->locator)($uniqueEventType);
            $result = $handler->handle($args);
        } catch (CommandValidationException $error) {
            return JsonResponse::fromJsonString(
                json_encode($error->getValidationResult()->getErrors()),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return JsonResponse::fromJsonString($result ?? '', $result ? 200 : 204);
    }

    /**
     * @Route("/api/test", methods={"get"}, name="js_test")
     */
    public function jsTest(Request $request): Response
    {
        $eventDateTime = \DateTime::createFromFormat('U.u', $_SERVER['REQUEST_TIME_FLOAT']);
        $formated = $eventDateTime->format('Y-d-m H:i:s.u');

        return new Response("{$_SERVER['REQUEST_TIME_FLOAT']} \n\n {$formated}");
    }
}
