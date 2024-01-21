<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament
{
    use HasExtraData;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $challongeId = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: Championship::class, fetch: 'EAGER', inversedBy: 'tournaments')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Championship $championship = null;

    #[ORM\OneToMany(mappedBy: 'tournament', targetEntity: TournamentResults::class, cascade: ['persist','remove'], fetch: 'EAGER')]
    private Collection $tournamentResults;

    public function __construct()
    {
        $this->tournamentResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getChampionship(): ?Championship
    {
        return $this->championship;
    }

    public function setChampionship(?Championship $championship): static
    {
        $this->championship = $championship;

        return $this;
    }

    /**
     * @return Collection<int, TournamentResults>
     */
    public function getTournamentResults(): Collection
    {
        return $this->tournamentResults;
    }

    public function addTournamentResult(TournamentResults $tournamentResult): static
    {
        if (!$this->tournamentResults->contains($tournamentResult)) {
            $this->tournamentResults->add($tournamentResult);
            $tournamentResult->setTournament($this);
        }

        return $this;
    }

    public function removeTournamentResult(TournamentResults $tournamentResult): static
    {
        if ($this->tournamentResults->removeElement($tournamentResult)) {
            // set the owning side to null (unless already changed)
            if ($tournamentResult->getTournament() === $this) {
                $tournamentResult->setTournament(null);
            }
        }

        return $this;
    }

    public function getChallongeId(): ?string
    {
        return $this->challongeId;
    }

    public function setChallongeId(string $challongeId): self
    {
        $this->challongeId = $challongeId;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
