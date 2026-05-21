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

#[AsCommand(name: 'app:create-admin', description: 'Create an admin user')]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('username', InputArgument::OPTIONAL, 'Username for the admin', 'admin')
             ->addArgument('password', InputArgument::OPTIONAL, 'Password for the admin', 'Admin@123')
             ->addOption('force', 'f', null, 'Update password if user already exists');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = (string) $input->getArgument('username');
        $plainPassword = (string) $input->getArgument('password');
        $email = $username;
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
            // Update existing user password
            $hashed = $this->passwordHasher->hashPassword($existing, $plainPassword);
            $existing->setPassword($hashed);
            if ($existing->getEmail() === null) {
                $existing->setEmail($email);
            }
            if (!$existing->isVerified()) {
                $existing->setIsVerified(true);
                $existing->setVerificationToken(null);
            }
            $this->em->flush();
            
            $io->success(sprintf('Password updated for user "%s".', $username));
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setCreatedAt(new \DateTime());
        $user->setIsVerified(true);
        $user->setVerificationToken(null);

        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('Admin user "%s" created with the provided password.', $username));

        return Command::SUCCESS;
    }
}
