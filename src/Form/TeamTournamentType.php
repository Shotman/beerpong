<?php

namespace App\Form;

use App\Entity\Player;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamTournamentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teams',CollectionType::class,[
                'entry_type' => TeamType::class,
                'entry_options' => ['label' => false, 'allow_delete' => true, "tournament" => $options["tournament"]],
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
                'label' => 'btn.start',
                'attr' => [
                    'class' => 'btn btn-success',
                ]
            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (PreSubmitEvent $event) use ($options) {
            $data = $event->getData();
            $em = $options["em"];
            if (!$data) {
                return;
            }
            $teams = $data['teams'];
            foreach ($teams as $key => $team) {
                $player1 = $em->getRepository(Player::class)->findOneByIdentifier($team['player1']);
                $player2 = $em->getRepository(Player::class)->findOneByIdentifier($team['player2']);
                if(is_null($player1)){
                    $player1 = new Player();
                    $player1->setName($team['player1']);
                    $em->persist($player1);
                    $em->flush();
                }
                if(is_null($player2)){
                    $player2 = new Player();
                    $player2->setName($team['player2']);
                    $em->persist($player2);
                    $em->flush();
                }
                $data['teams'][$key]['player1'] = $player1->getIdentifier();
                $data['teams'][$key]['player2'] = $player2->getIdentifier();
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ])->setRequired('em')->setRequired("tournament");
    }
}
