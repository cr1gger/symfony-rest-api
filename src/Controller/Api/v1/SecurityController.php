<?php

namespace App\Controller\Api\v1;

use App\Entity\Token;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecurityController extends GeneralController implements EventSubscriberInterface
{
    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $token_repository = $this->getDoctrine()->getRepository(Token::class);
        $controller = $event->getController();
        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if ($controller instanceof SecurityController) {
            $header_token = $request->headers->get('Authorization');
            $token = $token_repository->findOneBy(['token' => $header_token]);
            if (!$token || !$token->isValid())
            {
                $response = $this->json('Invalid token', Response::HTTP_FORBIDDEN);
                $response->send();
                exit;
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}