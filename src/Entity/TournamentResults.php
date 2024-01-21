<?php

namespace App\Entity;

use App\Repository\TournamentResultsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TournamentResultsRepository::class)]
class TournamentResults
{
    #[ORM\ManyToOne(cascade: ['persist'], fetch: 'EAGER', inversedBy: 'tournamentResults')]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\Id]
    private Tournament $tournament;

    #[ORM\ManyToOne(cascade: ['persist'], fetch: 'EAGER', inversedBy: 'tournamentResults')]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\Id]
    private Player $player;

    #[ORM\Column]
    private ?int $points = null;

    #[ORM\Column]
    private ?int $rank = null;

    public function getTournament(): Tournament
    {
        return $this->tournament;
    }

    public function setTournament(Tournament $tournament): static
    {
        $this->tournament = $tournament;

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }
}
