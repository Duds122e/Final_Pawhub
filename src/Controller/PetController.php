<?php

namespace App\Controller;

use App\Entity\Pet;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PetController extends AbstractController
{
    #[Route('/pet', name: 'app_pet')]
    public function index(PetRepository $petRepo): Response
    {
        $pets = $petRepo->findAll();

        return $this->render('pet/index.html.twig', [
            'pet_list' => $pets,
            'pets' => count($pets),
        ]);
    }

    #[Route('/pet/new', name: 'app_pet_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $pet = new Pet();

        $form = $this->createFormBuilder($pet)
            ->add('name', TextType::class)
            ->add('type', TextType::class)
            ->add('breed', TextType::class, ['required' => false])
            ->add('age', IntegerType::class, ['required' => false])
            ->add('status', TextType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Create Pet'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($pet);
            $em->flush();

            $this->addFlash('success', 'Pet created.');

            return $this->redirectToRoute('app_pet');
        }

        return $this->render('pet/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/pet/{id}', name: 'app_pet_show', requirements: ['id' => '\\d+'])]
    public function show(Pet $pet): Response
    {
        return $this->render('pet/show.html.twig', [
            'pet' => $pet,
        ]);
    }

    #[Route('/pet/{id}/edit', name: 'app_pet_edit', requirements: ['id' => '\\d+'])]
    public function edit(Request $request, Pet $pet, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder($pet)
            ->add('name', TextType::class)
            ->add('type', TextType::class)
            ->add('breed', TextType::class, ['required' => false])
            ->add('age', IntegerType::class, ['required' => false])
            ->add('status', TextType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Update Pet'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            // Log the edit action
            $log = new \App\Entity\SystemLog();
            $log->setType('EDIT');
            $log->setMessage('Edited pet: ' . $pet->getName() . ' (ID: ' . $pet->getId() . ')');
            $log->setUser($this->getUser());
            $log->setIsRead(false);
            $em->persist($log);
            $em->flush();
            $this->addFlash('success', 'Pet updated.');
            return $this->redirectToRoute('app_pet');
        }

        return $this->render('pet/edit.html.twig', [
            'form' => $form->createView(),
            'pet' => $pet,
        ]);
    }

    #[Route('/pet/{id}/delete', name: 'app_pet_delete', methods: ['POST'])]
    public function delete(Request $request, Pet $pet, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete'.$pet->getId(), $token)) {
            $petName = $pet->getName();
            $petId = $pet->getId();
            $em->remove($pet);
            $em->flush();
            // Log the delete action
            $log = new \App\Entity\SystemLog();
            $log->setType('DELETE');
            $log->setMessage('Deleted pet: ' . $petName . ' (ID: ' . $petId . ')');
            $log->setUser($this->getUser());
            $log->setIsRead(false);
            $em->persist($log);
            $em->flush();
            $this->addFlash('success', 'Pet deleted.');
        }

        return $this->redirectToRoute('app_pet');
    }
}
