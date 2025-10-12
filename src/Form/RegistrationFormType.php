<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class RegistrationFormType extends AbstractType
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
         // CHAMP : NOM

            ->add('nom', null, [
                'label' => 'Nom :',
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un nom",
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre nom doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])

             // CHAMP : PRÉNOM

            ->add('prenom', null, [
                'label' => 'Prénom :',
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un prénom",
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre prénom doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])

            // CHAMP : PSEUDONYME

            ->add('pseudo', null, [
                'label' => 'Pseudo :',
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un pseudonyme",
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre pseudonyme doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
           
            // CHAMP : ADRESSE MAIL

            ->add('email', EmailType::class, [
                'label' => 'Email :',
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir une adresse mail",
                    ]),
                    new Regex([
                        // Utilise une expression régulière pour valider le format de l'email.
                        'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                        'message' => "L'adresse mail indiquée est invalide",
                    ]),
                ],
            ])

            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => 'Mot de passe :',
                 // Aide les navigateurs à suggérer un nouveau mot de passe.
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moin {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
                ])
                ->add('agreeTerms', CheckboxType::class, [
                     'label' => 'Conditions d\'utilisations :',
                    'mapped' => false,
                    'constraints' => [
                        new IsTrue([
                            'label_html' => true,
                            'label' => 'J\'accepte les <a href"'. $this->router->generate('app_mentions_legales').'"target="_blank">Mentions Légales</a> et la <a href="' . $this->router->generate('app_politique_confidentialite').'"target="_blank">Politique de Confidentialité</a>.',
                        ]),
                    ],
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
