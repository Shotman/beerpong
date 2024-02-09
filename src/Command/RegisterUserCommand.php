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
    name: 'app:register-user',
    description: 'registers a new user in the system.',
)]
class RegisterUserCommand extends Command
{
    public function __construct(private readonly  UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addOption('roles', null, InputOption::VALUE_OPTIONAL, 'roles separated by comma')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        if ($username) {
            $password = $io->askHidden('What password do you want to use?');
            if(!$password){
                $io->error('Password required!');
                return Command::FAILURE;
            }
            $user = new User();
            $user->setUsername($username);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $password
            );
            $user->setPassword($hashedPassword);
        }
        else{
            $io->error('Username required!');
            return Command::FAILURE;
        }

        if ($input->getOption('roles')) {
            $user->setRoles(explode(',', $input->getOption('roles')));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $io->success('User registered successfully!');

        return Command::SUCCESS;
    }
}
