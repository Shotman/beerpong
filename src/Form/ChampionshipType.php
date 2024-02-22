<?php

namespace App\Form;

use App\Entity\Championship;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChampionshipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'championship.name',
                'required' => true,
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('date_start',DateType::class,[
                'label' => 'championship.date_start',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'input date datepicker-input',
                    "autocomplete" => "off",
                ],
            ])
            ->add('date_end',DateType::class,[
                'label' => 'championship.date_end',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'html5' => false,
                'attr' => [
                    'class' => 'input date datepicker-input',
                    "autocomplete" => "off",
                ],
            ])
            ->add("public",CheckboxType::class,[
                'label' => 'Championnat public ?',
                'required' => true,
                'attr' => ['autocomplete' => 'off'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Championship::class,
        ]);
    }
}
