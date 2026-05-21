<?php

namespace App\Entity;

use App\Repository\AdoptionRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Read;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Patch(),
        new Delete(),
    ]
)]

#[ORM\Entity(repositoryClass: AdoptionRequestRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AdoptionRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $residenceType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $householdMembers = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homeAgreement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $currentPets = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $previousPets = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $spayNeuter = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $petExperience = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dailySchedule = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $financials = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contingencyPlan = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'adoptionRequests')]
    private ?Pet $pet = null;

    #[ORM\ManyToOne(inversedBy: 'adoptionRequests')]
    private ?User $user = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPet(): ?Pet
    {
        return $this->pet;
    }

    public function setPet(?Pet $pet): static
    {
        $this->pet = $pet;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getResidenceType(): ?string
    {
        return $this->residenceType;
    }

    public function setResidenceType(?string $residenceType): static
    {
        $this->residenceType = $residenceType;

        return $this;
    }

    public function getHouseholdMembers(): ?string
    {
        return $this->householdMembers;
    }

    public function setHouseholdMembers(?string $householdMembers): static
    {
        $this->householdMembers = $householdMembers;

        return $this;
    }

    public function getHomeAgreement(): ?string
    {
        return $this->homeAgreement;
    }

    public function setHomeAgreement(?string $homeAgreement): static
    {
        $this->homeAgreement = $homeAgreement;

        return $this;
    }

    public function getCurrentPets(): ?string
    {
        return $this->currentPets;
    }

    public function setCurrentPets(?string $currentPets): static
    {
        $this->currentPets = $currentPets;

        return $this;
    }

    public function getPreviousPets(): ?string
    {
        return $this->previousPets;
    }

    public function setPreviousPets(?string $previousPets): static
    {
        $this->previousPets = $previousPets;

        return $this;
    }

    public function getSpayNeuter(): ?string
    {
        return $this->spayNeuter;
    }

    public function setSpayNeuter(?string $spayNeuter): static
    {
        $this->spayNeuter = $spayNeuter;

        return $this;
    }

    public function getPetExperience(): ?string
    {
        return $this->petExperience;
    }

    public function setPetExperience(?string $petExperience): static
    {
        $this->petExperience = $petExperience;

        return $this;
    }

    public function getDailySchedule(): ?string
    {
        return $this->dailySchedule;
    }

    public function setDailySchedule(?string $dailySchedule): static
    {
        $this->dailySchedule = $dailySchedule;

        return $this;
    }

    public function getFinancials(): ?string
    {
        return $this->financials;
    }

    public function setFinancials(?string $financials): static
    {
        $this->financials = $financials;

        return $this;
    }

    public function getContingencyPlan(): ?string
    {
        return $this->contingencyPlan;
    }

    public function setContingencyPlan(?string $contingencyPlan): static
    {
        $this->contingencyPlan = $contingencyPlan;

        return $this;
    }
}
