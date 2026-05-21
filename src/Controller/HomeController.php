<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // If user is logged in and is NOT admin, redirect to dashboard
        if ($this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_dashboard');
        }
        // Otherwise show landing page (for guests and admins)
        return $this->render('home/index.html.twig', [
            'is_landing_page' => true
        ]);
    }
}
