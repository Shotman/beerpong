<?php

namespace App\Structs;

use App\Entity\Player;
use App\Repository\PlayerRepository;

class Team
{
    public function __construct(public Player|string $player1,public Player|string|null $player2, public ?PlayerRepository $playerRepository = null)
    {
        if (is_string($player1)) {
            $this->player1 = $this->playerRepository?->findOneBy(['name' => $player1]) ?? new Player();
            if(!$this->player1->getId()){
                $this->player1->setName($player1);
                $this->playerRepository?->save($this->player1);
            }


        }
        if (is_string($player2)) {
            $this->player2 = $this->playerRepository?->findOneBy(['name' => $player2]) ?? new Player();
            if(!$this->player2->getId()){
                $this->player2->setName($player2);
                $this->playerRepository?->save($this->player2);
            }
        }
    }

    public function getTeamName(): string
    {
        $player1Name = $this->player1->getName();
        $player2Name = $this->player2?->getName();
        if ($player2Name) {
            return $player1Name . " & " . $player2Name;
        }
        return $player1Name;
    }

}