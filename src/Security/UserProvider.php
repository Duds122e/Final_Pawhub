<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserProvider implements UserProviderInterface
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $reloaded = $this->users->find($user->getId());
        if (!$reloaded instanceof User) {
            throw new UserNotFoundException(sprintf('User with id "%s" could not be reloaded.', (string) $user->getId()));
        }

        return $reloaded;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->users->findOneBy(['email' => $identifier])
            ?? $this->users->findOneBy(['username' => $identifier]);

        if (!$user instanceof User) {
            $ex = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $ex->setUserIdentifier($identifier);
            throw $ex;
        }

        return $user;
    }
}

