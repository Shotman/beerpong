<?php

namespace App\Form;

use App\Entity\Championship;
use App\Entity\Tournament;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'tournament.name',
                'required' => true,
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('gameName', null, [
                'label' => 'tournament.gameName',
                'required' => true,
                'data' => $options["data"]->getExtraData()["game"] ?? "BeerPong",
                'mapped' => false,
            ])
            ->add('date', DateType::class,[
                'label' => 'tournament.date',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'input date datepicker-input',
                    "autocomplete" => "off",
                ],
            ])
            ->add('championship', EntityType::class, [
                'empty_data' => null,
                'label' => 'tournament.championship',
                'attr' => ['class' => 'form-control form-control-lg'],
                'placeholder' => 'tournament.championship_placeholder',
                'class' => Championship::class,
                'choice_value' => 'id',
                'required' => false,
                'choice_label' => 'name',
                'autocomplete' => true,
            ])
            ->add('paid', CheckboxType::class, [
                'label' => 'Tournois payant ?',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'Tournois public ?',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tournament::class,
        ]);
    }
}
