<?php

namespace App\EventListener;

use App\Entity\News;
use App\Service\NewsletterService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;

#[AsDoctrineListener("postPersist")]
#[AsDoctrineListener("postUpdate")]
#[AsDoctrineListener("postFlush")]
class NewsletterPublish
{
    /** @var array<int, News> */
    private array $pending = [];

    public function __construct(
        private readonly NewsletterService $newsletterService
    )
    {
    }

    public function postPersist(PostPersistEventArgs $eventArgs): void
    {
        $object = $eventArgs->getObject();
        if ($object instanceof News && $object->isPublished() && $object->getPublishedAt() === null) {
            $this->onPublish($object);
        }
    }

    public function onPublish(News $news): void
    {
        $this->pending[spl_object_id($news)] = $news;
    }

    public function postUpdate(PostUpdateEventArgs $eventArgs): void
    {
        $object = $eventArgs->getObject();
        if ($object instanceof News && $object->isPublished() && $object->getPublishedAt() === null) {
            $this->onPublish($object);
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        // Dispatch after the original transaction commits so delivery failures
        // cannot roll back tracking for messages SES has already accepted.
        $pending = $this->pending;
        $this->pending = [];
        foreach ($pending as $news) {
            $this->newsletterService->sendNewsletter($news);
        }
    }
}
