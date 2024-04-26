<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Add a short description for your command',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'email ')
            ->addOption('password', null, InputOption::VALUE_NONE, 'Mot de passe ')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('email');

        if ($input->getOption('password')) {
            $password = 'azerty';
        }

        $admin = new User();

        $admin->setEmail($arg1);
        $admin->setRoles(['ROLE_USER','ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, $password));
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        if ($admin->getID()) {
            $io->success('Admin créer avec succé');
            return Command::SUCCESS;
        } else {
            $io->error(' failed');
            return Command::FAILURE;
        }
    }
}
