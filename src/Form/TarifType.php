<?php

namespace App\Form;

use App\Entity\Tarif;
use App\Entity\Categorie;
use App\Entity\Option;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TarifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, ['label' => 'Nom du tarif'])
            ->add('montant', MoneyType::class, ['label' => 'Prix HT', 'currency' => 'EUR'])
            
            // 1er menu : Le choix du type
            ->add('type_selection', ChoiceType::class, [
                'label' => 'Que voulez-vous tarifer ?',
                'choices' => [
                    'Un type d\'événement (Catégorie)' => 'type_categorie',
                    'Une option (Service)' => 'type_option',
                ],
                'mapped' => false, // Ce champ ne va pas en BDD
                'placeholder' => '-- Choisir --',
                'attr' => ['class' => 'select-type-tarif']
            ])

            // 2ème menu (A) : Les catégories
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Sélectionnez l\'événement',
                'placeholder' => '-- Choisir une catégorie --',
                'required' => false,
                'row_attr' => ['id' => 'row-categorie', 'style' => 'display:none;'] // Caché par défaut
            ])

            // 2ème menu (B) : Les options
            ->add('option', EntityType::class, [
                'class' => Option::class,
                'choice_label' => 'nom',
                'label' => 'Sélectionnez l\'option',
                'placeholder' => '-- Choisir une option --',
                'required' => false,
                'row_attr' => ['id' => 'row-option', 'style' => 'display:none;'] // Caché par défaut
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Tarif::class]);
    }
}