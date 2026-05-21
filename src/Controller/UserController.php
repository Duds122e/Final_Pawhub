<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(UserRepository $userRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $userRepo->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/new', name: 'app_user_new')]
    public function new(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = new User();
        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class)
            ->add('password', PasswordType::class)
            ->add('save', SubmitType::class, ['label' => 'Create'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hashed = $hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashed);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'User created.');
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/user/{id}', name: 'app_user_show', requirements: ['id' => '\\d+'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    #[Route('/user/{id}/edit', name: 'app_user_edit', requirements: ['id' => '\\d+'])]
    public function edit(Request $request, User $user, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class)
            ->add('password', PasswordType::class, ['required' => false])
            ->add('save', SubmitType::class, ['label' => 'Update'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData()) {
                $hashed = $hasher->hashPassword($user, $form->get('password')->getData());
                $user->setPassword($hashed);
            }
            $em->flush();
            // Log the edit action
            $log = new \App\Entity\SystemLog();
            $log->setType('EDIT');
            $log->setMessage('Edited user: ' . $user->getUsername() . ' (ID: ' . $user->getId() . ')');
            $log->setUser($this->getUser());
            $log->setIsRead(false);
            $em->persist($log);
            $em->flush();
            $this->addFlash('success', 'User updated.');
            return $this->redirectToRoute('app_user');
        }
        return $this->render('user/edit.html.twig', ['form' => $form->createView(), 'user' => $user]);
    }

    #[Route('/user/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $username = $user->getUsername();
            $userId = $user->getId();
            $em->remove($user);
            $em->flush();
            // Log the delete action
            $log = new \App\Entity\SystemLog();
            $log->setType('DELETE');
            $log->setMessage('Deleted user: ' . $username . ' (ID: ' . $userId . ')');
            $log->setUser($this->getUser());
            $log->setIsRead(false);
            $em->persist($log);
            $em->flush();
            $this->addFlash('success', 'User deleted.');
        }
        return $this->redirectToRoute('app_user');
    }
}
