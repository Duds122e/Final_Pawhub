<?php
require 'vendor/autoload.php';

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

$kernel = new App\Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();

// Get the password hasher service properly
$passwordHasher = $container->get('security.password_hasher');

// Create new user
$user = new App\Entity\User();
$user->setUsername('testuser');
$user->setRoles(['ROLE_ADMIN']);
$user->setCreatedAt(new DateTime('now'));

// Hash password
$newPassword = 'test123';
$hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
$user->setPassword($hashedPassword);

// Save to database
$entityManager = $container->get('doctrine.orm.entity_manager');
$entityManager->persist($user);
$entityManager->flush();

echo "User created successfully!\n";
echo "Username: testuser\n";
echo "Password: test123\n";
