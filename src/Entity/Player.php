<?php

namespace App\Entity;

use App\Entity\Listener\PlayerListener;
use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
#[ORM\EntityListeners([PlayerListener::class])]
#[ORM\HasLifecycleCallbacks]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\Regex(
            pattern: "/\&/i",
            match: false,
            message: "The name cannot contain the & character: {{ value }}"
        )
    ]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $identifier = null;

    #[
        ORM\OneToMany(
            mappedBy: "player",
            targetEntity: TournamentResults::class,
            cascade: ["persist"]
        )
    ]
    private Collection $tournamentResults;

    public function __construct() {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @return Collection<int, TournamentResults>
     */
    public function getTournamentResults(): Collection
    {
        return $this->tournamentResults;
    }

    public function getTotalPointsByChampionship(
        ?Championship $championship = null
    ): int {
        $totalPoints = 0;
        foreach ($this->tournamentResults as $tournamentResult) {
            if (
                $tournamentResult->getTournament()?->getChampionship() !==
                $championship
            ) {
                continue;
            }
            $totalPoints += $tournamentResult->getPoints();
        }
        return $totalPoints;
    }

    public function getTotalPoints(): int
    {
        $totalPoints = 0;
        foreach ($this->tournamentResults as $tournamentResult) {
            $totalPoints += $tournamentResult->getPoints();
        }
        return $totalPoints;
    }

    public function addTournamentResult(
        TournamentResults $tournamentResult
    ): static {
        if (!$this->tournamentResults->contains($tournamentResult)) {
            $this->tournamentResults->add($tournamentResult);
            $tournamentResult->setPlayer($this);
        }

        return $this;
    }

    public function removeTournamentResult(
        TournamentResults $tournamentResult
    ): static {
        if ($this->tournamentResults->removeElement($tournamentResult)) {
            // set the owning side to null (unless already changed)
            if ($tournamentResult->getPlayer() === $this) {
                $tournamentResult->setPlayer(null);
            }
        }

        return $this;
    }

    public function setIdentifier(string $identifier): Player
    {
        $this->identifier = $identifier;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getIdentifier();
    }
}
