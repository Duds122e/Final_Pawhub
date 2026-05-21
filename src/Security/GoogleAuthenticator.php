<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use GuzzleHttp\Client;

class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        // Create custom HTTP client with SSL verification disabled for development
        $httpClient = new Client([
            'verify' => false,
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]
        ]);
        
        $client = $this->clientRegistry->getClient('google');
        $provider = $client->getOAuth2Provider();
        $provider->setHttpClient($httpClient);
        
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client): User {
                return $this->getOrCreateUserFromGoogle($accessToken, $client);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login', ['google' => 'fail']));
    }

    private function getOrCreateUserFromGoogle(AccessToken $accessToken, $client): User
    {
        $googleUser = $client->fetchUserFromToken($accessToken);

        $userData = $googleUser->toArray();
        $email = $userData['email'] ?? null;
        if (!is_string($email) || $email === '') {
            throw new AuthenticationException('Google did not return an email address.');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]) ?? $this->userRepository->findOneBy(['username' => $email]);
        if ($user instanceof User) {
            if (!$user->isVerified()) {
                $user->setIsVerified(true);
                $this->entityManager->flush();
            }

            return $user;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setUsername($this->generateUniqueUsernameFromEmail($email));
        $user->setIsVerified(true);
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

