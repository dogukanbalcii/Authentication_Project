<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\RouterInterface;

class JwtSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private JWTTokenManagerInterface $jwtManager;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;
    private RefreshTokenGeneratorInterface $refreshTokenGenerator;
    private RefreshTokenManagerInterface $refreshTokenManager;

    public function __construct(JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager, RouterInterface $router, RefreshTokenGeneratorInterface $refreshTokenGenerator, RefreshTokenManagerInterface $refreshTokenManager)
    {
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->refreshTokenGenerator = $refreshTokenGenerator;
        $this->refreshTokenManager = $refreshTokenManager;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        $jwt = $this->jwtManager->create($user);

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 3600); // TTL IS TIME TO LIVE FOR REFRESH TOKEN

        $this->refreshTokenManager->save($refreshToken);

        $user->setToken($jwt);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $request->getSession()->set('jwt', $jwt);
        $request->getSession()->set('refresh_token', $refreshToken->getRefreshToken());

        return new RedirectResponse($this->router->generate('app_default'));
    }
}
