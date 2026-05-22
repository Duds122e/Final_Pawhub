<?php

namespace App\Controller;

use App\Repository\PetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Adoption-ready pets for mobile (status = available).
 */
final class ApiPetCatalogController extends AbstractController
{
    #[Route('/api/catalog/pets', name: 'api_catalog_pets', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(PetRepository $pets): JsonResponse
    {
        $items = [];
        foreach ($pets->findBy([], ['name' => 'ASC']) as $pet) {
            if (strtolower((string) $pet->getStatus()) !== 'available') {
                continue;
            }
            $items[] = [
                'id' => $pet->getId(),
                'name' => $pet->getName() ?? '',
                'type' => $pet->getType() ?? '',
                'breed' => $pet->getBreed() ?? '',
                'age' => (int) ($pet->getAge() ?? 0),
                'status' => $pet->getStatus() ?? 'available',
                'createdAt' => $pet->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        return new JsonResponse($items);
    }
}
