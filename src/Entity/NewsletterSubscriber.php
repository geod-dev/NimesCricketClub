<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractUid;
use App\Repository\NewsletterSubscriberRepository;
use DateTimeImmutable;
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

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $lastContactedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?News $lastNewsSent = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?NewsletterEmail $lastEmailSent = null;

    #[ORM\Column]
    private bool $sendFailed = false;

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

    public function getLastContactedAt(): ?DateTimeImmutable
    {
        return $this->lastContactedAt;
    }

    public function updateLastContactedAt(): static
    {
        $this->lastContactedAt = new DateTimeImmutable();
        return $this;
    }

    public function getLastEmailSent(): ?NewsletterEmail
    {
        return $this->lastEmailSent;
    }

    public function setLastEmailSent(?NewsletterEmail $lastEmailSent): NewsletterSubscriber
    {
        $this->lastEmailSent = $lastEmailSent;
        return $this;
    }

    public function getLastNewsSent(): ?News
    {
        return $this->lastNewsSent;
    }

    public function setLastNewsSent(?News $lastNewsSent): NewsletterSubscriber
    {
        $this->lastNewsSent = $lastNewsSent;
        return $this;
    }

    public function sendFailed(): bool
    {
        return $this->sendFailed;
    }

    public function setSendFailed(bool $sendFailed): static
    {
        $this->sendFailed = $sendFailed;

        return $this;
    }
}
