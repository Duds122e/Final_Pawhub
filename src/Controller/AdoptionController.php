<?php

namespace App\Controller;

use App\Entity\AdoptionRequest;
use App\Repository\AdoptionRequestRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdoptionController extends AbstractController
{
    #[Route('/adoption', name: 'app_adoption')]
    public function index(AdoptionRequestRepository $adoptRepo): Response
    {
        $requests = $adoptRepo->findAll();
        return $this->render('adoption/index.html.twig', [
            'requests' => $requests,
            'statuses' => self::ADOPTION_STATUSES,
            'pending' => count(array_filter(
                $requests,
                static fn (AdoptionRequest $r) => strtolower((string) $r->getStatus()) === 'pending'
            )),
        ]);
    }

    #[Route('/adoption/new', name: 'app_adoption_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $adopt = new AdoptionRequest();
        
        // Set default status
        $adopt->setStatus('Pending');
        
        $form = $this->createFormBuilder($adopt)
            ->add('petId', ChoiceType::class, [
                'label' => 'Select Pet *',
                'choices' => [
                    'Buddy the Dog' => 1,
                    'Luna the Cat' => 2,
                    'Max the Dog' => 3
                ],
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'required' => 'required'
                ]
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => 'Pending',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                    'Under Review' => 'Under Review',
                    'Completed' => 'Completed'
                ],
                'label' => 'Status *',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'required' => 'required'
                ]
            ])
            ->add('residenceType', ChoiceType::class, [
                'choices' => [
                    'House' => 'House',
                    'Apartment' => 'Apartment',
                    'Condo' => 'Condo',
                    'Mobile Home' => 'Mobile Home',
                    'Other' => 'Other',
                ],
                'required' => false,
                'label' => 'Residence Type',
                'placeholder' => 'Select residence type',
            ])
            ->add('householdMembers', ChoiceType::class, [
                'choices' => [
                    'Lives Alone' => 'Lives Alone',
                    '1-2 Members' => '1-2 Members',
                    '3-4 Members' => '3-4 Members',
                    '5+ Members' => '5+ Members',
                    'With Children' => 'With Children',
                    'Senior Community' => 'Senior Community',
                ],
                'required' => false,
                'label' => 'Household Members',
                'placeholder' => 'Select household type',
            ])
            ->add('homeAgreement', ChoiceType::class, [
                'choices' => [
                    'Yes' => 'Yes',
                    'No' => 'No',
                    'Need Permission' => 'Need Permission',
                ],
                'required' => false,
                'label' => 'Home Agreement',
                'placeholder' => 'Select agreement status',
            ])
            ->add('currentPets', ChoiceType::class, [
                'choices' => [
                    'None' => 'None',
                    'Dogs' => 'Dogs',
                    'Cats' => 'Cats',
                    'Other' => 'Other',
                    'Multiple' => 'Multiple',
                ],
                'required' => false,
                'label' => 'Current Pets',
                'placeholder' => 'Select current pets',
            ])
            ->add('previousPets', ChoiceType::class, [
                'choices' => [
                    'Never Owned' => 'Never Owned',
                    'Previously Owned' => 'Previously Owned',
                    'Currently Own' => 'Currently Own',
                    'Multiple Experiences' => 'Multiple Experiences',
                ],
                'required' => false,
                'label' => 'Previous Pets',
                'placeholder' => 'Select previous pet experience',
            ])
            ->add('spayNeuter', ChoiceType::class, [
                'choices' => [
                    'Yes, All Pets' => 'Yes, All Pets',
                    'Willing To' => 'Willing To',
                    'Unsure' => 'Unsure',
                    'No' => 'No',
                ],
                'required' => false,
                'label' => 'Spay/Neuter',
                'placeholder' => 'Select spay/neuter status',
            ])
            ->add('petExperience', ChoiceType::class, [
                'choices' => [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => 'Advanced',
                    'Very Experienced' => 'Very Experienced',
                ],
                'required' => false,
                'label' => 'Experience Level',
                'placeholder' => 'Select experience level',
            ])
            ->add('dailySchedule', ChoiceType::class, [
                'choices' => [
                    'Full-time Care' => 'Full-time Care',
                    'Part-time Care' => 'Part-time Care',
                    'Flexible Schedule' => 'Flexible Schedule',
                    'Outdoor Only' => 'Outdoor Only',
                    'Unsure' => 'Unsure',
                ],
                'required' => false,
                'label' => 'Daily Schedule',
                'placeholder' => 'Select daily schedule',
            ])
            ->add('financials', ChoiceType::class, [
                'choices' => [
                    'Pet Insurance' => 'Pet Insurance',
                    'Savings Available' => 'Savings Available',
                    'Limited Budget' => 'Limited Budget',
                    'Employer Benefits' => 'Employer Benefits',
                    'Other' => 'Other',
                ],
                'required' => false,
                'label' => 'Financials',
                'placeholder' => 'Select financial capability',
            ])
            ->add('contingencyPlan', ChoiceType::class, [
                'choices' => [
                    'Family Support' => 'Family Support',
                    'Professional Care' => 'Professional Care',
                    'Boarding Facility' => 'Boarding Facility',
                    'Friend/Neighbor' => 'Friend/Neighbor',
                    'None Planned' => 'None Planned',
                ],
                'required' => false,
                'label' => 'Contingency Plan',
                'placeholder' => 'Select contingency plan',
            ])
            ->add('save', SubmitType::class, ['label' => 'Create'])
            ->getForm();
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $em->persist($adopt);
                    $em->flush();
                    $this->addFlash('success', 'Adoption request created successfully!');
                    return $this->redirectToRoute('app_adoption');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'An error occurred while saving the adoption request: ' . $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Please correct the errors in the form.');
            }
        }
        return $this->render('adoption/new.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/adoption/{id}', name: 'app_adoption_show', requirements: ['id' => '\\d+'])]
    public function show(AdoptionRequest $adopt): Response
    {
        return $this->render('adoption/show.html.twig', ['adoption' => $adopt]);
    }

    private const ADOPTION_STATUSES = [
        'Pending',
        'Approved',
        'Rejected',
        'Under Review',
        'Completed',
    ];

    #[Route('/adoption/{id}/status', name: 'app_adoption_status', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function updateStatus(Request $request, AdoptionRequest $adopt, Connection $conn, EntityManagerInterface $em): Response
    {
        return $this->handleStatusUpdate($request, $adopt, $conn, $em, 'app_adoption');
    }

    #[Route('/adoption/{id}/edit', name: 'app_adoption_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, AdoptionRequest $adopt, Connection $conn, EntityManagerInterface $em): Response
    {
        $this->normalizeAdoptionStatus($adopt);

        if ($request->isMethod('POST')) {
            return $this->handleStatusUpdate($request, $adopt, $conn, $em, 'app_adoption_edit', ['id' => $adopt->getId()]);
        }

        return $this->render('adoption/edit.html.twig', [
            'adoption' => $adopt,
            'statuses' => self::ADOPTION_STATUSES,
        ]);
    }

    private function handleStatusUpdate(
        Request $request,
        AdoptionRequest $adopt,
        Connection $conn,
        EntityManagerInterface $em,
        string $errorRoute,
        array $errorRouteParams = [],
    ): Response {
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('adoption_update'.$adopt->getId(), $token)) {
            $this->addFlash('danger', 'Session expired. Please log out, log back in, and try again.');
            return $this->redirectToRoute($errorRoute, $errorRouteParams);
        }

        $status = (string) $request->request->get('status');
        if (!in_array($status, self::ADOPTION_STATUSES, true)) {
            $this->addFlash('danger', 'Please choose a valid status.');
            return $this->redirectToRoute($errorRoute, $errorRouteParams);
        }

        try {
            $conn->executeStatement(
                'UPDATE adoption_request SET status = ? WHERE id = ?',
                [$status, $adopt->getId()]
            );
            $em->clear();
            $this->addFlash('success', sprintf('Status updated to %s.', $status));
        } catch (\Throwable) {
            $this->addFlash('danger', 'Could not save status. Please try again.');
            return $this->redirectToRoute($errorRoute, $errorRouteParams);
        }

        return $this->redirectToRoute('app_adoption');
    }

    private function normalizeAdoptionStatus(AdoptionRequest $adopt): void
    {
        $status = $adopt->getStatus();
        if ($status === null || $status === '') {
            $adopt->setStatus('Pending');
            return;
        }

        $normalized = match (strtolower($status)) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'under review' => 'Under Review',
            'completed' => 'Completed',
            default => $status,
        };

        $adopt->setStatus($normalized);
    }

    #[Route('/adoption/{id}/delete', name: 'app_adoption_delete', methods: ['POST'])]
    public function delete(Request $request, AdoptionRequest $adopt, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$adopt->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Could not delete adoption request: invalid security token.');
            return $this->redirectToRoute('app_adoption_show', ['id' => $adopt->getId()]);
        }

        try {
            $em->remove($adopt);
            $em->flush();
            $this->addFlash('success', 'Adoption request deleted.');
        } catch (\Throwable) {
            $this->addFlash('error', 'Could not delete adoption request. Please try again.');
        }

        return $this->redirectToRoute('app_adoption');
    }
}

