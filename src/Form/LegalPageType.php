<?php

namespace App\Form;

use App\Entity\LegalPage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LegalPageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $legalPage = $options['data'];
        $isNew = $legalPage && $legalPage->getId() === null;

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du document',
                'attr' => ['placeholder' => 'Ex: Mentions Légales']
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug (Identifiant URL)',
                'help' => 'Ex: mentions-legales. Ne peut être modifié qu\'à la création.',
                'attr' => ['readonly' => !$isNew, 'class' => $isNew ? '' :  'form-control-disabled']
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu du document (HTML autorisé)',
                'attr' => ['rows' => 20]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LegalPage::class,
        ]);
    }
}
