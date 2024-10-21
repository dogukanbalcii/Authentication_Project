<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        $jwt = $this->jwtManager->create($user);

        $user->setToken($jwt);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new RedirectResponse($this->router->generate('app_default'));
    }
}