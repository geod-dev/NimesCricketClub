<?php

namespace App\Entity\Interface;

interface TranslatableInterface
{
    public function getTitle(string $locale): ?string;

    public function getContent(string $locale): ?string;

    public function getTitleFr(): ?string;

    public function setTitleFr(?string $titleFr): static;

    public function getTitleEn(): ?string;

    public function setTitleEn(?string $titleEn): static;

    public function getContentFr(): ?string;

    public function setContentFr(?string $contentFr): static;

    public function getContentEn(): ?string;

    public function setContentEn(?string $contentEn): static;

    public function getTranslationVersionHash(): ?string;

    public function setTranslationVersionHash(?string $translationVersionHash): static;
}
