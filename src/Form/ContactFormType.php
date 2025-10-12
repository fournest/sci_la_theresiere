<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Entity\Contact;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;



class ContactFormType extends AbstractType
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('objet', TextType::class, [
                'label' => 'Objet du message',
                'constraints' => [
                    new NotBlank(['message' => "Veuillez saisir un objet"]),
                    new Length(['min' => 5, 'minMessage' => 'L\'objet doit contenir au moins {{ limit }} caractères']),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'rows' => 10,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un message",
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                     'label' => 'Conditions d\'utilisations :',
                    'mapped' => false,
                    'constraints' => [
                        new IsTrue([
                            'message' => 'Vous devez accepter que vos données soient traitées pour que nous puissions vous répondre.'
                        ]),
                    ],
                    'label_html' => true,
                    'label' => 'J\'autorise ce site à utiliser mes données (Nom et Email)pour me recontacter suite à ma demande.(Voir notre <a href="' . $this->router->generate('app_politique_confidentialite', [], UrlGeneratorInterface::ABSOLUTE_PATH).'"target="_blank">Politique de Confidentialité</a>.',
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
