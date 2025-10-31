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
        $privacyPolicyUrl = $this->router->generate('app_legal_page_show', ['slug' => 'politique-de-confidentialite']); 
        
        // 2. Création du libellé HTML avec le lien
        $consentLabel = sprintf(
            'J\'ai lu et j\'accepte la <a href="%s" target="_blank" rel="noopener noreferrer" class="link-privacy">Politique de Confidentialité</a>.',
            $privacyPolicyUrl
        );
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
            ->add('consentementConfidentialite', CheckboxType::class, [
                'label' => $consentLabel,
                'label_html' => true,
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter que vos données soient traitées pour nous contacter.'
                    ]),
                ],

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
