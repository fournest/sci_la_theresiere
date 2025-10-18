<?php

namespace App\Form;


use App\Entity\Option;
use App\Entity\Reservation;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\IsTrue;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateResaDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début de réservation',
                'attr' => ['class' => 'js-datepicker'],
            ])
            ->add('dateResaFin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin de réservation',
                'attr' => ['class' => 'js-datepicker'],
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
                'label' => 'Catégories',
            ])
            ->add('options', EntityType::class, [
                'class' => Option::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'label' => 'Options (Maintenez Ctrl ou Cmd pour sélectionner plusieurs)',

            ])

            ->add('accepterConditions', CheckboxType::class, [
                'label' => 'J\'accepte les conditions',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions générales de location et la politique de confidentialité pour continuer'
                    ]),
                ],
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
