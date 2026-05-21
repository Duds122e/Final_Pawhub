<?php

namespace App\Controller;

use App\Repository\AdoptionRequestRepository;
use App\Repository\AppointmentRepository;
use App\Repository\PetRepository;
use App\Repository\ServiceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        PetRepository $petRepo,
        AdoptionRequestRepository $adoptRepo,
        AppointmentRepository $appRepo,
        ServiceRepository $serviceRepo,
        LoggerInterface $logger,
    ): Response {
        return $this->render('dashboard/index.html.twig', [
            'pets' => $this->safeCount($logger, 'pets', fn () => $petRepo->count([])),
            'pending' => $this->safeCount($logger, 'pending_adoptions', fn () => $adoptRepo->count(['status' => 'Pending'])),
            'appointments' => $this->safeCount($logger, 'appointments', fn () => $appRepo->count([])),
            'services' => $this->safeCount($logger, 'services', fn () => $serviceRepo->count([])),
        ]);
    }

    private function safeCount(LoggerInterface $logger, string $label, callable $count): int
    {
        try {
            return (int) $count();
        } catch (\Throwable $e) {
            $logger->error('Dashboard stat failed: {label}', [
                'label' => $label,
                'exception' => $e,
            ]);

            return 0;
        }
    }
}
