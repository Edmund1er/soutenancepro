<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'Ce code de salle existe déjà.')]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le code de la salle est obligatoire.')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'Le code doit contenir au moins 2 caractères.')]
    private ?string $code = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La capacité est obligatoire.')]
    #[Assert\Positive(message: 'La capacité doit être supérieure à 0.')]
    private ?int $capacite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La localisation est obligatoire.')]
    private ?string $localisation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(int $capacite): static
    {
        $this->capacite = $capacite;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;
        return $this;
    }
}