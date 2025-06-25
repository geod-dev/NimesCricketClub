<?php

namespace App\Entity\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait TranslatableTrait
{
    #[ORM\Column(type: Types::TEXT, length: 255)]
    private ?string $titleFr = null;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    private ?string $titleEn = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contentFr = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contentEn = null;

    #[Orm\Column(type: Types::TEXT, nullable: true)]
    private ?string $translationVersionHash = null;


    public function getTitle(string $locale): ?string
    {
        if ($locale === "en") return $this->titleEn;
        else return $this->titleFr;
    }

    public function getContent(string $locale): ?string
    {
        if ($locale === "en") return $this->contentEn;
        else return $this->contentFr;
    }

    public function getTitleFr(): ?string
    {
        return $this->titleFr;
    }

    public function setTitleFr(?string $titleFr): static
    {
        $this->titleFr = $titleFr;

        return $this;
    }

    public function getTitleEn(): ?string
    {
        return $this->titleEn;
    }

    public function setTitleEn(?string $titleEn): static
    {
        $this->titleEn = $titleEn;

        return $this;
    }

    public function getContentFr(): ?string
    {
        return $this->contentFr;
    }

    public function setContentFr(?string $contentFr): static
    {
        $this->contentFr = $contentFr;

        return $this;
    }

    public function getContentEn(): ?string
    {
        return $this->contentEn;
    }

    public function setContentEn(?string $contentEn): static
    {
        $this->contentEn = $contentEn;

        return $this;
    }

    public function getTranslationVersionHash(): ?string
    {
        return $this->translationVersionHash;
    }

    public function setTranslationVersionHash(?string $translationVersionHash): static
    {
        $this->translationVersionHash = $translationVersionHash;

        return $this;
    }
}
