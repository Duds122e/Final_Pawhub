<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

final class GoogleAccountService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function findOrCreateFromEmail(string $email): User
    {
        $email = trim(strtolower($email));
        if ($email === '') {
            throw new CustomUserMessageAuthenticationException('Google did not return an email address.');
        }

        $user = $this->userRepository->findOneBy(['email' => $email])
            ?? $this->userRepository->findOneBy(['username' => $email]);

        if ($user instanceof User) {
            if (!$user->isVerified()) {
                $user->setIsVerified(true);
                $user->setVerificationToken(null);
                $this->entityManager->flush();
            }

            return $user;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($this->generateUniqueUsernameFromEmail($email));
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $user->setPassword($this->passwordHasher->hashPassword($user, bin2hex(random_bytes(32))));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function generateUniqueUsernameFromEmail(string $email): string
    {
        $localPart = trim(strtolower((string) strstr($email, '@', true)));
        $base = preg_replace('/[^a-z0-9_\.]/', '', $localPart) ?: 'user';
        $candidate = $base;

        for ($i = 0; $i < 50; $i++) {
            if (!$this->userRepository->findOneBy(['username' => $candidate])) {
                return $candidate;
            }
            $candidate = $base . ($i + 2);
        }

        throw new CustomUserMessageAuthenticationException('Could not generate a unique username for this Google account.');
    }
}
