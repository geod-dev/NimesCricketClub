<?php

namespace App\Repository;

use App\Entity\News;
use App\Entity\NewsletterEmail;
use App\Entity\NewsletterSubscriber;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends ServiceEntityRepository<NewsletterSubscriber>
 */
class NewsletterSubscriberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsletterSubscriber::class);
    }

    public function updateLastNewsSent(array $emails, News $news): void
    {
        if ($emails === []) {
            return;
        }

        $qb = $this->createQueryBuilder('ns')
            ->update()
            ->set('ns.lastNewsSent', ':news')
            ->set('ns.lastContactedAt', ':now')
            ->set('ns.sendFailed', ':failed')
            ->where('ns.email IN (:emails)')
            ->setParameter('news', $news)
            ->setParameter('now', new DateTimeImmutable())
            ->setParameter('failed', false)
            ->setParameter('emails', $emails);

        $qb->getQuery()->execute();
    }

    public function updateLastEmailSent(array $emails, NewsletterEmail $entity): void
    {
        if ($emails === []) {
            return;
        }

        $qb = $this->createQueryBuilder('ns')
            ->update()
            ->set('ns.lastEmailSent', ':email')
            ->set('ns.lastContactedAt', ':now')
            ->set('ns.sendFailed', ':failed')
            ->where('ns.email IN (:emails)')
            ->setParameter('email', $entity->getId(), UuidType::NAME)
            ->setParameter('now', new DateTimeImmutable())
            ->setParameter('emails', $emails)
            ->setParameter('failed', false);

        $qb->getQuery()->execute();
    }

    public function setFailed(array $emails): void
    {
        if ($emails === []) {
            return;
        }

        $qb = $this->createQueryBuilder('ns')
            ->update()
            ->set('ns.sendFailed', ':failed')
            ->where('ns.email IN (:emails)')
            ->setParameter('failed', true)
            ->setParameter('emails', $emails);

        $qb->getQuery()->execute();
    }
}
