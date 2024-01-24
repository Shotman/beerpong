<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamTournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teams',CollectionType::class,[
                'entry_type' => TeamType::class,
                'entry_options' => ['label' => false, 'allow_delete' => true],
                "row_attr" => [
                    "class" => "col-md-6"
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'label' => false,
            ])
            ->add('save',SubmitType::class,[
                'translation_domain' => 'messages', // optional
                'label' => 'btn.save',
                'attr' => [
                    'class' => 'btn btn-success',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
