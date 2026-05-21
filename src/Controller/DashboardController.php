<?php

namespace App\Controller;

use App\Repository\AdoptionRequestRepository;
use App\Repository\AppointmentRepository;
use App\Repository\PetRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(PetRepository $petRepo, AdoptionRequestRepository $adoptRepo, AppointmentRepository $appRepo, ServiceRepository $serviceRepo): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'pets' => $petRepo->count([]),
            'pending' => $adoptRepo->count(['status' => 'Pending']),
            'appointments' => $appRepo->count([]),
            'services' => $serviceRepo->count([]),
        ]);
    }
}
