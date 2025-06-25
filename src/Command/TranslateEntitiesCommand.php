<?php

namespace App\Command;

use App\Entity\Interface\TranslatableInterface;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// This command is temporary in the code base to handle the translation update.
#[AsCommand(
    name: 'app:translate-entities',
    description: 'Translate all translatable entities.',
)]
class TranslateEntitiesCommand extends Command
{
    const translatableInterface = TranslatableInterface::class;

    public function __construct(
        private readonly TranslationService     $translationService,
        private readonly EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityNames = $this->getTranslatableEntityNames();
        $entities = [];

        foreach ($entityNames as $entityName) {
            $repository = $this->entityManager->getRepository($entityName);
            $entities = array_merge($entities, $repository->findAll());
        }

        for ($i = 0; $i < count($entities); $i++) {
            $this->translationService->translate($entities[$i]);
        }

        $this->entityManager->flush();
        return Command::SUCCESS;
    }

    private function getTranslatableEntityNames(): array
    {
        $classes = $this->entityManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $translatableEntities = [];
        foreach ($classes as $class) {
            $interfaces = class_implements($class);
            if (in_array(self::translatableInterface, $interfaces)) {
                $translatableEntities[] = $class;
            };
        }
        return $translatableEntities;
    }
}
