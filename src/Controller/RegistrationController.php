<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        EmailVerificationService $emailVerificationService,
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Backwards-compat safeguard: make sure email is set.
            if ($user->getEmail() === null && $user->getUsername() !== null) {
                $user->setEmail($user->getUsername());
            }

            // Email verification: new users must verify before login.
            $user->setIsVerified(false);
            $user->setVerificationToken($emailVerificationService->generateVerificationToken());

            $entityManager->persist($user);
            $entityManager->flush();

            $verificationUrl = $this->generateUrl(
                'app_verify_email',
                ['token' => $user->getVerificationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            try {
                $emailVerificationService->sendVerificationEmail($user, $verificationUrl);
            } catch (TransportExceptionInterface) {
                // Don't keep an account that cannot be verified.
                $entityManager->remove($user);
                $entityManager->flush();

                $this->addFlash('error', 'We could not send the verification email right now. Please check the SMTP configuration and try again.');
                return $this->redirectToRoute('app_register');
            } catch (\Throwable) {
                $entityManager->remove($user);
                $entityManager->flush();

                $this->addFlash('error', 'Unexpected error while sending the verification email. Please try again.');
                return $this->redirectToRoute('app_register');
            }

            $this->addFlash('success', 'Account created! Please check your email to verify your account before logging in.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
