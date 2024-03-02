<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function Symfony\Component\String\u;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Create an admin user.',
)]
class CreateAdminUserCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'The email of the new user')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the new user');

    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('email') && null !== $input->getArgument('password')) {
            return;
        }

        $this->io->title('Create Admin User Interactive Wizard');

        $email = $input->getArgument('email');

        if (null !== $email) {
            $this->io->text(' > <info>Email</info>: ' . $email);
        } else {
            $email = $this->io->ask('Email');
            $input->setArgument('email', $email);
        }

        $password = $input->getArgument('password');

        if (null !== $password) {
            $this->io->text(' > <info>Password</info>: ' . u('*')->repeat(u($password)->length()));
        } else {
            $password = $this->io->askHidden('Password (your type will be hidden)');
            $input->setArgument('password', $password);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');

        $user = new User();
        $user->setEmail($email)
            ->setRoles(["ROLE_ADMIN"]);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);


        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
