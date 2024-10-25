<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class TokenSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        $protectedRoutes = ['/admin', '/dashboard', '/register'];

        if (in_array($request->getPathInfo(), $protectedRoutes)) {
            if ($session->has('jwt')) {
                $token = $session->get('jwt');

                $tokenData = $this->decodeToken($token);

                if ($this->isTokenExpired($tokenData['exp'])) {
                    $event->setResponse(new RedirectResponse($this->router->generate('logout')));
                }
            } else {
                $event->setResponse(new RedirectResponse($this->router->generate('logout')));
            }
        }
    }

    private function isTokenExpired($expiration): bool
    {
        return $expiration < time();
    }

    private function decodeToken($token)
    {
        return json_decode(base64_decode(str_replace('_', '/', str_replace('-','+', explode('.', $token)[1]))), true);
    }
}
