<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\NewsletterSubscriber;
use App\Repository\NewsletterSubscriberRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class NewsletterService
{
    public function __construct(
        private readonly NewsletterSubscriberRepository $newsletterSubscriberRepository,
        private readonly EntityManagerInterface         $entityManager,
        private readonly Environment                    $twig,
        private readonly MailService                    $mailService
    )
    {
    }

    public function subscribeEmail(string $email): void
    {
        $subscriber = $this->newsletterSubscriberRepository->findOneBy(['email' => $email]);
        if ($subscriber) {
            if ($subscriber->getIsSubscribed()) return;
            $subscriber->setIsSubscribed(true);
            $this->mailService->resubscribe($email);
        } else {
            $subscriber = new NewsletterSubscriber();
            $subscriber->setEmail($email);
            $this->entityManager->persist($subscriber);
        }
        $this->entityManager->flush();
    }

    public function sendNewsletter(News $news): void
    {
        $subscribers = $this->newsletterSubscriberRepository->findBy(['isSubscribed' => true]);
        if (count($subscribers) === 0) {
            $news->setIsPublished(true)
                ->setPublishedAt(new DateTimeImmutable());
            $this->entityManager->flush();
            return;
        }

        $recipients = [];
        foreach ($subscribers as $subscriber) {
            $recipients[] = ["email" => $subscriber->getEmail()];
        }

        $response = $this->mailService->sendMail([
            "recipients" => $recipients,
            "body" => [
                "html" => $this->twig->render('emails/newsletter.html.twig', [
                    'news' => $news
                ]),
            ],
            "subject" => $news->getTitleFr() . " - Nîmes Cricket Club",
            "from_name" => "Nîmes Cricket Club - Newsletter"
        ]);

        if ($response->getStatusCode() === 200) {
            $news->setIsPublished(true)
                ->setPublishedAt(new DateTimeImmutable());
        } else {
            $news->setIsPublished(false)
                ->setPublishedAt(null);
        }
        $this->entityManager->flush();
    }

    public function sendCustomNewsletter(string $subject, string $content): void
    {
        $subscribers = $this->newsletterSubscriberRepository->findBy(['isSubscribed' => true]);
        if (count($subscribers) === 0) {
            return; // nothing to send
        }

        $recipients = [];
        foreach ($subscribers as $subscriber) {
            $recipients[] = ["email" => $subscriber->getEmail()];
        }

        $this->mailService->sendMail([
            "recipients" => $recipients,
            "body" => [
                "html" => $this->twig->render('emails/newsletter_custom.html.twig', [
                    'subject' => $subject,
                    'content' => $content,
                ]),
            ],
            "subject" => $subject . " - Nîmes Cricket Club",
            "from_name" => "Nîmes Cricket Club - Newsletter"
        ]);
    }
}
