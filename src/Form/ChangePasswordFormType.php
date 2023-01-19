<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le mot de passe est obligatoire.',
                        ]),
                        new Length([
                            'min' => 12,
                            'max' => 4000,
                            'minMessage' => 'Le mot de passe doit contenir au minimum {{ limit }} caractères.',
                            'maxMessage' => 'Le mot de passe doit contenir au maximum {{ limit }} caractères.',
                            // max length allowed by Symfony for security reasons
                        ]),
                        new Regex([
                            'pattern' => '#^(?=.*[a-zà-ÿ])(?=.*[A-ZÀ-Ỳ])(?=.*[0-9])(?=.*[^a-zà-ÿA-ZÀ-Ỳ0-9]).{12,4000}$#',
                            'match' => true,
                            'message' => "Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un caractère spécial et un chiffre.",
                        ]),
                        new NotCompromisedPassword([
                            'message' => "Ce mot de passe est facilement piratable! Veuillez en choisir un autre.",
                        ]),
                        new UserPassword([
                            'message' => 'Le mot de passe actuel est invalide.',
                        ])

                    ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le mot de passe est obligatoire.',
                        ]),
                        new Length([
                            'min' => 12,
                            'max' => 4000,
                            'minMessage' => 'Le mot de passe doit contenir au minimum {{ limit }} caractères.',
                            'maxMessage' => 'Le mot de passe doit contenir au maximum {{ limit }} caractères.',
                            // max length allowed by Symfony for security reasons
                        ]),
                        new Regex([
                            'pattern' => '#^(?=.*[a-zà-ÿ])(?=.*[A-ZÀ-Ỳ])(?=.*[0-9])(?=.*[^a-zà-ÿA-ZÀ-Ỳ0-9]).{12,4000}$#',
                            'match' => true,
                            'message' => "Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule, un caractère spécial et un chiffre.",
                        ]),
                        new NotCompromisedPassword([
                            'message' => "Ce mot de passe est facilement piratable! Veuillez en choisir un autre.",
                        ])
                    ],
                ],
                'invalid_message' => 'Le mot de passe doit être identique à sa confirmation.',
                // Instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "required_current_password" => false
        ]);
    }
}
