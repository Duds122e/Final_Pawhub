<?php

namespace App\Controller;

use App\Repository\SystemLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LogController extends AbstractController
{
    #[Route('/log', name: 'app_log')]
    public function index(SystemLogRepository $logRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $logs = $logRepo->findAll();
        return $this->render('log/index.html.twig', [
            'logs' => $logs,
        ]);
    }
}
