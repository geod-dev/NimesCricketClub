<?php

namespace App\Entity;

use App\Entity\Interface\TranslatableInterface;
use App\Entity\Trait\ImageTrait;
use App\Entity\Trait\TimestampTrait;
use App\Entity\Trait\TranslatableTrait;
use App\Repository\ActivityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Activity implements TranslatableInterface
{
    use ImageTrait;
    use TimestampTrait;
    use TranslatableTrait;

    const UPLOAD_PATH = "uploads/activity";

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
