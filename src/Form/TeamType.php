<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
        $resolver->setDefined("allow_delete")->setRequired("tournament");
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder->add("teamName", TextType::class, [
            "label" => "Nom d'équipe (facultatif)",
            "required" => false,
            "attr" => [
                "placeholder" => "Les joueurs du dimanche",
            ],
        ]);
        $builder
            ->add("player1", EntityType::class, [
                "class" => "App\Entity\Player",
                "choice_value" => "identifier", // default is 'id
                "choice_label" => "name",
                "allow_extra_fields" => true,
                "label" => "tournament.player1",
                "placeholder" => "tournament.selectPlayer",
                "required" => true,
                "autocomplete" => true,
                "by_reference" => false,
                "translation_domain" => "messages",
                "tom_select_options" => [
                    "create" => true,
                    "createOnBlur" => true,
                    "closeAfterSelect" => true,
                ],
                "attr" => [
                    "data-controller" => "custom-autocomplete",
                ],
            ])
            ->add("player2", EntityType::class, [
                "class" => "App\Entity\Player",
                "choice_value" => "identifier", // default is 'id
                "choice_label" => "name",
                "translation_domain" => "messages",
                "allow_extra_fields" => true,
                "label" => "tournament.player2",
                "placeholder" => "tournament.selectPlayer",
                "required" => false,
                "by_reference" => false,
                "autocomplete" => true,
                "tom_select_options" => [
                    "create" => true,
                    "createOnBlur" => true,
                    "closeAfterSelect" => true,
                ],
                "attr" => [
                    "data-controller" => "custom-autocomplete",
                ],
            ]);
        if ($options["tournament"]->isPaid()) {
            $builder->add("paid", CheckboxType::class, [
                "label" => "Participation payée",
                "required" => true,
                "attr" => [
                    "class" => "form-check-input",
                ],
            ]);
        }
        if ($options["allow_delete"]) {
            $builder->add("delete", ButtonType::class, [
                "translation_domain" => "messages",
                "label" => "btn.delete",
                "attr" => [
                    "class" => "btn btn-danger stimulus-delete",
                ],
            ]);
        }
    }
}
