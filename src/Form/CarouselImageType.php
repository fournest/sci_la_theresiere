<?php

namespace App\Form;

use App\Entity\CarouselImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType; // <-- Ajout de TextType
use Symfony\Component\Validator\Constraints\File;

class CarouselImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', FileType::class, [
                'label' => 'Image du Carrousel (JPG, PNG)',
                'mapped' => false,
                'required' => false,

                'constraints' => [
                    new File([
                        'maxSize' => '2M', // Taille maximale de 2 Mo
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier image valide (JPEG ou PNG).',
                    ])
                ],
            ])
            
            // AJOUT DU CHAMP CAPTION
            ->add('caption', TextType::class, [
                'label' => 'Légende de l\'image (optionnel)',
                'required' => false,
                'attr' => ['placeholder' => 'Ex: Une vue de la salle au lever du soleil...'],
            ])

            ->add('ordre', null, [
                'label' => 'Ordre d\'affichage',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CarouselImage::class,
        ]);
    }
}