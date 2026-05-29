<?php

namespace App\Controller;

use App\Entity\AdoptionRequest;
use App\Entity\Appointment;
use App\Repository\AdoptionRequestRepository;
use App\Repository\AppointmentRepository;
use App\Repository\PetRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Session-authenticated JSON/HTML fragments for admin live refresh (HTTP polling only).
 */
#[IsGranted('ROLE_USER')]
final class AdminLiveRefreshController extends AbstractController
{
    private const ADOPTION_STATUSES = [
        'Pending',
        'Approved',
        'Rejected',
        'Under Review',
        'Completed',
    ];

    #[Route('/dashboard/refresh/stats', name: 'admin_refresh_dashboard_stats', methods: ['GET'])]
    public function dashboardStats(
    PetRepository $petRepo,
    AdoptionRequestRepository $adoptRepo,
    AppointmentRepository $appRepo,
    ServiceRepository $serviceRepo,
    ): JsonResponse {
        $pets = $petRepo->count([]);
        $pending = $adoptRepo->count(['status' => 'Pending']);
        $appointments = $appRepo->count([]);
        $services = $serviceRepo->count([]);

        $version = hash('xxh3', implode('|', [$pets, $pending, $appointments, $services]));

        return new JsonResponse([
            'pets' => $pets,
            'pending' => $pending,
            'appointments' => $appointments,
            'services' => $services,
            'version' => $version,
            'updatedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ], 200, ['Cache-Control' => 'no-store, private']);
    }

    #[Route('/appointment/refresh/rows', name: 'admin_refresh_appointments', methods: ['GET'])]
    public function appointmentRows(AppointmentRepository $appointmentRepository): JsonResponse
    {
        $appointments = $appointmentRepository->findBy([], ['dateTime' => 'DESC']);
        $version = $this->versionFromIds(array_map(
            static fn (Appointment $a) => (int) $a->getId(),
            $appointments,
        ));

        return $this->rowsJsonResponse(
            'admin/refresh/_appointments_rows.html.twig',
            ['appointments' => $appointments],
            $version,
        );
    }

    #[Route('/service/refresh/rows', name: 'admin_refresh_services', methods: ['GET'])]
    public function serviceRows(ServiceRepository $serviceRepository): JsonResponse
    {
        $services = $serviceRepository->findBy([], ['id' => 'ASC']);
        $version = $this->versionFromIds(array_map(
            static fn ($s) => (int) $s->getId(),
            $services,
        ));

        return $this->rowsJsonResponse(
            'admin/refresh/_services_rows.html.twig',
            ['service_list' => $services],
            $version,
        );
    }

    #[Route('/adoption/refresh/rows', name: 'admin_refresh_adoptions', methods: ['GET'])]
    public function adoptionRows(AdoptionRequestRepository $adoptRepo): JsonResponse
    {
        $requests = $adoptRepo->findAll();
        $pending = count(array_filter(
            $requests,
            static fn (AdoptionRequest $r) => strtolower((string) $r->getStatus()) === 'pending',
        ));
        $version = $this->versionFromIds(array_map(
            static fn (AdoptionRequest $r) => (int) $r->getId(),
            $requests,
        ));

        $response = $this->rowsJsonResponse(
            'admin/refresh/_adoptions_rows.html.twig',
            [
                'requests' => $requests,
                'statuses' => self::ADOPTION_STATUSES,
            ],
            $version,
        );
        $data = json_decode($response->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $data['pending'] = $pending;

        return new JsonResponse($data, 200, ['Cache-Control' => 'no-store, private']);
    }

    /**
     * Lightweight heartbeat: returns versions for all admin lists (dashboard poll on any page).
     */
    #[Route('/dashboard/refresh/heartbeat', name: 'admin_refresh_heartbeat', methods: ['GET'])]
    public function heartbeat(
    Request $request,
    PetRepository $petRepo,
    AdoptionRequestRepository $adoptRepo,
    AppointmentRepository $appRepo,
    ServiceRepository $serviceRepo,
    ): JsonResponse {
        $appointmentIds = array_map(
            'intval',
            $appRepo->createQueryBuilder('a')
                ->select('a.id')
                ->orderBy('a.id', 'ASC')
                ->getQuery()
                ->getSingleColumnResult(),
        );
        $serviceIds = array_map(
            'intval',
            $serviceRepo->createQueryBuilder('s')
                ->select('s.id')
                ->orderBy('s.id', 'ASC')
                ->getQuery()
                ->getSingleColumnResult(),
        );
        $adoptionIds = array_map(
            'intval',
            $adoptRepo->createQueryBuilder('r')
                ->select('r.id')
                ->orderBy('r.id', 'ASC')
                ->getQuery()
                ->getSingleColumnResult(),
        );

        $pets = $petRepo->count([]);
        $pending = $adoptRepo->count(['status' => 'Pending']);
        $appointments = $appRepo->count([]);
        $services = $serviceRepo->count([]);

        $payload = [
            'dashboard' => hash('xxh3', implode('|', [$pets, $pending, $appointments, $services])),
            'appointments' => $this->versionFromIds($appointmentIds),
            'services' => $this->versionFromIds($serviceIds),
            'adoptions' => $this->versionFromIds($adoptionIds),
            'pendingAdoptions' => $pending,
            'updatedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        $clientVersion = (string) $request->query->get('v', '');
        $serverVersion = hash('xxh3', json_encode($payload, \JSON_THROW_ON_ERROR));
        $payload['version'] = $serverVersion;
        $payload['changed'] = $clientVersion === '' || $clientVersion !== $serverVersion;

        return new JsonResponse($payload, 200, ['Cache-Control' => 'no-store, private']);
    }

    /**
     * @param array<string, mixed> $viewData
     */
    private function rowsJsonResponse(string $template, array $viewData, string $version): JsonResponse
    {
        $html = $this->renderView($template, $viewData);

        return new JsonResponse([
            'version' => $version,
            'html' => $html,
            'updatedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ], 200, ['Cache-Control' => 'no-store, private']);
    }

    /** @param int[] $ids */
    private function versionFromIds(array $ids): string
    {
        return hash('xxh3', implode(',', $ids));
    }
}
