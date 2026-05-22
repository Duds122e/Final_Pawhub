<?php

namespace App\Controller;

use App\Entity\AdoptionRequest;
use App\Repository\AdoptionRequestRepository;
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
            'pending' => count(array_filter($requests, fn($r) => $r->getStatus() === 'Pending')),
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

    #[Route('/adoption/{id}/edit', name: 'app_adoption_edit', requirements: ['id' => '\\d+'])]
    public function edit(Request $request, AdoptionRequest $adopt, EntityManagerInterface $em): Response
    {
        $this->normalizeLegacyAdoptionFields($adopt);

        $form = $this->createFormBuilder($adopt)
            ->add('status', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getStatus(), [
                    'Pending' => 'Pending',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                    'Under Review' => 'Under Review',
                    'Completed' => 'Completed',
                ]),
                'label' => 'Status',
                'required' => true,
                'placeholder' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('residenceType', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getResidenceType(), [
                    'House' => 'House',
                    'Apartment' => 'Apartment',
                    'Condo' => 'Condo',
                    'Mobile Home' => 'Mobile Home',
                    'Other' => 'Other',
                ]),
                'required' => false,
                'label' => 'Residence Type',
                'placeholder' => '— Not set —',
            ])
            ->add('householdMembers', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getHouseholdMembers(), [
                    'Lives Alone' => 'Lives Alone',
                    '1-2 Members' => '1-2 Members',
                    '3-4 Members' => '3-4 Members',
                    '5+ Members' => '5+ Members',
                    'With Children' => 'With Children',
                    'Senior Community' => 'Senior Community',
                ]),
                'required' => false,
                'label' => 'Household Members',
                'placeholder' => '— Not set —',
            ])
            ->add('homeAgreement', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getHomeAgreement(), [
                    'Yes' => 'Yes',
                    'No' => 'No',
                    'Need Permission' => 'Need Permission',
                ]),
                'required' => false,
                'label' => 'Home Agreement',
                'placeholder' => '— Not set —',
            ])
            ->add('currentPets', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getCurrentPets(), [
                    'None' => 'None',
                    'Dogs' => 'Dogs',
                    'Cats' => 'Cats',
                    'Other' => 'Other',
                    'Multiple' => 'Multiple',
                ]),
                'required' => false,
                'label' => 'Current Pets',
                'placeholder' => '— Not set —',
            ])
            ->add('previousPets', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getPreviousPets(), [
                    'Never Owned' => 'Never Owned',
                    'Previously Owned' => 'Previously Owned',
                    'Currently Own' => 'Currently Own',
                    'Multiple Experiences' => 'Multiple Experiences',
                ]),
                'required' => false,
                'label' => 'Previous Pets',
                'placeholder' => '— Not set —',
            ])
            ->add('spayNeuter', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getSpayNeuter(), [
                    'Yes, All Pets' => 'Yes, All Pets',
                    'Willing To' => 'Willing To',
                    'Unsure' => 'Unsure',
                    'No' => 'No',
                ]),
                'required' => false,
                'label' => 'Spay/Neuter',
                'placeholder' => '— Not set —',
            ])
            ->add('petExperience', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getPetExperience(), [
                    'Beginner' => 'Beginner',
                    'Intermediate' => 'Intermediate',
                    'Advanced' => 'Advanced',
                    'Very Experienced' => 'Very Experienced',
                ]),
                'required' => false,
                'label' => 'Experience Level',
                'placeholder' => '— Not set —',
            ])
            ->add('dailySchedule', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getDailySchedule(), [
                    'Full-time Care' => 'Full-time Care',
                    'Part-time Care' => 'Part-time Care',
                    'Flexible Schedule' => 'Flexible Schedule',
                    'Outdoor Only' => 'Outdoor Only',
                    'Unsure' => 'Unsure',
                ]),
                'required' => false,
                'label' => 'Daily Schedule',
                'placeholder' => '— Not set —',
            ])
            ->add('financials', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getFinancials(), [
                    'Pet Insurance' => 'Pet Insurance',
                    'Savings Available' => 'Savings Available',
                    'Limited Budget' => 'Limited Budget',
                    'Employer Benefits' => 'Employer Benefits',
                    'Other' => 'Other',
                ]),
                'required' => false,
                'label' => 'Financials',
                'placeholder' => '— Not set —',
            ])
            ->add('contingencyPlan', ChoiceType::class, [
                'choices' => $this->choicesWithCurrent($adopt->getContingencyPlan(), [
                    'Family Support' => 'Family Support',
                    'Professional Care' => 'Professional Care',
                    'Boarding Facility' => 'Boarding Facility',
                    'Friend/Neighbor' => 'Friend/Neighbor',
                    'None Planned' => 'None Planned',
                ]),
                'required' => false,
                'label' => 'Contingency Plan',
                'placeholder' => '— Not set —',
            ])
            ->add('save', SubmitType::class, ['label' => 'Update'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Adoption request updated.');
            return $this->redirectToRoute('app_adoption');
        }
        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Could not update adoption request. Please check the highlighted fields.');
        }
        return $this->render('adoption/edit.html.twig', ['form' => $form->createView(), 'adoption' => $adopt]);
    }

    /**
     * @param array<string, string> $choices
     * @return array<string, string>
     */
    private function choicesWithCurrent(?string $current, array $choices): array
    {
        if ($current !== null && $current !== '' && !in_array($current, $choices, true)) {
            $choices['(Saved: ' . $current . ')'] = $current;
        }

        return $choices;
    }

    private function normalizeLegacyAdoptionFields(AdoptionRequest $adopt): void
    {
        $legacy = ['1' => 'Yes', '0' => 'No', 'true' => 'Yes', 'false' => 'No'];
        $home = $adopt->getHomeAgreement();
        if ($home !== null && isset($legacy[strtolower($home)])) {
            $adopt->setHomeAgreement($legacy[strtolower($home)]);
        }
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

