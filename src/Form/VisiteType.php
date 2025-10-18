<?php

namespace App\Form;

use App\Entity\Visite;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class VisiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateVisite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de visite',
                'attr' => ['class' => 'js-datepicker'],

            ])
            ->add('dateResaSouhaite', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de réservation souhaitée',
                'attr' => ['class' => 'js-datepicker'],

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visite::class,
        ]);
    }
}
