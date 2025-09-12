<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractUid;
use App\Repository\NewsletterSubscriberRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterSubscriberRepository::class)]
class NewsletterSubscriber extends AbstractUid
{
    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $unsubscribeToken = null;

    #[ORM\Column]
    private bool $isSubscribed = true;

    public function __construct()
    {
        parent::__construct();
        $this->generateUnsubscribeToken();
    }

    public function generateUnsubscribeToken(): void
    {
        $this->unsubscribeToken = bin2hex(random_bytes(16));
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUnsubscribeToken(): ?string
    {
        return $this->unsubscribeToken;
    }

    public function isSubscribed(): bool
    {
        return $this->isSubscribed;
    }

    public function setIsSubscribed(bool $isSubscribed): static
    {
        $this->isSubscribed = $isSubscribed;

        return $this;
    }
}
