<?php

namespace App\Entity;

use App\Entity\Trait\ImageTrait;
use App\Entity\Trait\TimestampTrait;
use App\Repository\CompetitionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Competition
{
    use TimestampTrait;

    // used as the logo of the opponent team
    use ImageTrait;

    const UPLOAD_PATH = "uploads/competitions";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $opponentName = null;

    #[ORM\Column]
    private ?string $opponentPoints = null;

    #[ORM\Column]
    private ?string $personalPoints = null;

    #[ORM\Column]
    private ?DateTimeImmutable $eventDate = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $win = null;

    #[ORM\Column]
    private ?float $personalOvers = null;

    #[ORM\Column]
    private ?float $opponentOvers = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpponentName(): ?string
    {
        return $this->opponentName;
    }

    public function setOpponentName(string $opponentName): static
    {
        $this->opponentName = $opponentName;

        return $this;
    }

    public function getOpponentPoints(): ?string
    {
        return $this->opponentPoints;
    }

    public function setOpponentPoints(string $opponentPoints): static
    {
        $this->opponentPoints = $opponentPoints;

        return $this;
    }

    public function getPersonalPoints(): ?string
    {
        return $this->personalPoints;
    }

    public function setPersonalPoints(string $personalPoints): static
    {
        $this->personalPoints = $personalPoints;

        return $this;
    }

    public function getEventDate(): ?DateTimeImmutable
    {
        return $this->eventDate;
    }

    public function setEventDate(DateTimeImmutable $eventDate): static
    {
        $this->eventDate = $eventDate;

        return $this;
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

    public function isWin(): ?bool
    {
        return $this->win;
    }

    public function setWin(bool $win): static
    {
        $this->win = $win;

        return $this;
    }

    public function getPersonalOvers(): ?float
    {
        return $this->personalOvers;
    }

    public function setPersonalOvers(float $personalOvers): static
    {
        $this->personalOvers = $personalOvers;

        return $this;
    }

    public function getOpponentOvers(): ?float
    {
        return $this->opponentOvers;
    }

    public function setOpponentOvers(float $opponentOvers): static
    {
        $this->opponentOvers = $opponentOvers;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
