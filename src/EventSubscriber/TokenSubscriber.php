<?php

namespace App\EventSubscriber;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;
    private RefreshTokenManagerInterface $refreshTokenManager;
    private JWTTokenManagerInterface $jwtTokenManager;
    private UserProviderInterface $userProvider;
    private EntityManagerInterface $entityManager;

    public function __construct(RouterInterface $router, RefreshTokenManagerInterface $refreshTokenManager, JWTTokenManagerInterface $jwtTokenManager, UserProviderInterface $userProvider, EntityManagerInterface $entityManager
    ) {
        $this->router = $router;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->jwtTokenManager = $jwtTokenManager;
        $this->userProvider = $userProvider;
        $this->entityManager = $entityManager;
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
                    if ($session->has('refresh_token')) {
                        $refreshToken = $session->get('refresh_token');
                        $refreshTokenObject = $this->refreshTokenManager->get($refreshToken);

                        if ($refreshTokenObject && !$this->isTokenExpired($refreshTokenObject->getValid())) {
                            $user = $this->userProvider->loadUserByIdentifier($tokenData['username']);

                            $newJwt = $this->jwtTokenManager->create($user);

                            $session->set('jwt', $newJwt);
                            $user->setToken($newJwt);

                            $this->entityManager->persist($user);
                            $this->entityManager->flush();

                            return;
                        }
                    }

                    // if refresh token is null or expired
                    $event->setResponse(new RedirectResponse($this->router->generate('logout')));
                }
            } else {
                $event->setResponse(new RedirectResponse($this->router->generate('logout')));
            }
        }
    }

    private function isTokenExpired($expiration): bool
    {
        if ($expiration instanceof DateTime) {
            $expiration = $expiration->getTimestamp();
        }

        return $expiration < time();
    }

    private function decodeToken($token)
    {
        return json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))), true);
    }
}
