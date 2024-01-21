<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
        $resolver->setDefined('allow_delete');
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder->add('player1',EntityType::class,[
            'class' => 'App\Entity\Player',
            'choice_value' => 'name', // default is 'id
            'choice_label' => 'name',
            'allow_extra_fields' => true,
            'label' => 'Player 1',
            'placeholder' => 'Select a player',
            'required' => true,
            'autocomplete' => true,
            'by_reference' => false,
            'tom_select_options' => [
                'create' => true,
                'createOnBlur' => true
            ]
        ])
        ->add('player2',EntityType::class,[
            'class' => 'App\Entity\Player',
            'choice_value' => 'name', // default is 'id
            'choice_label' => 'name',
            'allow_extra_fields' => true,
            'label' => 'Player 2',
            'placeholder' => 'Select a player',
            'required' => false,
            'by_reference' => false,
            'autocomplete' => true,
            'tom_select_options' => [
                'create' => true,
                'createOnBlur' => true,
            ]
        ])
        ;
        if($options['allow_delete']){
            $builder->add('delete',ButtonType::class,[
                'label' => 'Delete',
                'attr' => [
                    'class' => 'btn btn-danger stimulus-delete',
                ]
            ]);
        }
    }
}