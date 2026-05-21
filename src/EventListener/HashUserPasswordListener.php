<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Ensures passwords are hashed when users are created via API Platform or admin forms
 * that set a plain-text password on the entity.
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class HashUserPasswordListener
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->hashPassword($args->getObject());
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->hashPassword($args->getObject());
    }

    private function hashPassword(object $entity): void
    {
        if (!$entity instanceof User) {
            return;
        }

        $plain = $entity->getPassword();
        if ($plain === null || $plain === '') {
            return;
        }

        if ($this->isAlreadyHashed($plain)) {
            return;
        }

        $entity->setPassword($this->hasher->hashPassword($entity, $plain));
    }

    private function isAlreadyHashed(string $password): bool
    {
        return preg_match('/^\$2[ayb]\$.{56}$/', $password) === 1
            || str_starts_with($password, '$argon');
    }
}
