<?php

namespace App\EventListener;

use App\Entity\Interface\TranslatableInterface;
use App\Service\TranslationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

#[AsDoctrineListener("prePersist")]
#[AsDoctrineListener("preUpdate")]
class AutoTranslate
{
    public function __construct(
        private readonly TranslationService $translationService
    )
    {
    }

    public function prePersist(PrePersistEventArgs $eventArgs): void
    {
        $object = $eventArgs->getObject();
        if ($object instanceof TranslatableInterface) {
            $this->translationService->translate($object);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $object = $eventArgs->getObject();
        if ($object instanceof TranslatableInterface) {
            $this->translationService->translate($object);
        }
    }
}
