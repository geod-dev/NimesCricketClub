<?php

namespace App\Service;

use App\Entity\News;
use App\Entity\NewsletterEmail;
use App\Entity\NewsletterSubscriber;
use App\Repository\NewsletterSubscriberRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class NewsletterService
{
    public function __construct(
        private readonly NewsletterSubscriberRepository $newsletterSubscriberRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Environment $twig,
        private readonly MailService $mailService,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function subscribeEmail(string $email): void
    {
        $subscriber = $this->newsletterSubscriberRepository->findOneBy(['email' => $email]);
        if ($subscriber) {
            if ($subscriber->isSubscribed()) {
                return;
            }
            $subscriber->setIsSubscribed(true);
        } else {
            $subscriber = new NewsletterSubscriber();
            $subscriber->setEmail($email);
            $this->entityManager->persist($subscriber);
        }
        $this->entityManager->flush();
    }

    private function getEmailEntries(callable $filter): array
    {
        $subscribers = $this->newsletterSubscriberRepository->findBy(['isSubscribed' => true]);
        $emailEntries = [];

        foreach ($subscribers as $subscriber) {
            if (!$filter($subscriber)) {
                continue;
            }
            $emailEntries[] = [
                "Destination" => [
                    "ToAddresses" => [$subscriber->getEmail()]
                ],
                "ReplacementEmailContent" => [
                    "ReplacementTemplate" => [
                        "ReplacementTemplateData" =>
                            json_encode(
                                [
                                    "UNSUBSCRIBE_URL" => $this->urlGenerator->generate(
                                        'app_newsletter_unsubscribe',
                                        ['unsubscribeToken' => $subscriber->getUnsubscribeToken()],
                                        UrlGeneratorInterface::ABSOLUTE_URL
                                    )
                                ]
                            )
                    ]
                ]
            ];
        }
        return $emailEntries;
    }

    public function sendNewsletter(News $news): void
    {
        $emailEntries = $this->getEmailEntries(function (NewsletterSubscriber $subscriber) use ($news) {
            $lastNewsSent = $subscriber->getLastNewsSent();
            return $lastNewsSent === null || $lastNewsSent->getId() !== $news->getId();
        });
        if (count($emailEntries) === 0) {
            $this->updatePublication($news, true);
            return;
        }

        $html = $this->twig->render('emails/newsletter.html.twig', [
            'news' => $news
        ]);
        $subject = $news->getTitleFr() . " - Nîmes Cricket Club";
        $fromName = "Nîmes Cricket Club - Newsletter";

        $res = $this->mailService->sendMail(
            $emailEntries,
            $fromName,
            $subject,
            $html
        );
        $emails = $res['emails'];
        $this->newsletterSubscriberRepository->updateLastNewsSent($emails, $news);
        $this->newsletterSubscriberRepository->setFailed($res['failed']);
        if (!$res['done']) {
            $this->updatePublication($news, false);
            throw new RuntimeException(
                'Failed to send newsletter: ' . $res['error'] . ' (code: ' . $res['code'] . ')'
            );
        }
        $this->updatePublication($news, true);
    }

    private function updatePublication(News $news, bool $published): void
    {
        $publishedAt = $published ? new DateTimeImmutable() : null;
        // This service also runs from postFlush; do not recursively flush the
        // UnitOfWork while Doctrine is still finishing the original commit.
        $this->entityManager->createQueryBuilder()
            ->update(News::class, 'news')
            ->set('news.isPublished', ':published')
            ->set('news.publishedAt', ':publishedAt')
            ->where('news.id = :id')
            ->setParameter('published', $published)
            ->setParameter('publishedAt', $publishedAt, 'datetime_immutable')
            ->setParameter('id', $news->getId())
            ->getQuery()->execute();
        $news->setIsPublished($published)->setPublishedAt($publishedAt);
    }

    public function sendCustomNewsletter(NewsletterEmail $entity): bool
    {
        $emailEntries = $this->getEmailEntries(function (NewsletterSubscriber $subscriber) use ($entity) {
            $lastEmailSent = $subscriber->getLastEmailSent();
            return $lastEmailSent === null || $lastEmailSent->getId() !== $entity->getId();
        });
        if (count($emailEntries) === 0) {
            return true;
        }
        $html = $this->twig->render('emails/newsletter_custom.html.twig', [
            'subject' => $entity->getSubject(),
            'content' => $entity->getContent(),
        ]);
        $subject = $entity->getSubject() . " - Nîmes Cricket Club";
        $fromName = "Nîmes Cricket Club - Newsletter";

        $res = $this->mailService->sendMail(
            $emailEntries,
            $fromName,
            $subject,
            $html
        );
        $emails = $res['emails'];
        $this->newsletterSubscriberRepository->updateLastEmailSent($emails, $entity);
        $this->newsletterSubscriberRepository->setFailed($res['failed']);
        if (!$res['done']) {
            throw new RuntimeException(
                'Failed to send custom newsletter: ' . $res['error'] . ' (code: ' . $res['code'] . ')'
            );
        }
        return true;
    }
}
