<?php

namespace App\PresentationLayer\Subscriber;

use App\PresentationLayer\MeterEventLogging\ApiController;
use App\PresentationLayer\TokenValidator\TokenValidatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    private $tokenValidator;

    public function __construct(TokenValidatorInterface $tokenValidator)
    {
        $this->tokenValidator = $tokenValidator;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        $request = $event->getRequest();
        if ($controller instanceof ApiController) {
            $token = $event->getRequest()->query->get('token', '');
            if (!$this->tokenValidator->isTokenValid($token, $request)) {
                throw new AccessDeniedHttpException('This action needs a valid token!');
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
