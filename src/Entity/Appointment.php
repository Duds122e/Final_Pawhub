<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $petName = null;

    public function getPetName(): ?string { return $this->petName; }
    public function setPetName(?string $petName): self { $this->petName = $petName; return $this; }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotBlank]
    private $dateTime;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: Pet::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: false)]
    private $pet;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'appointments')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Service $service = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ownerName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emergencyContact = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $petSpecies = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $petBreed = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $petColor = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $petSex = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $petSpayNeuter = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $petWeight = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $petMicrochip = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $petDob = null;

    public function getId(): ?int { return $this->id; }
    public function getDateTime(): ?\DateTimeInterface { return $this->dateTime; }
    public function setDateTime(\DateTimeInterface $dateTime): self { $this->dateTime = $dateTime; return $this; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getPet(): ?Pet { return $this->pet; }
    public function setPet(?Pet $pet): self { $this->pet = $pet; return $this; }
    public function getService(): ?Service { return $this->service; }
    public function setService(?Service $service): self { $this->service = $service; return $this; }
    public function getLocation(): ?string { return $this->location; }
    public function setLocation(?string $location): self { $this->location = $location; return $this; }
    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): self { $this->reason = $reason; return $this; }
    public function getOwnerName(): ?string { return $this->ownerName; }
    public function setOwnerName(?string $ownerName): self { $this->ownerName = $ownerName; return $this; }
    public function getContactPhone(): ?string { return $this->contactPhone; }
    public function setContactPhone(?string $contactPhone): self { $this->contactPhone = $contactPhone; return $this; }
    public function getAddress(): ?string { return $this->address; }
    public function setAddress(?string $address): self { $this->address = $address; return $this; }
    public function getEmergencyContact(): ?string { return $this->emergencyContact; }
    public function setEmergencyContact(?string $emergencyContact): self { $this->emergencyContact = $emergencyContact; return $this; }
    public function getPetSpecies(): ?string { return $this->petSpecies; }
    public function setPetSpecies(?string $petSpecies): self { $this->petSpecies = $petSpecies; return $this; }
    public function getPetBreed(): ?string { return $this->petBreed; }
    public function setPetBreed(?string $petBreed): self { $this->petBreed = $petBreed; return $this; }
    public function getPetColor(): ?string { return $this->petColor; }
    public function setPetColor(?string $petColor): self { $this->petColor = $petColor; return $this; }
    public function getPetSex(): ?string { return $this->petSex; }
    public function setPetSex(?string $petSex): self { $this->petSex = $petSex; return $this; }
    public function getPetSpayNeuter(): ?string { return $this->petSpayNeuter; }
    public function setPetSpayNeuter(?string $petSpayNeuter): self { $this->petSpayNeuter = $petSpayNeuter; return $this; }
    public function getPetWeight(): ?string { return $this->petWeight; }
    public function setPetWeight(?string $petWeight): self { $this->petWeight = $petWeight; return $this; }
    public function getPetMicrochip(): ?string { return $this->petMicrochip; }
    public function setPetMicrochip(?string $petMicrochip): self { $this->petMicrochip = $petMicrochip; return $this; }
    public function getPetDob(): ?\DateTimeInterface { return $this->petDob; }
    public function setPetDob(?\DateTimeInterface $petDob): self { $this->petDob = $petDob; return $this; }
}
