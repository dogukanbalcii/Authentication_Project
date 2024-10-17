<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $user->getEmail();
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Please provide a valid email address.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            $plainPassword = $form->get('plainPassword')->getData();
            if (strlen($plainPassword) < 6 ||
                !preg_match('/[a-zA-Z]/', $plainPassword) ||  // En az bir harf
                !preg_match('/[0-9]/', $plainPassword)) {     // En az bir rakam
                $this->addFlash('error', 'Password must be at least 6 characters long and contain both letters and numbers.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            $selectedRole = $form->get('roles')->getData(); // 'roles' alanının ismi burada belirtilmeli
            if (empty($selectedRole)) {
                $this->addFlash('error', 'You must select a role (User, Admin, or Super Admin).');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            if (!$form->get('agreeTerms')->getData()) {
                $this->addFlash('error', 'You must agree 1231231212 to the terms.');
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form,
                ]);
            }

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
