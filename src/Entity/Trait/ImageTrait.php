<?php

namespace App\Entity\Trait;
use Doctrine\ORM\Mapping as ORM;

trait ImageTrait
{
    #[ORM\Column(type: 'string')]
    private string $image;

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImagePath(): string
    {
        return self::UPLOAD_PATH . DIRECTORY_SEPARATOR . $this->getImage();
    }
}