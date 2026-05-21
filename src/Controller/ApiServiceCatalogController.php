<?php

namespace App\Controller;

use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Simple JSON list for mobile clients (avoids API Platform serialization edge cases).
 */
final class ApiServiceCatalogController extends AbstractController
{
    #[Route('/api/catalog/services', name: 'api_catalog_services', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(ServiceRepository $services): JsonResponse
    {
        $items = [];
        foreach ($services->findBy([], ['name' => 'ASC']) as $service) {
            $items[] = [
                'id' => $service->getId(),
                'name' => $service->getName() ?? '',
                'description' => $service->getDescription() ?? '',
                'price' => (float) ($service->getPrice() ?? 0),
            ];
        }

        return new JsonResponse($items);
    }
}
