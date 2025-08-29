<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Option;
use App\Entity\Reservation;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'pseudo',
                'label' => 'Votre pseudo',
            ])
            ->add('dateResaDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début de réservation',
            ])
            ->add('dateResaFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin de réservation',
            ])
            ->add('dossierResa', null, [
                'label' => 'Numéro de dossier de réservation',
            ])
            ->add('acompte', CheckboxType::class, [
                'label' => 'Acompte versé ?',
                'required' => false,
            ])
            ->add('caution', CheckboxType::class, [
                'label' => 'Caution versé ?',
                'required' => false,
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'multiple' => false,
                // 'expanded' => true,
                'label' => 'Catégories',
            ])
            ->add('options', EntityType::class, [
                'class' => Option::class,
                'choice_label' => 'nom',
                'multiple' => true,
                // 'expanded' => true,
                'label' => 'Options',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
