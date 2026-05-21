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

#[AsCommand(name: 'app:create-user', description: 'Create or update a regular (ROLE_USER) account')]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::OPTIONAL, 'Username', 'user')
            ->addArgument('password', InputArgument::OPTIONAL, 'Plain password', 'user123')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Email (default: username@local.test)')
            ->addOption('force', 'f', null, 'Update password if user already exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = (string) $input->getArgument('username');
        $plainPassword = (string) $input->getArgument('password');
        $email = $input->getOption('email') ?? $username;
        if (!str_contains($email, '@')) {
            $email = $username.'@local.test';
        }

        $repo = $this->em->getRepository(User::class);
        $existing = $repo->findOneBy(['username' => $username]);
        $force = $input->getOption('force');

        if ($existing && !$force) {
            $io->warning(sprintf('User "%s" already exists. Use --force to update password.', $username));

            return Command::SUCCESS;
        }

        if ($existing && $force) {
            $hashed = $this->passwordHasher->hashPassword($existing, $plainPassword);
            $existing->setPassword($hashed);
            if ($existing->getEmail() === null) {
                $existing->setEmail($email);
            }
            if (!$existing->isVerified()) {
                $existing->setIsVerified(true);
                $existing->setVerificationToken(null);
            }
            $roles = $existing->getRoles();
            if (in_array('ROLE_ADMIN', $roles, true)) {
                $existing->setRoles(['ROLE_USER']);
            }
            $this->em->flush();

            $io->success(sprintf('User "%s" updated (ROLE_USER). Password set.', $username));

            return Command::SUCCESS;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTime());
        $user->setIsVerified(true);
        $user->setVerificationToken(null);

        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('User "%s" created (ROLE_USER).', $username));

        return Command::SUCCESS;
    }
}
