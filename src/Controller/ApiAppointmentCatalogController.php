<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Pet;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ApiAppointmentCatalogController extends AbstractController
{
    public function __construct(
        private readonly AppointmentRepository $appointments,
        private readonly ServiceRepository $services,
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/api/catalog/appointments', name: 'api_catalog_appointments_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        try {
            $rows = $this->appointments->findBy(['user' => $user], ['dateTime' => 'DESC']);

            return new JsonResponse(array_map(fn (Appointment $a) => $this->serialize($a), $rows));
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Could not load appointments: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/catalog/appointments', name: 'api_catalog_appointments_book', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function book(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], 400);
        }

        $serviceId = isset($data['serviceId']) ? (int) $data['serviceId'] : 0;
        $dateTimeRaw = isset($data['dateTime']) ? (string) $data['dateTime'] : '';
        if ($serviceId <= 0 || $dateTimeRaw === '') {
            return new JsonResponse(['error' => 'serviceId and dateTime are required'], 400);
        }

        $service = $this->services->find($serviceId);
        if (!$service instanceof Service) {
            return new JsonResponse(['error' => 'Service not found'], 404);
        }

        try {
            $dateTime = new \DateTime($dateTimeRaw);
        } catch (\Exception) {
            return new JsonResponse(['error' => 'Invalid dateTime'], 400);
        }

        $petName = trim((string) ($data['petName'] ?? ''));
        $petSpecies = trim((string) ($data['petSpecies'] ?? 'Dog'));
        $petBreed = trim((string) ($data['petBreed'] ?? ''));

        $pet = new Pet();
        $pet->setName($petName !== '' ? $petName : trim($petSpecies . ' ' . $petBreed));
        $pet->setType($petSpecies !== '' ? $petSpecies : 'Pet');
        $pet->setBreed($petBreed !== '' ? $petBreed : '-');
        $pet->setAge(0);
        $pet->setStatus('Active');

        $appointment = new Appointment();
        $appointment->setUser($user);
        $appointment->setPet($pet);
        $appointment->setService($service);
        $appointment->setDateTime($dateTime);
        $appointment->setPetName($pet->getName());
        $appointment->setLocation((string) ($data['location'] ?? ''));
        $appointment->setReason((string) ($data['reason'] ?? ''));
        $appointment->setOwnerName((string) ($data['ownerName'] ?? ''));
        $appointment->setContactPhone((string) ($data['contactPhone'] ?? ''));
        $appointment->setAddress((string) ($data['address'] ?? ''));
        $appointment->setEmergencyContact((string) ($data['emergencyContact'] ?? ''));
        $appointment->setPetSpecies($petSpecies);
        $appointment->setPetBreed($petBreed);
        $appointment->setPetColor((string) ($data['petColor'] ?? ''));
        $appointment->setPetSex((string) ($data['petSex'] ?? ''));
        $appointment->setPetSpayNeuter((string) ($data['petSpayNeuter'] ?? ''));
        $appointment->setPetWeight((string) ($data['petWeight'] ?? ''));
        $appointment->setPetMicrochip((string) ($data['petMicrochip'] ?? ''));

        if (!empty($data['petDob'])) {
            try {
                $appointment->setPetDob(new \DateTime((string) $data['petDob']));
            } catch (\Exception) {
            }
        }

        try {
            $this->em->persist($pet);
            $this->em->persist($appointment);
            $this->em->flush();
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Could not save appointment: ' . $e->getMessage()], 500);
        }

        return new JsonResponse($this->serialize($appointment), 201);
    }

    private function serialize(Appointment $a): array
    {
        $user = $a->getUser();
        $pet = $a->getPet();
        $service = $a->getService();

        return [
            'id' => $a->getId(),
            'dateTime' => $a->getDateTime()?->format(\DateTimeInterface::ATOM),
            'petName' => $a->getPetName(),
            'location' => $a->getLocation(),
            'reason' => $a->getReason(),
            'ownerName' => $a->getOwnerName(),
            'contactPhone' => $a->getContactPhone(),
            'address' => $a->getAddress(),
            'emergencyContact' => $a->getEmergencyContact(),
            'petSpecies' => $a->getPetSpecies(),
            'petBreed' => $a->getPetBreed(),
            'petColor' => $a->getPetColor(),
            'petSex' => $a->getPetSex(),
            'petSpayNeuter' => $a->getPetSpayNeuter(),
            'petWeight' => $a->getPetWeight(),
            'petMicrochip' => $a->getPetMicrochip(),
            'petDob' => $a->getPetDob()?->format('Y-m-d'),
            'user' => $user ? '/api/users/' . $user->getId() : null,
            'pet' => $pet ? '/api/pets/' . $pet->getId() : null,
            'service' => $service ? '/api/services/' . $service->getId() : null,
        ];
    }
}
