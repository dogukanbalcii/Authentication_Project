<?php

namespace App\Services\Auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationService
{
    public function registerUser(User $user, FormInterface $form, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): bool
    {
        $plainPassword = $form->get('plainPassword')->getData();
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        return true;
    }

    public function validateRegistrationForm(FormInterface $form, array &$errors): bool
    {
        $email = $form->get('email')->getData();
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid email address.';
            return false;
        }

        $plainPassword = $form->get('plainPassword')->getData();
        if (strlen($plainPassword) < 6 ||
            !preg_match('/[a-zA-Z]/', $plainPassword) ||  // least one character
            !preg_match('/[0-9]/', $plainPassword)) {     // least one number
            $errors[] = 'Password must be at least 6 characters long and contain both letters and numbers.';
            return false;
        }

        $selectedRole = $form->get('roles')->getData();
        if (empty($selectedRole)) {
            $errors[] = 'You must select a role (User, Admin, or Super Admin).';
            return false;
        }

        if (!$form->get('agreeTerms')->getData()) {
            $errors[] = 'You must agree to the terms.';
            return false;
        }

        return true; // If all validations are true
    }
}
