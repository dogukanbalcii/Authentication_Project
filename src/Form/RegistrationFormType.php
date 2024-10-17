<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ]);

        $roles = [];
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $roles = [
                'User' => 'ROLE_USER',
                'Admin' => 'ROLE_ADMIN',
                'Super Admin' => 'ROLE_SUPER_ADMIN',
            ];
        } elseif ($this->security->isGranted('ROLE_ADMIN')) {
            $roles = [
                'User' => 'ROLE_USER',
                'Admin' => 'ROLE_ADMIN',
                'Super Admin' => 'ROLE_SUPER_ADMIN',
            ];
        } else {
            $roles = [
                'User' => 'ROLE_USER',
                'Admin' => 'ROLE_ADMIN',
            ];
        }


        $builder->add('roles', ChoiceType::class, [
            'choices' => $roles,
            'expanded' => true,
            'multiple' => true,
            'label' => 'Select Role',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'register_item',
        ]);
    }
}
