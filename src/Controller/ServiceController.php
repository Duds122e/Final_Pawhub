<?php

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{
    #[Route('/service', name: 'app_service')]
    public function index(ServiceRepository $svcRepo): Response
    {
        $services = $svcRepo->findAll();
        return $this->render('service/index.html.twig', [
            'service_list' => $services,
            'services' => count($services),
        ]);
    }

    #[Route('/service/new', name: 'app_service_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $service = new Service();
        $form = $this->createFormBuilder($service)
            ->add('name', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('price', NumberType::class, ['html5' => true, 'scale' => 2])
            ->add('save', SubmitType::class, ['label' => 'Create'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($service->getDescription() === null) {
                $service->setDescription('');
            }
            $em->persist($service);
            $em->flush();
            $this->addFlash('success', 'Service created.');
            return $this->redirectToRoute('app_service');
        }
        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Could not create service. Please check the form for errors.');
        }
        return $this->render('service/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/service/{id}', name: 'app_service_show', requirements: ['id' => '\\d+'])]
    public function show(Service $service): Response
    {
        return $this->render('service/show.html.twig', ['service' => $service]);
    }

    #[Route('/service/{id}/edit', name: 'app_service_edit', requirements: ['id' => '\\d+'])]
    public function edit(Request $request, Service $service, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($service)
            ->add('name', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('price', NumberType::class, ['html5' => true, 'scale' => 2])
            ->add('save', SubmitType::class, ['label' => 'Update'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($service->getDescription() === null) {
                $service->setDescription('');
            }
            $em->flush();
            // Log the edit action
            $log = new \App\Entity\SystemLog();
            $log->setType('EDIT');
            $log->setMessage('Edited service: ' . $service->getName() . ' (ID: ' . $service->getId() . ')');
            $log->setUser($this->getUser());
            $log->setIsRead(false);
            $em->persist($log);
            $em->flush();
            $this->addFlash('success', 'Service updated.');
            return $this->redirectToRoute('app_service');
        }
        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Could not update service. Please check the form for errors.');
        }
        return $this->render('service/edit.html.twig', ['form' => $form->createView(), 'service' => $service]);
    }

    #[Route('/service/{id}/delete', name: 'app_service_delete', methods: ['POST'])]
    public function delete(Request $request, Service $service, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            $serviceName = $service->getName();
            $serviceId = $service->getId();
            $em->remove($service);
            $em->flush();
            // Log the delete action
            $log = new \App\Entity\SystemLog();
            $log->setType('DELETE');
            $log->setMessage('Deleted service: ' . $serviceName . ' (ID: ' . $serviceId . ')');
            $log->setUser($this->getUser());
            $log->setIsRead(false);
            $em->persist($log);
            $em->flush();
            $this->addFlash('success', 'Service deleted.');
        }
        return $this->redirectToRoute('app_service');
    }
}
