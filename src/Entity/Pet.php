<?php

namespace App\Entity;

use App\Repository\PetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

#[ORM\Entity(repositoryClass: PetRepository::class)]
#[ORM\Table(name: 'pet')]
#[ORM\HasLifecycleCallbacks]
class Pet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $breed = null;

    #[ORM\Column]
    private ?int $age = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Appointment>
     */
    #[ORM\OneToMany(targetEntity: Appointment::class, mappedBy: 'pet', cascade: ['remove'], orphanRemoval: true)]
    private Collection $appointments;

    /**
     * @var Collection<int, AdoptionRequest>
     */
    #[ORM\OneToMany(targetEntity: AdoptionRequest::class, mappedBy: 'pet', cascade: ['remove'], orphanRemoval: true)]
    private Collection $adoptionRequests;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
        $this->adoptionRequests = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getBreed(): ?string
    {
        return $this->breed;
    }

    public function setBreed(?string $breed): static
    {
        $this->breed = $breed;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;

        return $this;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setPet($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getPet() === $this) {
                $appointment->setPet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AdoptionRequest>
     */
    public function getAdoptionRequests(): Collection
    {
        return $this->adoptionRequests;
    }

    public function addAdoptionRequest(AdoptionRequest $adoptionRequest): static
    {
        if (!$this->adoptionRequests->contains($adoptionRequest)) {
            $this->adoptionRequests->add($adoptionRequest);
            $adoptionRequest->setPet($this);
        }

        return $this;
    }

    public function removeAdoptionRequest(AdoptionRequest $adoptionRequest): static
    {
        if ($this->adoptionRequests->removeElement($adoptionRequest)) {
            // set the owning side to null (unless already changed)
            if ($adoptionRequest->getPet() === $this) {
                $adoptionRequest->setPet(null);
            }
        }

        return $this;
    }
}
