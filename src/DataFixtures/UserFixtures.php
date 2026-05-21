<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@local.test');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        
        // Hash the admin password
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin123'
        );
        $admin->setPassword($hashedPassword);
        
        // Set the created_at field using DateTime
        $admin->setCreatedAt(new \DateTime());
        
        $manager->persist($admin);

        $user = new User();
        $user->setUsername('user');
        $user->setEmail('user@local.test');
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $user->setCreatedAt(new \DateTime());
        $manager->persist($user);

        $manager->flush();
    }
}
