<?php

namespace App\Controller;

use App\Service\GoogleAccountService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiGoogleAuthController extends AbstractController
{
    #[Route('/api/auth/google', name: 'api_auth_google', methods: ['POST'])]
    public function google(
        Request $request,
        HttpClientInterface $httpClient,
        JWTTokenManagerInterface $jwtManager,
        GoogleAccountService $googleAccounts,
        #[Autowire('%env(OAUTH_GOOGLE_CLIENT_ID)%')] string $googleClientId,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $idToken = isset($data['idToken']) ? trim((string) $data['idToken']) : '';
        if ($idToken === '') {
            return new JsonResponse(['error' => 'idToken is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $response = $httpClient->request(
                'GET',
                'https://oauth2.googleapis.com/tokeninfo',
                ['query' => ['id_token' => $idToken]],
            );

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                return new JsonResponse(['error' => 'Invalid Google sign-in token'], Response::HTTP_UNAUTHORIZED);
            }

            $payload = $response->toArray(false);
        } catch (\Throwable) {
            return new JsonResponse(['error' => 'Could not verify Google token'], Response::HTTP_BAD_GATEWAY);
        }

        $audience = (string) ($payload['aud'] ?? '');
        if ($audience === '' || $audience !== $googleClientId) {
            return new JsonResponse(['error' => 'Google token audience mismatch'], Response::HTTP_UNAUTHORIZED);
        }

        $email = (string) ($payload['email'] ?? '');
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return new JsonResponse(['error' => 'Google account has no valid email'], Response::HTTP_BAD_REQUEST);
        }

        if (
            isset($payload['email_verified'])
            && !in_array($payload['email_verified'], ['true', true, '1', 1], true)
        ) {
            return new JsonResponse(['error' => 'Google email is not verified'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $googleAccounts->findOrCreateFromEmail($email);
        } catch (\Throwable $e) {
            return new JsonResponse(
                ['error' => $e->getMessage() ?: 'Could not create account from Google'],
                Response::HTTP_CONFLICT,
            );
        }

        $token = $jwtManager->create($user);

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'username' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ],
        ]);
    }
}
