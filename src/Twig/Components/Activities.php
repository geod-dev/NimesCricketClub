<?php

namespace App\Twig\Components;

use App\Repository\ActivityRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Activities
{
    public function __construct(
        private readonly ActivityRepository $repository
    )
    {
    }

    public function getActivities(): array
    {
        // an example method that returns an array of Products
        return $this->repository->findAll();
    }
}
