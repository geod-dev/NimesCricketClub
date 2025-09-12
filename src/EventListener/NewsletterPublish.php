<?php

namespace App\EventListener;

use App\Entity\News;
use App\Service\NewsletterService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;

#[AsDoctrineListener("postPersist")]
#[AsDoctrineListener("postUpdate")]
class NewsletterPublish
{
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
        $this->newsletterService->sendNewsletter($news);
    }

    public function postUpdate(PostUpdateEventArgs $eventArgs): void
    {
        $object = $eventArgs->getObject();
        if ($object instanceof News && $object->isPublished() && $object->getPublishedAt() === null) {
            $this->onPublish($object);
        }
    }
}
