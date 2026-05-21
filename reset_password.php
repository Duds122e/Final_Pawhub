<?php
require 'vendor/autoload.php';

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

$kernel = new App\Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine.orm.entity_manager');
$passwordHasher = $container->get('security.user_password_hasher');

// Find admin user
$user = $entityManager->getRepository(App\Entity\User::class)->findOneBy(['username' => 'admin']);

if ($user) {
    // Set new password
    $newPassword = 'admin123';
    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
    $user->setPassword($hashedPassword);
    
    $entityManager->flush();
    
    echo "Admin password reset successfully!\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
} else {
    echo "Admin user not found!\n";
}
